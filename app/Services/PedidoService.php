<?php

/**
 * Raiders Paradise - Tienda en línea de accesorios premium para motociclistas.
 *
 * Servicio con la lógica de negocio de compras, facturación y estados de pedido.
 *
 * @package   RaidersParadise
 * @author    Equipo de desarrollo Raiders Paradise (ADSO - SENA)
 * @copyright 2026 Raiders Paradise
 * @license   MIT
 * @version   2.0.0
 * @since     2026-10-29
 */

namespace App\Services;

use App\Models\Cliente;
use App\Models\Factura;
use App\Models\Inventario;
use App\Models\Pedido;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Lógica de negocio del proceso de compra.
 *
 * Convierte el carrito en pedido, descuenta inventario y genera la factura dentro
 * de una sola transacción de base de datos. También gestiona los cambios de estado
 * (incluida la cancelación, que devuelve las unidades al inventario).
 *
 * @see \App\Http\Controllers\CheckoutController
 * @see \App\Http\Controllers\Admin\PedidoController
 */
class PedidoService
{
    /**
     * Crea un pedido a partir del carrito activo del cliente.
     *
     * Pasos (en una transacción): bloquea el inventario de cada producto
     * (lockForUpdate) y verifica disponibilidad, crea el pedido y sus líneas con el
     * precio vigente, descuenta existencias, genera la factura, vacía el carrito y
     * actualiza los datos de envío del cliente.
     *
     * @param Cliente $cliente Cliente que compra.
     * @param array{direccion_envio: string, ciudad_envio: string, telefono?: string, notas?: string|null, metodo_pago: string} $datos
     *        Datos validados del formulario de checkout.
     * @return Pedido Pedido creado con sus detalles y factura cargados.
     * @throws \Illuminate\Validation\ValidationException Si el carrito está vacío, un producto está inactivo
     *         o se pide más de lo disponible.
     * @throws \Throwable Si la transacción falla (se revierte todo).
     */
    public function crearDesdeCarrito(Cliente $cliente, array $datos): Pedido
    {
        return DB::transaction(function () use ($cliente, $datos) {
            $carrito = $cliente->carritoActivo()->load('detalles.producto');

            if ($carrito->detalles->isEmpty()) {
                throw ValidationException::withMessages(['carrito' => 'Tu carrito está vacío.']);
            }

            $total = 0;
            $lineas = [];

            foreach ($carrito->detalles as $detalle) {
                $producto = $detalle->producto;
                $inventario = Inventario::where('id_producto', $producto->id_producto)->lockForUpdate()->first();
                $disponible = $inventario?->cantidad_disponible ?? 0;

                if ($producto->estado_producto !== 'activo') {
                    throw ValidationException::withMessages(['carrito' => "El producto {$producto->nombre_producto} ya no está disponible."]);
                }
                if ($detalle->cantidad > $disponible) {
                    throw ValidationException::withMessages(['carrito' => "Solo quedan {$disponible} unidades de {$producto->nombre_producto}."]);
                }

                // Se usa el precio vigente del producto, no el guardado en el carrito
                $precio = (float) $producto->precio_producto;
                $subtotal = $precio * $detalle->cantidad;
                $total += $subtotal;
                $lineas[] = [$producto, $inventario, $detalle->cantidad, $precio, $subtotal];
            }

            $pedido = Pedido::create([
                'id_cliente' => $cliente->id_cliente,
                'fecha' => now(),
                'estado' => 'pendiente',
                'total' => $total,
                'direccion_envio' => $datos['direccion_envio'],
                'ciudad_envio' => $datos['ciudad_envio'],
                'notas' => $datos['notas'] ?? null,
            ]);

            foreach ($lineas as [$producto, $inventario, $cantidad, $precio, $subtotal]) {
                $pedido->detalles()->create([
                    'id_producto' => $producto->id_producto,
                    'cantidad' => $cantidad,
                    'precio_unitario' => $precio,
                    'sub_total' => $subtotal,
                ]);
                $inventario->decrement('cantidad_disponible', $cantidad);
            }

            $this->generarFactura($pedido, $datos['metodo_pago']);

            // Se vacía el carrito y se actualizan los datos de envío del cliente
            $carrito->detalles()->delete();
            $cliente->update([
                'direccion' => $datos['direccion_envio'],
                'ciudad' => $datos['ciudad_envio'],
                'telefono' => $datos['telefono'] ?? $cliente->telefono,
            ]);

            return $pedido->fresh(['detalles.producto', 'factura']);
        });
    }

    /**
     * Genera la factura de un pedido.
     *
     * Los precios incluyen IVA, por lo que el subtotal se calcula como total / 1,19.
     * La factura queda "pagada" salvo en pago contra entrega, que queda "pendiente".
     *
     * @param Pedido $pedido Pedido a facturar (con su total calculado).
     * @param string $metodoPago Clave de {@see Factura::METODOS_PAGO}.
     * @param \DateTimeInterface|string|null $fecha Fecha de la factura; por defecto, ahora.
     * @return Factura Factura creada con número FE-000000.
     */
    public function generarFactura(Pedido $pedido, string $metodoPago, $fecha = null): Factura
    {
        $total = (float) $pedido->total;
        $subtotal = round($total / (1 + Factura::IVA), 2); // los precios incluyen IVA
        $pagada = $metodoPago !== 'contraentrega';

        return Factura::create([
            'id_pedido' => $pedido->id_pedido,
            'numero_factura' => 'FE-'.str_pad((string) $pedido->id_pedido, 6, '0', STR_PAD_LEFT),
            'fecha_factura' => $fecha ?? now(),
            'subtotal' => $subtotal,
            'impuesto' => round($total - $subtotal, 2),
            'total_factura' => $total,
            'estado_factura' => $pagada ? 'pagada' : 'pendiente',
            'pago' => $pagada ? $total : 0,
            'metodo_pago' => $metodoPago,
        ]);
    }

    /**
     * Cambia el estado de un pedido aplicando las reglas de negocio.
     *
     * - Cancelar: devuelve las unidades al inventario y anula la factura.
     * - Entregar un pedido contra entrega: marca la factura como pagada.
     * - Un pedido cancelado no puede reactivarse.
     *
     * @param Pedido $pedido Pedido con sus detalles y factura cargados.
     * @param string $estado Nuevo estado (clave de {@see Pedido::ESTADOS}).
     * @return void
     * @throws \Illuminate\Validation\ValidationException Si se intenta reactivar un pedido cancelado.
     */
    public function cambiarEstado(Pedido $pedido, string $estado): void
    {
        DB::transaction(function () use ($pedido, $estado) {
            $anterior = $pedido->estado;
            if ($anterior === $estado) {
                return;
            }
            if ($anterior === 'cancelado') {
                throw ValidationException::withMessages(['estado' => 'Un pedido cancelado no puede reactivarse.']);
            }

            if ($estado === 'cancelado') {
                foreach ($pedido->detalles as $detalle) {
                    Inventario::where('id_producto', $detalle->id_producto)->increment('cantidad_disponible', $detalle->cantidad);
                }
                $pedido->factura?->update(['estado_factura' => 'anulada']);
            }

            if ($estado === 'entregado' && $pedido->factura && $pedido->factura->estado_factura === 'pendiente') {
                // Pago contra entrega: se marca pagada al entregar
                $pedido->factura->update(['estado_factura' => 'pagada', 'pago' => $pedido->factura->total_factura]);
            }

            $pedido->update(['estado' => $estado]);
        });
    }
}

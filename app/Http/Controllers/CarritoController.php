<?php

/**
 * Raiders Paradise - Tienda en línea de accesorios premium para motociclistas.
 *
 * Controlador del carrito de compras del cliente.
 *
 * @package   RaidersParadise
 * @author    Equipo de desarrollo Raiders Paradise (ADSO - SENA)
 * @copyright 2026 Raiders Paradise
 * @license   MIT
 * @version   2.0.0
 * @since     2026-10-29
 */

namespace App\Http\Controllers;

use App\Models\DetalleCarrito;
use App\Models\Producto;
use Illuminate\Http\Request;

/**
 * Gestiona el carrito persistente del cliente autenticado.
 *
 * Todas las rutas requieren sesión y rol Cliente. Antes de modificar una línea se
 * verifica que pertenezca al carrito del usuario.
 */
class CarritoController extends Controller
{
    /**
     * Devuelve el cliente del usuario autenticado, creándolo si hace falta.
     *
     * @param \Illuminate\Http\Request $request Petición HTTP actual.
     * @return \App\Models\Cliente
     */
    private function cliente(Request $request)
    {
        return $request->user()->clienteAsociado();
    }

    /**
     * Muestra el carrito con sus productos.
     *
     * @param \Illuminate\Http\Request $request Petición HTTP actual.
     * @return \Illuminate\Contracts\View\View
     */
    public function index(Request $request)
    {
        $carrito = $this->cliente($request)->carritoActivo()->load('detalles.producto.inventario', 'detalles.producto.marca');

        return view('tienda.carrito', compact('carrito'));
    }

    /**
     * Agrega un producto al carrito o suma unidades si ya estaba.
     *
     * Rechaza productos no disponibles y cantidades mayores al stock.
     *
     * @param \Illuminate\Http\Request $request Petición HTTP actual.
     * @param Producto $producto Producto a agregar.
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Validation\ValidationException Si los datos no cumplen las reglas de validación.
     */
    public function agregar(Request $request, Producto $producto)
    {
        $datos = $request->validate(['cantidad' => ['nullable', 'integer', 'min:1', 'max:20']]);
        $cantidad = (int) ($datos['cantidad'] ?? 1);

        if (! $producto->disponible) {
            return back()->with('error', 'Este producto no está disponible en este momento.');
        }

        $carrito = $this->cliente($request)->carritoActivo();
        $detalle = $carrito->detalles()->firstOrNew(['id_producto' => $producto->id_producto]);
        $nueva = ($detalle->exists ? $detalle->cantidad : 0) + $cantidad;

        if ($nueva > $producto->stock) {
            return back()->with('error', "Solo hay {$producto->stock} unidades disponibles de {$producto->nombre_producto}.");
        }

        $detalle->fill([
            'cantidad' => $nueva,
            'precio_unitario' => $producto->precio_producto,
            'subtotal' => $nueva * $producto->precio_producto,
        ])->save();

        return back()->with('ok', "Agregaste {$producto->nombre_producto} al carrito.");
    }

    /**
     * Cambia la cantidad de una línea del carrito (entre 1 y 20, sin superar el stock).
     *
     * @param \Illuminate\Http\Request $request Petición HTTP actual.
     * @param DetalleCarrito $detalle Línea a modificar.
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Validation\ValidationException Si los datos no cumplen las reglas de validación.
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException 403 si la línea es de otro cliente.
     */
    public function actualizar(Request $request, DetalleCarrito $detalle)
    {
        $this->autorizar($request, $detalle);
        $datos = $request->validate(['cantidad' => ['required', 'integer', 'min:1', 'max:20']]);
        $stock = $detalle->producto->stock;

        if ($datos['cantidad'] > $stock) {
            return back()->with('error', "Solo hay {$stock} unidades disponibles.");
        }

        $detalle->update([
            'cantidad' => $datos['cantidad'],
            'precio_unitario' => $detalle->producto->precio_producto,
            'subtotal' => $datos['cantidad'] * $detalle->producto->precio_producto,
        ]);

        return back()->with('ok', 'Cantidad actualizada.');
    }

    /**
     * Quita una línea del carrito.
     *
     * @param \Illuminate\Http\Request $request Petición HTTP actual.
     * @param DetalleCarrito $detalle Línea a eliminar.
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException 403 si la línea es de otro cliente.
     */
    public function eliminar(Request $request, DetalleCarrito $detalle)
    {
        $this->autorizar($request, $detalle);
        $detalle->delete();

        return back()->with('ok', 'Producto retirado del carrito.');
    }

    /**
     * Aborta con 403 si la línea no pertenece al carrito del usuario autenticado.
     *
     * @param \Illuminate\Http\Request $request Petición HTTP actual.
     * @param DetalleCarrito $detalle
     * @return void
     */
    private function autorizar(Request $request, DetalleCarrito $detalle): void
    {
        abort_unless($detalle->carrito->id_cliente === $request->user()->cliente?->id_cliente, 403);
    }
}

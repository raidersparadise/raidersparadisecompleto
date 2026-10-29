<?php

/**
 * Raiders Paradise - Tienda en línea de accesorios premium para motociclistas.
 *
 * Controlador del proceso de pago (checkout).
 *
 * @package   RaidersParadise
 * @author    Equipo de desarrollo Raiders Paradise (ADSO - SENA)
 * @copyright 2026 Raiders Paradise
 * @license   MIT
 * @version   2.0.0
 * @since     2026-10-29
 */

namespace App\Http\Controllers;

use App\Models\Factura;
use App\Services\PedidoService;
use Illuminate\Http\Request;

/**
 * Confirmación de la compra: datos de entrega y medio de pago (simulado).
 */
class CheckoutController extends Controller
{
    /**
     * Muestra el formulario de checkout; si el carrito está vacío redirige al carrito.
     *
     * @param \Illuminate\Http\Request $request Petición HTTP actual.
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
     */
    public function show(Request $request)
    {
        $cliente = $request->user()->cliente;
        $carrito = $cliente?->carritoActivo()->load('detalles.producto');

        if (! $carrito || $carrito->detalles->isEmpty()) {
            return redirect()->route('carrito')->with('error', 'Tu carrito está vacío.');
        }

        $metodos = Factura::METODOS_PAGO;

        return view('tienda.checkout', compact('cliente', 'carrito', 'metodos'));
    }

    /**
     * Valida los datos de entrega y crea el pedido con su factura.
     *
     * @param \Illuminate\Http\Request $request Petición HTTP actual.
     * @param PedidoService $servicio Servicio de compras (inyectado).
     * @return \Illuminate\Http\RedirectResponse Al detalle del pedido creado.
     * @throws \Illuminate\Validation\ValidationException Si los datos no cumplen las reglas de validación.
     */
    public function store(Request $request, PedidoService $servicio)
    {
        $datos = $request->validate([
            'direccion_envio' => ['required', 'string', 'max:150'],
            'ciudad_envio' => ['required', 'string', 'max:60'],
            'telefono' => ['required', 'string', 'max:20'],
            'notas' => ['nullable', 'string', 'max:255'],
            'metodo_pago' => ['required', 'in:'.implode(',', array_keys(Factura::METODOS_PAGO))],
        ], [], ['direccion_envio' => 'dirección', 'ciudad_envio' => 'ciudad', 'metodo_pago' => 'medio de pago']);

        $pedido = $servicio->crearDesdeCarrito($request->user()->cliente, $datos);

        return redirect()->route('cuenta.pedido', $pedido)->with('ok', "¡Pedido {$pedido->codigo} confirmado! Te avisaremos cuando salga a ruta.");
    }
}

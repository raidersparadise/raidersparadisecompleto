<?php

/**
 * Raiders Paradise - Tienda en línea de accesorios premium para motociclistas.
 *
 * Controlador de gestión de pedidos del panel administrativo.
 *
 * @package   RaidersParadise
 * @author    Equipo de desarrollo Raiders Paradise (ADSO - SENA)
 * @copyright 2026 Raiders Paradise
 * @license   MIT
 * @version   2.0.0
 * @since     2026-10-29
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Services\PedidoService;
use Illuminate\Http\Request;

/**
 * Consulta de pedidos y cambio de estado.
 */
class PedidoController extends Controller
{
    /**
     * Lista paginada con filtro por estado, búsqueda por número o cliente y conteo por estado.
     *
     * @param \Illuminate\Http\Request $request Petición HTTP actual.
     * @return \Illuminate\Contracts\View\View
     */
    public function index(Request $request)
    {
        $pedidos = Pedido::with('cliente', 'factura')
            ->when($request->query('estado'), fn ($w, $e) => $w->where('estado', $e))
            ->when($request->query('q'), function ($w, $q) {
                $id = (int) preg_replace('/\D/', '', $q);
                $w->where(fn ($x) => $x->where('id_pedido', $id)
                    ->orWhereHas('cliente', fn ($c) => $c->where('nombre_cliente', 'like', "%{$q}%")->orWhere('apellido_cliente', 'like', "%{$q}%")->orWhere('email', 'like', "%{$q}%")));
            })
            ->latest('fecha')->paginate(15)->withQueryString();

        $conteo = Pedido::selectRaw('estado, COUNT(*) as n')->groupBy('estado')->pluck('n', 'estado');

        return view('admin.pedidos.index', compact('pedidos', 'conteo'));
    }

    /**
     * Detalle del pedido: productos, cliente, entrega y pago.
     *
     * @param Pedido $pedido
     * @return \Illuminate\Contracts\View\View
     */
    public function show(Pedido $pedido)
    {
        $pedido->load('cliente', 'detalles.producto', 'factura');

        return view('admin.pedidos.show', compact('pedido'));
    }

    /**
     * Cambia el estado del pedido aplicando las reglas de {@see PedidoService::cambiarEstado()}.
     *
     * @param \Illuminate\Http\Request $request Petición HTTP actual.
     * @param Pedido $pedido
     * @param PedidoService $servicio Servicio de compras (inyectado).
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Validation\ValidationException Si los datos no cumplen las reglas de validación.
     */
    public function update(Request $request, Pedido $pedido, PedidoService $servicio)
    {
        $datos = $request->validate(['estado' => ['required', 'in:'.implode(',', array_keys(Pedido::ESTADOS))]]);
        $servicio->cambiarEstado($pedido->load('detalles', 'factura'), $datos['estado']);

        return back()->with('ok', "Pedido {$pedido->codigo} actualizado a «{$pedido->fresh()->estado_texto}».");
    }
}

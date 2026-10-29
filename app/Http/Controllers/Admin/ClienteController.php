<?php

/**
 * Raiders Paradise - Tienda en línea de accesorios premium para motociclistas.
 *
 * Controlador de consulta de clientes del panel administrativo.
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
use App\Models\Cliente;
use Illuminate\Http\Request;

/**
 * Listado y ficha de clientes con su historial de compras.
 */
class ClienteController extends Controller
{
    /**
     * Lista paginada ordenada por valor comprado, con búsqueda por nombre, correo o cédula.
     *
     * @param \Illuminate\Http\Request $request Petición HTTP actual.
     * @return \Illuminate\Contracts\View\View
     */
    public function index(Request $request)
    {
        $q = $request->query('q');
        $clientes = Cliente::withCount('pedidos')
            ->withSum(['pedidos as total_comprado' => fn ($p) => $p->where('estado', '!=', 'cancelado')], 'total')
            ->when($q, fn ($w) => $w->where(fn ($x) => $x->where('nombre_cliente', 'like', "%{$q}%")->orWhere('apellido_cliente', 'like', "%{$q}%")->orWhere('email', 'like', "%{$q}%")->orWhere('documento', 'like', "%{$q}%")))
            ->orderByDesc('total_comprado')
            ->paginate(15)->withQueryString();

        return view('admin.clientes.index', compact('clientes'));
    }

    /**
     * Ficha del cliente con indicadores, pedidos y datos de contacto.
     *
     * @param Cliente $cliente
     * @return \Illuminate\Contracts\View\View
     */
    public function show(Cliente $cliente)
    {
        $cliente->load(['pedidos' => fn ($q) => $q->latest('fecha'), 'pqrs', 'usuario']);

        return view('admin.clientes.show', compact('cliente'));
    }
}

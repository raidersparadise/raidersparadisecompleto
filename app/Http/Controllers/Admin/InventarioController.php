<?php

/**
 * Raiders Paradise - Tienda en línea de accesorios premium para motociclistas.
 *
 * Controlador del módulo de inventario.
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
use App\Models\Inventario;
use Illuminate\Http\Request;

/**
 * Consulta de existencias y registro de movimientos (entrada, salida, ajuste).
 */
class InventarioController extends Controller
{
    /**
     * Lista el inventario con búsqueda y filtro por nivel (bajo | agotado) y un resumen.
     *
     * @param \Illuminate\Http\Request $request Petición HTTP actual.
     * @return \Illuminate\Contracts\View\View
     */
    public function index(Request $request)
    {
        $filtro = $request->query('nivel');
        $q = $request->query('q');

        $items = Inventario::with('producto.categoria', 'producto.marca')
            ->when($q, fn ($w) => $w->whereHas('producto', fn ($p) => $p->where('nombre_producto', 'like', "%{$q}%")->orWhere('sku', 'like', "%{$q}%")))
            ->when($filtro === 'bajo', fn ($w) => $w->whereColumn('cantidad_disponible', '<=', 'cantidad_minima')->where('cantidad_disponible', '>', 0))
            ->when($filtro === 'agotado', fn ($w) => $w->where('cantidad_disponible', 0))
            ->orderBy('cantidad_disponible')
            ->paginate(20)->withQueryString();

        $resumen = [
            'total' => Inventario::count(),
            'bajo' => Inventario::whereColumn('cantidad_disponible', '<=', 'cantidad_minima')->where('cantidad_disponible', '>', 0)->count(),
            'agotado' => Inventario::where('cantidad_disponible', 0)->count(),
        ];

        return view('admin.inventario', compact('items', 'resumen', 'filtro'));
    }

    /**
     * Registra un movimiento: entrada (+), salida (−) o ajuste a una cantidad exacta.
     * También actualiza el stock mínimo. No permite existencias negativas.
     *
     * @param \Illuminate\Http\Request $request Petición HTTP actual.
     * @param Inventario $inventario
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Validation\ValidationException Si los datos no cumplen las reglas de validación.
     */
    public function update(Request $request, Inventario $inventario)
    {
        $datos = $request->validate([
            'movimiento' => ['required', 'in:entrada,salida,ajuste'],
            'cantidad' => ['required', 'integer', 'min:0', 'max:100000'],
            'cantidad_minima' => ['required', 'integer', 'min:0', 'max:10000'],
        ]);

        $nueva = match ($datos['movimiento']) {
            'entrada' => $inventario->cantidad_disponible + $datos['cantidad'],
            'salida' => $inventario->cantidad_disponible - $datos['cantidad'],
            'ajuste' => $datos['cantidad'],
        };

        if ($nueva < 0) {
            return back()->with('error', 'La salida supera las unidades disponibles.');
        }

        $inventario->update(['cantidad_disponible' => $nueva, 'cantidad_minima' => $datos['cantidad_minima']]);

        return back()->with('ok', "Inventario de {$inventario->producto->nombre_producto} actualizado: {$nueva} unidades.");
    }
}

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
use App\Support\AlertaStock;
use App\Support\Tallas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

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

        // Al abrir el inventario se dan por leídas las alertas de stock bajo
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        $items = Inventario::with('producto.categoria', 'producto.marca', 'producto.tallas')
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
     * Registra un movimiento de inventario (entrada, salida o ajuste) en una talla.
     * El total del producto se recalcula como la suma de sus tallas.
     *
     * @param  \Illuminate\Http\Request  $request  Campos: movimiento, talla, cantidad, cantidad_minima.
     * @param  \App\Models\Inventario  $inventario
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Inventario $inventario)
    {
        $producto = $inventario->producto->load('tallas');
        $opciones = $producto->tallas->pluck('talla')->all();

        $datos = $request->validate([
            'movimiento' => ['required', 'in:entrada,salida,ajuste'],
            'talla' => [$opciones ? 'required' : 'nullable', Rule::in($opciones ?: [''])],
            'cantidad' => ['required', 'integer', 'min:0', 'max:100000'],
            'cantidad_minima' => ['required', 'integer', 'min:0', 'max:10000'],
        ]);

        // Producto sin tallas registradas: se mueve el total, como antes
        $fila = $opciones ? $producto->tallas->firstWhere('talla', $datos['talla']) : null;
        $actual = $fila ? $fila->cantidad_disponible : $inventario->cantidad_disponible;

        $nueva = match ($datos['movimiento']) {
            'entrada' => $actual + $datos['cantidad'],
            'salida' => $actual - $datos['cantidad'],
            'ajuste' => $datos['cantidad'],
        };

        if ($nueva < 0) {
            return back()->with('error', 'La salida supera las unidades disponibles'.($fila && $fila->talla !== Tallas::UNICA ? " en talla {$fila->talla}." : '.'));
        }

        DB::transaction(function () use ($fila, $nueva, $inventario, $producto, $datos) {
            // Primero el movimiento (la alerta compara con el mínimo vigente) y luego el nuevo mínimo
            if ($fila) {
                $fila->update(['cantidad_disponible' => $nueva]);
                $producto->sincronizarStock(); // incluye la alerta de stock bajo
            } else {
                $nivelAntes = $inventario->nivel;
                $inventario->update(['cantidad_disponible' => $nueva]);
                if (AlertaStock::cruzoMinimo($nivelAntes, $inventario)) {
                    DB::afterCommit(fn () => AlertaStock::enviar(collect([$inventario->fresh()])));
                }
            }
            $inventario->refresh()->update(['cantidad_minima' => $datos['cantidad_minima']]);
        });

        $total = $inventario->fresh()->cantidad_disponible;
        $detalle = $fila && $fila->talla !== Tallas::UNICA ? " (talla {$fila->talla}: {$nueva})" : '';

        return back()->with('ok', "Inventario de {$producto->nombre_producto} actualizado: {$total} unidades{$detalle}.");
    }
}

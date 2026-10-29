<?php

/**
 * Raiders Paradise - Tienda en línea de accesorios premium para motociclistas.
 *
 * Controlador del tablero de inicio del panel administrativo.
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
use App\Models\Inventario;
use App\Models\Pedido;
use App\Models\Pqr;
use App\Services\ReporteService;

/**
 * Indicadores y gráficas del panel (Administrador y Vendedor).
 */
class DashboardController extends Controller
{
    /**
     * Muestra los indicadores del mes, la comparación con el mes anterior, gráficas
     * de ventas (30 días) y categorías (90 días), últimos pedidos y alertas de stock.
     *
     * @param ReporteService $reportes Servicio de reportes (inyectado).
     * @return \Illuminate\Contracts\View\View
     */
    public function index(ReporteService $reportes)
    {
        $inicioMes = now()->startOfMonth();
        $inicioMesAnterior = now()->subMonthNoOverflow()->startOfMonth();
        $finMesAnterior = now()->subMonthNoOverflow()->endOfMonth();

        $ventasMes = Pedido::where('estado', '!=', 'cancelado')->where('fecha', '>=', $inicioMes)->sum('total');
        $ventasMesAnterior = Pedido::where('estado', '!=', 'cancelado')->whereBetween('fecha', [$inicioMesAnterior, $finMesAnterior])->sum('total');
        $pedidosMes = Pedido::where('estado', '!=', 'cancelado')->where('fecha', '>=', $inicioMes)->count();

        $kpis = [
            'ventas_mes' => (float) $ventasMes,
            'variacion' => $ventasMesAnterior > 0 ? (($ventasMes - $ventasMesAnterior) / $ventasMesAnterior) : null,
            'pedidos_mes' => $pedidosMes,
            'ticket' => $pedidosMes ? $ventasMes / $pedidosMes : 0,
            'por_despachar' => Pedido::whereIn('estado', ['pendiente', 'programado'])->count(),
            'clientes' => Cliente::count(),
            'stock_bajo' => Inventario::whereColumn('cantidad_disponible', '<=', 'cantidad_minima')->count(),
            'pqr_abiertas' => Pqr::whereNotIn('estado', ['cerrada', 'rechazada'])->count(),
        ];

        $ventas30 = $reportes->generar('ventas', now()->subDays(29), now())['grafica'];
        $categorias = $reportes->generar('categorias', now()->subDays(89), now())['grafica'];

        $ultimosPedidos = Pedido::with('cliente')->latest('fecha')->take(6)->get();
        $alertasStock = Inventario::with('producto')
            ->whereColumn('cantidad_disponible', '<=', 'cantidad_minima')
            ->orderBy('cantidad_disponible')->take(6)->get();

        return view('admin.dashboard', compact('kpis', 'ventas30', 'categorias', 'ultimosPedidos', 'alertasStock'));
    }
}

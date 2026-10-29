{{--
    Vista: admin.dashboard
    Tablero de inicio: indicadores del mes, gráficas de ventas y categorías, últimos pedidos y alertas de stock.

    Usada por:  Admin\DashboardController@index  (GET /admin)
    Variables:  $kpis, $ventas30, $categorias (datos de gráfica), $ultimosPedidos, $alertasStock
--}}
@extends('layouts.admin')
@section('titulo', 'Inicio')

@section('contenido')
<div class="admin-cabeza">
    <div>
        <h1>Hola, {{ auth()->user()->nombre_usuario }}</h1>
        <p>Así va la tienda en {{ now()->translatedFormat('F \d\e Y') }}.</p>
    </div>
    @if (auth()->user()->esAdmin())
        <a class="btn btn-brasa" href="{{ route('admin.reportes.index') }}"><x-icono n="chart" />Generar reportes</a>
    @endif
</div>

<div class="kpis">
    <div class="kpi destacado">
        <div class="etq">Ventas del mes</div>
        <div class="valor">@cop($kpis['ventas_mes'])</div>
        @if (!is_null($kpis['variacion']))
            <div class="delta">{{ $kpis['variacion'] >= 0 ? '▲' : '▼' }} {{ number_format(abs($kpis['variacion']) * 100, 1, ',', '.') }}% frente al mes anterior</div>
        @endif
    </div>
    <div class="kpi"><div class="etq">Pedidos del mes</div><div class="valor">{{ $kpis['pedidos_mes'] }}</div><div class="delta muted">Ticket promedio @cop($kpis['ticket'])</div></div>
    <a class="kpi" href="{{ route('admin.pedidos.index', ['estado' => 'pendiente']) }}" style="text-decoration:none"><div class="etq">Por despachar</div><div class="valor">{{ $kpis['por_despachar'] }}</div><div class="delta muted">Pendientes y programados</div></a>
    <a @class(['kpi', 'alerta-kpi' => $kpis['stock_bajo'] > 0]) href="{{ route('admin.inventario.index', ['nivel' => 'bajo']) }}" style="text-decoration:none"><div class="etq">Productos por reponer</div><div class="valor">{{ $kpis['stock_bajo'] }}</div><div class="delta muted">{{ $kpis['pqr_abiertas'] }} PQR abiertas · {{ $kpis['clientes'] }} clientes</div></a>
</div>

<div class="grid-2">
    <section class="panel">
        <div class="panel-cabeza"><h2>Ventas de los últimos 30 días</h2></div>
        <div class="grafica" data-grafica='@json($ventas30)'></div>
    </section>
    <section class="panel">
        <div class="panel-cabeza"><h2>Ventas por categoría</h2><span class="small muted">90 días</span></div>
        <div class="grafica" data-grafica='@json($categorias)'></div>
    </section>
</div>

<div class="grid-2">
    <section class="panel">
        <div class="panel-cabeza"><h2>Últimos pedidos</h2><a class="btn btn-fantasma btn-sm" href="{{ route('admin.pedidos.index') }}">Ver todos</a></div>
        <div class="tabla-wrap" style="border:0">
            <table class="tabla">
                <thead><tr><th>Pedido</th><th>Cliente</th><th>Fecha</th><th>Estado</th><th class="num">Total</th></tr></thead>
                <tbody>
                @foreach ($ultimosPedidos as $p)
                    <tr>
                        <td><a href="{{ route('admin.pedidos.show', $p) }}"><strong>{{ $p->codigo }}</strong></a></td>
                        <td>{{ $p->cliente->nombre_completo }}</td>
                        <td>{{ $p->fecha->format('d/m H:i') }}</td>
                        <td><span class="badge badge-{{ $p->estado }}">{{ $p->estado_texto }}</span></td>
                        <td class="num">@cop($p->total)</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </section>
    <section class="panel">
        <div class="panel-cabeza"><h2>Alertas de inventario</h2><a class="btn btn-fantasma btn-sm" href="{{ route('admin.inventario.index') }}">Inventario</a></div>
        @if ($alertasStock->isEmpty())
            <p class="muted">Todo el inventario está por encima del mínimo.</p>
        @else
            <ul class="lista-simple">
                @foreach ($alertasStock as $i)
                    <li>
                        <span>{{ $i->producto->nombre_producto }}<br><span class="small muted">Mínimo {{ $i->cantidad_minima }}</span></span>
                        <span class="badge badge-{{ $i->nivel }}">{{ $i->cantidad_disponible }} {{ $i->cantidad_disponible === 1 ? 'unidad' : 'unidades' }}</span>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>
</div>
@endsection

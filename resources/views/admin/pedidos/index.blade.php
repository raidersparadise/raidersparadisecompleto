{{--
    Vista: admin.pedidos.index
    Listado de pedidos con pestañas por estado, búsqueda y cambio rápido de estado.

    Usada por:  Admin\PedidoController@index  (GET /admin/pedidos)
    Variables:  $pedidos (paginados), $conteo (por estado)
--}}
@extends('layouts.admin')
@section('titulo', 'Pedidos')

@section('contenido')
<div class="admin-cabeza"><div><h1>Pedidos</h1><p>Actualiza el estado a medida que preparas y despachas cada envío.</p></div></div>
<div class="tabs">
    <a href="{{ route('admin.pedidos.index', request()->except('estado', 'page')) }}" @class(['activo' => !request('estado')])>Todos <small>{{ $conteo->sum() }}</small></a>
    @foreach (\App\Models\Pedido::ESTADOS as $k => $txt)
        <a href="{{ route('admin.pedidos.index', array_merge(request()->except('page'), ['estado' => $k])) }}" @class(['activo' => request('estado') === $k])>{{ $txt }} <small>{{ $conteo[$k] ?? 0 }}</small></a>
    @endforeach
</div>
<form class="filtros-admin" method="GET">
    @if (request('estado'))<input type="hidden" name="estado" value="{{ request('estado') }}">@endif
    <input class="input" type="search" name="q" value="{{ request('q') }}" placeholder="N.º de pedido, cliente o correo" aria-label="Buscar pedido">
    <button class="btn btn-sm" type="submit">Buscar</button>
</form>
<div class="tabla-wrap">
    <table class="tabla">
        <thead><tr><th>Pedido</th><th>Fecha</th><th>Cliente</th><th>Ciudad</th><th>Pago</th><th class="num">Total</th><th>Estado</th></tr></thead>
        <tbody>
        @forelse ($pedidos as $p)
            <tr>
                <td><a href="{{ route('admin.pedidos.show', $p) }}"><strong>{{ $p->codigo }}</strong></a></td>
                <td>{{ $p->fecha->format('d/m/Y H:i') }}</td>
                <td>{{ $p->cliente->nombre_completo }}</td>
                <td>{{ $p->ciudad_envio }}</td>
                <td>@if($p->factura)<span class="badge badge-{{ $p->factura->estado_factura }}">{{ $p->factura->metodo_pago_texto }}</span>@endif</td>
                <td class="num">@cop($p->total)</td>
                <td>
                    @if ($p->estado === 'cancelado')
                        <span class="badge badge-cancelado">Cancelado</span>
                    @else
                        <form method="POST" action="{{ route('admin.pedidos.update', $p) }}">
                            @csrf @method('PUT')
                            <select class="select" name="estado" data-autoenviar aria-label="Estado de {{ $p->codigo }}" style="min-height:36px;padding:4px 8px;width:auto">
                                @foreach (\App\Models\Pedido::ESTADOS as $k => $txt)<option value="{{ $k }}" @selected($p->estado === $k)>{{ $txt }}</option>@endforeach
                            </select>
                        </form>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="7" class="muted" style="text-align:center;padding:32px">No hay pedidos con este filtro.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
{{ $pedidos->links() }}
@endsection

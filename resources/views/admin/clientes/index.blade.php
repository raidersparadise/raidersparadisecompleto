{{--
    Vista: admin.clientes.index
    Listado de clientes con pedidos y total comprado.

    Usada por:  Admin\ClienteController@index  (GET /admin/clientes)
    Variables:  $clientes (paginados, con pedidos_count y total_comprado)
--}}
@extends('layouts.admin')
@section('titulo', 'Clientes')

@section('contenido')
<div class="admin-cabeza"><div><h1>Clientes</h1><p>{{ $clientes->total() }} clientes registrados, ordenados por valor de compra.</p></div></div>
<form class="filtros-admin" method="GET">
    <input class="input" type="search" name="q" value="{{ request('q') }}" placeholder="Nombre, correo o cédula" aria-label="Buscar cliente">
    <button class="btn btn-sm" type="submit">Buscar</button>
</form>
<div class="tabla-wrap">
    <table class="tabla">
        <thead><tr><th>Cliente</th><th>Contacto</th><th>Ciudad</th><th>Cuenta web</th><th class="num">Pedidos</th><th class="num">Total comprado</th><th></th></tr></thead>
        <tbody>
        @forelse ($clientes as $c)
            <tr>
                <td><strong>{{ $c->nombre_completo }}</strong>@if($c->documento)<br><span class="small muted">C.C. {{ $c->documento }}</span>@endif</td>
                <td>{{ $c->email }}<br><span class="small muted">{{ $c->telefono }}</span></td>
                <td>{{ $c->ciudad ?? '—' }}</td>
                <td>@if($c->id_usuario)<span class="badge badge-ok">Sí</span>@else<span class="badge">Sin cuenta</span>@endif</td>
                <td class="num">{{ $c->pedidos_count }}</td>
                <td class="num">@cop($c->total_comprado ?? 0)</td>
                <td class="right"><a class="btn btn-fantasma btn-sm" href="{{ route('admin.clientes.show', $c) }}">Ver</a></td>
            </tr>
        @empty
            <tr><td colspan="7" class="muted" style="text-align:center;padding:32px">No encontramos clientes con esa búsqueda.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
{{ $clientes->links() }}
@endsection

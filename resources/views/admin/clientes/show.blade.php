{{--
    Vista: admin.clientes.show
    Ficha del cliente con indicadores, historial de pedidos y contacto.

    Usada por:  Admin\ClienteController@show  (GET /admin/clientes/{cliente})
    Variables:  $cliente (con pedidos, pqrs, usuario)
--}}
@extends('layouts.admin')
@section('titulo', $cliente->nombre_completo)

@section('contenido')
@php($validos = $cliente->pedidos->where('estado', '!=', 'cancelado'))
<div class="admin-cabeza">
    <div><h1>{{ $cliente->nombre_completo }}</h1><p>Cliente desde {{ $cliente->created_at?->translatedFormat('F \d\e Y') }}</p></div>
    <a class="btn btn-fantasma" href="{{ route('admin.clientes.index') }}"><x-icono n="atras" />Volver</a>
</div>
<div class="kpis">
    <div class="kpi"><div class="etq">Pedidos</div><div class="valor">{{ $cliente->pedidos->count() }}</div></div>
    <div class="kpi"><div class="etq">Total comprado</div><div class="valor">@cop($validos->sum('total'))</div></div>
    <div class="kpi"><div class="etq">Ticket promedio</div><div class="valor">@cop($validos->count() ? $validos->avg('total') : 0)</div></div>
    <div class="kpi"><div class="etq">PQR radicadas</div><div class="valor">{{ $cliente->pqrs->count() }}</div></div>
</div>
<div class="grid-2">
    <section class="panel">
        <div class="panel-cabeza"><h2>Historial de pedidos</h2></div>
        <div class="tabla-wrap" style="border:0">
            <table class="tabla">
                <thead><tr><th>Pedido</th><th>Fecha</th><th>Estado</th><th class="num">Total</th></tr></thead>
                <tbody>
                @forelse ($cliente->pedidos as $p)
                    <tr><td><a href="{{ route('admin.pedidos.show', $p) }}">{{ $p->codigo }}</a></td><td>{{ $p->fecha->format('d/m/Y') }}</td><td><span class="badge badge-{{ $p->estado }}">{{ $p->estado_texto }}</span></td><td class="num">@cop($p->total)</td></tr>
                @empty
                    <tr><td colspan="4" class="muted">Sin pedidos.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </section>
    <section class="panel">
        <div class="panel-cabeza"><h2>Datos de contacto</h2></div>
        <dl class="ficha" style="margin:0;border:0;padding:0">
            <dt>Correo</dt><dd>{{ $cliente->email }}</dd>
            <dt>Celular</dt><dd>{{ $cliente->telefono ?? '—' }}</dd>
            <dt>Cédula</dt><dd>{{ $cliente->documento ?? '—' }}</dd>
            <dt>Dirección</dt><dd>{{ $cliente->direccion ?? '—' }}</dd>
            <dt>Ciudad</dt><dd>{{ $cliente->ciudad ?? '—' }}</dd>
            <dt>Cuenta web</dt><dd>{{ $cliente->usuario ? 'Activa' : 'No tiene' }}</dd>
        </dl>
    </section>
</div>
@endsection

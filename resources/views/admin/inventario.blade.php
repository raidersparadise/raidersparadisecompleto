{{--
    Vista: admin.inventario
    Inventario con pestañas por nivel y formulario en línea para entradas, salidas y ajustes.

    Usada por:  Admin\InventarioController@index  (GET /admin/inventario)
    Variables:  $items (paginados), $resumen, $filtro
--}}
@extends('layouts.admin')
@section('titulo', 'Inventario')

@section('contenido')
<div class="admin-cabeza">
    <div><h1>Inventario</h1><p>Registra entradas de mercancía, salidas o ajustes por conteo físico.</p></div>
</div>
<div class="tabs">
    <a href="{{ route('admin.inventario.index') }}" @class(['activo' => !$filtro])>Todos <small>{{ $resumen['total'] }}</small></a>
    <a href="{{ route('admin.inventario.index', ['nivel' => 'bajo']) }}" @class(['activo' => $filtro === 'bajo'])>Stock bajo <small>{{ $resumen['bajo'] }}</small></a>
    <a href="{{ route('admin.inventario.index', ['nivel' => 'agotado']) }}" @class(['activo' => $filtro === 'agotado'])>Agotados <small>{{ $resumen['agotado'] }}</small></a>
</div>
<form class="filtros-admin" method="GET">
    @if ($filtro)<input type="hidden" name="nivel" value="{{ $filtro }}">@endif
    <input class="input" type="search" name="q" value="{{ request('q') }}" placeholder="Buscar producto o SKU" aria-label="Buscar">
    <button class="btn btn-sm" type="submit">Buscar</button>
</form>
<div class="tabla-wrap">
    <table class="tabla">
        <thead><tr><th>Producto</th><th>Categoría</th><th class="num">Disponible</th><th class="num">Mínimo</th><th>Estado</th><th>Registrar movimiento</th></tr></thead>
        <tbody>
        @forelse ($items as $i)
            <tr>
                <td><div class="prod"><img src="{{ $i->producto->imagen_url }}" alt=""><span><strong>{{ $i->producto->nombre_producto }}</strong><br><span class="small muted">{{ $i->producto->sku }}</span></span></div></td>
                <td>{{ $i->producto->categoria->nombre_categoria }}</td>
                <td class="num"><strong>{{ $i->cantidad_disponible }}</strong></td>
                <td class="num">{{ $i->cantidad_minima }}</td>
                <td><span class="badge badge-{{ $i->nivel === 'ok' ? 'normal' : $i->nivel }}">{{ ['ok' => 'Normal', 'bajo' => 'Stock bajo', 'agotado' => 'Agotado'][$i->nivel] }}</span></td>
                <td>
                    <form class="inline-form" method="POST" action="{{ route('admin.inventario.update', $i) }}">
                        @csrf @method('PUT')
                        <select class="select" name="movimiento" aria-label="Tipo de movimiento">
                            <option value="entrada">Entrada +</option>
                            <option value="salida">Salida −</option>
                            <option value="ajuste">Ajustar a</option>
                        </select>
                        <input class="input" type="number" name="cantidad" min="0" value="1" style="width:72px" aria-label="Cantidad">
                        <input class="input" type="number" name="cantidad_minima" min="0" value="{{ $i->cantidad_minima }}" style="width:64px" aria-label="Stock mínimo" title="Stock mínimo">
                        <button class="btn btn-sm" type="submit">Aplicar</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="muted" style="text-align:center;padding:32px">No hay productos en esta vista.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
{{ $items->links() }}
@endsection

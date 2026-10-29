{{--
    Vista: admin.productos.index
    Listado de productos con búsqueda, filtros y acciones de editar y eliminar.

    Usada por:  Admin\ProductoController@index  (GET /admin/productos)
    Variables:  $productos (paginados), $categorias
--}}
@extends('layouts.admin')
@section('titulo', 'Productos')

@section('contenido')
<div class="admin-cabeza">
    <div><h1>Productos</h1><p>{{ $productos->total() }} referencias en el catálogo.</p></div>
    <a class="btn btn-brasa" href="{{ route('admin.productos.create') }}"><x-icono n="mas" />Nuevo producto</a>
</div>
<form class="filtros-admin" method="GET">
    <label class="sr-only" for="q">Buscar</label>
    <input id="q" class="input" type="search" name="q" value="{{ request('q') }}" placeholder="Nombre o SKU">
    <select class="select" name="categoria" data-autoenviar aria-label="Categoría">
        <option value="">Todas las categorías</option>
        @foreach ($categorias as $c)<option value="{{ $c->id_categoria }}" @selected(request('categoria') == $c->id_categoria)>{{ $c->nombre_categoria }}</option>@endforeach
    </select>
    <select class="select" name="estado" data-autoenviar aria-label="Estado">
        <option value="">Activos e inactivos</option>
        <option value="activo" @selected(request('estado') === 'activo')>Solo activos</option>
        <option value="inactivo" @selected(request('estado') === 'inactivo')>Solo inactivos</option>
    </select>
    <button class="btn btn-sm" type="submit">Buscar</button>
</form>
<div class="tabla-wrap">
    <table class="tabla">
        <thead><tr><th>Producto</th><th>SKU</th><th>Categoría</th><th class="num">Precio</th><th class="num">Stock</th><th>Estado</th><th></th></tr></thead>
        <tbody>
        @forelse ($productos as $p)
            <tr>
                <td><div class="prod"><img src="{{ $p->imagen_url }}" alt=""><span><strong>{{ $p->nombre_producto }}</strong><br><span class="small muted">{{ $p->marca->nombre_marca }}</span></span></div></td>
                <td class="small">{{ $p->sku }}</td>
                <td>{{ $p->categoria->nombre_categoria }}</td>
                <td class="num">@cop($p->precio_producto)</td>
                <td class="num"><span class="badge badge-{{ $p->inventario?->nivel ?? 'agotado' }}">{{ $p->stock }}</span></td>
                <td><span class="badge badge-{{ $p->estado_producto }}">{{ ucfirst($p->estado_producto) }}</span></td>
                <td>
                    <div class="acciones">
                        <a class="btn btn-fantasma btn-sm" href="{{ route('admin.productos.edit', $p) }}"><x-icono n="editar" size="16" />Editar</a>
                        <form method="POST" action="{{ route('admin.productos.destroy', $p) }}" data-confirmar="¿Eliminar {{ $p->nombre_producto }}? Si tiene ventas se marcará como inactivo.">
                            @csrf @method('DELETE')
                            <button class="btn btn-peligro btn-sm" type="submit" aria-label="Eliminar {{ $p->nombre_producto }}"><x-icono n="borrar" size="16" /></button>
                        </form>
                    </div>
                </td>
            </tr>
        @empty
            <tr><td colspan="7" class="muted" style="text-align:center;padding:32px">No hay productos que coincidan con la búsqueda.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
{{ $productos->links() }}
@endsection

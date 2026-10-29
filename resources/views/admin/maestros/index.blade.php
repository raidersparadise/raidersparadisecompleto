{{--
    Vista: admin.maestros.index
    Listado genérico de categorías, marcas o proveedores.

    Usada por:  Admin\MaestroController@index  (GET /admin/maestros/{tipo})
    Variables:  $cfg (configuración del tipo), $registros
--}}
@extends('layouts.admin')
@section('titulo', $cfg['plural'])

@section('contenido')
<div class="admin-cabeza">
    <div><h1>{{ $cfg['plural'] }}</h1><p>{{ $registros->count() }} registros.</p></div>
    <a class="btn btn-brasa" href="{{ route('admin.maestros.create', $cfg['tipo']) }}"><x-icono n="mas" />Agregar {{ $cfg['singular'] }}</a>
</div>
<div class="tabla-wrap">
    <table class="tabla">
        <thead><tr>
            @foreach ($cfg['campos'] as $campo => [$label]) <th>{{ $label }}</th> @endforeach
            <th class="num">Productos</th><th></th>
        </tr></thead>
        <tbody>
        @forelse ($registros as $r)
            <tr>
                @foreach ($cfg['campos'] as $campo => $def)
                    <td @if($loop->first) style="font-weight:600;color:var(--tinta)" @endif>{{ $r->$campo ?: '—' }}</td>
                @endforeach
                <td class="num">{{ $r->productos_count }}</td>
                <td>
                    <div class="acciones">
                        <a class="btn btn-fantasma btn-sm" href="{{ route('admin.maestros.edit', [$cfg['tipo'], $r->getKey()]) }}"><x-icono n="editar" size="16" />Editar</a>
                        <form method="POST" action="{{ route('admin.maestros.destroy', [$cfg['tipo'], $r->getKey()]) }}" data-confirmar="¿Eliminar este registro?">
                            @csrf @method('DELETE')
                            <button class="btn btn-peligro btn-sm" type="submit" @disabled($r->productos_count > 0) title="{{ $r->productos_count ? 'Tiene productos asociados' : 'Eliminar' }}"><x-icono n="borrar" size="16" /><span class="sr-only">Eliminar</span></button>
                        </form>
                    </div>
                </td>
            </tr>
        @empty
            <tr><td colspan="{{ count($cfg['campos']) + 2 }}" class="muted" style="text-align:center;padding:32px">Aún no hay registros. Agrega el primero.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection

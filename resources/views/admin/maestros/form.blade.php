{{--
    Vista: admin.maestros.form
    Formulario genérico para crear o editar categorías, marcas o proveedores; los campos salen de $cfg.

    Usada por:  Admin\MaestroController@create / @edit
    Variables:  $cfg, $registro
--}}
@extends('layouts.admin')
@section('titulo', ($registro->exists ? 'Editar ' : 'Agregar ').$cfg['singular'])

@section('contenido')
<div class="admin-cabeza">
    <div><h1>{{ $registro->exists ? 'Editar '.$cfg['singular'] : 'Agregar '.$cfg['singular'] }}</h1></div>
    <a class="btn btn-fantasma" href="{{ route('admin.maestros.index', $cfg['tipo']) }}"><x-icono n="atras" />Volver</a>
</div>
<form class="form-admin panel" method="POST" action="{{ $registro->exists ? route('admin.maestros.update', [$cfg['tipo'], $registro->getKey()]) : route('admin.maestros.store', $cfg['tipo']) }}">
    @csrf
    @if ($registro->exists) @method('PUT') @endif
    <div class="form-grid">
        @foreach ($cfg['campos'] as $campo => [$label, $tipo, $reglas])
            <div @class(['campo', 'completo' => $tipo === 'textarea'])>
                <label for="{{ $campo }}">{{ $label }}{{ in_array('required', $reglas) ? ' *' : '' }}</label>
                @if ($tipo === 'textarea')
                    <textarea id="{{ $campo }}" class="textarea @error($campo) invalido @enderror" name="{{ $campo }}">{{ old($campo, $registro->$campo) }}</textarea>
                @else
                    <input id="{{ $campo }}" class="input @error($campo) invalido @enderror" type="{{ $tipo }}" name="{{ $campo }}" value="{{ old($campo, $registro->$campo) }}" @required(in_array('required', $reglas))>
                @endif
                @error($campo)<span class="error">{{ $message }}</span>@enderror
            </div>
        @endforeach
    </div>
    <div class="flex"><button class="btn btn-brasa" type="submit">Guardar</button><a class="btn btn-fantasma" href="{{ route('admin.maestros.index', $cfg['tipo']) }}">Cancelar</a></div>
</form>
@endsection

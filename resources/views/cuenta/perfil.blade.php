{{--
    Vista: cuenta.perfil
    Formulario de datos personales, de entrega y cambio de contraseña.

    Usada por:  CuentaController@perfil  (GET /mi-cuenta)
    Variables:  $usuario (con cliente)
--}}
@extends('layouts.tienda')
@section('titulo', 'Mis datos')

@section('contenido')
<div class="cabecera-pagina"><div class="contenedor"><h1>Mis datos</h1><p>Mantén actualizada tu información de contacto y entrega.</p></div></div>
<div class="contenedor cuenta">
    @include('cuenta._menu')
    <form class="caja" method="POST" action="{{ route('cuenta.actualizar') }}">
        @csrf @method('PUT')
        @php($c = $usuario->cliente)
        <div class="form-grid">
            <div class="campo"><label for="nombre">Nombre</label><input id="nombre" class="input" name="nombre" value="{{ old('nombre', $usuario->nombre_usuario) }}" required></div>
            <div class="campo"><label for="apellido">Apellido</label><input id="apellido" class="input" name="apellido" value="{{ old('apellido', $usuario->apellido_usuario) }}" required></div>
            <div class="campo"><label for="email">Correo</label><input id="email" class="input" type="email" name="email" value="{{ old('email', $usuario->email) }}" required></div>
            <div class="campo"><label for="telefono">Celular</label><input id="telefono" class="input" name="telefono" value="{{ old('telefono', $usuario->telefono) }}"></div>
            @if ($c)
                <div class="campo"><label for="documento">Cédula</label><input id="documento" class="input" name="documento" value="{{ old('documento', $c->documento) }}"></div>
                <div class="campo"><label for="ciudad">Ciudad</label><input id="ciudad" class="input" name="ciudad" value="{{ old('ciudad', $c->ciudad) }}"></div>
                <div class="campo completo"><label for="direccion">Dirección de entrega</label><input id="direccion" class="input" name="direccion" value="{{ old('direccion', $c->direccion) }}"></div>
            @endif
        </div>
        <h3 style="margin-top:12px">Cambiar contraseña</h3>
        <p class="small muted">Déjala en blanco si no quieres cambiarla.</p>
        <div class="form-grid">
            <div class="campo"><label for="password">Nueva contraseña</label><input id="password" class="input" type="password" name="password" autocomplete="new-password"></div>
            <div class="campo"><label for="password_confirmation">Repítela</label><input id="password_confirmation" class="input" type="password" name="password_confirmation" autocomplete="new-password"></div>
        </div>
        <button class="btn" type="submit">Guardar cambios</button>
    </form>
</div>
@endsection

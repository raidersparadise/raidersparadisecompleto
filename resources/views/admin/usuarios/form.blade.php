{{--
    Vista: admin.usuarios.form
    Formulario de creación y edición de usuarios con rol y estado.

    Usada por:  Admin\UsuarioController@create / @edit
    Variables:  $usuario, $roles
--}}
@extends('layouts.admin')
@section('titulo', $usuario->exists ? 'Editar usuario' : 'Nuevo usuario')

@section('contenido')
<div class="admin-cabeza">
    <div><h1>{{ $usuario->exists ? $usuario->nombre_completo : 'Nuevo usuario' }}</h1></div>
    <a class="btn btn-fantasma" href="{{ route('admin.usuarios.index') }}"><x-icono n="atras" />Volver</a>
</div>
<form class="form-admin panel" method="POST" action="{{ $usuario->exists ? route('admin.usuarios.update', $usuario) : route('admin.usuarios.store') }}">
    @csrf
    @if ($usuario->exists) @method('PUT') @endif
    <div class="form-grid">
        <div class="campo"><label for="nombre_usuario">Nombre *</label><input id="nombre_usuario" class="input" name="nombre_usuario" value="{{ old('nombre_usuario', $usuario->nombre_usuario) }}" required></div>
        <div class="campo"><label for="apellido_usuario">Apellido *</label><input id="apellido_usuario" class="input" name="apellido_usuario" value="{{ old('apellido_usuario', $usuario->apellido_usuario) }}" required></div>
        <div class="campo"><label for="email">Correo *</label><input id="email" class="input" type="email" name="email" value="{{ old('email', $usuario->email) }}" required></div>
        <div class="campo"><label for="telefono">Celular</label><input id="telefono" class="input" name="telefono" value="{{ old('telefono', $usuario->telefono) }}"></div>
        <div class="campo"><label for="id_rol">Rol *</label>
            <select id="id_rol" class="select" name="id_rol" required>
                @foreach ($roles as $r)<option value="{{ $r->id_rol }}" @selected(old('id_rol', $usuario->id_rol) == $r->id_rol)>{{ $r->nombre_rol }}</option>@endforeach
            </select>
            <span class="ayuda">Administrador: todo. Vendedor: sin reportes ni usuarios. Cliente: solo tienda.</span></div>
        <div class="campo" style="align-self:center"><label class="check"><input type="checkbox" name="activo" value="1" @checked(old('activo', $usuario->activo))> Cuenta activa (puede iniciar sesión)</label></div>
        <div class="campo"><label for="password">{{ $usuario->exists ? 'Nueva contraseña' : 'Contraseña *' }}</label><input id="password" class="input" type="password" name="password" autocomplete="new-password" @required(!$usuario->exists)>
            @if ($usuario->exists)<span class="ayuda">Déjala vacía para conservar la actual.</span>@endif</div>
        <div class="campo"><label for="password_confirmation">Confirmar contraseña</label><input id="password_confirmation" class="input" type="password" name="password_confirmation" autocomplete="new-password"></div>
    </div>
    <div class="flex"><button class="btn btn-brasa" type="submit">Guardar</button><a class="btn btn-fantasma" href="{{ route('admin.usuarios.index') }}">Cancelar</a></div>
</form>
@endsection

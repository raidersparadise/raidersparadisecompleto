{{--
    Vista: auth.registro
    Formulario de registro de clientes con autorización de tratamiento de datos.

    Usada por:  AuthController@showRegistro  (GET /registro)
    Variables:  —
--}}
@extends('layouts.tienda')
@section('titulo', 'Crear cuenta')
@section('sin_alertas', true)

@section('contenido')
<div class="auth">
    <div class="auth-lado">
        <img src="{{ asset('images/logo-blanco.png') }}" alt="">
        <h2>Únete a los Raiders</h2>
        <p>Con tu cuenta compras más rápido, sigues tus envíos y guardas tus facturas en un solo lugar.</p>
    </div>
    <div class="auth-form">
        <div class="caja">
            <h1 style="font-size:2rem">Crear cuenta</h1>
            <p class="muted">¿Ya tienes cuenta? <a href="{{ route('login') }}">Ingresa aquí</a>.</p>
            @include('partials.alertas')
            <form method="POST" action="{{ route('registro') }}">
                @csrf
                <div class="form-grid">
                    <div class="campo"><label for="nombre">Nombre</label><input id="nombre" class="input @error('nombre') invalido @enderror" name="nombre" value="{{ old('nombre') }}" autocomplete="given-name" required></div>
                    <div class="campo"><label for="apellido">Apellido</label><input id="apellido" class="input @error('apellido') invalido @enderror" name="apellido" value="{{ old('apellido') }}" autocomplete="family-name" required></div>
                </div>
                <div class="campo"><label for="email">Correo</label><input id="email" class="input @error('email') invalido @enderror" type="email" name="email" value="{{ old('email') }}" autocomplete="email" required></div>
                <div class="campo"><label for="telefono">Celular <span class="muted">(opcional)</span></label><input id="telefono" class="input" name="telefono" value="{{ old('telefono') }}" inputmode="tel" autocomplete="tel"></div>
                <div class="form-grid">
                    <div class="campo"><label for="password">Contraseña</label><input id="password" class="input @error('password') invalido @enderror" type="password" name="password" autocomplete="new-password" required></div>
                    <div class="campo"><label for="password_confirmation">Repítela</label><input id="password_confirmation" class="input" type="password" name="password_confirmation" autocomplete="new-password" required></div>
                </div>
                <p class="ayuda small muted" style="margin-top:-8px">Mínimo 8 caracteres, con letras y números.</p>
                <label class="check" style="margin-bottom:18px"><input type="checkbox" name="terminos" value="1" @checked(old('terminos'))> Acepto el tratamiento de mis datos personales según la Ley 1581 de 2012.</label>
                <button class="btn btn-brasa btn-bloque" type="submit">Crear mi cuenta</button>
            </form>
        </div>
    </div>
</div>
@endsection

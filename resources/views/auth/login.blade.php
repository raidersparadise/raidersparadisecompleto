{{--
    Vista: auth.login
    Formulario de ingreso de clientes y personal.

    Usada por:  AuthController@showLogin  (GET /login)
    Variables:  —
--}}
@extends('layouts.tienda')
@section('titulo', 'Ingresar')
@section('sin_alertas', true)

@section('contenido')
<div class="auth">
    <div class="auth-tarjeta">
    <div class="auth-lado">
        <img class="auth-logo" src="{{ asset('images/logo-blanco.png') }}" alt="Raiders Paradise">
        <div>
            <h2>Bienvenido de nuevo <span>a la ruta</span></h2>
            <p>Ingresa para seguir tus pedidos, descargar tus facturas y comprar más rápido.</p>
            <ul class="auth-beneficios">
                <li><x-icono n="camion" /> Sigue tus pedidos en tiempo real</li>
                <li><x-icono n="doc" /> Descarga tus facturas cuando quieras</li>
                <li><x-icono n="escudo" /> Productos 100% originales con garantía</li>
            </ul>
        </div>
    </div>
    <div class="auth-form">
        <div class="caja">
            <h1>Ingresar</h1>
            <p class="muted">¿Aún no tienes cuenta? <a href="{{ route('registro') }}">Créala en un minuto</a>.</p>
            @include('partials.alertas')
            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="campo">
                    <label for="email">Correo</label>
                    <input id="email" class="input" type="email" name="email" value="{{ old('email') }}" autocomplete="email" required autofocus>
                </div>
                <div class="campo">
                    <label for="password">Contraseña</label>
                    <input id="password" class="input" type="password" name="password" autocomplete="current-password" required>
                </div>
                <label class="check" style="margin-bottom:18px"><input type="checkbox" name="recordar" value="1"> Mantener la sesión iniciada</label>
                <button class="btn btn-brasa btn-bloque" type="submit">Ingresar</button>
            </form>
        </div>
    </div>
    </div>
</div>
@endsection

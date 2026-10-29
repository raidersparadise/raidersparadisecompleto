{{--
    Vista: auth.login
    Formulario de ingreso. En entorno local muestra las cuentas de prueba.

    Usada por:  AuthController@showLogin  (GET /login)
    Variables:  —
--}}
@extends('layouts.tienda')
@section('titulo', 'Ingresar')
@section('sin_alertas', true)

@section('contenido')
<div class="auth">
    <div class="auth-lado">
        <img src="{{ asset('images/logo-blanco.png') }}" alt="">
        <h2>Bienvenido de nuevo a la ruta</h2>
        <p>Ingresa para seguir tus pedidos, descargar tus facturas y comprar más rápido.</p>
    </div>
    <div class="auth-form">
        <div class="caja">
            <h1 style="font-size:2rem">Ingresar</h1>
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

            @if (app()->environment('local', 'testing'))
                <details class="cuentas-demo">
                    <summary>Cuentas de prueba</summary>
                    <p class="muted" style="margin:6px 0 0">Contraseña de todas: <code>Raiders2026*</code>. Toca un correo para llenarlo.</p>
                    <table>
                        @foreach ([['admin@raidersparadise.co', 'Administrador'], ['ventas@raidersparadise.co', 'Vendedor'], ['juan.perez@correo.co', 'Cliente'], ['maria.torres@correo.co', 'Cliente'], ['santiago.lopez@correo.co', 'Cliente']] as [$correo, $rol])
                            <tr><td><button type="button" data-demo="{{ $correo }}" data-pass="Raiders2026*">{{ $correo }}</button></td><td class="muted">{{ $rol }}</td></tr>
                        @endforeach
                    </table>
                </details>
            @endif
        </div>
    </div>
</div>
@endsection

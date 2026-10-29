{{--
    Vista: tienda.pqr
    Formulario para radicar PQR (con datos personales si el visitante no tiene sesión).

    Usada por:  PqrController@create  (GET /pqr)
    Variables:  $usuario (o null), $tipos (Pqr::TIPOS)
--}}
@extends('layouts.tienda')
@section('titulo', 'Peticiones, quejas y reclamos')
@section('sin_alertas', true)

@section('contenido')
<div class="cabecera-pagina"><div class="contenedor"><h1>Atención al cliente</h1><p>Radica una petición, queja, reclamo o sugerencia. Te respondemos en máximo 15 días hábiles.</p></div></div>
<div class="contenedor layout-compra">
    <div class="caja">
        @include('partials.alertas')
        <form method="POST" action="{{ route('pqr.store') }}">
            @csrf
            @guest
                <div class="form-grid">
                    <div class="campo"><label for="nombre">Nombre</label><input id="nombre" class="input" name="nombre" value="{{ old('nombre') }}" required></div>
                    <div class="campo"><label for="apellido">Apellido</label><input id="apellido" class="input" name="apellido" value="{{ old('apellido') }}" required></div>
                    <div class="campo"><label for="email">Correo</label><input id="email" class="input" type="email" name="email" value="{{ old('email') }}" required></div>
                    <div class="campo"><label for="telefono">Celular <span class="muted">(opcional)</span></label><input id="telefono" class="input" name="telefono" value="{{ old('telefono') }}"></div>
                </div>
            @else
                <p class="alerta alerta-info">Radicarás como <strong>&nbsp;{{ auth()->user()->nombre_completo }}&nbsp;</strong> ({{ auth()->user()->email }}).</p>
            @endguest
            <div class="campo">
                <span class="etiqueta">Tipo de solicitud</span>
                <div class="opciones-pago" style="grid-template-columns:repeat(4,minmax(0,1fr))">
                    @foreach ($tipos as $k => $txt)
                        <label class="opcion"><input type="radio" name="tipo" value="{{ $k }}" @checked(old('tipo', 'peticion') === $k)><span>{{ $txt }}</span></label>
                    @endforeach
                </div>
            </div>
            <div class="campo"><label for="asunto">Asunto</label><input id="asunto" class="input" name="asunto" maxlength="120" value="{{ old('asunto') }}" placeholder="Ej: Cambio de talla de mi casco" required></div>
            <div class="campo">
                <label for="descripcion">Cuéntanos qué pasó</label>
                <textarea id="descripcion" class="textarea" name="descripcion" maxlength="1000" required placeholder="Incluye el número de pedido si aplica (RP-00012)">{{ old('descripcion') }}</textarea>
            </div>
            @guest
                <label class="check" style="margin-bottom:16px"><input type="checkbox" name="terminos" value="1" @checked(old('terminos'))> Autorizo el tratamiento de mis datos personales para atender esta solicitud (Ley 1581 de 2012).</label>
            @endguest
            <button class="btn btn-brasa" type="submit">Radicar solicitud</button>
        </form>
    </div>
    <aside class="caja">
        <h3>Antes de escribir</h3>
        <ul class="beneficios" style="margin-top:0">
            <li><x-icono n="truck" />Sigue tus pedidos en «Mis pedidos».</li>
            <li><x-icono n="retorno" />Los cambios de talla se aceptan hasta 30 días con etiquetas.</li>
            <li><x-icono n="escudo" />La garantía de fábrica cubre defectos, no golpes ni caídas.</li>
        </ul>
        @auth
            @unless (auth()->user()->esPersonal())
                <a class="btn btn-linea btn-bloque" style="margin-top:18px" href="{{ route('cuenta.pqrs') }}">Ver mis PQR</a>
            @endunless
        @endauth
    </aside>
</div>
@endsection

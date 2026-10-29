{{--
    Vista: errors.layout
    Layout de las páginas de error.

    Usada por:  Lo extienden errors/403, 404, 419 y 500.
    Variables:  —
    Secciones:  @section('codigo'), @section('mensaje'), @section('detalle')
--}}
<!DOCTYPE html>
<html lang="es">
<head>@include('partials.head')<title>@yield('codigo') | Raiders Paradise</title></head>
<body>
<div class="error-pagina">
    <div>
        <img src="{{ asset('images/logo-indigo.png') }}" alt="Raiders Paradise" width="72" style="margin:0 auto 12px">
        <div class="codigo">@yield('codigo')</div>
        <h1 style="font-size:2rem">@yield('mensaje')</h1>
        <p class="muted">@yield('detalle')</p>
        <a class="btn btn-brasa" href="{{ url('/') }}">Volver a la tienda</a>
    </div>
</div>
</body>
</html>

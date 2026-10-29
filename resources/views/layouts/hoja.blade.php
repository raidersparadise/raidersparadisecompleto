{{--
    Vista: layouts.hoja
    Layout imprimible tamaño carta/A4 para facturas y reportes PDF, con barra para imprimir y encabezado de la empresa.

    Usada por:  Lo extienden cuenta/factura y admin/reportes/imprimir.
    Variables:  $autoimprimir (bool, opcional: abre el diálogo de impresión al cargar)
    Secciones:  @section('titulo'), @section('contenido')
--}}
<!DOCTYPE html>
<html lang="es">
<head>
    @include('partials.head')
    <title>@yield('titulo') | Raiders Paradise</title>
</head>
<body @if(!empty($autoimprimir)) data-autoimprimir @endif>
<div class="hoja">
    <div class="barra-impresion no-imprimir">
        <a class="btn btn-fantasma btn-sm" href="{{ url()->previous() }}"><x-icono n="atras" />Volver</a>
        <button class="btn btn-sm" type="button" onclick="window.print()"><x-icono n="imprimir" />Imprimir o guardar PDF</button>
    </div>
    <div class="hoja-cabeza">
        @include('partials.marca', ['logo' => 'images/logo-indigo.png'])
        <div class="right small">
            <strong>Raiders Paradise S.A.S.</strong><br>NIT 901.234.567-8<br>Bogotá, Colombia<br>tienda@raidersparadise.co
        </div>
    </div>
    @yield('contenido')
</div>
<script src="{{ asset('js/raiders.js') }}?v=1.0"></script>
</body>
</html>

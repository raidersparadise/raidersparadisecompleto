{{--
    Vista: admin.reportes.imprimir
    Versión imprimible del reporte (PDF): se abre el diálogo de impresión al cargar.

    Usada por:  Admin\ReporteController@generar con formato=pdf
    Variables:  $reporte
--}}
@extends('layouts.hoja', ['autoimprimir' => true])
@section('titulo', $reporte['titulo'])

@section('contenido')
<h1 style="font-size:2rem;margin-bottom:2px">{{ $reporte['titulo'] }}</h1>
<p class="muted">
    {{ $reporte['usa_fechas'] ? 'Periodo: '.$reporte['desde']->format('d/m/Y').' a '.$reporte['hasta']->format('d/m/Y').'. ' : '' }}
    Generado el {{ now()->format('d/m/Y h:i a') }} por {{ auth()->user()->nombre_completo }}.
</p>
@include('admin.reportes._contenido')
@endsection

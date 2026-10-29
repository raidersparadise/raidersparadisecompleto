{{--
    Vista: admin.reportes.ver
    Reporte en pantalla con filtro de fechas y botones de exportación.

    Usada por:  Admin\ReporteController@generar con formato=pantalla
    Variables:  $reporte (estructura de ReporteService), $tipos
--}}
@extends('layouts.admin')
@section('titulo', $reporte['titulo'])

@section('contenido')
@php($q = ['desde' => $reporte['desde']->toDateString(), 'hasta' => $reporte['hasta']->toDateString()])
<div class="admin-cabeza">
    <div>
        <h1>{{ $reporte['titulo'] }}</h1>
        <p>{{ $reporte['usa_fechas'] ? 'Del '.$reporte['desde']->format('d/m/Y').' al '.$reporte['hasta']->format('d/m/Y') : 'Estado al '.now()->format('d/m/Y h:i a') }}</p>
    </div>
    <div class="flex">
        <a class="btn btn-fantasma" href="{{ route('admin.reportes.index') }}"><x-icono n="atras" />Reportes</a>
        <a class="btn btn-linea" target="_blank" href="{{ route('admin.reportes.generar', [$reporte['tipo']] + $q + ['formato' => 'pdf']) }}"><x-icono n="imprimir" />PDF</a>
        <a class="btn btn-brasa" href="{{ route('admin.reportes.generar', [$reporte['tipo']] + $q + ['formato' => 'csv']) }}"><x-icono n="descarga" />Descargar Excel</a>
    </div>
</div>
@if ($reporte['usa_fechas'])
    <form class="filtros-admin panel" method="GET" action="{{ route('admin.reportes.generar', $reporte['tipo']) }}" style="padding:14px 16px">
        <label class="flex" style="gap:8px"><span class="small muted">Desde</span><input class="input" type="date" name="desde" value="{{ $q['desde'] }}"></label>
        <label class="flex" style="gap:8px"><span class="small muted">Hasta</span><input class="input" type="date" name="hasta" value="{{ $q['hasta'] }}"></label>
        <div class="rangos-rapidos">
            <button type="button" data-rango="7">7 días</button><button type="button" data-rango="30">30 días</button><button type="button" data-rango="mes">Este mes</button><button type="button" data-rango="90">90 días</button><button type="button" data-rango="anio">Este año</button>
        </div>
        <button class="btn btn-sm" type="submit">Actualizar</button>
    </form>
@endif
@include('admin.reportes._contenido')
@endsection

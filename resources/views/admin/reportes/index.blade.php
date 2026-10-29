{{--
    Vista: admin.reportes.index
    Catálogo de reportes con selección de fechas y botones Ver / PDF / Excel, e historial de generación.

    Usada por:  Admin\ReporteController@index  (GET /admin/reportes)
    Variables:  $tipos (ReporteService::TIPOS), $historial
--}}
@extends('layouts.admin')
@section('titulo', 'Reportes')

@section('contenido')
<div class="admin-cabeza">
    <div><h1>Reportes gerenciales</h1><p>Elige el periodo y genera el reporte en pantalla, en PDF para imprimir o en Excel (CSV).</p></div>
</div>
@php
    $iconos = ['chart' => 'chart', 'star' => 'star', 'grid' => 'grid', 'users' => 'users', 'truck' => 'truck', 'box' => 'box', 'chat' => 'chat'];
@endphp
<div class="reportes-grid">
    @foreach ($tipos as $clave => $t)
        <article class="reporte-card">
            <div class="ico"><x-icono :n="$iconos[$t['icono']] ?? 'chart'" size="22" /></div>
            <h3>{{ $t['titulo'] }}</h3>
            <p>{{ $t['descripcion'] }}</p>
            <form method="GET" action="{{ route('admin.reportes.generar', $clave) }}">
                @if ($t['usa_fechas'])
                    <div class="fechas">
                        <label><span class="small muted">Desde</span><input class="input" type="date" name="desde" value="{{ now()->subDays(29)->toDateString() }}" max="{{ now()->toDateString() }}"></label>
                        <label><span class="small muted">Hasta</span><input class="input" type="date" name="hasta" value="{{ now()->toDateString() }}" max="{{ now()->toDateString() }}"></label>
                    </div>
                    <div class="rangos-rapidos">
                        <button type="button" data-rango="7">7 días</button>
                        <button type="button" data-rango="30">30 días</button>
                        <button type="button" data-rango="mes">Este mes</button>
                        <button type="button" data-rango="90">90 días</button>
                        <button type="button" data-rango="anio">Este año</button>
                    </div>
                @else
                    <p class="small muted">Muestra el estado actual, no depende de fechas.</p>
                @endif
                <div class="botones">
                    <button class="btn btn-sm" type="submit" name="formato" value="pantalla"><x-icono n="ojo" size="16" />Ver</button>
                    <button class="btn btn-linea btn-sm" type="submit" name="formato" value="pdf" formtarget="_blank" title="Abrir versión para imprimir o guardar como PDF"><x-icono n="imprimir" size="16" />PDF</button>
                    <button class="btn btn-linea btn-sm" type="submit" name="formato" value="csv" title="Descargar para Excel"><x-icono n="descarga" size="16" />Excel</button>
                </div>
            </form>
        </article>
    @endforeach
</div>

<section class="panel" style="margin-top:22px">
    <div class="panel-cabeza"><h2>Historial de reportes generados</h2></div>
    @if ($historial->isEmpty())
        <p class="muted mb-0">Todavía no se ha generado ningún reporte.</p>
    @else
        <div class="tabla-wrap" style="border:0">
            <table class="tabla">
                <thead><tr><th>Reporte</th><th>Periodo</th><th>Formato</th><th>Generado por</th><th>Fecha</th></tr></thead>
                <tbody>
                @foreach ($historial as $h)
                    <tr>
                        <td>{{ $h->tipo_reporte }}</td>
                        <td>{{ $h->fecha_desde ? $h->fecha_desde->format('d/m/Y').' a '.$h->fecha_hasta->format('d/m/Y') : 'Estado actual' }}</td>
                        <td>{{ ['pantalla' => 'En pantalla', 'pdf' => 'PDF', 'csv' => 'Excel (CSV)'][$h->formato] ?? $h->formato }}</td>
                        <td>{{ $h->usuario?->nombre_completo }}</td>
                        <td>{{ $h->fecha_generacion->format('d/m/Y h:i a') }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</section>
@endsection

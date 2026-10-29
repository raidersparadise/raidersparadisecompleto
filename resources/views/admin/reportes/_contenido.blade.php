{{--
    Vista: admin.reportes._contenido
    Cuerpo común de un reporte: indicadores, gráfica y tabla con totales.

    Usada por:  Incluido por admin/reportes/ver e imprimir.
    Variables:  $reporte
--}}
<div class="kpis">
    @foreach ($reporte['kpis'] as [$etq, $valor, $tipo])
        <div @class(['kpi', 'destacado' => $loop->first])><div class="etq">{{ $etq }}</div><div class="valor" @if($tipo === 'text') style="font-size:1.35rem" @endif>@include('admin.reportes._valor', ['v' => $valor, 'tipo' => $tipo])</div></div>
    @endforeach
</div>
@if ($reporte['grafica'])
    <section class="panel" style="margin-bottom:18px">
        <div class="panel-cabeza"><h2>{{ $reporte['grafica']['titulo'] }}</h2></div>
        <div class="grafica" data-grafica='@json($reporte['grafica'])'></div>
    </section>
@endif
<div class="tabla-wrap">
    <table class="tabla">
        <thead><tr>@foreach ($reporte['columnas'] as [$col, $tipo])<th @class(['num' => in_array($tipo, ['money', 'int', 'pct'])])>{{ $col }}</th>@endforeach</tr></thead>
        <tbody>
        @forelse ($reporte['filas'] as $fila)
            <tr>@foreach ($fila as $i => $v)<td @class(['num' => in_array($reporte['columnas'][$i][1], ['money', 'int', 'pct'])])>@include('admin.reportes._valor', ['v' => $v, 'tipo' => $reporte['columnas'][$i][1]])</td>@endforeach</tr>
        @empty
            <tr><td colspan="{{ count($reporte['columnas']) }}" class="muted" style="text-align:center;padding:28px">No hay movimientos en el periodo seleccionado. Prueba con un rango más amplio.</td></tr>
        @endforelse
        </tbody>
        @if (!empty($reporte['totales']) && count($reporte['filas']))
            <tfoot><tr>@foreach ($reporte['totales'] as $i => $v)<td @class(['num' => in_array($reporte['columnas'][$i][1], ['money', 'int', 'pct'])])>@if($v !== '')@include('admin.reportes._valor', ['v' => $v, 'tipo' => $i === 0 ? 'text' : $reporte['columnas'][$i][1]])@endif</td>@endforeach</tr></tfoot>
        @endif
    </table>
</div>

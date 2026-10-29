{{--
    Vista: admin.reportes._valor
    Formatea un valor de reporte según su tipo de columna (money, int, pct, date, estado, text).

    Usada por:  Incluido por admin/reportes/_contenido.
    Variables:  $v (valor), $tipo (tipo de columna)
--}}
@php
    $salida = match ($tipo) {
        'money' => \App\Support\Formato::cop($v),
        'int' => is_numeric($v) ? number_format($v, 0, ',', '.') : $v,
        'pct' => is_numeric($v) ? number_format($v * 100, 1, ',', '.').'%' : $v,
        'date' => $v ? \Carbon\Carbon::parse($v)->format('d/m/Y') : '',
        default => $v,
    };
@endphp
@if ($tipo === 'estado')
<span class="badge badge-{{ \Illuminate\Support\Str::slug($v) }}">{{ $v }}</span>
@else
{{ $salida }}
@endif

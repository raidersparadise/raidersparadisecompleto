{{--
    Vista: admin.pqr.index
    Listado de PQR con filtros por estado y tipo.

    Usada por:  Admin\PqrController@index  (GET /admin/pqr)
    Variables:  $pqrs (paginadas)
--}}
@extends('layouts.admin')
@section('titulo', 'PQR')

@section('contenido')
<div class="admin-cabeza"><div><h1>Peticiones, quejas y reclamos</h1><p>Asigna un responsable y responde dentro de los 15 días hábiles.</p></div></div>
<form class="filtros-admin" method="GET">
    <select class="select" name="estado" data-autoenviar aria-label="Estado">
        <option value="">Todos los estados</option>
        @foreach (\App\Models\Pqr::ESTADOS as $e)<option value="{{ $e }}" @selected(request('estado') === $e)>{{ ucfirst($e) }}</option>@endforeach
    </select>
    <select class="select" name="tipo" data-autoenviar aria-label="Tipo">
        <option value="">Todos los tipos</option>
        @foreach (\App\Models\Pqr::TIPOS as $k => $t)<option value="{{ $k }}" @selected(request('tipo') === $k)>{{ $t }}</option>@endforeach
    </select>
</form>
<div class="tabla-wrap">
    <table class="tabla">
        <thead><tr><th>Radicado</th><th>Fecha</th><th>Cliente</th><th>Tipo</th><th>Asunto</th><th>Responsable</th><th>Estado</th><th></th></tr></thead>
        <tbody>
        @forelse ($pqrs as $p)
            <tr>
                <td><strong>{{ $p->radicado }}</strong></td>
                <td>{{ $p->fecha->format('d/m/Y') }}<br><span class="small muted">hace {{ $p->fecha->diffForHumans(null, true) }}</span></td>
                <td>{{ $p->cliente->nombre_completo }}</td>
                <td>{{ $p->tipo_texto }}</td>
                <td>{{ \Illuminate\Support\Str::limit($p->asunto, 40) }}</td>
                <td>{{ $p->responsable?->nombre_completo ?? 'Sin asignar' }}</td>
                <td><span class="badge badge-{{ str_replace(' ', '-', $p->estado) }}">{{ ucfirst($p->estado) }}</span></td>
                <td class="right"><a class="btn btn-fantasma btn-sm" href="{{ route('admin.pqr.show', $p) }}">Atender</a></td>
            </tr>
        @empty
            <tr><td colspan="8" class="muted" style="text-align:center;padding:32px">No hay PQR con este filtro.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
{{ $pqrs->links() }}
@endsection

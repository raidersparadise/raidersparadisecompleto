{{--
    Vista: admin.pqr.show
    Detalle de la PQR y formulario de gestión (responsable, estado y respuesta).

    Usada por:  Admin\PqrController@show  (GET /admin/pqr/{pqr})
    Variables:  $pqr, $personal (usuarios Administrador y Vendedor)
--}}
@extends('layouts.admin')
@section('titulo', $pqr->radicado)

@section('contenido')
<div class="admin-cabeza">
    <div><h1>{{ $pqr->radicado }}: {{ $pqr->asunto }}</h1><p>{{ $pqr->tipo_texto }} radicada el {{ $pqr->fecha->format('d/m/Y') }} por {{ $pqr->cliente->nombre_completo }}</p></div>
    <a class="btn btn-fantasma" href="{{ route('admin.pqr.index') }}"><x-icono n="atras" />Volver</a>
</div>
<div class="grid-2">
    <section class="panel">
        <div class="panel-cabeza"><h2>Solicitud del cliente</h2><span class="badge badge-{{ str_replace(' ', '-', $pqr->estado) }}">{{ ucfirst($pqr->estado) }}</span></div>
        <p style="white-space:pre-line">{{ $pqr->descripcion_pqr }}</p>
        <p class="small muted mb-0">{{ $pqr->cliente->email }} · {{ $pqr->cliente->telefono ?? 'sin celular' }}</p>
    </section>
    <form class="panel" method="POST" action="{{ route('admin.pqr.update', $pqr) }}">
        @csrf @method('PUT')
        <div class="panel-cabeza"><h2>Gestión</h2></div>
        <div class="campo"><label for="id_usuario">Responsable</label>
            <select id="id_usuario" class="select" name="id_usuario">
                <option value="">Sin asignar</option>
                @foreach ($personal as $u)<option value="{{ $u->id_usuario }}" @selected(old('id_usuario', $pqr->id_usuario) == $u->id_usuario)>{{ $u->nombre_completo }} ({{ $u->rol->nombre_rol }})</option>@endforeach
            </select></div>
        <div class="campo"><label for="estado">Estado</label>
            <select id="estado" class="select" name="estado">
                @foreach (\App\Models\Pqr::ESTADOS as $e)<option value="{{ $e }}" @selected(old('estado', $pqr->estado) === $e)>{{ ucfirst($e) }}</option>@endforeach
            </select></div>
        <div class="campo"><label for="respuesta">Respuesta al cliente</label>
            <textarea id="respuesta" class="textarea @error('respuesta') invalido @enderror" name="respuesta" placeholder="El cliente verá esta respuesta en «Mis PQR».">{{ old('respuesta', $pqr->respuesta) }}</textarea>
            <span class="ayuda">Obligatoria para cerrar o rechazar.</span></div>
        <button class="btn btn-brasa" type="submit">Guardar gestión</button>
    </form>
</div>
@endsection

{{--
    Vista: cuenta.pqrs
    PQR radicadas por el cliente con su estado y respuesta.

    Usada por:  CuentaController@pqrs  (GET /mis-pqr)
    Variables:  $pqrs
--}}
@extends('layouts.tienda')
@section('titulo', 'Mis PQR')

@section('contenido')
<div class="cabecera-pagina"><div class="contenedor"><h1>Mis PQR</h1><p>Seguimiento de tus peticiones, quejas y reclamos.</p></div></div>
<div class="contenedor cuenta">
    @include('cuenta._menu')
    <section class="stack">
        <div class="flex between"><span class="muted">{{ $pqrs->count() }} solicitudes</span><a class="btn btn-brasa btn-sm" href="{{ route('pqr.create') }}">Radicar nueva</a></div>
        @forelse ($pqrs as $p)
            <article class="caja">
                <div class="flex between">
                    <div><strong>{{ $p->radicado }}</strong> <span class="muted small">{{ $p->tipo_texto }}, {{ $p->fecha->format('d/m/Y') }}</span></div>
                    <span class="badge badge-{{ str_replace(' ', '-', $p->estado) }}">{{ ucfirst($p->estado) }}</span>
                </div>
                <h3 style="margin:10px 0 6px;font-family:var(--f-texto);font-size:1.05rem">{{ $p->asunto }}</h3>
                <p class="muted">{{ $p->descripcion_pqr }}</p>
                @if ($p->respuesta)
                    <div class="alerta alerta-info mb-0"><x-icono n="chat" /><div><strong>Respuesta de Raiders Paradise</strong><br>{{ $p->respuesta }}</div></div>
                @endif
            </article>
        @empty
            <div class="vacio"><x-icono n="chat" size="48" /><h3>No has radicado solicitudes</h3><p class="muted">Si algo no salió bien con tu compra, cuéntanos.</p></div>
        @endforelse
    </section>
</div>
@endsection

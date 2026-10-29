{{--
    Vista: partials.alertas
    Mensajes de éxito (session ok), error (session error) y errores de validación.

    Usada por:  Incluido por los layouts y algunos formularios.
    Variables:  session(ok|error), $errors
--}}
@if (session('ok'))
    <div class="alerta alerta-ok" role="status" data-auto>
        <x-icono n="check" /> <div>{{ session('ok') }}</div>
        <button type="button" class="cerrar" aria-label="Cerrar">&times;</button>
    </div>
@endif
@if (session('error'))
    <div class="alerta alerta-error" role="alert">
        <x-icono n="alerta" /> <div>{{ session('error') }}</div>
        <button type="button" class="cerrar" aria-label="Cerrar">&times;</button>
    </div>
@endif
@if ($errors->any())
    <div class="alerta alerta-error" role="alert">
        <x-icono n="alerta" />
        <div>
            @if ($errors->count() === 1)
                {{ $errors->first() }}
            @else
                Revisa los siguientes campos:
                <ul>@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            @endif
        </div>
        <button type="button" class="cerrar" aria-label="Cerrar">&times;</button>
    </div>
@endif

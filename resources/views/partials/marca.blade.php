{{--
    Vista: partials.marca
    Logo y nombre de la marca enlazados al inicio.

    Usada por:  Incluido por los layouts y el pie de página.
    Variables:  $href (opcional, destino del enlace), $logo (opcional, ruta de la imagen)
--}}
<a href="{{ $href ?? route('home') }}" class="marca" aria-label="Raiders Paradise, inicio">
    <img src="{{ asset($logo ?? 'images/logo-blanco.png') }}" alt="" width="42" height="40">
    <span>Raiders Paradise<small>Equipamiento premium para motociclistas</small></span>
</a>

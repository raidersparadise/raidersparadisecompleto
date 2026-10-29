{{--
    Vista: partials.paginacion
    Paginación personalizada (registrada como vista por defecto en AppServiceProvider).

    Usada por:  La usa $paginador->links().
    Variables:  $paginator, $elements (los provee Laravel)
--}}
@if ($paginator->hasPages())
    <nav class="paginacion" aria-label="Paginación">
        @if ($paginator->onFirstPage())
            <span class="off" aria-hidden="true">&lsaquo;</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Anterior">&lsaquo;</a>
        @endif
        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="off">{{ $element }}</span>
            @endif
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="actual" aria-current="page">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Siguiente">&rsaquo;</a>
        @else
            <span class="off" aria-hidden="true">&rsaquo;</span>
        @endif
    </nav>
@endif

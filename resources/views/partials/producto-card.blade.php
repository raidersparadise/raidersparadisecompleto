{{--
    Vista: partials.producto-card
    Tarjeta de producto del catálogo: carrusel de fotos, marca, nombre, tallas disponibles, precio, stock y botón de carrito
    (los productos con talla llevan a la ficha para elegirla).

    Usada por:  Incluido por tienda/home, tienda/catalogo y tienda/producto.
    Variables:  $producto (App\Models\Producto con marca e inventario cargados)
--}}
@php
    $stock = $producto->stock;
    $fotos = array_slice($producto->galeria, 0, 5);
    $tallasCard = $producto->usaTallas() ? $producto->tallas : collect();
@endphp
<article class="producto-card">
    <a class="foto" href="{{ route('producto', $producto) }}" @if (count($fotos) > 1) data-carrusel @endif>
        @foreach ($fotos as $n => $url)
            <img src="{{ $url }}" alt="{{ $n === 0 ? $producto->nombre_producto : '' }}" loading="lazy" width="400" height="400" @class(['activa' => $n === 0])>
        @endforeach
        @if (count($fotos) > 1)
            <button type="button" class="carrusel-flecha ant" data-carrusel-mover="-1" aria-label="Foto anterior" tabindex="-1">‹</button>
            <button type="button" class="carrusel-flecha sig" data-carrusel-mover="1" aria-label="Foto siguiente" tabindex="-1">›</button>
            <span class="carrusel-puntos" aria-hidden="true">@foreach ($fotos as $n => $u)<i @class(['activa' => $n === 0])></i>@endforeach</span>
        @endif
        @if (($producto->vistas360_count ?? 0) > 1)
            <span class="sello-360" title="Este producto tiene vista 360°">360°</span>
        @endif
        @if ($stock <= 0)
            <span class="badge badge-agotado etiq">Agotado</span>
        @elseif ($producto->destacado)
            <span class="badge etiq" style="background:#391C1D;color:#fff">Destacado</span>
        @endif
    </a>
    <div class="info">
        <span class="marca-p">{{ $producto->marca->nombre_marca }}</span>
        <h3><a href="{{ route('producto', $producto) }}">{{ $producto->nombre_producto }}</a></h3>
        @if ($tallasCard->isNotEmpty())
            <div class="tallas-card" aria-label="Tallas disponibles">
                @foreach ($tallasCard as $t)<span @class(['agotada' => $t->cantidad_disponible <= 0]) title="{{ $t->cantidad_disponible > 0 ? 'Disponible' : 'Agotada' }}">{{ $t->talla }}</span>@endforeach
            </div>
        @endif
        @if ($stock > 0 && $stock <= ($producto->inventario->cantidad_minima ?? 0))
            <span class="stock-txt stock-bajo">Últimas {{ $stock }} unidades</span>
        @endif
        <div class="pie">
            <span class="precio">@cop($producto->precio_producto)</span>
            @if ($tallasCard->isNotEmpty())
                {{-- Con tallas: se elige la talla en la ficha del producto --}}
                <a class="btn-carrito" href="{{ route('producto', $producto) }}" @if ($stock <= 0) aria-disabled="true" @endif aria-label="Elegir talla de {{ $producto->nombre_producto }}" title="Elegir talla">
                    <x-icono n="carrito" />
                </a>
            @else
            <form method="POST" action="{{ route('carrito.agregar', $producto) }}">
                @csrf
                <button class="btn-carrito" type="submit" @disabled($stock <= 0) aria-label="Agregar {{ $producto->nombre_producto }} al carrito" title="Agregar al carrito">
                    <x-icono n="carrito" />
                </button>
            </form>
            @endif
        </div>
    </div>
</article>

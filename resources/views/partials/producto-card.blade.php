{{--
    Vista: partials.producto-card
    Tarjeta de producto del catálogo: imagen, marca, nombre, precio, stock y botón de agregar al carrito.

    Usada por:  Incluido por tienda/home, tienda/catalogo y tienda/producto.
    Variables:  $producto (App\Models\Producto con marca e inventario cargados)
--}}
@php($stock = $producto->stock)
<article class="producto-card">
    <a class="foto" href="{{ route('producto', $producto) }}">
        <img src="{{ $producto->imagen_url }}" alt="{{ $producto->nombre_producto }}" loading="lazy" width="400" height="400">
        @if ($stock <= 0)
            <span class="badge badge-agotado etiq">Agotado</span>
        @elseif ($producto->destacado)
            <span class="badge etiq" style="background:#391C1D;color:#fff">Destacado</span>
        @endif
    </a>
    <div class="info">
        <span class="marca-p">{{ $producto->marca->nombre_marca }}</span>
        <h3><a href="{{ route('producto', $producto) }}">{{ $producto->nombre_producto }}</a></h3>
        @if ($stock > 0 && $stock <= ($producto->inventario->cantidad_minima ?? 0))
            <span class="stock-txt stock-bajo">Últimas {{ $stock }} unidades</span>
        @endif
        <div class="pie">
            <span class="precio">@cop($producto->precio_producto)</span>
            <form method="POST" action="{{ route('carrito.agregar', $producto) }}">
                @csrf
                <button class="btn-carrito" type="submit" @disabled($stock <= 0) aria-label="Agregar {{ $producto->nombre_producto }} al carrito" title="Agregar al carrito">
                    <x-icono n="carrito" />
                </button>
            </form>
        </div>
    </div>
</article>

{{--
    Vista: tienda.producto
    Detalle de producto: imagen, precio, ficha técnica, disponibilidad, selector de cantidad y relacionados.

    Usada por:  TiendaController@producto  (GET /producto/{producto})
    Variables:  $producto, $relacionados
--}}
@extends('layouts.tienda')
@section('titulo', $producto->nombre_producto)
@section('descripcion', $producto->descripcion_producto)

@section('contenido')
@php($stock = $producto->stock)
<div class="contenedor">
    <div class="detalle">
        <div class="galeria"><img src="{{ $producto->imagen_url }}" alt="{{ $producto->nombre_producto }}" width="600" height="600"></div>
        <div>
            <div class="migas" style="color:var(--suave)">
                <a href="{{ route('catalogo') }}">Catálogo</a> /
                <a href="{{ route('catalogo', ['categoria' => $producto->id_categoria]) }}">{{ $producto->categoria->nombre_categoria }}</a>
            </div>
            <span class="marca-p">{{ $producto->marca->nombre_marca }}</span>
            <h1>{{ $producto->nombre_producto }}</h1>
            <div class="precio">@cop($producto->precio_producto)</div>
            <div class="iva">IVA incluido · Hasta 3 cuotas sin interés con tarjeta</div>

            <p style="margin-top:18px">{{ $producto->descripcion_producto }}</p>

            <dl class="ficha">
                <dt>Referencia</dt><dd>{{ $producto->sku }}</dd>
                <dt>Marca</dt><dd>{{ $producto->marca->nombre_marca }}</dd>
                <dt>Categoría</dt><dd>{{ $producto->categoria->nombre_categoria }}</dd>
                <dt>Disponibilidad</dt>
                <dd>
                    @if ($stock <= 0)
                        <span class="stock-no">Agotado — escríbenos y te avisamos cuando llegue</span>
                    @elseif ($stock <= ($producto->inventario->cantidad_minima ?? 0))
                        <span class="stock-bajo">Últimas {{ $stock }} unidades</span>
                    @else
                        <span class="stock-ok">En stock, sale en 24 horas</span>
                    @endif
                </dd>
            </dl>

            @if ($stock > 0)
                <form class="comprar" method="POST" action="{{ route('carrito.agregar', $producto) }}">
                    @csrf
                    <div class="cantidad">
                        <button type="button" data-paso="-1" aria-label="Quitar una unidad">−</button>
                        <label class="sr-only" for="cantidad">Cantidad</label>
                        <input id="cantidad" type="number" name="cantidad" value="1" min="1" max="{{ min($stock, 20) }}">
                        <button type="button" data-paso="1" aria-label="Agregar una unidad">+</button>
                    </div>
                    <button class="btn btn-brasa" type="submit"><x-icono n="carrito" />Agregar al carrito</button>
                </form>
            @else
                <a class="btn btn-linea" href="{{ route('pqr.create') }}">Preguntar por disponibilidad</a>
            @endif

            <ul class="beneficios">
                <li><x-icono n="escudo" />Producto original con garantía del fabricante</li>
                <li><x-icono n="camion" />Envío gratis desde $ 300.000 a todo Colombia</li>
                <li><x-icono n="retorno" />Cambio de talla hasta 30 días después de la compra</li>
            </ul>
        </div>
    </div>

    @if ($relacionados->isNotEmpty())
        <section style="padding-bottom:64px">
            <div class="seccion-cabeza"><h2>También te puede interesar</h2></div>
            <div class="productos-grid">
                @foreach ($relacionados as $producto)
                    @include('partials.producto-card')
                @endforeach
            </div>
        </section>
    @endif
</div>
@endsection

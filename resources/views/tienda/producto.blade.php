{{--
    Vista: tienda.producto
    Detalle de producto: galería de fotos con zoom, precio, ficha técnica, selector de talla (S–XL) y de cantidad, guía de tallas y relacionados.

    Usada por:  TiendaController@producto  (GET /producto/{producto})
    Variables:  $producto, $relacionados
--}}
@extends('layouts.tienda')
@section('titulo', $producto->nombre_producto)
@section('descripcion', $producto->descripcion_producto)

@section('contenido')
<div class="contenedor">
    <div class="detalle">
        @php
            $stock = $producto->stock;
            $fotos = $producto->galeria;
            $cuadros = $producto->vistas360->pluck('url')->all();
            $usaTallas = $producto->usaTallas() && $producto->tallas->isNotEmpty();
            $cat = $producto->categoria->nombre_categoria;
            $tallaElegida = old('talla', $usaTallas ? optional($producto->tallas->firstWhere('cantidad_disponible', '>', 0))->talla : null);
        @endphp
        {{-- Galería tipo marketplace: miniaturas a la izquierda; foto grande con efecto 3D y
             ampliación sutil al pasar el mouse (clic = zoom de detalle). Si el producto tiene
             cuadros 360°, la primera miniatura abre el visor 360° (arrastrar para girar). --}}
        <div class="galeria-ml" data-galeria>
            @if (count($fotos) > 1 || $cuadros)
                <ul class="galeria-miniaturas" role="tablist" aria-label="Fotos del producto">
                    @if ($cuadros)
                        <li><button type="button" role="tab" class="activa miniatura-360" data-modo-360 aria-label="Vista 360°" aria-selected="true"><img src="{{ $cuadros[0] }}" alt="" width="64" height="64"><span>360°</span></button></li>
                    @endif
                    @foreach ($fotos as $n => $url)
                        <li><button type="button" role="tab" @class(['activa' => $n === 0 && ! $cuadros]) data-foto="{{ $url }}" aria-label="Ver foto {{ $n + 1 }} de {{ count($fotos) }}" aria-selected="{{ $n === 0 && ! $cuadros ? 'true' : 'false' }}"><img src="{{ $url }}" alt="" loading="lazy" width="64" height="64"></button></li>
                    @endforeach
                </ul>
            @endif
            <div class="galeria-principal" data-zoom>
                <div class="foto-3d" @if ($cuadros) hidden @endif>
                    <img src="{{ $fotos[0] }}" alt="{{ $producto->nombre_producto }}" width="600" height="600" data-foto-principal draggable="false">
                </div>
                @if ($cuadros)
                    <div class="visor-360" data-visor-360='@json($cuadros)' data-autogiro>
                        <img src="{{ $cuadros[0] }}" alt="{{ $producto->nombre_producto }} — vista 360°" width="600" height="600" draggable="false">
                        <span class="visor-360-ayuda"><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M21 12a9 9 0 1 1-3-6.7M21 3v6h-6"/></svg> Arrastra para girar 360°</span>
                    </div>
                @endif
                @if (count($fotos) > 1)
                    <button type="button" class="galeria-flecha ant" data-mover="-1" aria-label="Foto anterior">‹</button>
                    <button type="button" class="galeria-flecha sig" data-mover="1" aria-label="Foto siguiente">›</button>
                    <span class="galeria-contador" @if ($cuadros) hidden @endif><span data-foto-n>1</span> / {{ count($fotos) }}</span>
                @endif
                <span class="galeria-pista">@if ($cuadros) 360° @else Vista 3D · clic para ampliar @endif</span>
            </div>
        </div>
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

            @if ($producto->etiquetas->isNotEmpty())
                <div class="chips" style="margin-top:12px" aria-label="Etiquetas">
                    @foreach ($producto->etiquetas as $e)
                        <a class="chip" href="{{ route('catalogo', ['etiqueta' => $e->slug]) }}">#{{ $e->nombre }}</a>
                    @endforeach
                </div>
            @endif

            <dl class="ficha">
                <dt>Referencia</dt><dd>{{ $producto->sku }}</dd>
                <dt>Marca</dt><dd>{{ $producto->marca->nombre_marca }}</dd>
                <dt>Categoría</dt><dd>{{ $producto->categoria->nombre_categoria }}</dd>
                <dt>Tallas</dt><dd>{{ $usaTallas ? $producto->tallas->pluck('talla')->implode(' · ') : 'Única' }}</dd>
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
                <form class="comprar" method="POST" action="{{ route('carrito.agregar', $producto) }}" data-form-tallas>
                    @csrf
                    @if ($usaTallas)
                        <fieldset class="selector-tallas">
                            <legend>Talla: <strong data-talla-texto>{{ $tallaElegida ?? 'elige una' }}</strong>
                                @if (isset(\App\Support\Tallas::GUIA[$cat]))
                                    <button type="button" class="enlace-guia" data-abrir="#guia-tallas">Guía de tallas</button>
                                @endif
                            </legend>
                            <div class="tallas-opciones">
                                @foreach ($producto->tallas as $t)
                                    <label @class(['talla-chip', 'agotada' => $t->cantidad_disponible <= 0]) title="{{ $t->cantidad_disponible > 0 ? $t->cantidad_disponible.' disponibles' : 'Agotada' }}">
                                        <input type="radio" name="talla" value="{{ $t->talla }}" data-stock="{{ $t->cantidad_disponible }}" @checked($tallaElegida === $t->talla) @disabled($t->cantidad_disponible <= 0) required>
                                        <span>{{ $t->talla }}</span>
                                    </label>
                                @endforeach
                            </div>
                            <p class="tallas-stock small" data-talla-stock>
                                @if ($tallaElegida) {{ $producto->stockTalla($tallaElegida) }} {{ $producto->stockTalla($tallaElegida) === 1 ? 'disponible' : 'disponibles' }} en talla {{ $tallaElegida }} @endif
                            </p>
                            @error('talla')<p class="error-campo">{{ $message }}</p>@enderror
                        </fieldset>
                    @elseif ($producto->tallas->isNotEmpty())
                        <input type="hidden" name="talla" value="{{ $producto->tallas->first()->talla }}">
                        <p class="small muted" style="width:100%;margin:0">Talla única</p>
                    @endif
                    <div class="cantidad">
                        <button type="button" data-paso="-1" aria-label="Quitar una unidad">−</button>
                        <label class="sr-only" for="cantidad">Cantidad</label>
                        <input id="cantidad" type="number" name="cantidad" value="1" min="1" max="{{ min($usaTallas && $tallaElegida ? $producto->stockTalla($tallaElegida) : $stock, 20) }}">
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

    @if ($usaTallas && isset(\App\Support\Tallas::GUIA[$cat]))
        <dialog id="guia-tallas" class="guia-tallas">
            <form method="dialog"><button class="cerrar-dialogo" aria-label="Cerrar">×</button></form>
            <h2>Guía de tallas · {{ $cat }}</h2>
            <p class="muted small">{{ \App\Support\Tallas::MEDIDA[$cat] }}. Si estás entre dos tallas, elige la mayor.</p>
            <table class="tabla">
                <thead><tr><th>Talla</th><th>{{ \App\Support\Tallas::MEDIDA[$cat] }}</th></tr></thead>
                <tbody>@foreach (\App\Support\Tallas::GUIA[$cat] as $t => $medida)<tr><td><strong>{{ $t }}</strong></td><td>{{ $medida }}</td></tr>@endforeach</tbody>
            </table>
        </dialog>
    @endif

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

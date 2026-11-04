
{{--
    Vista: tienda.home
    Página de inicio: hero, beneficios, categorías, productos destacados,
    franja de PQR y marcas.

    Usada por: TiendaController@home (GET /)
    Variables: $destacados, $categorias (con productos_count), $marcas
--}}
@extends('layouts.tienda')

@section('titulo', 'Accesorios premium para motociclistas')

@section('contenido')


{{-- HERO PRINCIPAL RAIDERS PARADISE --}}
<section class="hero hero-banner">
    <div class="hero-banner-contenido">

        <img
            src="{{ asset('images/hero-raiders.png') }}"
            alt="Raiders Paradise - Accesorios premium para motociclistas"
            class="hero-banner-imagen"
            fetchpriority="high"
        >

        {{-- Botón Ver catálogo --}}
        <a
            href="{{ route('catalogo') }}"
            class="hero-banner-enlace hero-banner-catalogo"
            aria-label="Ver catálogo">
        </a>

        {{-- Botón Elegir mi casco --}}
        <a
            href="{{ route('catalogo', [
                'categoria' => $categorias->firstWhere(
                    'nombre_categoria',
                    'Cascos'
                )?->id_categoria
            ]) }}"
            class="hero-banner-enlace hero-banner-cascos"
            aria-label="Elegir mi casco">
        </a>

    </div>
</section>

<style>
    /* HERO PRINCIPAL */
    .hero.hero-banner {
        padding: 0;
        background: #080f29;
        overflow: hidden;
    }

    .hero-banner-contenido {
        position: relative;
        width: 100%;
        max-width: 1774px;
        margin: 0 auto;
        line-height: 0;
    }

    .hero-banner-imagen {
        display: block;
        width: 100%;
        height: auto;
    }

    /* Áreas interactivas de los botones */
    .hero-banner-enlace {
        position: absolute;
        display: block;
        top: 65.5%;
        height: 9.5%;
        z-index: 5;
        background: transparent;
        border-radius: 18px;
    }

    .hero-banner-catalogo {
        left: 4.4%;
        width: 17.5%;
    }

    .hero-banner-cascos {
        left: 23.5%;
        width: 19.5%;
    }

    .hero-banner-enlace:focus-visible {
        outline: 3px solid white;
        outline-offset: 3px;
    }
</style>


        <div class="hero-visual" aria-hidden="true">
            <div class="hero-placa"></div>

            <img src="{{ asset('images/casco-hero.png') }}" alt="">

            <div class="hero-dato">
                <strong>Certificación ECE 22.06</strong>
                <span>Todos nuestros cascos cumplen la norma vigente.</span>
            </div>
        </div>
    </div>
</section>

{{-- BENEFICIOS --}}
<section class="confianza" aria-label="Por qué comprar con nosotros">
    <div class="contenedor">

        <div>
            <x-icono n="escudo" />
            <span>
                <strong>100% originales</strong>
                Garantía de fábrica
            </span>
        </div>

        <div>
            <x-icono n="camion" />
            <span>
                <strong>Envío nacional</strong>
                De 2 a 5 días hábiles
            </span>
        </div>

        <div>
            <x-icono n="tarjeta" />
            <span>
                <strong>Pago flexible</strong>
                Nequi, PSE, tarjeta o contra entrega
            </span>
        </div>

        <div>
            <x-icono n="retorno" />
            <span>
                <strong>Cambio de talla</strong>
                Hasta 30 días después
            </span>
        </div>

    </div>
</section>

{{-- CATEGORÍAS --}}
<section class="seccion">
    <div class="contenedor">

        <div class="seccion-cabeza">
            <div>
                <h2>Compra por categoría</h2>
                <p>Todo el equipo, de la cabeza a los pies.</p>
            </div>
        </div>

        @php
            $iconos = [
                'Accesorios'   => 'accesorios.png',
                'Botas'        => 'botas.png',
                'Cascos'       => 'cascos.png',
                'Chaquetas'    => 'chaquetas.png',
                'Guantes'      => 'guantes.png',
                'Impermeables' => 'impermeables.png',
            ];
        @endphp

        <div class="categorias-grid">

            @foreach ($categorias as $c)

                @php
                    $imagenCategoria = $iconos[$c->nombre_categoria] ?? null;
                @endphp

                <a class="cat-tile"
                   href="{{ route('catalogo', [
                       'categoria' => $c->id_categoria
                   ]) }}">

                    @if ($imagenCategoria)
                        <img
                            src="{{ asset('images/productos/' . $imagenCategoria) }}"
                            alt="{{ $c->nombre_categoria }}"
                            width="76"
                            height="76"
                            loading="lazy"
                            decoding="async"
                        >
                    @else
                        <img
                            src="{{ asset('images/productos/generico.svg') }}"
                            alt="{{ $c->nombre_categoria }}"
                            width="76"
                            height="76"
                            loading="lazy"
                            decoding="async"
                        >
                    @endif

                    <div>
                        <strong>{{ $c->nombre_categoria }}</strong>
                        <br>

                        <span>
                            {{ $c->productos_count }}
                            {{ $c->productos_count === 1 ? 'producto' : 'productos' }}
                        </span>
                    </div>

                </a>

            @endforeach

        </div>
    </div>
</section>

{{-- PRODUCTOS DESTACADOS --}}
<section class="seccion" style="padding-top: 0">
    <div class="contenedor">

        <div class="seccion-cabeza">
            <div>
                <h2>Los favoritos del taller</h2>
                <p>Lo que más recomiendan nuestros asesores este mes.</p>
            </div>

            <a class="btn btn-linea btn-sm"
               href="{{ route('catalogo') }}">
                Ver todo el catálogo
            </a>
        </div>

        <div class="productos-grid">

            @foreach ($destacados as $producto)
                @include('partials.producto-card')
            @endforeach

        </div>
    </div>
</section>

{{-- FRANJA DE PQR --}}
<section class="franja-cuero">
    <div class="contenedor">

        <div>
            <h2>¿Tienes una duda, queja o reclamo?</h2>

            <p>
                Radica tu PQR en línea y recibe un número de seguimiento
                al instante. Respondemos en máximo 15 días hábiles.
            </p>
        </div>

        <a class="btn btn-brasa" href="{{ route('pqr.create') }}">
            Radicar una PQR
        </a>

    </div>
</section>

{{-- MARCAS --}}
<section class="seccion">
    <div class="contenedor">

        <div class="seccion-cabeza">
            <div>
                <h2>Marcas que manejamos</h2>
            </div>
        </div>

        <div class="marcas-lista">

            @foreach ($marcas as $m)

                <a href="{{ route('catalogo', [
                    'marca' => $m->id_marca
                ]) }}">
                    {{ $m->nombre_marca }}
                </a>

            @endforeach

        </div>
    </div>
</section>

@endsection

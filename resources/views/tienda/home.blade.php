{{--
    Vista: tienda.home
    Página de inicio: hero, beneficios, categorías, productos destacados, franja de PQR y marcas.

    Usada por:  TiendaController@home  (GET /)
    Variables:  $destacados, $categorias (con productos_count), $marcas
--}}
@extends('layouts.tienda')
@section('titulo', 'Accesorios premium para motociclistas')

@section('contenido')
<section class="hero">
    <div class="contenedor">
        <div>
            <h1>Equípate para la ruta que mereces</h1>
            <p class="lead">Cascos, guantes, chaquetas, impermeables y botas de Shoei, AGV, Alpinestars, Dainese y más. Originales, con garantía y asesoría de motociclistas.</p>
            <div class="botones">
                <a class="btn btn-brasa" href="{{ route('catalogo') }}">Ver catálogo</a>
                <a class="btn btn-linea" href="{{ route('catalogo', ['categoria' => $categorias->firstWhere('nombre_categoria', 'Cascos')?->id_categoria]) }}">Elegir mi casco</a>
            </div>
        </div>
        <div class="hero-visual" aria-hidden="true">
            <div class="hero-placa"></div>
            <img src="{{ asset('images/casco-hero.svg') }}" alt="">
            <div class="hero-dato">
                <strong>Certificación ECE 22.06</strong>
                <span>Todos nuestros cascos cumplen la norma vigente.</span>
            </div>
        </div>
    </div>
</section>

<section class="confianza" aria-label="Por qué comprar con nosotros">
    <div class="contenedor">
        <div><x-icono n="escudo" /><span><strong>100% originales</strong>Garantía de fábrica</span></div>
        <div><x-icono n="camion" /><span><strong>Envío nacional</strong>De 2 a 5 días hábiles</span></div>
        <div><x-icono n="tarjeta" /><span><strong>Pago flexible</strong>Nequi, PSE, tarjeta o contra entrega</span></div>
        <div><x-icono n="retorno" /><span><strong>Cambio de talla</strong>Hasta 30 días después</span></div>
    </div>
</section>

<section class="seccion">
    <div class="contenedor">
        <div class="seccion-cabeza">
            <div><h2>Compra por categoría</h2><p>Todo el equipo, de la cabeza a los pies.</p></div>
        </div>
        @php
            $iconos = ['Cascos' => 'casco-indigo', 'Guantes' => 'guantes-naranja', 'Chaquetas' => 'chaqueta-negro', 'Impermeables' => 'impermeable-naranja', 'Botas' => 'botas-indigo', 'Accesorios' => 'intercom-negro'];
        @endphp
        <div class="categorias-grid">
            @foreach ($categorias as $c)
                <a class="cat-tile" href="{{ route('catalogo', ['categoria' => $c->id_categoria]) }}">
                    <img src="{{ asset('images/productos/'.($iconos[$c->nombre_categoria] ?? 'generico').'.svg') }}" alt="" width="76" height="76">
                    <div><strong>{{ $c->nombre_categoria }}</strong><br><span>{{ $c->productos_count }} {{ $c->productos_count === 1 ? 'producto' : 'productos' }}</span></div>
                </a>
            @endforeach
        </div>
    </div>
</section>

<section class="seccion" style="padding-top:0">
    <div class="contenedor">
        <div class="seccion-cabeza">
            <div><h2>Los favoritos del taller</h2><p>Lo que más recomiendan nuestros asesores este mes.</p></div>
            <a class="btn btn-linea btn-sm" href="{{ route('catalogo') }}">Ver todo el catálogo</a>
        </div>
        <div class="productos-grid">
            @foreach ($destacados as $producto)
                @include('partials.producto-card')
            @endforeach
        </div>
    </div>
</section>

<section class="franja-cuero">
    <div class="contenedor">
        <div>
            <h2>¿Tienes una duda, queja o reclamo?</h2>
            <p>Radica tu PQR en línea y recibe un número de seguimiento al instante. Respondemos en máximo 15 días hábiles.</p>
        </div>
        <a class="btn btn-brasa" href="{{ route('pqr.create') }}">Radicar una PQR</a>
    </div>
</section>

<section class="seccion">
    <div class="contenedor">
        <div class="seccion-cabeza"><div><h2>Marcas que manejamos</h2></div></div>
        <div class="marcas-lista">
            @foreach ($marcas as $m)
                <a href="{{ route('catalogo', ['marca' => $m->id_marca]) }}">{{ $m->nombre_marca }}</a>
            @endforeach
        </div>
    </div>
</section>
@endsection

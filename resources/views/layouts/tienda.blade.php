{{--
    Vista: layouts.tienda
    Layout de la tienda pública: barra de promoción, encabezado con menú, buscador, carrito y menú de usuario; alertas y pie de página.

    Usada por:  Lo extienden las vistas de tienda/, auth/ y cuenta/.
    Variables:  $carritoItems (int, unidades en el carrito; lo inyecta AppServiceProvider)
    Secciones:  @section('titulo'), @section('descripcion'), @section('contenido'), @section('sin_alertas'), @stack('scripts')
--}}
<!DOCTYPE html>
<html lang="es">
<head>
    @include('partials.head')
    <title>@yield('titulo', 'Accesorios premium para motociclistas') | Raiders Paradise</title>
    <meta name="description" content="@yield('descripcion', 'Cascos, guantes, chaquetas, impermeables y botas de las mejores marcas para motociclistas en Colombia.')">
</head>
<body>
<a href="#contenido" class="sr-only">Saltar al contenido</a>
<div class="barra-promo">Envío gratis a todo Colombia en compras desde <strong>$ 300.000</strong>. Paga con Nequi, PSE, tarjeta o contra entrega</div>
<header class="header">
    <div class="contenedor">
        @include('partials.marca')
        <nav class="nav" aria-label="Principal">
            <a href="{{ route('catalogo') }}" @class(['activo' => request()->routeIs('catalogo', 'producto')])>Catálogo</a>
            <a href="{{ route('catalogo', ['categoria' => \App\Models\Categoria::where('nombre_categoria', 'Cascos')->value('id_categoria')]) }}">Cascos</a>
            <a href="{{ route('pqr.create') }}" @class(['activo' => request()->routeIs('pqr.*')])>Atención al cliente</a>
        </nav>
        <form class="buscador" action="{{ route('catalogo') }}" method="GET" role="search">
            <x-icono n="buscar" />
            <label for="buscar-global" class="sr-only">Buscar productos</label>
            <input id="buscar-global" type="search" name="q" value="{{ request('q') }}" placeholder="Busca cascos, guantes, marcas…">
        </form>
        <div class="acciones-header">
            @auth
                @if (auth()->user()->esPersonal())
                    <a class="icono-btn" href="{{ route('admin.dashboard') }}"><x-icono n="grid" /><span class="txt">Panel</span></a>
                @else
                    <a class="icono-btn" href="{{ route('carrito') }}" aria-label="Carrito, {{ $carritoItems }} productos">
                        <x-icono n="carrito" />
                        @if ($carritoItems > 0)<span class="contador">{{ $carritoItems }}</span>@endif
                    </a>
                @endif
                <div class="menu-usuario" id="menu-usuario">
                    <button class="icono-btn" type="button" data-toggle="#menu-usuario" aria-expanded="false" aria-haspopup="true">
                        <span class="avatar">{{ auth()->user()->iniciales }}</span>
                        <span class="txt">{{ auth()->user()->nombre_usuario }}</span>
                    </button>
                    <div class="panel">
                        <div class="cabeza"><strong>{{ auth()->user()->nombre_completo }}</strong><br><span class="small muted">{{ auth()->user()->email }}</span></div>
                        @if (auth()->user()->esPersonal())
                            <a href="{{ route('admin.dashboard') }}">Panel administrativo</a>
                        @else
                            <a href="{{ route('cuenta.pedidos') }}">Mis pedidos</a>
                            <a href="{{ route('cuenta.pqrs') }}">Mis PQR</a>
                        @endif
                        <a href="{{ route('cuenta.perfil') }}">Mis datos</a>
                        <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit">Cerrar sesión</button></form>
                    </div>
                </div>
            @else
                <a class="icono-btn" href="{{ route('login') }}"><x-icono n="usuario" /><span class="txt">Ingresar</span></a>
                <a class="btn btn-brasa btn-sm" href="{{ route('registro') }}">Crear cuenta</a>
            @endauth
        </div>
    </div>
</header>

<main id="contenido">
    @hasSection('sin_alertas')
    @else
        @if (session('ok') || session('error') || $errors->any())
            <div class="contenedor" style="padding-top:20px">@include('partials.alertas')</div>
        @endif
    @endif
    @yield('contenido')
</main>

<footer class="footer">
    <div class="contenedor">
        <div class="cols">
            <div>
                @include('partials.marca')
                <p style="margin-top:14px;max-width:34ch">Microempresa colombiana dedicada a la protección y el estilo del motociclista. Solo productos originales con garantía de fábrica.</p>
            </div>
            <div>
                <h4>Tienda</h4>
                @foreach (\App\Models\Categoria::orderBy('nombre_categoria')->get() as $c)
                    <a href="{{ route('catalogo', ['categoria' => $c->id_categoria]) }}">{{ $c->nombre_categoria }}</a>
                @endforeach
            </div>
            <div>
                <h4>Ayuda</h4>
                <a href="{{ route('pqr.create') }}">Peticiones, quejas y reclamos</a>
                <a href="{{ auth()->check() ? route('cuenta.pedidos') : route('login') }}">Seguir mi pedido</a>
                <a href="{{ route('pqr.create') }}">Cambios y garantías</a>
            </div>
            <div>
                <h4>Contacto</h4>
                <a href="mailto:tienda@raidersparadise.co">tienda@raidersparadise.co</a>
                <a href="tel:+576017001234">(601) 700 1234</a>
                <span>Bogotá, Colombia</span>
            </div>
        </div>
        <div class="legal">
            <span>© {{ date('Y') }} Raiders Paradise. Proyecto formativo ADSO — SENA.</span>
            <span>Precios en pesos colombianos con IVA incluido.</span>
        </div>
    </div>
</footer>
<script src="{{ asset('js/raiders.js') }}?v=1.0"></script>
@stack('scripts')
</body>
</html>

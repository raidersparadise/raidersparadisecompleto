{{--
    Vista: cuenta._menu
    Menú lateral de la zona "Mi cuenta".

    Usada por:  Incluido por las vistas de cuenta/.
    Variables:  auth()->user()
--}}
<nav class="menu-cuenta" aria-label="Mi cuenta">
    @unless (auth()->user()->esPersonal())
        <a href="{{ route('cuenta.pedidos') }}" @class(['activo' => request()->routeIs('cuenta.pedido', 'cuenta.pedidos')])><x-icono n="truck" />Mis pedidos</a>
        <a href="{{ route('cuenta.pqrs') }}" @class(['activo' => request()->routeIs('cuenta.pqrs')])><x-icono n="chat" />Mis PQR</a>
    @endunless
    <a href="{{ route('cuenta.perfil') }}" @class(['activo' => request()->routeIs('cuenta.perfil')])><x-icono n="usuario" />Mis datos</a>
    <form method="POST" action="{{ route('logout') }}">@csrf
        <button class="btn btn-fantasma btn-bloque" style="justify-content:flex-start;padding-left:12px" type="submit"><x-icono n="salir" />Cerrar sesión</button>
    </form>
</nav>

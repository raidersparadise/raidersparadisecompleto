{{--
    Vista: layouts.admin
    Layout del panel administrativo: menú lateral por secciones (según rol), barra superior con usuario y zona de contenido con alertas.

    Usada por:  Lo extienden las vistas de admin/.
    Variables:  Usa auth()->user(); calcula los contadores de pedidos pendientes y PQR recibidas.
    Secciones:  @section('titulo'), @section('contenido'), @stack('scripts')
--}}
<!DOCTYPE html>
<html lang="es">
<head>
    @include('partials.head')
    <title>@yield('titulo', 'Panel') | Raiders Paradise Admin</title>
</head>
<body>
@php
    $u = auth()->user();
    $pqrAbiertas = \App\Models\Pqr::whereIn('estado', ['recibida'])->count();
    $porDespachar = \App\Models\Pedido::where('estado', 'pendiente')->count();
@endphp
<div class="admin">
    <aside class="sidebar" id="sidebar" aria-label="Menú del panel">
        @include('partials.marca', ['href' => route('admin.dashboard')])
        <nav>
            <a href="{{ route('admin.dashboard') }}" @class(['activo' => request()->routeIs('admin.dashboard')])><x-icono n="casa" />Inicio</a>

            <span class="grupo-nav">Ventas</span>
            <a href="{{ route('admin.pedidos.index') }}" @class(['activo' => request()->routeIs('admin.pedidos.*')])><x-icono n="truck" />Pedidos @if($porDespachar)<span class="cuenta-n">{{ $porDespachar }}</span>@endif</a>
            <a href="{{ route('admin.clientes.index') }}" @class(['activo' => request()->routeIs('admin.clientes.*')])><x-icono n="users" />Clientes</a>
            <a href="{{ route('admin.pqr.index') }}" @class(['activo' => request()->routeIs('admin.pqr.*')])><x-icono n="chat" />PQR @if($pqrAbiertas)<span class="cuenta-n">{{ $pqrAbiertas }}</span>@endif</a>

            <span class="grupo-nav">Catálogo</span>
            <a href="{{ route('admin.productos.index') }}" @class(['activo' => request()->routeIs('admin.productos.*')])><x-icono n="tag" />Productos</a>
            <a href="{{ route('admin.inventario.index') }}" @class(['activo' => request()->routeIs('admin.inventario.*')])><x-icono n="box" />Inventario</a>
            <a href="{{ route('admin.maestros.index', 'categorias') }}" @class(['activo' => request()->is('admin/maestros/categorias*')])><x-icono n="grid" />Categorías</a>
            <a href="{{ route('admin.maestros.index', 'marcas') }}" @class(['activo' => request()->is('admin/maestros/marcas*')])><x-icono n="star" />Marcas</a>
            <a href="{{ route('admin.maestros.index', 'proveedores') }}" @class(['activo' => request()->is('admin/maestros/proveedores*')])><x-icono n="fabrica" />Proveedores</a>

            @if ($u->esAdmin())
                <span class="grupo-nav">Administración</span>
                <a href="{{ route('admin.reportes.index') }}" @class(['activo' => request()->routeIs('admin.reportes.*')])><x-icono n="chart" />Reportes</a>
                <a href="{{ route('admin.usuarios.index') }}" @class(['activo' => request()->routeIs('admin.usuarios.*')])><x-icono n="llave" />Usuarios</a>
            @endif
        </nav>
        <div class="pie-side">
            <nav>
                <a href="{{ route('home') }}" target="_blank"><x-icono n="tienda" />Ver tienda</a>
            </nav>
        </div>
    </aside>

    <div class="admin-main">
        <header class="topbar">
            <button class="btn btn-fantasma btn-sm btn-menu" type="button" data-toggle="#sidebar" aria-label="Abrir menú"><x-icono n="menu" /></button>
            <span class="titulo">@yield('titulo', 'Panel')</span>
            <div class="usuario">
                <span class="avatar">{{ $u->iniciales }}</span>
                <span>{{ $u->nombre_completo }}<small>{{ $u->rol->nombre_rol }}</small></span>
                <form method="POST" action="{{ route('logout') }}">@csrf
                    <button class="btn btn-fantasma btn-sm" type="submit" title="Cerrar sesión"><x-icono n="salir" /><span class="sr-only">Cerrar sesión</span></button>
                </form>
            </div>
        </header>
        <main class="admin-contenido" id="contenido">
            @include('partials.alertas')
            @yield('contenido')
        </main>
    </div>
</div>
<script src="{{ asset('js/raiders.js') }}?v=1.0"></script>
@stack('scripts')
</body>
</html>

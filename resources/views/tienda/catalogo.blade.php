{{--
    Vista: tienda.catalogo
    Catálogo con filtros (búsqueda, categoría, marca, precio), chips de filtros activos, orden y paginación.

    Usada por:  TiendaController@catalogo  (GET /catalogo)
    Variables:  $productos (paginados), $categorias, $marcas, $filtros, $categoriaActual
--}}
@extends('layouts.tienda')
@section('titulo', $categoriaActual?->nombre_categoria ?? 'Catálogo')

@section('contenido')
<div class="cabecera-pagina">
    <div class="contenedor">
        <div class="migas"><a href="{{ route('home') }}">Inicio</a> / Catálogo</div>
        <h1>{{ $categoriaActual?->nombre_categoria ?? 'Catálogo completo' }}</h1>
        <p>{{ $categoriaActual?->descripcion_categoria ?? 'Equipamiento premium para cada tipo de motociclista.' }}</p>
    </div>
</div>

<div class="contenedor catalogo">
    <aside class="filtros" aria-label="Filtros">
        <form method="GET" action="{{ route('catalogo') }}">
            @if (!empty($filtros['orden']))<input type="hidden" name="orden" value="{{ $filtros['orden'] }}">@endif
            <div class="grupo">
                <label for="q" class="etiqueta">Buscar</label>
                <input id="q" class="input" type="search" name="q" value="{{ $filtros['q'] ?? '' }}" placeholder="Nombre o marca" style="margin-top:6px">
            </div>
            <div class="grupo">
                <span class="etiqueta">Categoría</span>
                <div class="lista-filtro" style="margin-top:6px">
                    <a href="{{ route('catalogo', array_merge(request()->except('categoria', 'page'))) }}" @class(['activo' => empty($filtros['categoria'])])>Todas</a>
                    @foreach ($categorias as $c)
                        <a href="{{ route('catalogo', array_merge(request()->except('page'), ['categoria' => $c->id_categoria])) }}" @class(['activo' => ($filtros['categoria'] ?? null) == $c->id_categoria])>{{ $c->nombre_categoria }}</a>
                    @endforeach
                </div>
            </div>
            <div class="grupo">
                <label for="marca" class="etiqueta">Marca</label>
                <select id="marca" name="marca" class="select" style="margin-top:6px">
                    <option value="">Todas las marcas</option>
                    @foreach ($marcas as $m)
                        <option value="{{ $m->id_marca }}" @selected(($filtros['marca'] ?? null) == $m->id_marca)>{{ $m->nombre_marca }}</option>
                    @endforeach
                </select>
                @if (!empty($filtros['categoria']))<input type="hidden" name="categoria" value="{{ $filtros['categoria'] }}">@endif
            </div>
            <div class="grupo">
                <span class="etiqueta">Precio (COP)</span>
                <div class="rango" style="margin-top:6px">
                    <label class="sr-only" for="min">Mínimo</label>
                    <input id="min" class="input" type="number" name="min" min="0" step="50000" placeholder="Desde" value="{{ $filtros['min'] ?? '' }}">
                    <label class="sr-only" for="max">Máximo</label>
                    <input id="max" class="input" type="number" name="max" min="0" step="50000" placeholder="Hasta" value="{{ $filtros['max'] ?? '' }}">
                </div>
            </div>
            <button class="btn btn-bloque" type="submit">Aplicar filtros</button>
        </form>
    </aside>

    <section>
        <div class="barra-resultados">
            <div>
                <strong>{{ $productos->total() }}</strong> {{ $productos->total() === 1 ? 'producto' : 'productos' }}
                @php($activos = collect($filtros)->except('orden')->filter(fn($v) => $v !== null && $v !== ''))
                @if ($activos->isNotEmpty())
                    <div class="chips" style="margin-top:8px">
                        @foreach ($activos as $k => $v)
                            @php($txt = match($k) { 'categoria' => $categorias->firstWhere('id_categoria', $v)?->nombre_categoria, 'marca' => $marcas->firstWhere('id_marca', $v)?->nombre_marca, 'min' => 'Desde '.\App\Support\Formato::cop($v), 'max' => 'Hasta '.\App\Support\Formato::cop($v), default => '“'.$v.'”' })
                            <a class="chip" href="{{ route('catalogo', request()->except($k, 'page')) }}" aria-label="Quitar filtro {{ $txt }}">{{ $txt }} <span>&times;</span></a>
                        @endforeach
                    </div>
                @endif
            </div>
            <form method="GET" action="{{ route('catalogo') }}">
                @foreach (request()->except('orden', 'page') as $k => $v)<input type="hidden" name="{{ $k }}" value="{{ $v }}">@endforeach
                <label class="sr-only" for="orden">Ordenar por</label>
                <select id="orden" class="select" name="orden" data-autoenviar>
                    <option value="recientes" @selected(($filtros['orden'] ?? '') === 'recientes')>Más recientes</option>
                    <option value="precio_asc" @selected(($filtros['orden'] ?? '') === 'precio_asc')>Precio: menor a mayor</option>
                    <option value="precio_desc" @selected(($filtros['orden'] ?? '') === 'precio_desc')>Precio: mayor a menor</option>
                    <option value="nombre" @selected(($filtros['orden'] ?? '') === 'nombre')>Nombre A–Z</option>
                </select>
            </form>
        </div>

        @if ($productos->isEmpty())
            <div class="vacio">
                <x-icono n="buscar" size="48" />
                <h3>No encontramos productos con esos filtros</h3>
                <p class="muted">Prueba con otra marca o amplía el rango de precio.</p>
                <a class="btn" href="{{ route('catalogo') }}">Ver todo el catálogo</a>
            </div>
        @else
            <div class="productos-grid" style="grid-template-columns:repeat(auto-fill,minmax(220px,1fr))">
                @foreach ($productos as $producto)
                    @include('partials.producto-card')
                @endforeach
            </div>
            {{ $productos->links() }}
        @endif
    </section>
</div>
@endsection

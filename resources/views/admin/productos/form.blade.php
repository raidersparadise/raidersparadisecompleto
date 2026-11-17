{{--
    Vista: admin.productos.form
    Formulario de creación y edición de productos (datos, foto principal, galería, publicación e inventario por talla).

    Usada por:  Admin\ProductoController@create / @edit
    Variables:  $producto (nuevo o existente), $categorias, $marcas, $proveedores
--}}
@extends('layouts.admin')
@section('titulo', $producto->exists ? 'Editar producto' : 'Nuevo producto')

@section('contenido')
<div class="admin-cabeza">
    <div><h1>{{ $producto->exists ? $producto->nombre_producto : 'Nuevo producto' }}</h1><p>Los campos marcados son obligatorios para publicar en la tienda.</p></div>
    <a class="btn btn-fantasma" href="{{ route('admin.productos.index') }}"><x-icono n="atras" />Volver</a>
</div>
<form class="form-admin" method="POST" enctype="multipart/form-data" action="{{ $producto->exists ? route('admin.productos.update', $producto) : route('admin.productos.store') }}">
    @csrf
    @if ($producto->exists) @method('PUT') @endif
    <section class="panel">
        <div class="panel-cabeza"><h2>Información general</h2></div>
        <div class="form-grid">
            <div class="campo"><label for="nombre_producto">Nombre *</label><input id="nombre_producto" class="input @error('nombre_producto') invalido @enderror" name="nombre_producto" value="{{ old('nombre_producto', $producto->nombre_producto) }}" required maxlength="120"></div>
            <div class="campo"><label for="sku">SKU / referencia *</label><input id="sku" class="input @error('sku') invalido @enderror" name="sku" value="{{ old('sku', $producto->sku) }}" required maxlength="30" placeholder="CAS-SHO-001"></div>
            <div class="campo"><label for="id_categoria">Categoría *</label>
                <select id="id_categoria" class="select" name="id_categoria" required>
                    <option value="">Selecciona…</option>
                    @foreach ($categorias as $c)<option value="{{ $c->id_categoria }}" data-tallas="{{ \App\Support\Tallas::categoriaUsaTallas($c->nombre_categoria) ? 1 : 0 }}" @selected(old('id_categoria', $producto->id_categoria) == $c->id_categoria)>{{ $c->nombre_categoria }}</option>@endforeach
                </select></div>
            <div class="campo"><label for="id_marca">Marca *</label>
                <select id="id_marca" class="select" name="id_marca" required>
                    <option value="">Selecciona…</option>
                    @foreach ($marcas as $m)<option value="{{ $m->id_marca }}" @selected(old('id_marca', $producto->id_marca) == $m->id_marca)>{{ $m->nombre_marca }}</option>@endforeach
                </select></div>
            <div class="campo"><label for="id_proveedor">Proveedor *</label>
                <select id="id_proveedor" class="select" name="id_proveedor" required>
                    <option value="">Selecciona…</option>
                    @foreach ($proveedores as $pv)<option value="{{ $pv->id_proveedor }}" @selected(old('id_proveedor', $producto->id_proveedor) == $pv->id_proveedor)>{{ $pv->nombre_proveedor }}</option>@endforeach
                </select></div>
            <div class="campo"><label for="precio_producto">Precio de venta (COP, IVA incluido) *</label><input id="precio_producto" class="input @error('precio_producto') invalido @enderror" type="number" min="1000" step="100" name="precio_producto" value="{{ old('precio_producto', $producto->precio_producto ? (int) $producto->precio_producto : '') }}" required></div>
            <div class="campo completo"><label for="descripcion_producto">Descripción</label><textarea id="descripcion_producto" class="textarea" name="descripcion_producto" maxlength="1000">{{ old('descripcion_producto', $producto->descripcion_producto) }}</textarea></div>
            <div class="campo completo"><label for="etiquetas">Etiquetas <span class="muted">(separadas por coma, máximo 10)</span></label><input id="etiquetas" class="input @error('etiquetas') invalido @enderror" name="etiquetas" maxlength="255" placeholder="impermeable, cuero, touring" value="{{ old('etiquetas', $producto->exists ? $producto->etiquetas->pluck('nombre')->join(', ') : '') }}"><span class="ayuda">Ayudan a que el producto aparezca en el buscador de la tienda.</span></div>
            <div class="campo completo"><label for="comentario_producto">Nota interna <span class="muted">(no se muestra en la tienda)</span></label><input id="comentario_producto" class="input" name="comentario_producto" maxlength="255" value="{{ old('comentario_producto', $producto->comentario_producto) }}"></div>
        </div>
    </section>
    <section class="panel">
        <div class="panel-cabeza"><h2>Imagen y publicación</h2></div>
        <div class="flex" style="align-items:flex-start;gap:20px">
            <img id="preview" class="preview-img" src="{{ $producto->imagen_url }}" alt="Vista previa">
            <div class="grow">
                <div class="campo"><label for="imagen">Imagen del producto</label><input id="imagen" class="input" type="file" name="imagen" accept="image/png,image/jpeg,image/webp" data-preview="#preview"><span class="ayuda">Foto principal (la que se ve primero). JPG, PNG o WEBP de máximo 8 MB; se comprime automáticamente a WebP (máx. 1200 px) y se le quitan los datos EXIF/GPS.</span></div>
                <div class="form-grid">
                    <div class="campo"><label for="estado_producto">Estado</label>
                        <select id="estado_producto" class="select" name="estado_producto">
                            <option value="activo" @selected(old('estado_producto', $producto->estado_producto) === 'activo')>Activo (visible en la tienda)</option>
                            <option value="inactivo" @selected(old('estado_producto', $producto->estado_producto) === 'inactivo')>Inactivo (oculto)</option>
                        </select></div>
                    <div class="campo" style="align-self:end"><label class="check"><input type="hidden" name="destacado" value="0"><input type="checkbox" name="destacado" value="1" @checked(old('destacado', $producto->destacado))> Mostrar en «Los favoritos del taller»</label></div>
                </div>
            </div>
        </div>
    </section>
    <section class="panel">
        <div class="panel-cabeza"><h2>Galería de fotos</h2></div>
        <p class="muted small" style="margin-top:0">Fotos adicionales que el cliente ve en la ficha del producto y al pasar el mouse por la tarjeta del catálogo (vistas de frente, espalda, detalles, etiqueta…).</p>
        @if ($producto->exists && $producto->imagenes->isNotEmpty())
            <div class="galeria-admin">
                @foreach ($producto->imagenes as $img)
                    <label class="galeria-admin-item">
                        <img src="{{ $img->url }}" alt="Foto {{ $loop->iteration }}">
                        <span class="check"><input type="checkbox" name="quitar_imagenes[]" value="{{ $img->id_producto_imagen }}"> Quitar</span>
                    </label>
                @endforeach
            </div>
        @endif
        <div class="campo"><label for="imagenes">Agregar fotos</label><input id="imagenes" class="input @error('imagenes.*') invalido @enderror" type="file" name="imagenes[]" accept="image/png,image/jpeg,image/webp" multiple><span class="ayuda">Puedes elegir varias a la vez (hasta 8, máximo 8 MB cada una; se comprimen automáticamente).</span></div>
    </section>
    <section class="panel">
        <div class="panel-cabeza"><h2>Vista 360°</h2></div>
        <p class="muted small" style="margin-top:0">Sube de 12 a 36 fotos tomadas girando el producto sobre su eje, con la misma distancia y fondo blanco. Se ordenan por nombre de archivo (01.jpg, 02.jpg, …). Una nueva tanda reemplaza la anterior.</p>
        @if ($producto->exists && $producto->vistas360->isNotEmpty())
            <div class="flex" style="align-items:center;gap:16px;margin-bottom:12px">
                <div class="visor-360 visor-360-mini" data-visor-360='@json($producto->vistas360->pluck('url'))'>
                    <img src="{{ $producto->vistas360->first()->url }}" alt="Vista 360°" draggable="false">
                </div>
                <div>
                    <strong>{{ $producto->vistas360->count() }} cuadros</strong> · arrastra la imagen para probar el giro
                    <label class="check" style="margin-top:8px"><input type="checkbox" name="quitar_360" value="1"> Quitar la vista 360°</label>
                </div>
            </div>
        @endif
        <div class="campo"><label for="vistas360">{{ $producto->exists && $producto->vistas360->isNotEmpty() ? 'Reemplazar cuadros 360°' : 'Cuadros 360°' }}</label><input id="vistas360" class="input @error('vistas360.*') invalido @enderror" type="file" name="vistas360[]" accept="image/png,image/jpeg,image/webp" multiple><span class="ayuda">JPG, PNG o WEBP de máximo 1 MB cada uno (ideal 800 × 800 px).</span></div>
    </section>
    @php
        $tallasActuales = $producto->exists ? $producto->tallas->pluck('cantidad_disponible', 'talla') : collect();
        $usaTallas = $producto->exists ? $producto->usaTallas() : true;
    @endphp
    <section class="panel">
        <div class="panel-cabeza"><h2>Inventario por talla</h2></div>
        <div data-grupo-tallas="1" @if (! $usaTallas) hidden @endif>
            <p class="muted small" style="margin-top:0">Unidades disponibles en cada talla. El total del inventario se calcula solo.</p>
            <div class="tallas-admin">
                @foreach (\App\Support\Tallas::TALLAS as $t)
                    <div class="campo"><label for="stock_{{ $t }}">Talla {{ $t }}</label><input id="stock_{{ $t }}" class="input" type="number" min="0" name="stock_tallas[{{ $t }}]" value="{{ old('stock_tallas.'.$t, $tallasActuales[$t] ?? 0) }}" @disabled(! $usaTallas)></div>
                @endforeach
            </div>
        </div>
        <div data-grupo-tallas="0" @if ($usaTallas) hidden @endif>
            <p class="muted small" style="margin-top:0">Los accesorios se venden en talla única.</p>
            <div class="tallas-admin">
                <div class="campo"><label for="stock_unica">Unidades (talla única)</label><input id="stock_unica" class="input" type="number" min="0" name="stock_tallas[{{ \App\Support\Tallas::UNICA }}]" value="{{ old('stock_tallas.'.\App\Support\Tallas::UNICA, $tallasActuales[\App\Support\Tallas::UNICA] ?? 0) }}" @disabled($usaTallas)></div>
            </div>
        </div>
        <div class="form-grid" style="margin-top:8px">
            <div class="campo"><label for="cantidad_minima">Stock mínimo total (alerta) *</label><input id="cantidad_minima" class="input" type="number" min="0" name="cantidad_minima" value="{{ old('cantidad_minima', $producto->inventario->cantidad_minima ?? 3) }}" required></div>
            @if ($producto->exists)
                <div class="campo"><label>Total disponible</label><div class="input" style="display:flex;align-items:center;background:var(--papel)">{{ $producto->stock }} unidades</div></div>
            @endif
        </div>
    </section>
    <div class="flex" style="margin-top:18px">
        <button class="btn btn-brasa" type="submit">{{ $producto->exists ? 'Guardar cambios' : 'Crear producto' }}</button>
        <a class="btn btn-fantasma" href="{{ route('admin.productos.index') }}">Cancelar</a>
    </div>
</form>
@endsection

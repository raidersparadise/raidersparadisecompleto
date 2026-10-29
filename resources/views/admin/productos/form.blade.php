{{--
    Vista: admin.productos.form
    Formulario de creación y edición de productos (datos, imagen, publicación e inventario).

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
                    @foreach ($categorias as $c)<option value="{{ $c->id_categoria }}" @selected(old('id_categoria', $producto->id_categoria) == $c->id_categoria)>{{ $c->nombre_categoria }}</option>@endforeach
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
            <div class="campo completo"><label for="comentario_producto">Nota interna <span class="muted">(no se muestra en la tienda)</span></label><input id="comentario_producto" class="input" name="comentario_producto" maxlength="255" value="{{ old('comentario_producto', $producto->comentario_producto) }}"></div>
        </div>
    </section>
    <section class="panel">
        <div class="panel-cabeza"><h2>Imagen y publicación</h2></div>
        <div class="flex" style="align-items:flex-start;gap:20px">
            <img id="preview" class="preview-img" src="{{ $producto->imagen_url }}" alt="Vista previa">
            <div class="grow">
                <div class="campo"><label for="imagen">Imagen del producto</label><input id="imagen" class="input" type="file" name="imagen" accept="image/png,image/jpeg,image/webp" data-preview="#preview"><span class="ayuda">JPG, PNG o WEBP de máximo 2 MB. Ideal: cuadrada de 800 × 800 px.</span></div>
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
        <div class="panel-cabeza"><h2>Inventario</h2></div>
        <div class="form-grid">
            <div class="campo"><label for="cantidad_disponible">Unidades disponibles *</label><input id="cantidad_disponible" class="input" type="number" min="0" name="cantidad_disponible" value="{{ old('cantidad_disponible', $producto->inventario->cantidad_disponible ?? 0) }}" required></div>
            <div class="campo"><label for="cantidad_minima">Stock mínimo (alerta) *</label><input id="cantidad_minima" class="input" type="number" min="0" name="cantidad_minima" value="{{ old('cantidad_minima', $producto->inventario->cantidad_minima ?? 3) }}" required></div>
        </div>
    </section>
    <div class="flex" style="margin-top:18px">
        <button class="btn btn-brasa" type="submit">{{ $producto->exists ? 'Guardar cambios' : 'Crear producto' }}</button>
        <a class="btn btn-fantasma" href="{{ route('admin.productos.index') }}">Cancelar</a>
    </div>
</form>
@endsection

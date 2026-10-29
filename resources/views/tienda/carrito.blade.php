{{--
    Vista: tienda.carrito
    Carrito de compras: líneas con cantidad editable, resumen con IVA y acceso al pago.

    Usada por:  CarritoController@index  (GET /carrito)
    Variables:  $carrito (con detalles.producto)
--}}
@extends('layouts.tienda')
@section('titulo', 'Carrito')

@section('contenido')
<div class="cabecera-pagina"><div class="contenedor"><h1>Tu carrito</h1><p>Revisa tus productos antes de pagar.</p></div></div>
<div class="contenedor">
    @if ($carrito->detalles->isEmpty())
        <div class="vacio" style="margin:32px 0 64px">
            <x-icono n="carrito" size="48" />
            <h3>Tu carrito está vacío</h3>
            <p class="muted">Explora el catálogo y agrega el equipo para tu próxima rodada.</p>
            <a class="btn btn-brasa" href="{{ route('catalogo') }}">Ir al catálogo</a>
        </div>
    @else
        <div class="layout-compra">
            <div class="caja">
                @foreach ($carrito->detalles as $d)
                    @php($p = $d->producto)
                    <div class="linea-carrito">
                        <img src="{{ $p->imagen_url }}" alt="">
                        <div>
                            <span class="small muted">{{ $p->marca->nombre_marca }}</span>
                            <h3><a href="{{ route('producto', $p) }}" style="color:inherit;text-decoration:none">{{ $p->nombre_producto }}</a></h3>
                            <span class="small muted">@cop($p->precio_producto) c/u</span>
                            @if ($d->cantidad > $p->stock)
                                <div class="error">Solo quedan {{ $p->stock }} unidades. Ajusta la cantidad.</div>
                            @endif
                            <div class="controles">
                                <form class="inline-form" method="POST" action="{{ route('carrito.actualizar', $d) }}">
                                    @csrf @method('PATCH')
                                    <label class="sr-only" for="cant-{{ $d->id_detalle_carrito }}">Cantidad</label>
                                    <input id="cant-{{ $d->id_detalle_carrito }}" class="input" type="number" name="cantidad" min="1" max="{{ max(1, min(20, $p->stock)) }}" value="{{ $d->cantidad }}">
                                    <button class="btn btn-fantasma btn-sm" type="submit">Actualizar</button>
                                </form>
                                <form method="POST" action="{{ route('carrito.eliminar', $d) }}">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-fantasma btn-sm" type="submit"><x-icono n="borrar" size="16" />Quitar</button>
                                </form>
                            </div>
                        </div>
                        <strong class="num precio" style="font-size:1.25rem">@cop($d->subtotal)</strong>
                    </div>
                @endforeach
            </div>
            <aside class="caja resumen">
                <h3>Resumen</h3>
                @php($total = $carrito->total)
                <div class="fila"><span>Productos ({{ $carrito->cantidad_items }})</span><span>@cop($total)</span></div>
                <div class="fila"><span>Envío</span><span>{{ $total >= 300000 ? 'Gratis' : 'Se calcula al despachar' }}</span></div>
                <div class="fila muted small"><span>IVA incluido</span><span>@cop($total - $total / 1.19)</span></div>
                <div class="fila total"><span>Total</span><span>@cop($total)</span></div>
                <a class="btn btn-brasa btn-bloque" style="margin-top:16px" href="{{ route('checkout') }}">Continuar al pago</a>
                <a class="btn btn-fantasma btn-bloque" style="margin-top:6px" href="{{ route('catalogo') }}">Seguir comprando</a>
            </aside>
        </div>
    @endif
</div>
@endsection

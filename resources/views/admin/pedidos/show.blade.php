{{--
    Vista: admin.pedidos.show
    Detalle de pedido: productos, totales con IVA, cambio de estado, cliente, entrega y pago.

    Usada por:  Admin\PedidoController@show  (GET /admin/pedidos/{pedido})
    Variables:  $pedido
--}}
@extends('layouts.admin')
@section('titulo', 'Pedido '.$pedido->codigo)

@section('contenido')
<div class="admin-cabeza">
    <div><h1>Pedido {{ $pedido->codigo }}</h1><p>{{ $pedido->fecha->translatedFormat('l d \d\e F \d\e Y, h:i a') }}</p></div>
    <div class="flex">
        @if ($pedido->factura)<a class="btn btn-linea" href="{{ route('cuenta.factura', $pedido) }}" target="_blank"><x-icono n="doc" />Factura {{ $pedido->factura->numero_factura }}</a>@endif
        <a class="btn btn-fantasma" href="{{ route('admin.pedidos.index') }}"><x-icono n="atras" />Volver</a>
    </div>
</div>
<div class="grid-2">
    <section class="panel">
        <div class="panel-cabeza"><h2>Productos</h2></div>
        <div class="tabla-wrap" style="border:0">
            <table class="tabla">
                <thead><tr><th>Producto</th><th class="num">Cant.</th><th class="num">Precio</th><th class="num">Subtotal</th></tr></thead>
                <tbody>
                @foreach ($pedido->detalles as $d)
                    <tr><td><div class="prod"><img src="{{ $d->producto->imagen_url }}" alt="">{{ $d->producto->nombre_producto }}</div></td><td class="num">{{ $d->cantidad }}</td><td class="num">@cop($d->precio_unitario)</td><td class="num">@cop($d->sub_total)</td></tr>
                @endforeach
                </tbody>
                <tfoot>
                    @if ($pedido->factura)
                        <tr><td colspan="3">Subtotal</td><td class="num">@cop($pedido->factura->subtotal)</td></tr>
                        <tr><td colspan="3">IVA 19%</td><td class="num">@cop($pedido->factura->impuesto)</td></tr>
                    @endif
                    <tr><td colspan="3">Total</td><td class="num">@cop($pedido->total)</td></tr>
                </tfoot>
            </table>
        </div>
    </section>
    <div class="stack">
        <section class="panel">
            <div class="panel-cabeza"><h2>Estado</h2><span class="badge badge-{{ $pedido->estado }}">{{ $pedido->estado_texto }}</span></div>
            @if ($pedido->estado !== 'cancelado')
                <form method="POST" action="{{ route('admin.pedidos.update', $pedido) }}" data-confirmar="¿Confirmas el cambio de estado? Si cancelas, las unidades regresan al inventario y la factura se anula.">
                    @csrf @method('PUT')
                    <div class="campo"><label for="estado">Cambiar a</label>
                        <select id="estado" class="select" name="estado">
                            @foreach (\App\Models\Pedido::ESTADOS as $k => $txt)<option value="{{ $k }}" @selected($pedido->estado === $k)>{{ $txt }}</option>@endforeach
                        </select></div>
                    <button class="btn btn-bloque" type="submit">Actualizar estado</button>
                </form>
            @else
                <p class="muted mb-0">Pedido cancelado. Las unidades ya regresaron al inventario.</p>
            @endif
        </section>
        <section class="panel">
            <div class="panel-cabeza"><h2>Cliente y entrega</h2></div>
            <p class="mb-0"><a href="{{ route('admin.clientes.show', $pedido->cliente) }}"><strong>{{ $pedido->cliente->nombre_completo }}</strong></a><br>
            {{ $pedido->cliente->email }}<br>{{ $pedido->cliente->telefono }}<br><br>
            <span class="muted small">Dirección</span><br>{{ $pedido->direccion_envio }}, {{ $pedido->ciudad_envio }}
            @if ($pedido->notas)<br><br><span class="muted small">Indicaciones</span><br>{{ $pedido->notas }}@endif</p>
        </section>
        @if ($pedido->factura)
        <section class="panel">
            <div class="panel-cabeza"><h2>Pago</h2><span class="badge badge-{{ $pedido->factura->estado_factura }}">{{ ucfirst($pedido->factura->estado_factura) }}</span></div>
            <p class="mb-0">{{ $pedido->factura->metodo_pago_texto }}<br><span class="muted small">Recaudado: @cop($pedido->factura->pago)</span></p>
        </section>
        @endif
    </div>
</div>
@endsection

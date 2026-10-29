{{--
    Vista: cuenta.factura
    Factura de venta imprimible con subtotal, IVA y total.

    Usada por:  CuentaController@factura  (GET /factura/{pedido})
    Variables:  $pedido (con factura, detalles.producto, cliente)
--}}
@extends('layouts.hoja')
@section('titulo', 'Factura '.$pedido->factura->numero_factura)

@section('contenido')
@php($f = $pedido->factura)
<div class="flex between" style="align-items:flex-start;margin-bottom:22px">
    <div>
        <h1 style="font-size:2rem;margin-bottom:4px">Factura de venta {{ $f->numero_factura }}</h1>
        <span class="muted">Pedido {{ $pedido->codigo }} · Documento de prueba sin validez ante la DIAN</span>
    </div>
    <span class="badge badge-{{ $f->estado_factura }}" style="font-size:.9rem">{{ ucfirst($f->estado_factura) }}</span>
</div>
<div class="grid-2-igual" style="margin-bottom:22px">
    <div class="panel">
        <span class="muted small">Cliente</span><br>
        <strong>{{ $pedido->cliente->nombre_completo }}</strong><br>
        @if ($pedido->cliente->documento)C.C. {{ $pedido->cliente->documento }}<br>@endif
        {{ $pedido->cliente->email }}<br>{{ $pedido->direccion_envio }}, {{ $pedido->ciudad_envio }}
    </div>
    <div class="panel">
        <span class="muted small">Datos de la factura</span><br>
        Fecha: <strong>{{ $f->fecha_factura->format('d/m/Y h:i a') }}</strong><br>
        Medio de pago: <strong>{{ $f->metodo_pago_texto }}</strong><br>
        Valor pagado: <strong>@cop($f->pago)</strong>
    </div>
</div>
<div class="tabla-wrap">
    <table class="tabla">
        <thead><tr><th>Referencia</th><th>Descripción</th><th class="num">Cant.</th><th class="num">Valor unitario</th><th class="num">Total</th></tr></thead>
        <tbody>
        @foreach ($pedido->detalles as $d)
            <tr><td>{{ $d->producto->sku }}</td><td>{{ $d->producto->nombre_producto }}</td><td class="num">{{ $d->cantidad }}</td><td class="num">@cop($d->precio_unitario)</td><td class="num">@cop($d->sub_total)</td></tr>
        @endforeach
        </tbody>
        <tfoot>
            <tr><td colspan="4" class="right">Subtotal</td><td class="num">@cop($f->subtotal)</td></tr>
            <tr><td colspan="4" class="right">IVA 19%</td><td class="num">@cop($f->impuesto)</td></tr>
            <tr><td colspan="4" class="right" style="font-size:1.1rem">Total</td><td class="num" style="font-size:1.1rem">@cop($f->total_factura)</td></tr>
        </tfoot>
    </table>
</div>
<p class="small muted" style="margin-top:22px">Gracias por comprar en Raiders Paradise. Conserva este documento para hacer efectiva la garantía de tus productos.</p>
@endsection

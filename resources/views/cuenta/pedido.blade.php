{{--
    Vista: cuenta.pedido
    Detalle de un pedido con barra de seguimiento, dirección y enlace a la factura.

    Usada por:  CuentaController@pedido  (GET /mis-pedidos/{pedido})
    Variables:  $pedido (con detalles.producto, factura, cliente)
--}}
@extends('layouts.tienda')
@section('titulo', 'Pedido '.$pedido->codigo)

@section('contenido')
<div class="cabecera-pagina"><div class="contenedor">
    <div class="migas"><a href="{{ route('cuenta.pedidos') }}">Mis pedidos</a> / {{ $pedido->codigo }}</div>
    <h1>Pedido {{ $pedido->codigo }}</h1>
    <p>Realizado el {{ $pedido->fecha->translatedFormat('d \d\e F \d\e Y, h:i a') }}</p>
</div></div>
<div class="contenedor cuenta">
    @include('cuenta._menu')
    <section class="stack">
        <div class="caja">
            @if ($pedido->estado === 'cancelado')
                <div class="alerta alerta-error mb-0"><x-icono n="alerta" />Este pedido fue cancelado. Si pagaste en línea, el reembolso se refleja en 5 a 8 días hábiles.</div>
            @else
                @php($orden = ['pendiente' => 0, 'programado' => 1, 'en_curso' => 2, 'entregado' => 3][$pedido->estado] ?? 0)
                <ol class="seguimiento" style="padding:0">
                    @foreach (['Recibido', 'Preparando envío', 'En camino', 'Entregado'] as $i => $paso)
                        <li @class(['hecho' => $i <= $orden])>{{ $paso }}</li>
                    @endforeach
                </ol>
            @endif
            <div class="flex between">
                <div><span class="muted small">Entregar en</span><br><strong>{{ $pedido->direccion_envio }}, {{ $pedido->ciudad_envio }}</strong></div>
                @if ($pedido->factura)
                    <a class="btn btn-linea" href="{{ route('cuenta.factura', $pedido) }}"><x-icono n="doc" />Ver factura {{ $pedido->factura->numero_factura }}</a>
                @endif
            </div>
        </div>
        <div class="tabla-wrap">
            <table class="tabla">
                <thead><tr><th>Producto</th><th class="num">Cantidad</th><th class="num">Precio</th><th class="num">Subtotal</th></tr></thead>
                <tbody>
                @foreach ($pedido->detalles as $d)
                    <tr>
                        <td><div class="prod"><img src="{{ $d->producto->imagen_url }}" alt="">{{ $d->producto->nombre_producto }}</div></td>
                        <td class="num">{{ $d->cantidad }}</td>
                        <td class="num">@cop($d->precio_unitario)</td>
                        <td class="num">@cop($d->sub_total)</td>
                    </tr>
                @endforeach
                </tbody>
                <tfoot><tr><td colspan="3">Total (IVA incluido)</td><td class="num">@cop($pedido->total)</td></tr></tfoot>
            </table>
        </div>
    </section>
</div>
@endsection

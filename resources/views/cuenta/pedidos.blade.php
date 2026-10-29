{{--
    Vista: cuenta.pedidos
    Listado de pedidos del cliente con estado, pago y total.

    Usada por:  CuentaController@pedidos  (GET /mis-pedidos)
    Variables:  $pedidos (paginados, con factura)
--}}
@extends('layouts.tienda')
@section('titulo', 'Mis pedidos')

@section('contenido')
<div class="cabecera-pagina"><div class="contenedor"><h1>Mis pedidos</h1><p>Consulta el estado de tus compras y descarga tus facturas.</p></div></div>
<div class="contenedor cuenta">
    @include('cuenta._menu')
    <section>
        @if ($pedidos->isEmpty())
            <div class="vacio">
                <x-icono n="truck" size="48" />
                <h3>Todavía no tienes pedidos</h3>
                <p class="muted">Cuando compres, aquí verás el estado de cada envío.</p>
                <a class="btn btn-brasa" href="{{ route('catalogo') }}">Ir al catálogo</a>
            </div>
        @else
            <div class="tabla-wrap">
                <table class="tabla">
                    <thead><tr><th>Pedido</th><th>Fecha</th><th>Estado</th><th>Pago</th><th class="num">Total</th><th></th></tr></thead>
                    <tbody>
                    @foreach ($pedidos as $p)
                        <tr>
                            <td><strong>{{ $p->codigo }}</strong></td>
                            <td>{{ $p->fecha->format('d/m/Y') }}</td>
                            <td><span class="badge badge-{{ $p->estado }}">{{ $p->estado_texto }}</span></td>
                            <td>{{ $p->factura?->metodo_pago_texto }}</td>
                            <td class="num">@cop($p->total)</td>
                            <td class="right"><a class="btn btn-fantasma btn-sm" href="{{ route('cuenta.pedido', $p) }}">Ver detalle</a></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            {{ $pedidos->links() }}
        @endif
    </section>
</div>
@endsection

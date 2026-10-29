{{--
    Vista: tienda.checkout
    Finalizar compra: datos de entrega, medio de pago (simulado) y resumen del pedido.

    Usada por:  CheckoutController@show  (GET /checkout)
    Variables:  $cliente, $carrito, $metodos (Factura::METODOS_PAGO)
--}}
@extends('layouts.tienda')
@section('titulo', 'Finalizar compra')

@section('contenido')
<div class="cabecera-pagina"><div class="contenedor"><h1>Finalizar compra</h1><p>Confirma tus datos de entrega y el medio de pago.</p></div></div>
<div class="contenedor">
    <form method="POST" action="{{ route('checkout.store') }}" class="layout-compra">
        @csrf
        <div class="stack">
            <div class="caja">
                <h3>Datos de entrega</h3>
                <div class="form-grid">
                    <div class="campo completo">
                        <label for="direccion_envio">Dirección</label>
                        <input id="direccion_envio" class="input @error('direccion_envio') invalido @enderror" name="direccion_envio" value="{{ old('direccion_envio', $cliente->direccion) }}" placeholder="Ej: Cra. 15 # 93-60, Apto 402" required>
                        @error('direccion_envio')<span class="error">{{ $message }}</span>@enderror
                    </div>
                    <div class="campo">
                        <label for="ciudad_envio">Ciudad</label>
                        <input id="ciudad_envio" class="input @error('ciudad_envio') invalido @enderror" name="ciudad_envio" value="{{ old('ciudad_envio', $cliente->ciudad) }}" required>
                        @error('ciudad_envio')<span class="error">{{ $message }}</span>@enderror
                    </div>
                    <div class="campo">
                        <label for="telefono">Celular de contacto</label>
                        <input id="telefono" class="input @error('telefono') invalido @enderror" name="telefono" value="{{ old('telefono', $cliente->telefono) }}" inputmode="tel" required>
                        @error('telefono')<span class="error">{{ $message }}</span>@enderror
                    </div>
                    <div class="campo completo">
                        <label for="notas">Indicaciones para el mensajero <span class="muted">(opcional)</span></label>
                        <input id="notas" class="input" name="notas" value="{{ old('notas') }}" placeholder="Ej: dejar en portería">
                    </div>
                </div>
            </div>
            <div class="caja">
                <h3>Medio de pago</h3>
                <div class="opciones-pago">
                    @php($ayudas = ['nequi' => 'Pago inmediato desde tu app', 'pse' => 'Débito desde tu banco', 'tarjeta' => 'Visa, Mastercard, Amex', 'contraentrega' => 'Pagas al recibir'])
                    @foreach ($metodos as $k => $txt)
                        <label class="opcion">
                            <input type="radio" name="metodo_pago" value="{{ $k }}" @checked(old('metodo_pago', 'nequi') === $k)>
                            <span>{{ $txt }}<small>{{ $ayudas[$k] }}</small></span>
                        </label>
                    @endforeach
                </div>
                <p class="small muted" style="margin:12px 0 0">Entorno de pruebas: el pago se registra como aprobado sin conectarse a una pasarela real.</p>
            </div>
        </div>
        <aside class="caja resumen">
            <h3>Tu pedido</h3>
            @foreach ($carrito->detalles as $d)
                <div class="fila small"><span>{{ $d->cantidad }} × {{ $d->producto->nombre_producto }}</span><span>@cop($d->cantidad * $d->producto->precio_producto)</span></div>
            @endforeach
            @php($total = $carrito->detalles->sum(fn($d) => $d->cantidad * $d->producto->precio_producto))
            <div class="fila muted small" style="margin-top:8px"><span>Subtotal sin IVA</span><span>@cop($total / 1.19)</span></div>
            <div class="fila muted small"><span>IVA (19%)</span><span>@cop($total - $total / 1.19)</span></div>
            <div class="fila total"><span>Total</span><span>@cop($total)</span></div>
            <button class="btn btn-brasa btn-bloque" style="margin-top:16px" type="submit">Confirmar y pagar</button>
            <a class="btn btn-fantasma btn-bloque" style="margin-top:6px" href="{{ route('carrito') }}">Volver al carrito</a>
        </aside>
    </form>
</div>
@endsection

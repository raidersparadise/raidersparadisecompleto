<?php

/**
 * Raiders Paradise - Tienda en línea de accesorios premium para motociclistas.
 *
 * Controlador del carrito de compras del cliente.
 *
 * @package   RaidersParadise
 * @author    Equipo de desarrollo Raiders Paradise (ADSO - SENA)
 * @copyright 2026 Raiders Paradise
 * @license   MIT
 * @version   2.0.0
 * @since     2026-10-29
 */

namespace App\Http\Controllers;

use App\Models\DetalleCarrito;
use App\Models\Producto;
use App\Support\Tallas;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Gestiona el carrito persistente del cliente autenticado.
 *
 * Todas las rutas requieren sesión y rol Cliente. Antes de modificar una línea se
 * verifica que pertenezca al carrito del usuario.
 */
class CarritoController extends Controller
{
    /**
     * Devuelve el cliente del usuario autenticado, creándolo si hace falta.
     *
     * @param \Illuminate\Http\Request $request Petición HTTP actual.
     * @return \App\Models\Cliente
     */
    private function cliente(Request $request)
    {
        return $request->user()->clienteAsociado();
    }

    /**
     * Muestra el carrito con sus productos.
     *
     * @param \Illuminate\Http\Request $request Petición HTTP actual.
     * @return \Illuminate\Contracts\View\View
     */
    public function index(Request $request)
    {
        $carrito = $this->cliente($request)->carritoActivo()->load('detalles.producto.inventario', 'detalles.producto.marca', 'detalles.producto.tallas');

        return view('tienda.carrito', compact('carrito'));
    }

    /**
     * Agrega un producto al carrito en la talla elegida (o suma unidades si ya estaba en esa talla).
     *
     * Los productos con tallas (cascos, guantes, chaquetas, impermeables, botas) exigen elegir
     * S, M, L o XL; los accesorios se agregan en talla única. El stock se valida por talla.
     *
     * @param  \Illuminate\Http\Request  $request  Campos: cantidad (opcional), talla.
     * @param  \App\Models\Producto  $producto
     * @return \Illuminate\Http\RedirectResponse
     */
    public function agregar(Request $request, Producto $producto)
    {
        $producto->load('tallas', 'categoria', 'inventario');
        $opciones = $producto->tallas->pluck('talla')->all();

        if (! $producto->disponible) {
            return back()->with('error', 'Este producto no está disponible en este momento.');
        }

        $datos = $request->validate([
            'cantidad' => ['nullable', 'integer', 'min:1', 'max:20'],
            'talla' => [$producto->usaTallas() && $opciones ? 'required' : 'nullable', 'string', Rule::in($opciones ?: [Tallas::UNICA])],
        ], ['talla.required' => 'Elige una talla antes de agregar el producto al carrito.'], ['talla' => 'talla']);
        $cantidad = (int) ($datos['cantidad'] ?? 1);
        $talla = $datos['talla'] ?? ($opciones ? $opciones[0] : null);

        $stock = $talla !== null && $opciones ? $producto->stockTalla($talla) : $producto->stock;
        $etiqueta = $talla && $talla !== Tallas::UNICA ? " en talla {$talla}" : '';

        $carrito = $this->cliente($request)->carritoActivo();
        $detalle = $carrito->detalles()->firstOrNew(['id_producto' => $producto->id_producto, 'talla' => $talla]);
        $nueva = ($detalle->exists ? $detalle->cantidad : 0) + $cantidad;

        if ($nueva > $stock) {
            return back()->withInput()->with('error', $stock > 0
                ? "Solo hay {$stock} unidades disponibles de {$producto->nombre_producto}{$etiqueta}."
                : "{$producto->nombre_producto} está agotado{$etiqueta}. Prueba con otra talla.");
        }

        $detalle->fill([
            'cantidad' => $nueva,
            'precio_unitario' => $producto->precio_producto,
            'subtotal' => $nueva * $producto->precio_producto,
        ])->save();

        return back()->with('ok', "Agregaste {$producto->nombre_producto}{$etiqueta} al carrito.");
    }

    /**
     * Cambia la cantidad de una línea del carrito, validando el stock de su talla.
     *
     * @param  \Illuminate\Http\Request  $request  Campo: cantidad.
     * @param  \App\Models\DetalleCarrito  $detalle
     * @return \Illuminate\Http\RedirectResponse
     */
    public function actualizar(Request $request, DetalleCarrito $detalle)
    {
        $this->autorizar($request, $detalle);
        $datos = $request->validate(['cantidad' => ['required', 'integer', 'min:1', 'max:20']]);
        $producto = $detalle->producto->load('tallas', 'inventario');
        $stock = $detalle->talla !== null && $producto->tallas->isNotEmpty() ? $producto->stockTalla($detalle->talla) : $producto->stock;

        if ($datos['cantidad'] > $stock) {
            return back()->with('error', "Solo hay {$stock} unidades disponibles".($detalle->talla && $detalle->talla !== Tallas::UNICA ? " en talla {$detalle->talla}." : '.'));
        }

        $detalle->update([
            'cantidad' => $datos['cantidad'],
            'precio_unitario' => $producto->precio_producto,
            'subtotal' => $datos['cantidad'] * $producto->precio_producto,
        ]);

        return back()->with('ok', 'Cantidad actualizada.');
    }

    /**
     * Quita una línea del carrito.
     *
     * @param \Illuminate\Http\Request $request Petición HTTP actual.
     * @param DetalleCarrito $detalle Línea a eliminar.
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException 403 si la línea es de otro cliente.
     */
    public function eliminar(Request $request, DetalleCarrito $detalle)
    {
        $this->autorizar($request, $detalle);
        $detalle->delete();

        return back()->with('ok', 'Producto retirado del carrito.');
    }

    /**
     * Aborta con 403 si la línea no pertenece al carrito del usuario autenticado.
     *
     * @param \Illuminate\Http\Request $request Petición HTTP actual.
     * @param DetalleCarrito $detalle
     * @return void
     */
    private function autorizar(Request $request, DetalleCarrito $detalle): void
    {
        abort_unless($detalle->carrito->id_cliente === $request->user()->cliente?->id_cliente, 403);
    }
}

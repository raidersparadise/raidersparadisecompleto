<?php

/**
 * Raiders Paradise - Tienda en línea de accesorios premium para motociclistas.
 *
 * Controlador de la zona privada del cliente ("Mi cuenta").
 *
 * @package   RaidersParadise
 * @author    Equipo de desarrollo Raiders Paradise (ADSO - SENA)
 * @copyright 2026 Raiders Paradise
 * @license   MIT
 * @version   2.0.0
 * @since     2026-10-29
 */

namespace App\Http\Controllers;

use App\Models\Pedido;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * Pedidos, facturas, datos personales y PQR del usuario autenticado.
 *
 * Un cliente solo puede ver sus propios pedidos y facturas; el personal
 * (Administrador y Vendedor) puede ver cualquier factura.
 */
class CuentaController extends Controller
{
    /**
     * Lista paginada de los pedidos del cliente.
     *
     * @param \Illuminate\Http\Request $request Petición HTTP actual.
     * @return \Illuminate\Contracts\View\View
     */
    public function pedidos(Request $request)
    {
        $pedidos = Pedido::with('factura')
            ->where('id_cliente', $request->user()->cliente?->id_cliente)
            ->latest('fecha')->paginate(10);

        return view('cuenta.pedidos', compact('pedidos'));
    }

    /**
     * Detalle y seguimiento de un pedido del cliente.
     *
     * @param \Illuminate\Http\Request $request Petición HTTP actual.
     * @param Pedido $pedido
     * @return \Illuminate\Contracts\View\View
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException 403 si el pedido es de otro cliente.
     */
    public function pedido(Request $request, Pedido $pedido)
    {
        $this->autorizar($request, $pedido);
        $pedido->load('detalles.producto', 'factura', 'cliente');

        return view('cuenta.pedido', compact('pedido'));
    }

    /**
     * Factura imprimible de un pedido.
     *
     * @param \Illuminate\Http\Request $request Petición HTTP actual.
     * @param Pedido $pedido
     * @return \Illuminate\Contracts\View\View
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException 403 si un cliente intenta ver una factura ajena; 404 si el pedido no tiene factura.
     */
    public function factura(Request $request, Pedido $pedido)
    {
        if (! $request->user()->esPersonal()) {
            $this->autorizar($request, $pedido);
        }
        $pedido->load('detalles.producto', 'factura', 'cliente');
        abort_unless($pedido->factura, 404);

        return view('cuenta.factura', compact('pedido'));
    }

    /**
     * Formulario con los datos del usuario y de su registro de cliente.
     *
     * @param \Illuminate\Http\Request $request Petición HTTP actual.
     * @return \Illuminate\Contracts\View\View
     */
    public function perfil(Request $request)
    {
        $usuario = $request->user()->load('cliente');

        return view('cuenta.perfil', compact('usuario'));
    }

    /**
     * Guarda los datos personales y, si se envía, la nueva contraseña.
     *
     * Mantiene sincronizados los datos de las tablas usuario y cliente.
     *
     * @param \Illuminate\Http\Request $request Petición HTTP actual.
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Validation\ValidationException Si los datos no cumplen las reglas de validación.
     */
    public function actualizarPerfil(Request $request)
    {
        $usuario = $request->user();
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:40'],
            'apellido' => ['required', 'string', 'max:40'],
            'email' => ['required', 'email', 'max:100', Rule::unique('usuario', 'email')->ignore($usuario->id_usuario, 'id_usuario')],
            'telefono' => ['nullable', 'string', 'max:20'],
            'documento' => ['nullable', 'string', 'max:20'],
            'direccion' => ['nullable', 'string', 'max:100'],
            'ciudad' => ['nullable', 'string', 'max:60'],
            'password' => ['nullable', 'confirmed', Password::min(8)->letters()->numbers()],
        ]);

        $usuario->fill([
            'nombre_usuario' => $datos['nombre'],
            'apellido_usuario' => $datos['apellido'],
            'email' => $datos['email'],
            'telefono' => $datos['telefono'],
        ]);
        if (! empty($datos['password'])) {
            $usuario->password = $datos['password'];
        }
        $usuario->save();

        if ($usuario->cliente) {
            $usuario->cliente->update([
                'nombre_cliente' => $datos['nombre'],
                'apellido_cliente' => $datos['apellido'],
                'email' => $datos['email'],
                'telefono' => $datos['telefono'],
                'documento' => $datos['documento'],
                'direccion' => $datos['direccion'],
                'ciudad' => $datos['ciudad'],
            ]);
        }

        return back()->with('ok', 'Tus datos fueron actualizados.');
    }

    /**
     * Lista de las PQR radicadas por el cliente con sus respuestas.
     *
     * @param \Illuminate\Http\Request $request Petición HTTP actual.
     * @return \Illuminate\Contracts\View\View
     */
    public function pqrs(Request $request)
    {
        $pqrs = $request->user()->cliente?->pqrs()->latest('fecha')->get() ?? collect();

        return view('cuenta.pqrs', compact('pqrs'));
    }

    /**
     * Aborta con 403 si el pedido no pertenece al cliente autenticado.
     *
     * @param \Illuminate\Http\Request $request Petición HTTP actual.
     * @param Pedido $pedido
     * @return void
     */
    private function autorizar(Request $request, Pedido $pedido): void
    {
        abort_unless($pedido->id_cliente === $request->user()->cliente?->id_cliente, 403);
    }
}

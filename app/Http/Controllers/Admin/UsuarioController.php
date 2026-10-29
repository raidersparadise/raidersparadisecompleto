<?php

/**
 * Raiders Paradise - Tienda en línea de accesorios premium para motociclistas.
 *
 * Controlador de gestión de usuarios (solo Administrador).
 *
 * @package   RaidersParadise
 * @author    Equipo de desarrollo Raiders Paradise (ADSO - SENA)
 * @copyright 2026 Raiders Paradise
 * @license   MIT
 * @version   2.0.0
 * @since     2026-10-29
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * CRUD de cuentas de acceso y asignación de roles.
 *
 * Los usuarios no se borran: se desactivan para conservar la trazabilidad.
 */
class UsuarioController extends Controller
{
    /**
     * Lista de usuarios con filtro por rol.
     *
     * @param \Illuminate\Http\Request $request Petición HTTP actual.
     * @return \Illuminate\Contracts\View\View
     */
    public function index(Request $request)
    {
        $usuarios = Usuario::with('rol')
            ->when($request->query('rol'), fn ($w, $r) => $w->where('id_rol', $r))
            ->orderBy('id_rol')->orderBy('nombre_usuario')->paginate(20)->withQueryString();
        $roles = Rol::orderBy('id_rol')->get();

        return view('admin.usuarios.index', compact('usuarios', 'roles'));
    }

    /**
     * Formulario para crear un usuario.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function create()
    {
        return view('admin.usuarios.form', ['usuario' => new Usuario(['activo' => true]), 'roles' => Rol::orderBy('id_rol')->get()]);
    }

    /**
     * Crea el usuario; si es Cliente, también su registro en la tabla cliente.
     *
     * @param \Illuminate\Http\Request $request Petición HTTP actual.
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Validation\ValidationException Si los datos no cumplen las reglas de validación.
     */
    public function store(Request $request)
    {
        $datos = $this->validar($request);
        $usuario = Usuario::create($datos);
        if ($usuario->tieneRol(Rol::CLIENTE)) {
            $usuario->clienteAsociado();
        }

        return redirect()->route('admin.usuarios.index')->with('ok', 'Usuario creado.');
    }

    /**
     * Formulario para editar un usuario.
     *
     * @param Usuario $usuario
     * @return \Illuminate\Contracts\View\View
     */
    public function edit(Usuario $usuario)
    {
        return view('admin.usuarios.form', ['usuario' => $usuario, 'roles' => Rol::orderBy('id_rol')->get()]);
    }

    /**
     * Actualiza el usuario. La contraseña solo cambia si se envía una nueva.
     * Un administrador no puede desactivarse ni cambiar su propio rol.
     *
     * @param \Illuminate\Http\Request $request Petición HTTP actual.
     * @param Usuario $usuario
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Validation\ValidationException Si los datos no cumplen las reglas de validación.
     */
    public function update(Request $request, Usuario $usuario)
    {
        $datos = $this->validar($request, $usuario);
        if (empty($datos['password'])) {
            unset($datos['password']);
        }
        if ($usuario->is($request->user()) && (! $datos['activo'] || (int) $datos['id_rol'] !== $usuario->id_rol)) {
            return back()->with('error', 'No puedes desactivarte ni cambiar tu propio rol.');
        }
        $usuario->update($datos);

        return redirect()->route('admin.usuarios.index')->with('ok', 'Usuario actualizado.');
    }

    /**
     * Desactiva la cuenta (no la borra). No permite desactivar la propia cuenta.
     *
     * @param \Illuminate\Http\Request $request Petición HTTP actual.
     * @param Usuario $usuario
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request, Usuario $usuario)
    {
        if ($usuario->is($request->user())) {
            return back()->with('error', 'No puedes eliminar tu propia cuenta.');
        }
        // Se desactiva para conservar trazabilidad (reportes, PQR atendidas, pedidos)
        $usuario->update(['activo' => false]);

        return back()->with('ok', "La cuenta de {$usuario->nombre_completo} fue desactivada.");
    }

    /**
     * Reglas de validación del formulario de usuario.
     *
     * @param \Illuminate\Http\Request $request Petición HTTP actual.
     * @param Usuario|null $usuario Usuario en edición (la contraseña pasa a ser opcional).
     * @return array<string, mixed> Datos validados, con "activo" como booleano.
     * @throws \Illuminate\Validation\ValidationException Si los datos no cumplen las reglas de validación.
     */
    private function validar(Request $request, ?Usuario $usuario = null): array
    {
        $datos = $request->validate([
            'nombre_usuario' => ['required', 'string', 'max:40'],
            'apellido_usuario' => ['required', 'string', 'max:40'],
            'email' => ['required', 'email', 'max:100', Rule::unique('usuario', 'email')->ignore($usuario?->id_usuario, 'id_usuario')],
            'telefono' => ['nullable', 'string', 'max:20'],
            'id_rol' => ['required', 'exists:rol,id_rol'],
            'password' => [$usuario ? 'nullable' : 'required', 'confirmed', Password::min(8)->letters()->numbers()],
        ], [], ['nombre_usuario' => 'nombre', 'apellido_usuario' => 'apellido', 'id_rol' => 'rol', 'password' => 'contraseña']);
        $datos['activo'] = $request->boolean('activo');

        return $datos;
    }
}

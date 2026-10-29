<?php

/**
 * Raiders Paradise - Tienda en línea de accesorios premium para motociclistas.
 *
 * Controlador de autenticación: ingreso, registro y cierre de sesión.
 *
 * @package   RaidersParadise
 * @author    Equipo de desarrollo Raiders Paradise (ADSO - SENA)
 * @copyright 2026 Raiders Paradise
 * @license   MIT
 * @version   2.0.0
 * @since     2026-10-29
 */

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;

/**
 * Gestiona el acceso de los usuarios.
 *
 * Rutas: GET/POST /login, GET/POST /registro (solo invitados) y POST /logout.
 * El ingreso y el registro tienen límite de 6 intentos por minuto.
 */
class AuthController extends Controller
{
    /**
     * Muestra el formulario de ingreso.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function showLogin()
    {
        return view('auth.login');
    }

    /**
     * Valida las credenciales e inicia la sesión.
     *
     * Solo permite el ingreso de cuentas activas. Regenera la sesión para evitar
     * fijación de sesión y redirige al panel (personal) o a la tienda (clientes).
     *
     * @param \Illuminate\Http\Request $request Petición HTTP actual.
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Validation\ValidationException Si los datos no cumplen las reglas de validación.
     */
    public function login(Request $request)
    {
        $credenciales = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [], ['email' => 'correo', 'password' => 'contraseña']);

        if (! Auth::attempt($credenciales + ['activo' => true], $request->boolean('recordar'))) {
            return back()->withErrors(['email' => 'El correo o la contraseña no son correctos, o la cuenta está inactiva.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();
        $usuario = Auth::user();

        if ($usuario->esPersonal()) {
            return redirect()->intended(route('admin.dashboard'))->with('ok', "Bienvenido(a), {$usuario->nombre_usuario}.");
        }

        return redirect()->intended(route('home'))->with('ok', "¡Hola, {$usuario->nombre_usuario}! Qué bueno verte de nuevo.");
    }

    /**
     * Muestra el formulario de registro de clientes.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function showRegistro()
    {
        return view('auth.registro');
    }

    /**
     * Registra un nuevo cliente y lo deja con la sesión iniciada.
     *
     * Crea el usuario con rol Cliente y su registro en la tabla cliente. Si ya
     * existía un cliente con ese correo (sin cuenta), lo enlaza en vez de duplicarlo.
     *
     * @param \Illuminate\Http\Request $request Petición HTTP actual.
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Validation\ValidationException Si los datos no cumplen las reglas de validación.
     */
    public function registro(Request $request)
    {
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:40'],
            'apellido' => ['required', 'string', 'max:40'],
            'email' => ['required', 'email', 'max:100', 'unique:usuario,email'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
            'terminos' => ['accepted'],
        ], [
            'terminos.accepted' => 'Debes aceptar el tratamiento de datos personales.',
        ], ['email' => 'correo', 'password' => 'contraseña']);

        $usuario = DB::transaction(function () use ($datos) {
            $usuario = Usuario::create([
                'id_rol' => Rol::where('nombre_rol', Rol::CLIENTE)->value('id_rol'),
                'nombre_usuario' => $datos['nombre'],
                'apellido_usuario' => $datos['apellido'],
                'email' => $datos['email'],
                'telefono' => $datos['telefono'] ?? null,
                'password' => $datos['password'],
            ]);

            // Si ya existía como cliente (por ejemplo, radicó una PQR sin cuenta) se enlaza
            $cliente = Cliente::firstOrNew(['email' => $datos['email']]);
            $cliente->fill([
                'id_usuario' => $usuario->id_usuario,
                'nombre_cliente' => $datos['nombre'],
                'apellido_cliente' => $datos['apellido'],
                'telefono' => $datos['telefono'] ?? $cliente->telefono,
            ])->save();

            return $usuario;
        });

        Auth::login($usuario);
        $request->session()->regenerate();

        return redirect()->intended(route('catalogo'))->with('ok', '¡Tu cuenta fue creada! Ya puedes comprar.');
    }

    /**
     * Cierra la sesión, la invalida y regenera el token CSRF.
     *
     * @param \Illuminate\Http\Request $request Petición HTTP actual.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('ok', 'Sesión cerrada. ¡Buena ruta!');
    }
}

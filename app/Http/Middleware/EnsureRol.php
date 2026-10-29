<?php

/**
 * Raiders Paradise - Tienda en línea de accesorios premium para motociclistas.
 *
 * Middleware de autorización por rol.
 *
 * @package   RaidersParadise
 * @author    Equipo de desarrollo Raiders Paradise (ADSO - SENA)
 * @copyright 2026 Raiders Paradise
 * @license   MIT
 * @version   2.0.0
 * @since     2026-10-29
 */

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restringe una ruta a uno o varios roles.
 *
 * Registrado con el alias "rol" en bootstrap/app.php. Uso en rutas:
 * Route::middleware('rol:Administrador,Vendedor')
 *
 * Si un usuario del personal intenta entrar a rutas de cliente, se le redirige al
 * panel con un mensaje; en cualquier otro caso se responde 403.
 */
class EnsureRol
{
    /**
     * Verifica que el usuario autenticado tenga alguno de los roles permitidos.
     *
     * @param \Illuminate\Http\Request $request Petición HTTP actual.
     * @param Closure $next Siguiente middleware de la cadena.
     * @param string ...$roles Roles permitidos.
     * @return Response
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException 403 si no tiene permiso.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $usuario = $request->user();

        if (! $usuario || ! $usuario->tieneRol(...$roles)) {
            if ($usuario && in_array('Cliente', $roles, true)) {
                return redirect()->route('admin.dashboard')
                    ->with('error', 'El carrito y las compras son exclusivos de cuentas de cliente.');
            }
            abort(403, 'No tienes permisos para acceder a esta sección.');
        }

        return $next($request);
    }
}

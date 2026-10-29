<?php

/**
 * Raiders Paradise - Tienda en línea de accesorios premium para motociclistas.
 *
 * Controlador de atención de PQR en el panel administrativo.
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
use App\Models\Pqr;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Http\Request;

/**
 * Gestión de PQR: asignación de responsable, estado y respuesta al cliente.
 */
class PqrController extends Controller
{
    /**
     * Lista paginada con filtros por estado y tipo.
     *
     * @param \Illuminate\Http\Request $request Petición HTTP actual.
     * @return \Illuminate\Contracts\View\View
     */
    public function index(Request $request)
    {
        $pqrs = Pqr::with('cliente', 'responsable')
            ->when($request->query('estado'), fn ($w, $e) => $w->where('estado', $e))
            ->when($request->query('tipo'), fn ($w, $t) => $w->where('tipo', $t))
            ->latest('fecha')->latest('id_pqr')->paginate(15)->withQueryString();

        return view('admin.pqr.index', compact('pqrs'));
    }

    /**
     * Detalle de la PQR con el formulario de gestión.
     *
     * @param Pqr $pqr
     * @return \Illuminate\Contracts\View\View
     */
    public function show(Pqr $pqr)
    {
        $pqr->load('cliente', 'responsable');
        $personal = Usuario::whereHas('rol', fn ($r) => $r->whereIn('nombre_rol', [Rol::ADMIN, Rol::VENDEDOR]))->orderBy('nombre_usuario')->get();

        return view('admin.pqr.show', compact('pqr', 'personal'));
    }

    /**
     * Guarda la gestión de la PQR.
     *
     * Reglas: la respuesta es obligatoria para cerrar o rechazar; al asignar un
     * responsable a una PQR "recibida" pasa a "asignada"; al cerrar o rechazar se
     * registra la fecha de cierre.
     *
     * @param \Illuminate\Http\Request $request Petición HTTP actual.
     * @param Pqr $pqr
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Validation\ValidationException Si los datos no cumplen las reglas de validación.
     */
    public function update(Request $request, Pqr $pqr)
    {
        $datos = $request->validate([
            'estado' => ['required', 'in:'.implode(',', Pqr::ESTADOS)],
            'id_usuario' => ['nullable', 'exists:usuario,id_usuario'],
            'respuesta' => ['nullable', 'string', 'max:2000', 'required_if:estado,cerrada,rechazada'],
        ], ['respuesta.required_if' => 'Escribe la respuesta al cliente antes de cerrar o rechazar la PQR.']);

        if ($datos['estado'] === 'recibida' && ! empty($datos['id_usuario'])) {
            $datos['estado'] = 'asignada';
        }
        $datos['fecha_cierre'] = in_array($datos['estado'], ['cerrada', 'rechazada']) ? ($pqr->fecha_cierre ?? now()) : null;

        $pqr->update($datos);

        return back()->with('ok', "PQR {$pqr->radicado} actualizada.");
    }
}

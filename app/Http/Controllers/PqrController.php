<?php

/**
 * Raiders Paradise - Tienda en línea de accesorios premium para motociclistas.
 *
 * Controlador público de radicación de PQR.
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
use App\Models\Pqr;
use Illuminate\Http\Request;

/**
 * Formulario para radicar peticiones, quejas, reclamos y sugerencias.
 *
 * Los visitantes sin cuenta deben dar sus datos y autorizar el tratamiento de
 * datos personales; se crea o reutiliza su registro de cliente por correo.
 */
class PqrController extends Controller
{
    /**
     * Muestra el formulario de PQR.
     *
     * @param \Illuminate\Http\Request $request Petición HTTP actual.
     * @return \Illuminate\Contracts\View\View
     */
    public function create(Request $request)
    {
        $usuario = $request->user();

        return view('tienda.pqr', ['usuario' => $usuario, 'tipos' => Pqr::TIPOS]);
    }

    /**
     * Valida y registra la PQR en estado "recibida" y muestra el radicado.
     *
     * Limitado a 10 envíos por minuto.
     *
     * @param \Illuminate\Http\Request $request Petición HTTP actual.
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Validation\ValidationException Si los datos no cumplen las reglas de validación.
     */
    public function store(Request $request)
    {
        $usuario = $request->user();
        $reglas = [
            'tipo' => ['required', 'in:'.implode(',', array_keys(Pqr::TIPOS))],
            'asunto' => ['required', 'string', 'max:120'],
            'descripcion' => ['required', 'string', 'min:10', 'max:1000'],
        ];
        if (! $usuario) {
            $reglas += [
                'nombre' => ['required', 'string', 'max:40'],
                'apellido' => ['required', 'string', 'max:40'],
                'email' => ['required', 'email', 'max:150'],
                'telefono' => ['nullable', 'string', 'max:20'],
                'terminos' => ['accepted'],
            ];
        }
        $datos = $request->validate($reglas, ['terminos.accepted' => 'Debes autorizar el tratamiento de datos.'], ['descripcion' => 'descripción']);

        if ($usuario) {
            $cliente = $usuario->clienteAsociado();
        } else {
            $cliente = Cliente::firstOrCreate(['email' => $datos['email']], [
                'nombre_cliente' => $datos['nombre'],
                'apellido_cliente' => $datos['apellido'],
                'telefono' => $datos['telefono'] ?? null,
            ]);
        }

        $pqr = Pqr::create([
            'id_cliente' => $cliente->id_cliente,
            'tipo' => $datos['tipo'],
            'asunto' => $datos['asunto'],
            'descripcion_pqr' => $datos['descripcion'],
            'estado' => 'recibida',
            'fecha' => now()->toDateString(),
        ]);

        return redirect()->route('pqr.create')->with('ok', "Recibimos tu solicitud con radicado {$pqr->radicado}. Te responderemos en máximo 15 días hábiles.");
    }
}

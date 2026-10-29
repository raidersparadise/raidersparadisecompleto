<?php

/**
 * Raiders Paradise - Tienda en línea de accesorios premium para motociclistas.
 *
 * Controlador genérico del CRUD de tablas maestras.
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
use App\Models\Categoria;
use App\Models\Marca;
use App\Models\Proveedor;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * CRUD genérico de categorías, marcas y proveedores.
 *
 * Rutas: /admin/maestros/{tipo}, donde {tipo} es categorias, marcas o proveedores.
 * La configuración de cada tabla (modelo, campos y reglas) está en CONFIG, de modo
 * que una sola clase y dos vistas sirven para las tres tablas.
 */
class MaestroController extends Controller
{
    /**
     * Configuración por tipo: modelo, tabla, llave, textos, orden y campos.
     * Cada campo: [etiqueta, tipo de input, reglas de validación, ¿único?].
     *
     * @var array<string, array<string, mixed>>
     */
    private const CONFIG = [
        'categorias' => [
            'modelo' => Categoria::class, 'tabla' => 'categoria', 'pk' => 'id_categoria',
            'singular' => 'categoría', 'plural' => 'Categorías', 'orden' => 'nombre_categoria',
            'campos' => [
                'nombre_categoria' => ['Nombre', 'text', ['required', 'string', 'max:40'], true],
                'descripcion_categoria' => ['Descripción', 'textarea', ['nullable', 'string', 'max:255'], false],
            ],
        ],
        'marcas' => [
            'modelo' => Marca::class, 'tabla' => 'marca', 'pk' => 'id_marca',
            'singular' => 'marca', 'plural' => 'Marcas', 'orden' => 'nombre_marca',
            'campos' => [
                'nombre_marca' => ['Nombre', 'text', ['required', 'string', 'max:40'], true],
                'descripcion_marca' => ['Descripción', 'textarea', ['nullable', 'string', 'max:255'], false],
            ],
        ],
        'proveedores' => [
            'modelo' => Proveedor::class, 'tabla' => 'proveedor', 'pk' => 'id_proveedor',
            'singular' => 'proveedor', 'plural' => 'Proveedores', 'orden' => 'nombre_proveedor',
            'campos' => [
                'nombre_proveedor' => ['Razón social', 'text', ['required', 'string', 'max:80'], false],
                'nit' => ['NIT', 'text', ['nullable', 'string', 'max:20'], false],
                'telefono_proveedor' => ['Teléfono', 'text', ['nullable', 'string', 'max:20'], false],
                'email_proveedor' => ['Correo', 'email', ['nullable', 'email', 'max:100'], false],
                'direccion_proveedor' => ['Dirección', 'text', ['nullable', 'string', 'max:100'], false],
            ],
        ],
    ];

    /**
     * Configuración del tipo solicitado.
     *
     * @param string $tipo categorias | marcas | proveedores
     * @return array<string, mixed>
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException Si el tipo no existe.
     */
    private function cfg(string $tipo): array
    {
        abort_unless(isset(self::CONFIG[$tipo]), 404);

        return self::CONFIG[$tipo] + ['tipo' => $tipo];
    }

    /**
     * Lista los registros con el número de productos asociados.
     *
     * @param string $tipo
     * @return \Illuminate\Contracts\View\View
     */
    public function index(string $tipo)
    {
        $cfg = $this->cfg($tipo);
        $registros = $cfg['modelo']::withCount('productos')->orderBy($cfg['orden'])->get();

        return view('admin.maestros.index', compact('cfg', 'registros'));
    }

    /**
     * Formulario para crear un registro.
     *
     * @param string $tipo
     * @return \Illuminate\Contracts\View\View
     */
    public function create(string $tipo)
    {
        $cfg = $this->cfg($tipo);
        $registro = new $cfg['modelo']();

        return view('admin.maestros.form', compact('cfg', 'registro'));
    }

    /**
     * Valida y crea un registro.
     *
     * @param \Illuminate\Http\Request $request Petición HTTP actual.
     * @param string $tipo
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Validation\ValidationException Si los datos no cumplen las reglas de validación.
     */
    public function store(Request $request, string $tipo)
    {
        $cfg = $this->cfg($tipo);
        $cfg['modelo']::create($request->validate($this->reglas($cfg)));

        return redirect()->route('admin.maestros.index', $tipo)->with('ok', ucfirst($cfg['singular']).' creada correctamente.');
    }

    /**
     * Formulario para editar un registro.
     *
     * @param string $tipo
     * @param int $id Llave primaria del registro.
     * @return \Illuminate\Contracts\View\View
     */
    public function edit(string $tipo, int $id)
    {
        $cfg = $this->cfg($tipo);
        $registro = $cfg['modelo']::findOrFail($id);

        return view('admin.maestros.form', compact('cfg', 'registro'));
    }

    /**
     * Valida y guarda los cambios de un registro.
     *
     * @param \Illuminate\Http\Request $request Petición HTTP actual.
     * @param string $tipo
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Validation\ValidationException Si los datos no cumplen las reglas de validación.
     */
    public function update(Request $request, string $tipo, int $id)
    {
        $cfg = $this->cfg($tipo);
        $registro = $cfg['modelo']::findOrFail($id);
        $registro->update($request->validate($this->reglas($cfg, $id)));

        return redirect()->route('admin.maestros.index', $tipo)->with('ok', 'Cambios guardados.');
    }

    /**
     * Elimina un registro, salvo que tenga productos asociados.
     *
     * @param string $tipo
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(string $tipo, int $id)
    {
        $cfg = $this->cfg($tipo);
        $registro = $cfg['modelo']::findOrFail($id);

        if ($registro->productos()->exists()) {
            return back()->with('error', "No se puede eliminar: hay productos asociados a esta {$cfg['singular']}.");
        }
        $registro->delete();

        return back()->with('ok', 'Registro eliminado.');
    }

    /**
     * Construye las reglas de validación a partir de la configuración, agregando la
     * regla de valor único (ignorando el registro en edición) donde corresponde.
     *
     * @param array<string, mixed> $cfg
     * @param int|null $id Registro en edición.
     * @return array<string, array<int, mixed>>
     */
    private function reglas(array $cfg, ?int $id = null): array
    {
        $reglas = [];
        foreach ($cfg['campos'] as $campo => [$label, $tipoCampo, $r, $unico]) {
            if ($unico) {
                $r[] = Rule::unique($cfg['tabla'], $campo)->ignore($id, $cfg['pk']);
            }
            $reglas[$campo] = $r;
        }

        return $reglas;
    }
}

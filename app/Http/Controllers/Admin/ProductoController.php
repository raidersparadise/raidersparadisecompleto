<?php

/**
 * Raiders Paradise - Tienda en línea de accesorios premium para motociclistas.
 *
 * Controlador del CRUD de productos del panel administrativo.
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
use App\Models\Etiqueta;
use App\Models\Inventario;
use App\Models\Marca;
use App\Models\Producto;
use App\Models\ProductoTalla;
use App\Models\Proveedor;
use App\Support\ImagenOptimizada;
use App\Support\Tallas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Crear, editar, listar y eliminar productos con su imagen e inventario.
 */
class ProductoController extends Controller
{
    /**
     * Lista paginada con búsqueda por nombre o SKU y filtros de categoría y estado.
     *
     * @param \Illuminate\Http\Request $request Petición HTTP actual.
     * @return \Illuminate\Contracts\View\View
     */
    public function index(Request $request)
    {
        $q = $request->query('q');
        $productos = Producto::with('categoria', 'marca', 'inventario')
            ->when($q, fn ($w) => $w->where(fn ($x) => $x->where('nombre_producto', 'like', "%{$q}%")->orWhere('sku', 'like', "%{$q}%")))
            ->when($request->query('categoria'), fn ($w, $c) => $w->where('id_categoria', $c))
            ->when($request->query('estado'), fn ($w, $e) => $w->where('estado_producto', $e))
            ->orderBy('nombre_producto')
            ->paginate(15)->withQueryString();

        $categorias = Categoria::orderBy('nombre_categoria')->get();

        return view('admin.productos.index', compact('productos', 'categorias'));
    }

    /**
     * Formulario para crear un producto.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function create()
    {
        return view('admin.productos.form', $this->opciones() + ['producto' => new Producto(['estado_producto' => 'activo'])]);
    }

    /**
     * Crea el producto y su registro de inventario en una transacción.
     *
     * @param \Illuminate\Http\Request $request Petición HTTP actual.
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Validation\ValidationException Si los datos no cumplen las reglas de validación.
     */
    public function store(Request $request)
    {
        $datos = $this->validar($request);

        DB::transaction(function () use ($request, $datos) {
            $producto = Producto::create($this->datosProducto($request, $datos));
            Inventario::create([
                'id_producto' => $producto->id_producto,
                'cantidad_disponible' => 0,
                'cantidad_minima' => $datos['cantidad_minima'],
            ]);
            $this->guardarTallas($producto, $datos);
            $this->guardarGaleria($request, $producto);
            $producto->etiquetas()->sync(Etiqueta::idsDesdeTexto($request->input('etiquetas')));
        });

        return redirect()->route('admin.productos.index')->with('ok', 'Producto creado correctamente.');
    }

    /**
     * Formulario para editar un producto.
     *
     * @param Producto $producto
     * @return \Illuminate\Contracts\View\View
     */
    public function edit(Producto $producto)
    {
        $producto->load('inventario', 'tallas', 'imagenes', 'vistas360', 'etiquetas');

        return view('admin.productos.form', $this->opciones() + compact('producto'));
    }

    /**
     * Actualiza el producto y su inventario en una transacción.
     *
     * @param \Illuminate\Http\Request $request Petición HTTP actual.
     * @param Producto $producto
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Validation\ValidationException Si los datos no cumplen las reglas de validación.
     */
    public function update(Request $request, Producto $producto)
    {
        $datos = $this->validar($request, $producto);

        DB::transaction(function () use ($request, $datos, $producto) {
            $producto->update($this->datosProducto($request, $datos, $producto));
            Inventario::updateOrCreate(
                ['id_producto' => $producto->id_producto],
                ['cantidad_minima' => $datos['cantidad_minima']]
            );
            $this->guardarTallas($producto, $datos);
            $this->guardarGaleria($request, $producto);
            $producto->etiquetas()->sync(Etiqueta::idsDesdeTexto($request->input('etiquetas')));
        });

        return redirect()->route('admin.productos.index')->with('ok', 'Producto actualizado.');
    }

    /**
     * Elimina un producto; si tiene ventas registradas lo marca como inactivo.
     *
     * @param Producto $producto
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Producto $producto)
    {
        // Si ya tiene ventas no se borra (se perdería el historial): se inactiva
        if ($producto->detallesPedido()->exists()) {
            $producto->update(['estado_producto' => 'inactivo']);

            return back()->with('ok', 'El producto tiene ventas registradas, por eso se marcó como inactivo en lugar de eliminarlo.');
        }

        $producto->delete();

        return back()->with('ok', 'Producto eliminado.');
    }

    /**
     * Reglas de validación del formulario de producto (SKU único, precio, imagen de hasta 8 MB, etiquetas...).
     *
     * @param \Illuminate\Http\Request $request Petición HTTP actual.
     * @param Producto|null $producto Producto en edición, para excluirlo de la validación de SKU único.
     * @return array<string, mixed> Datos validados.
     * @throws \Illuminate\Validation\ValidationException Si los datos no cumplen las reglas de validación.
     */
    private function validar(Request $request, ?Producto $producto = null): array
    {
        return $request->validate([
            'sku' => ['required', 'string', 'max:30', Rule::unique('producto', 'sku')->ignore($producto?->id_producto, 'id_producto')],
            'nombre_producto' => ['required', 'string', 'max:120'],
            'descripcion_producto' => ['nullable', 'string', 'max:1000'],
            'precio_producto' => ['required', 'numeric', 'min:1000', 'max:99999999'],
            'id_categoria' => ['required', 'exists:categoria,id_categoria'],
            'id_marca' => ['required', 'exists:marca,id_marca'],
            'id_proveedor' => ['required', 'exists:proveedor,id_proveedor'],
            'estado_producto' => ['required', 'in:activo,inactivo'],
            'destacado' => ['nullable', 'boolean'],
            'comentario_producto' => ['nullable', 'string', 'max:255'],
            'imagen' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
            'stock_tallas' => ['required', 'array'],
            'stock_tallas.*' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'imagenes' => ['nullable', 'array', 'max:8'],
            'imagenes.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
            'etiquetas' => ['nullable', 'string', 'max:255'],
            'quitar_imagenes' => ['nullable', 'array'],
            'quitar_imagenes.*' => ['integer'],
            'vistas360' => ['nullable', 'array', 'max:72'],
            'vistas360.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:1024'],
            'quitar_360' => ['nullable', 'boolean'],
            'cantidad_minima' => ['required', 'integer', 'min:0', 'max:10000'],
        ], [], [
            'nombre_producto' => 'nombre', 'precio_producto' => 'precio', 'id_categoria' => 'categoría',
            'id_marca' => 'marca', 'id_proveedor' => 'proveedor', 'stock_tallas.*' => 'unidades por talla', 'imagenes.*' => 'foto adicional', 'vistas360.*' => 'cuadro 360°',
            'cantidad_minima' => 'cantidad mínima',
        ]);
    }

    /**
     * Prepara los atributos a guardar y procesa la imagen subida.
     *
     * La imagen se guarda en public/uploads/productos (no requiere storage:link) y
     * se borra la imagen anterior si también era un archivo subido.
     *
     * @param \Illuminate\Http\Request $request Petición HTTP actual.
     * @param array<string, mixed> $datos Datos validados.
     * @param Producto|null $producto Producto en edición.
     * @return array<string, mixed> Atributos para Producto::create() o update().
     */
    private function datosProducto(Request $request, array $datos, ?Producto $producto = null): array
    {
        $campos = collect($datos)->except(['imagen', 'stock_tallas', 'cantidad_minima', 'imagenes', 'quitar_imagenes', 'vistas360', 'quitar_360', 'etiquetas'])->all();
        $campos['destacado'] = $request->boolean('destacado');

        if ($request->hasFile('imagen')) {
            // Se comprime (WebP, máx. 1200 px, sin EXIF/GPS) y se guarda en public/uploads
            // para que funcione sin "php artisan storage:link"
            $img = ImagenOptimizada::guardar($request->file('imagen'), 'uploads/productos', $datos['nombre_producto']);

            if ($producto && str_starts_with((string) $producto->imagen_producto, 'uploads/')) {
                File::delete(public_path($producto->imagen_producto));
            }
            $campos['imagen_producto'] = $img['ruta'];
        }

        return $campos;
    }

        /**
     * Guarda las unidades por talla según la categoría (S–XL o talla única)
     * y deja el inventario total igual a la suma de las tallas.
     *
     * @param  \App\Models\Producto  $producto
     * @param  array<string, mixed>  $datos  Datos validados (incluye stock_tallas).
     * @return void
     */
    private function guardarTallas(Producto $producto, array $datos): void
    {
        $categoria = Categoria::find($datos['id_categoria'])?->nombre_categoria;
        $tallas = Tallas::paraCategoria($categoria);
        $stock = $datos['stock_tallas'] ?? [];

        // Si cambia de "con tallas" a "talla única" (o al revés) se conserva el total
        $anterior = (int) ProductoTalla::where('id_producto', $producto->id_producto)->sum('cantidad_disponible');
        $sinDatos = collect($tallas)->every(fn ($t) => ! array_key_exists($t, $stock));
        if ($sinDatos) {
            $stock = $tallas === [Tallas::UNICA] ? [Tallas::UNICA => $anterior] : Tallas::repartir($anterior);
        }

        ProductoTalla::where('id_producto', $producto->id_producto)->whereNotIn('talla', $tallas)->delete();
        foreach ($tallas as $orden => $talla) {
            ProductoTalla::updateOrCreate(
                ['id_producto' => $producto->id_producto, 'talla' => $talla],
                ['cantidad_disponible' => (int) ($stock[$talla] ?? 0), 'orden' => $orden]
            );
        }
        $producto->sincronizarStock();
    }

    /**
     * Agrega las fotos adicionales subidas, elimina las marcadas y guarda los cuadros de la vista 360°.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Producto  $producto
     * @return void
     */
    private function guardarGaleria(Request $request, Producto $producto): void
    {
        foreach ($producto->imagenes()->whereIn('id_producto_imagen', $request->input('quitar_imagenes', []))->get() as $img) {
            if (str_starts_with($img->ruta, 'uploads/')) {
                File::delete(public_path($img->ruta));
            }
            $img->delete();
        }

        $orden = (int) $producto->imagenes()->max('orden');
        foreach ($request->file('imagenes', []) as $archivo) {
            $img = ImagenOptimizada::guardar($archivo, 'uploads/productos', $producto->nombre_producto.'-galeria');
            $producto->imagenes()->create([
                'ruta' => $img['ruta'], 'tipo' => 'galeria', 'orden' => ++$orden,
                'ancho' => $img['ancho'], 'alto' => $img['alto'], 'peso_bytes' => $img['peso_bytes'],
                'camara' => $img['camara'], 'fecha_toma' => $img['fecha_toma'],
            ]);
        }

        // Vista 360°: una nueva tanda de cuadros reemplaza la anterior (orden = nombre del archivo)
        $cuadros = $request->file('vistas360', []);
        if ($request->boolean('quitar_360') || $cuadros) {
            foreach ($producto->vistas360()->get() as $img) {
                if (str_starts_with($img->ruta, 'uploads/')) {
                    File::delete(public_path($img->ruta));
                }
                $img->delete();
            }
        }
        if ($cuadros) {
            usort($cuadros, fn ($a, $b) => strnatcasecmp($a->getClientOriginalName(), $b->getClientOriginalName()));
            $carpeta360 = 'uploads/productos/360/'.Str::slug($producto->sku);
            foreach ($cuadros as $n => $archivo) {
                $nombre = str_pad((string) ($n + 1), 2, '0', STR_PAD_LEFT).'-'.Str::random(4);
                $img = ImagenOptimizada::guardarComo($archivo, $carpeta360, $nombre);
                $producto->vistas360()->create([
                    'ruta' => $img['ruta'], 'tipo' => '360', 'orden' => $n + 1,
                    'ancho' => $img['ancho'], 'alto' => $img['alto'], 'peso_bytes' => $img['peso_bytes'],
                ]);
            }
        }
    }

    /**
     * Listas para los selectores del formulario: categorías, marcas y proveedores.
     *
     * @return array<string, \Illuminate\Database\Eloquent\Collection>
     */
    private function opciones(): array
    {
        return [
            'categorias' => Categoria::orderBy('nombre_categoria')->get(),
            'marcas' => Marca::orderBy('nombre_marca')->get(),
            'proveedores' => Proveedor::orderBy('nombre_proveedor')->get(),
        ];
    }
}

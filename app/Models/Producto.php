<?php

/**
 * Raiders Paradise - Tienda en línea de accesorios premium para motociclistas.
 *
 * Modelo Eloquent de la tabla "producto".
 *
 * @package   RaidersParadise
 * @author    Equipo de desarrollo Raiders Paradise (ADSO - SENA)
 * @copyright 2026 Raiders Paradise
 * @license   MIT
 * @version   2.0.0
 * @since     2026-10-29
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Producto del catálogo.
 *
 * Los precios se guardan en pesos colombianos e incluyen IVA. Un producto con
 * ventas no se elimina: se marca como "inactivo" para conservar el historial.
 *
 * @property int $id_producto
 * @property int $id_categoria
 * @property int $id_marca
 * @property int $id_proveedor
 * @property string $sku
 * @property string $nombre_producto
 * @property string|null $descripcion_producto
 * @property string $precio_producto
 * @property string $estado_producto Valores: "activo" o "inactivo".
 * @property bool $destacado
 * @property string|null $imagen_producto Ruta relativa a public/.
 * @property string|null $comentario_producto Nota interna, no visible en la tienda.
 * @property-read int $stock
 * @property-read bool $disponible
 * @property-read string $imagen_url
 */
class Producto extends Model
{
    /**
     * Nombre de la tabla en la base de datos.
     *
     * @var string
     */
    protected $table = 'producto';

    /**
     * Llave primaria personalizada (convención del proyecto: id_<tabla>).
     *
     * @var string
     */
    protected $primaryKey = 'id_producto';

    /**
     * Atributos que se pueden asignar masivamente con create() o fill().
     *
     * @var list<string>
     */
    protected $fillable = [
        'id_categoria', 'id_marca', 'id_proveedor', 'sku', 'nombre_producto', 'descripcion_producto',
        'precio_producto', 'estado_producto', 'destacado', 'imagen_producto', 'comentario_producto',
    ];

    /**
     * Conversión automática de tipos al leer y escribir atributos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'precio_producto' => 'decimal:2',
        'destacado' => 'boolean',
    ];

    /**
     * Categoría a la que pertenece el producto.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Categoria, $this>
     */
    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'id_categoria', 'id_categoria');
    }

    /**
     * Marca del producto.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Marca, $this>
     */
    public function marca()
    {
        return $this->belongsTo(Marca::class, 'id_marca', 'id_marca');
    }

    /**
     * Proveedor del producto.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Proveedor, $this>
     */
    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'id_proveedor', 'id_proveedor');
    }

    /**
     * Registro de existencias del producto.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne<\App\Models\Inventario, $this>
     */
    public function inventario()
    {
        return $this->hasOne(Inventario::class, 'id_producto', 'id_producto');
    }

    /**
     * Líneas de pedido en las que se ha vendido el producto.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\DetallePedido, $this>
     */
    public function detallesPedido()
    {
        return $this->hasMany(DetallePedido::class, 'id_producto', 'id_producto');
    }

    /**
     * Scope de consulta: solo productos visibles en la tienda.
     *
     * Uso: Producto::activos()->get()
     *
     * @param Builder<Producto> $q Consulta en construcción.
     * @return Builder<Producto>
     */
    public function scopeActivos(Builder $q): Builder
    {
        return $q->where('estado_producto', 'activo');
    }

    /**
     * Accesor $producto->stock: unidades disponibles (0 si no tiene inventario).
     *
     * @return int
     */
    public function getStockAttribute(): int
    {
        return (int) ($this->inventario->cantidad_disponible ?? 0);
    }

    /**
     * Accesor $producto->disponible: activo y con al menos una unidad.
     *
     * @return bool
     */
    public function getDisponibleAttribute(): bool
    {
        return $this->estado_producto === 'activo' && $this->stock > 0;
    }

    /**
     * Accesor $producto->imagen_url: URL pública de la imagen.
     *
     * Admite ilustraciones de muestra (images/...), archivos subidos (uploads/...)
     * y URLs externas. Si no hay imagen devuelve una ilustración genérica.
     *
     * @return string URL absoluta.
     */
    public function getImagenUrlAttribute(): string
    {
        $img = $this->imagen_producto;
        if (! $img) {
            return asset('images/productos/generico.svg');
        }
        if (str_starts_with($img, 'http')) {
            return $img;
        }

        return asset($img);
    }
}

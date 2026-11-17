<?php

/**
 * Raiders Paradise - Tienda en línea de accesorios premium para motociclistas.
 *
 * Modelo Eloquent de la tabla "producto_imagen".
 *
 * @package   RaidersParadise
 * @author    Equipo de desarrollo Raiders Paradise (ADSO - SENA)
 * @copyright 2026 Raiders Paradise
 * @license   MIT
 * @version   2.2.0
 * @since     2026-11-04
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Foto adicional de un producto para la galería (la principal sigue en producto.imagen_producto).
 *
 * @property int $id_producto_imagen
 * @property int $id_producto
 * @property string $ruta Ruta relativa a public/ (ej. images/productos/galeria/x.webp o uploads/productos/x.jpg).
 * @property string $tipo "galeria" o "360" (cuadro de la vista 360°).
 * @property int $orden
 * @property-read string $url
 */
class ProductoImagen extends Model
{
    /** @var string */
    protected $table = 'producto_imagen';

    /** @var string */
    protected $primaryKey = 'id_producto_imagen';

    /** @var array<int, string> */
    protected $fillable = ['id_producto', 'ruta', 'tipo', 'orden', 'ancho', 'alto', 'peso_bytes', 'camara', 'fecha_toma'];

    /**
     * Producto al que pertenece la foto.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function producto()
    {
        return $this->belongsTo(Producto::class, 'id_producto', 'id_producto');
    }

    /**
     * URL pública de la foto.
     *
     * @return string
     */
    public function getUrlAttribute(): string
    {
        return str_starts_with($this->ruta, 'http') ? $this->ruta : asset($this->ruta);
    }
}

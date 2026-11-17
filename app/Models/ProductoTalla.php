<?php

/**
 * Raiders Paradise - Tienda en línea de accesorios premium para motociclistas.
 *
 * Modelo Eloquent de la tabla "producto_talla".
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
 * Existencias de un producto en una talla concreta.
 * La suma de todas las tallas de un producto es su inventario total.
 *
 * @property int $id_producto_talla
 * @property int $id_producto
 * @property string $talla S, M, L, XL o "Única".
 * @property int $cantidad_disponible
 * @property int $orden
 */
class ProductoTalla extends Model
{
    /** @var string */
    protected $table = 'producto_talla';

    /** @var string */
    protected $primaryKey = 'id_producto_talla';

    /** @var array<int, string> */
    protected $fillable = ['id_producto', 'talla', 'cantidad_disponible', 'orden'];

    /** @var array<string, string> */
    protected $casts = ['cantidad_disponible' => 'integer', 'orden' => 'integer'];

    /**
     * Producto al que pertenece la talla.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function producto()
    {
        return $this->belongsTo(Producto::class, 'id_producto', 'id_producto');
    }
}

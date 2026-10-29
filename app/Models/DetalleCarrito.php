<?php

/**
 * Raiders Paradise - Tienda en línea de accesorios premium para motociclistas.
 *
 * Modelo Eloquent de la tabla "detalle_carrito".
 *
 * @package   RaidersParadise
 * @author    Equipo de desarrollo Raiders Paradise (ADSO - SENA)
 * @copyright 2026 Raiders Paradise
 * @license   MIT
 * @version   2.0.0
 * @since     2026-10-29
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Línea del carrito: un producto con su cantidad y precio.
 *
 * @property int $id_detalle_carrito
 * @property int $id_carrito
 * @property int $id_producto
 * @property int $cantidad
 * @property string $precio_unitario
 * @property string $subtotal
 */
class DetalleCarrito extends Model
{
    /**
     * Nombre de la tabla en la base de datos.
     *
     * @var string
     */
    protected $table = 'detalle_carrito';

    /**
     * Llave primaria personalizada (convención del proyecto: id_<tabla>).
     *
     * @var string
     */
    protected $primaryKey = 'id_detalle_carrito';

    /**
     * Atributos que se pueden asignar masivamente con create() o fill().
     *
     * @var list<string>
     */
    protected $fillable = ['id_carrito', 'id_producto', 'cantidad', 'precio_unitario', 'subtotal'];

    /**
     * Carrito al que pertenece la línea.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Carrito, $this>
     */
    public function carrito()
    {
        return $this->belongsTo(Carrito::class, 'id_carrito', 'id_carrito');
    }

    /**
     * Producto agregado.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Producto, $this>
     */
    public function producto()
    {
        return $this->belongsTo(Producto::class, 'id_producto', 'id_producto');
    }
}

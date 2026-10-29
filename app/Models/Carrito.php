<?php

/**
 * Raiders Paradise - Tienda en línea de accesorios premium para motociclistas.
 *
 * Modelo Eloquent de la tabla "carrito".
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
 * Carrito de compras persistente de un cliente.
 *
 * @property int $id_carrito
 * @property int $id_cliente
 * @property string $fecha_agregado
 * @property-read float $total
 * @property-read int $cantidad_items
 */
class Carrito extends Model
{
    /**
     * Nombre de la tabla en la base de datos.
     *
     * @var string
     */
    protected $table = 'carrito';

    /**
     * Llave primaria personalizada (convención del proyecto: id_<tabla>).
     *
     * @var string
     */
    protected $primaryKey = 'id_carrito';

    /**
     * Atributos que se pueden asignar masivamente con create() o fill().
     *
     * @var list<string>
     */
    protected $fillable = ['id_cliente', 'fecha_agregado'];

    /**
     * Cliente dueño del carrito.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Cliente, $this>
     */
    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'id_cliente', 'id_cliente');
    }

    /**
     * Productos agregados al carrito.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\DetalleCarrito, $this>
     */
    public function detalles()
    {
        return $this->hasMany(DetalleCarrito::class, 'id_carrito', 'id_carrito');
    }

    /**
     * Accesor $carrito->total: suma de los subtotales de las líneas.
     *
     * @return float Valor en pesos con IVA incluido.
     */
    public function getTotalAttribute(): float
    {
        return (float) $this->detalles->sum('subtotal');
    }

    /**
     * Accesor $carrito->cantidad_items: número total de unidades.
     *
     * @return int
     */
    public function getCantidadItemsAttribute(): int
    {
        return (int) $this->detalles->sum('cantidad');
    }
}

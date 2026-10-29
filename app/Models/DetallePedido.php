<?php

/**
 * Raiders Paradise - Tienda en línea de accesorios premium para motociclistas.
 *
 * Modelo Eloquent de la tabla "detalle_pedido".
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
 * Línea de un pedido: producto, cantidad y precio al momento de la compra.
 *
 * @property int $id_detalle_pedido
 * @property int $id_pedido
 * @property int $id_producto
 * @property int $cantidad
 * @property string $precio_unitario
 * @property string $sub_total
 */
class DetallePedido extends Model
{
    /**
     * Nombre de la tabla en la base de datos.
     *
     * @var string
     */
    protected $table = 'detalle_pedido';

    /**
     * Llave primaria personalizada (convención del proyecto: id_<tabla>).
     *
     * @var string
     */
    protected $primaryKey = 'id_detalle_pedido';

    /**
     * Atributos que se pueden asignar masivamente con create() o fill().
     *
     * @var list<string>
     */
    protected $fillable = ['id_pedido', 'id_producto', 'cantidad', 'precio_unitario', 'sub_total'];

    /**
     * Pedido al que pertenece la línea.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Pedido, $this>
     */
    public function pedido()
    {
        return $this->belongsTo(Pedido::class, 'id_pedido', 'id_pedido');
    }

    /**
     * Producto vendido.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Producto, $this>
     */
    public function producto()
    {
        return $this->belongsTo(Producto::class, 'id_producto', 'id_producto');
    }
}

<?php

/**
 * Raiders Paradise - Tienda en línea de accesorios premium para motociclistas.
 *
 * Modelo Eloquent de la tabla "inventario".
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
 * Existencias de un producto (un registro por producto).
 *
 * @property int $id_inventario
 * @property int $id_producto
 * @property int $cantidad_disponible
 * @property int $cantidad_minima Umbral para la alerta de reposición.
 * @property-read string $nivel
 */
class Inventario extends Model
{
    /**
     * Nombre de la tabla en la base de datos.
     *
     * @var string
     */
    protected $table = 'inventario';

    /**
     * Llave primaria personalizada (convención del proyecto: id_<tabla>).
     *
     * @var string
     */
    protected $primaryKey = 'id_inventario';

    /**
     * Atributos que se pueden asignar masivamente con create() o fill().
     *
     * @var list<string>
     */
    protected $fillable = ['id_producto', 'cantidad_disponible', 'cantidad_minima'];

    /**
     * Producto al que corresponden las existencias.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Producto, $this>
     */
    public function producto()
    {
        return $this->belongsTo(Producto::class, 'id_producto', 'id_producto');
    }

    /**
     * Accesor $inventario->nivel: estado de las existencias.
     *
     * @return string "agotado" (0 unidades), "bajo" (igual o menor al mínimo) u "ok".
     */
    public function getNivelAttribute(): string
    {
        if ($this->cantidad_disponible <= 0) {
            return 'agotado';
        }

        return $this->cantidad_disponible <= $this->cantidad_minima ? 'bajo' : 'ok';
    }
}

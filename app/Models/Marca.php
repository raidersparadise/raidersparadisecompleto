<?php

/**
 * Raiders Paradise - Tienda en línea de accesorios premium para motociclistas.
 *
 * Modelo Eloquent de la tabla "marca".
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
 * Marca o fabricante de los productos (Shoei, AGV, Alpinestars, etc.).
 *
 * @property int $id_marca
 * @property string $nombre_marca
 * @property string|null $descripcion_marca
 */
class Marca extends Model
{
    /**
     * Nombre de la tabla en la base de datos.
     *
     * @var string
     */
    protected $table = 'marca';

    /**
     * Llave primaria personalizada (convención del proyecto: id_<tabla>).
     *
     * @var string
     */
    protected $primaryKey = 'id_marca';

    /**
     * Atributos que se pueden asignar masivamente con create() o fill().
     *
     * @var list<string>
     */
    protected $fillable = ['nombre_marca', 'descripcion_marca'];

    /**
     * Productos de la marca.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Producto, $this>
     */
    public function productos()
    {
        return $this->hasMany(Producto::class, 'id_marca', 'id_marca');
    }
}

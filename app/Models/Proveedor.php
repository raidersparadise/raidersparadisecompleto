<?php

/**
 * Raiders Paradise - Tienda en línea de accesorios premium para motociclistas.
 *
 * Modelo Eloquent de la tabla "proveedor".
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
 * Proveedor que abastece los productos de la tienda.
 *
 * @property int $id_proveedor
 * @property string|null $nit
 * @property string $nombre_proveedor
 * @property string|null $telefono_proveedor
 * @property string|null $direccion_proveedor
 * @property string|null $email_proveedor
 */
class Proveedor extends Model
{
    /**
     * Nombre de la tabla en la base de datos.
     *
     * @var string
     */
    protected $table = 'proveedor';

    /**
     * Llave primaria personalizada (convención del proyecto: id_<tabla>).
     *
     * @var string
     */
    protected $primaryKey = 'id_proveedor';

    /**
     * Atributos que se pueden asignar masivamente con create() o fill().
     *
     * @var list<string>
     */
    protected $fillable = ['nit', 'nombre_proveedor', 'telefono_proveedor', 'direccion_proveedor', 'email_proveedor'];

    /**
     * Productos suministrados por el proveedor.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Producto, $this>
     */
    public function productos()
    {
        return $this->hasMany(Producto::class, 'id_proveedor', 'id_proveedor');
    }
}

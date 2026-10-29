<?php

/**
 * Raiders Paradise - Tienda en línea de accesorios premium para motociclistas.
 *
 * Modelo Eloquent de la tabla "categoria".
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
 * Categoría del catálogo (Cascos, Guantes, Chaquetas, Impermeables, Botas, Accesorios).
 *
 * @property int $id_categoria
 * @property string $nombre_categoria
 * @property string|null $descripcion_categoria
 */
class Categoria extends Model
{
    /**
     * Nombre de la tabla en la base de datos.
     *
     * @var string
     */
    protected $table = 'categoria';

    /**
     * Llave primaria personalizada (convención del proyecto: id_<tabla>).
     *
     * @var string
     */
    protected $primaryKey = 'id_categoria';

    /**
     * Atributos que se pueden asignar masivamente con create() o fill().
     *
     * @var list<string>
     */
    protected $fillable = ['nombre_categoria', 'descripcion_categoria'];

    // Nota: el modelo original referenciaba Producto::class, que no existía. Ya fue creado.
    /**
     * Productos de la categoría.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Producto, $this>
     */
    public function productos()
    {
        return $this->hasMany(Producto::class, 'id_categoria', 'id_categoria');
    }
}

<?php

/**
 * Raiders Paradise - Tienda en línea de accesorios premium para motociclistas.
 *
 * Modelo Eloquent de la tabla "rol": perfiles de acceso del sistema.
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
 * Rol de usuario del sistema.
 *
 * Define los tres perfiles de acceso: Administrador, Vendedor y Cliente.
 * La autorización por rol se aplica con el middleware {@see \App\Http\Middleware\EnsureRol}.
 *
 * @property int $id_rol
 * @property string $nombre_rol
 * @property string|null $descripcion
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Usuario> $usuarios
 */
class Rol extends Model
{
    /**
     * Nombre del rol con acceso total (incluye reportes y usuarios).
     *
     * @var string
     */
    public const ADMIN = 'Administrador';

    /**
     * Nombre del rol de personal de ventas e inventario.
     *
     * @var string
     */
    public const VENDEDOR = 'Vendedor';

    /**
     * Nombre del rol de los compradores de la tienda.
     *
     * @var string
     */
    public const CLIENTE = 'Cliente';

    /**
     * Nombre de la tabla en la base de datos.
     *
     * @var string
     */
    protected $table = 'rol';

    /**
     * Llave primaria personalizada (convención del proyecto: id_<tabla>).
     *
     * @var string
     */
    protected $primaryKey = 'id_rol';

    /**
     * Atributos que se pueden asignar masivamente con create() o fill().
     *
     * @var list<string>
     */
    protected $fillable = ['nombre_rol', 'descripcion'];

    /**
     * Usuarios que tienen este rol.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Usuario, $this>
     */
    public function usuarios()
    {
        return $this->hasMany(Usuario::class, 'id_rol', 'id_rol');
    }
}

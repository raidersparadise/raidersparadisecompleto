<?php

/**
 * Raiders Paradise - Tienda en línea de accesorios premium para motociclistas.
 *
 * Modelo Eloquent de la tabla "cliente".
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
 * Cliente de la tienda.
 *
 * Puede existir sin cuenta web (por ejemplo, si radicó una PQR como visitante)
 * o estar enlazado a un {@see Usuario} mediante id_usuario.
 *
 * @property int $id_cliente
 * @property int|null $id_usuario
 * @property string|null $documento
 * @property string $nombre_cliente
 * @property string $apellido_cliente
 * @property string $email
 * @property string|null $telefono
 * @property string|null $direccion
 * @property string|null $ciudad
 * @property-read string $nombre_completo
 */
class Cliente extends Model
{
    /**
     * Nombre de la tabla en la base de datos.
     *
     * @var string
     */
    protected $table = 'cliente';

    /**
     * Llave primaria personalizada (convención del proyecto: id_<tabla>).
     *
     * @var string
     */
    protected $primaryKey = 'id_cliente';

    /**
     * Atributos que se pueden asignar masivamente con create() o fill().
     *
     * @var list<string>
     */
    protected $fillable = [
        'id_usuario', 'documento', 'nombre_cliente', 'apellido_cliente', 'email', 'telefono', 'direccion', 'ciudad',
    ];

    /**
     * Cuenta de acceso del cliente, si tiene.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Usuario, $this>
     */
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }

    /**
     * Pedidos realizados por el cliente.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Pedido, $this>
     */
    public function pedidos()
    {
        return $this->hasMany(Pedido::class, 'id_cliente', 'id_cliente');
    }

    /**
     * Carrito de compras activo (uno por cliente).
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne<\App\Models\Carrito, $this>
     */
    public function carrito()
    {
        return $this->hasOne(Carrito::class, 'id_cliente', 'id_cliente');
    }

    /**
     * Peticiones, quejas y reclamos radicados por el cliente.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Pqr, $this>
     */
    public function pqrs()
    {
        return $this->hasMany(Pqr::class, 'id_cliente', 'id_cliente');
    }

    /**
     * Accesor $cliente->nombre_completo: nombre y apellido.
     *
     * @return string
     */
    public function getNombreCompletoAttribute(): string
    {
        return trim($this->nombre_cliente.' '.$this->apellido_cliente);
    }

    /**
     * Devuelve el carrito activo del cliente o lo crea vacío si no existe.
     *
     * @return Carrito
     */
    public function carritoActivo(): Carrito
    {
        return $this->carrito()->firstOrCreate([], ['fecha_agregado' => now()->toDateString()]);
    }
}

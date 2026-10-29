<?php

/**
 * Raiders Paradise - Tienda en línea de accesorios premium para motociclistas.
 *
 * Modelo Eloquent de la tabla "pedido".
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
 * Pedido de compra de un cliente.
 *
 * Flujo de estados: pendiente -> programado -> en_curso -> entregado, o cancelado.
 * La lógica de creación y cambio de estado está en {@see \App\Services\PedidoService}.
 *
 * @property int $id_pedido
 * @property int $id_cliente
 * @property \Illuminate\Support\Carbon $fecha
 * @property string $estado
 * @property string $total
 * @property string|null $direccion_envio
 * @property string|null $ciudad_envio
 * @property string|null $notas
 * @property-read string $estado_texto
 * @property-read string $codigo
 */
class Pedido extends Model
{
    /**
     * Estados posibles del pedido y su texto para mostrar al usuario.
     *
     * @var array<string, string>
     */
    public const ESTADOS = [
        'pendiente' => 'Pendiente',
        'programado' => 'Programado',
        'en_curso' => 'En camino',
        'entregado' => 'Entregado',
        'cancelado' => 'Cancelado',
    ];

    /**
     * Nombre de la tabla en la base de datos.
     *
     * @var string
     */
    protected $table = 'pedido';

    /**
     * Llave primaria personalizada (convención del proyecto: id_<tabla>).
     *
     * @var string
     */
    protected $primaryKey = 'id_pedido';

    /**
     * Atributos que se pueden asignar masivamente con create() o fill().
     *
     * @var list<string>
     */
    protected $fillable = ['id_cliente', 'fecha', 'estado', 'total', 'direccion_envio', 'ciudad_envio', 'notas'];

    /**
     * Conversión automática de tipos al leer y escribir atributos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'fecha' => 'datetime',
        'total' => 'decimal:2',
    ];

    /**
     * Cliente que hizo el pedido.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Cliente, $this>
     */
    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'id_cliente', 'id_cliente');
    }

    /**
     * Productos del pedido.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\DetallePedido, $this>
     */
    public function detalles()
    {
        return $this->hasMany(DetallePedido::class, 'id_pedido', 'id_pedido');
    }

    /**
     * Factura generada para el pedido.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne<\App\Models\Factura, $this>
     */
    public function factura()
    {
        return $this->hasOne(Factura::class, 'id_pedido', 'id_pedido');
    }

    /**
     * Accesor $pedido->estado_texto: estado legible (por ejemplo "En camino").
     *
     * @return string
     */
    public function getEstadoTextoAttribute(): string
    {
        return self::ESTADOS[$this->estado] ?? ucfirst($this->estado);
    }

    /**
     * Accesor $pedido->codigo: código visible del pedido.
     *
     * @return string Formato RP-00000, por ejemplo "RP-00086".
     */
    public function getCodigoAttribute(): string
    {
        return 'RP-'.str_pad((string) $this->id_pedido, 5, '0', STR_PAD_LEFT);
    }
}

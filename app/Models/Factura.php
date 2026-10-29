<?php

/**
 * Raiders Paradise - Tienda en línea de accesorios premium para motociclistas.
 *
 * Modelo Eloquent de la tabla "factura".
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
 * Factura de venta de un pedido (documento de prueba, sin validez ante la DIAN).
 *
 * @property int $id_factura
 * @property int $id_pedido
 * @property string $numero_factura Formato FE-000000.
 * @property \Illuminate\Support\Carbon $fecha_factura
 * @property string $subtotal Valor sin IVA.
 * @property string $impuesto IVA del 19 %.
 * @property string $total_factura
 * @property string $estado_factura Valores: pagada, pendiente o anulada.
 * @property string $pago Valor recaudado.
 * @property string $metodo_pago Clave de {@see Factura::METODOS_PAGO}.
 * @property-read string $metodo_pago_texto
 */
class Factura extends Model
{
    /**
     * Tarifa de IVA aplicada en Colombia (19 %). Los precios del catálogo ya la incluyen.
     *
     * @var float
     */
    public const IVA = 0.19;

    /**
     * Medios de pago aceptados (simulados) y su texto para mostrar.
     *
     * @var array<string, string>
     */
    public const METODOS_PAGO = [
        'nequi' => 'Nequi',
        'pse' => 'PSE',
        'tarjeta' => 'Tarjeta crédito / débito',
        'contraentrega' => 'Pago contra entrega',
    ];

    /**
     * Nombre de la tabla en la base de datos.
     *
     * @var string
     */
    protected $table = 'factura';

    /**
     * Llave primaria personalizada (convención del proyecto: id_<tabla>).
     *
     * @var string
     */
    protected $primaryKey = 'id_factura';

    /**
     * Atributos que se pueden asignar masivamente con create() o fill().
     *
     * @var list<string>
     */
    protected $fillable = [
        'id_pedido', 'numero_factura', 'fecha_factura', 'subtotal', 'impuesto', 'total_factura',
        'estado_factura', 'pago', 'metodo_pago',
    ];

    /**
     * Conversión automática de tipos al leer y escribir atributos.
     *
     * @var array<string, string>
     */
    protected $casts = ['fecha_factura' => 'datetime'];

    /**
     * Pedido facturado.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Pedido, $this>
     */
    public function pedido()
    {
        return $this->belongsTo(Pedido::class, 'id_pedido', 'id_pedido');
    }

    /**
     * Accesor $factura->metodo_pago_texto: nombre legible del medio de pago.
     *
     * @return string
     */
    public function getMetodoPagoTextoAttribute(): string
    {
        return self::METODOS_PAGO[$this->metodo_pago] ?? $this->metodo_pago;
    }
}

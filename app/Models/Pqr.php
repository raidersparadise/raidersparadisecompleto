<?php

/**
 * Raiders Paradise - Tienda en línea de accesorios premium para motociclistas.
 *
 * Modelo Eloquent de la tabla "pqr".
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
 * Petición, queja, reclamo o sugerencia radicada por un cliente.
 *
 * @property int $id_pqr
 * @property int|null $id_usuario Empleado responsable de atenderla.
 * @property int $id_cliente
 * @property string $tipo
 * @property string $asunto
 * @property string $descripcion_pqr
 * @property string $estado
 * @property string|null $respuesta
 * @property \Illuminate\Support\Carbon $fecha
 * @property \Illuminate\Support\Carbon|null $fecha_cierre
 * @property-read string $tipo_texto
 * @property-read string $radicado
 */
class Pqr extends Model
{
    /**
     * Tipos de solicitud y su texto para mostrar.
     *
     * @var array<string, string>
     */
    public const TIPOS = ['peticion' => 'Petición', 'queja' => 'Queja', 'reclamo' => 'Reclamo', 'sugerencia' => 'Sugerencia'];

    /**
     * Estados del ciclo de atención de una PQR.
     *
     * @var list<string>
     */
    public const ESTADOS = ['recibida', 'asignada', 'en proceso', 'requerido', 'cerrada', 'rechazada'];

    /**
     * Nombre de la tabla en la base de datos.
     *
     * @var string
     */
    protected $table = 'pqr';

    /**
     * Llave primaria personalizada (convención del proyecto: id_<tabla>).
     *
     * @var string
     */
    protected $primaryKey = 'id_pqr';

    /**
     * Atributos que se pueden asignar masivamente con create() o fill().
     *
     * @var list<string>
     */
    protected $fillable = ['id_usuario', 'id_cliente', 'tipo', 'asunto', 'descripcion_pqr', 'estado', 'respuesta', 'fecha', 'fecha_cierre'];

    /**
     * Conversión automática de tipos al leer y escribir atributos.
     *
     * @var array<string, string>
     */
    protected $casts = ['fecha' => 'date', 'fecha_cierre' => 'datetime'];

    /**
     * Cliente que radicó la solicitud.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Cliente, $this>
     */
    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'id_cliente', 'id_cliente');
    }

    /**
     * Empleado asignado para atenderla.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Usuario, $this>
     */
    public function responsable()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }

    /**
     * Accesor $pqr->tipo_texto: tipo legible (por ejemplo "Reclamo").
     *
     * @return string
     */
    public function getTipoTextoAttribute(): string
    {
        return self::TIPOS[$this->tipo] ?? $this->tipo;
    }

    /**
     * Accesor $pqr->radicado: número de radicado visible.
     *
     * @return string Formato PQR-0000, por ejemplo "PQR-0008".
     */
    public function getRadicadoAttribute(): string
    {
        return 'PQR-'.str_pad((string) $this->id_pqr, 4, '0', STR_PAD_LEFT);
    }
}

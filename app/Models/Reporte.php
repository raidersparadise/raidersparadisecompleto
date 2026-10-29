<?php

/**
 * Raiders Paradise - Tienda en línea de accesorios premium para motociclistas.
 *
 * Modelo Eloquent de la tabla "reporte".
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
 * Registro de auditoría de cada reporte generado por el administrador.
 *
 * @property int $id_reporte
 * @property int $id_usuario
 * @property string $tipo_reporte
 * @property string $formato Valores: pantalla, pdf o csv.
 * @property \Illuminate\Support\Carbon|null $fecha_desde
 * @property \Illuminate\Support\Carbon|null $fecha_hasta
 * @property \Illuminate\Support\Carbon $fecha_generacion
 */
class Reporte extends Model
{
    /**
     * Nombre de la tabla en la base de datos.
     *
     * @var string
     */
    protected $table = 'reporte';

    /**
     * Llave primaria personalizada (convención del proyecto: id_<tabla>).
     *
     * @var string
     */
    protected $primaryKey = 'id_reporte';

    /**
     * Atributos que se pueden asignar masivamente con create() o fill().
     *
     * @var list<string>
     */
    protected $fillable = ['id_usuario', 'tipo_reporte', 'formato', 'fecha_desde', 'fecha_hasta', 'fecha_generacion'];

    /**
     * Conversión automática de tipos al leer y escribir atributos.
     *
     * @var array<string, string>
     */
    protected $casts = ['fecha_desde' => 'date', 'fecha_hasta' => 'date', 'fecha_generacion' => 'datetime'];

    /**
     * Usuario que generó el reporte.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Usuario, $this>
     */
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }
}

<?php

/**
 * Raiders Paradise - Tienda en línea de accesorios premium para motociclistas.
 *
 * Modelo Etiqueta: palabras clave de los productos para el buscador.
 *
 * @package   RaidersParadise
 * @author    Equipo de desarrollo Raiders Paradise (ADSO - SENA)
 * @copyright 2026 Raiders Paradise
 * @license   MIT
 * @version   2.3.0
 * @since     2026-11-07
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * @property int $id_etiqueta
 * @property string $nombre
 * @property string $slug
 */
class Etiqueta extends Model
{
    /** Máximo de etiquetas por producto. */
    public const MAXIMO = 10;

    /** @var string */
    protected $table = 'etiqueta';

    /** @var string */
    protected $primaryKey = 'id_etiqueta';

    /** @var list<string> */
    protected $fillable = ['nombre', 'slug'];

    /**
     * Productos que tienen esta etiqueta.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function productos()
    {
        return $this->belongsToMany(Producto::class, 'producto_etiqueta', 'id_etiqueta', 'id_producto');
    }

    /**
     * Convierte "Cuero, touring ,Impermeable" en ids de etiqueta (crea las que no existan).
     *
     * @param  string|null  $texto  Etiquetas separadas por coma.
     * @return array<int, int>
     */
    public static function idsDesdeTexto(?string $texto): array
    {
        return collect(explode(',', (string) $texto))
            ->map(fn ($t) => Str::limit(trim(preg_replace('/\s+/', ' ', mb_strtolower($t))), 40, ''))
            ->filter(fn ($t) => Str::slug($t) !== '')
            ->unique(fn ($t) => Str::slug($t))
            ->take(self::MAXIMO)
            ->map(fn ($t) => static::firstOrCreate(['slug' => Str::slug($t)], ['nombre' => $t])->id_etiqueta)
            ->values()
            ->all();
    }
}

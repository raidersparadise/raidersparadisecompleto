<?php

/**
 * Raiders Paradise - Tienda en línea de accesorios premium para motociclistas.
 *
 * Comando: importa fotos principales, de galería y de la vista 360° desde carpetas.
 *
 * @package   RaidersParadise
 * @author    Equipo de desarrollo Raiders Paradise (ADSO - SENA)
 * @copyright 2026 Raiders Paradise
 * @license   MIT
 * @version   2.3.0
 * @since     2026-11-04
 */

namespace App\Console\Commands;

use App\Models\Producto;
use App\Support\CatalogoNuevo;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * php artisan productos:importar-fotos
 *
 * Carpetas que revisa (dentro de public/images/productos):
 *   nuevos/<SKU>.webp|jpg|png     Foto principal. Si el producto estaba "pendiente de foto", lo publica.
 *   galeria/<SKU>/*.webp|jpg|png  Fotos adicionales de la galería (se agregan las que falten).
 *   360/<SKU>/*.webp|jpg|png      Cuadros de la vista 360°, en orden de giro por nombre
 *                                 (01.jpg, 02.jpg, …). Reemplazan la vista 360° anterior.
 *
 * El SKU de la carpeta puede ir en mayúsculas o minúsculas (CAS-SHO-001 o cas-sho-001).
 */
class ImportarFotos extends Command
{
    /** @var string */
    protected $signature = 'productos:importar-fotos {--sku= : Importar solo este SKU}';

    /** @var string */
    protected $description = 'Registra fotos principales, de galería y cuadros 360° dejados en public/images/productos';

    /** Extensiones de imagen aceptadas. */
    private const EXT = ['webp', 'jpg', 'jpeg', 'png', 'avif'];

    /**
     * Ejecuta el comando.
     *
     * @return int
     */
    public function handle(): int
    {
        $productos = Producto::query()
            ->when($this->option('sku'), fn ($q, $sku) => $q->where('sku', $sku))
            ->orderBy('id_producto')->get();
        $filas = [];

        foreach ($productos as $p) {
            $principal = $this->principal($p);
            $galeria = $this->galeria($p);
            $vista = $this->vista360($p);
            if ($principal || $galeria || $vista !== null) {
                $filas[] = [$p->sku, $p->nombre_producto, $principal ? 'sí' : '—', $galeria ?: '—', $vista ?? '—', $p->fresh()->estado_producto];
            }
        }

        if (! $filas) {
            $this->warn('No se encontraron fotos nuevas. Revisa las carpetas nuevos/, galeria/<SKU>/ y 360/<SKU>/ en public/images/productos.');

            return self::SUCCESS;
        }

        $this->table(['SKU', 'Producto', 'Foto principal', 'Fotos galería', 'Cuadros 360°', 'Estado'], $filas);
        $this->info('Fotos importadas.');

        return self::SUCCESS;
    }

    /**
     * Foto principal en nuevos/<SKU>.ext. Publica el producto si estaba pendiente de foto.
     *
     * @param  \App\Models\Producto  $p
     * @return bool
     */
    private function principal(Producto $p): bool
    {
        $ruta = CatalogoNuevo::fotoPrincipal($p->sku);
        if (! $ruta || $ruta === $p->imagen_producto) {
            return false;
        }
        $datos = ['imagen_producto' => $ruta];
        if ($p->comentario_producto === CatalogoNuevo::PENDIENTE) {
            $datos += ['estado_producto' => 'activo', 'comentario_producto' => null];
        }
        $p->update($datos);

        return true;
    }

    /**
     * Fotos de galeria/<SKU>/ que aún no estén registradas.
     *
     * @param  \App\Models\Producto  $p
     * @return int Fotos agregadas.
     */
    private function galeria(Producto $p): int
    {
        $archivos = $this->archivos('galeria', $p->sku);
        $orden = (int) $p->imagenes()->max('orden');
        $nuevas = 0;
        foreach ($archivos as $ruta) {
            if (! $p->imagenes()->where('ruta', $ruta)->exists()) {
                $p->imagenes()->create(['ruta' => $ruta, 'tipo' => 'galeria', 'orden' => ++$orden]);
                $nuevas++;
            }
        }

        return $nuevas;
    }

    /**
     * Cuadros de 360/<SKU>/ (reemplazan la vista anterior si cambió).
     *
     * @param  \App\Models\Producto  $p
     * @return int|null Cuadros registrados, o null si no hay cambios.
     */
    private function vista360(Producto $p): ?int
    {
        $archivos = $this->archivos('360', $p->sku);
        if (count($archivos) < 2 || $archivos === $p->vistas360()->pluck('ruta')->all()) {
            return null;
        }
        DB::transaction(function () use ($p, $archivos) {
            $p->vistas360()->delete();
            foreach ($archivos as $n => $ruta) {
                $p->vistas360()->create(['ruta' => $ruta, 'tipo' => '360', 'orden' => $n + 1]);
            }
        });

        return count($archivos);
    }

    /**
     * Imágenes de images/productos/<carpeta>/<SKU>/ en orden natural (1, 2, … 10).
     *
     * @param  string  $carpeta  "galeria" o "360".
     * @param  string  $sku
     * @return array<int, string> Rutas relativas a public/.
     */
    private function archivos(string $carpeta, string $sku): array
    {
        foreach ([$sku, strtolower($sku)] as $nombre) {
            $dir = public_path("images/productos/{$carpeta}/{$nombre}");
            if (is_dir($dir)) {
                $lista = array_values(array_filter(scandir($dir), fn ($f) => in_array(strtolower(pathinfo($f, PATHINFO_EXTENSION)), self::EXT, true)));
                natsort($lista);

                return array_map(fn ($f) => "images/productos/{$carpeta}/{$nombre}/{$f}", array_values($lista));
            }
        }

        return [];
    }
}

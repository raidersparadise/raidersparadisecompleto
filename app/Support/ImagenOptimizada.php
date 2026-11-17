<?php

/**
 * Raiders Paradise - Tienda en línea de accesorios premium para motociclistas.
 *
 * Optimización de imágenes subidas desde el panel: lee los metadatos (EXIF),
 * endereza la foto, la reduce a un ancho máximo, elimina EXIF/GPS y la guarda en WebP.
 *
 * Usa Intervention Image si está instalado (composer require intervention/image-laravel:^1.5);
 * si no, usa directamente la extensión GD de PHP, así que funciona sin dependencias nuevas.
 *
 * @package   RaidersParadise
 * @author    Equipo de desarrollo Raiders Paradise (ADSO - SENA)
 * @copyright 2026 Raiders Paradise
 * @license   MIT
 * @version   2.1.0
 * @since     2026-11-07
 */

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Comprime imágenes y extrae sus metadatos.
 */
class ImagenOptimizada
{
    /** Ancho máximo en píxeles (las imágenes más pequeñas no se agrandan). */
    public const ANCHO_MAX = 1200;

    /** Calidad WebP/JPEG (0-100). 80 es casi imperceptible y reduce mucho el peso. */
    public const CALIDAD = 80;

    /**
     * Guarda la imagen con un nombre "slug-aleatorio.webp".
     *
     * @param  UploadedFile  $archivo  Archivo subido.
     * @param  string  $carpeta  Carpeta relativa a public/ (ej. "uploads/productos").
     * @param  string  $nombreBase  Texto para el nombre del archivo (se convierte en slug).
     * @return array{ruta:string, ancho:int, alto:int, peso_bytes:int, peso_original:int, camara:?string, fecha_toma:?string, tenia_gps:bool}
     */
    public static function guardar(UploadedFile $archivo, string $carpeta, string $nombreBase): array
    {
        return self::guardarComo($archivo, $carpeta, Str::slug($nombreBase).'-'.Str::random(6));
    }

    /**
     * Guarda la imagen con el nombre indicado (sin extensión).
     *
     * @param  UploadedFile  $archivo  Archivo subido.
     * @param  string  $carpeta  Carpeta relativa a public/.
     * @param  string  $nombre  Nombre del archivo sin extensión.
     * @return array{ruta:string, ancho:int, alto:int, peso_bytes:int, peso_original:int, camara:?string, fecha_toma:?string, tenia_gps:bool}
     */
    public static function guardarComo(UploadedFile $archivo, string $carpeta, string $nombre): array
    {
        $carpeta = trim($carpeta, '/');
        $dir = public_path($carpeta);
        File::ensureDirectoryExists($dir);

        $origen = $archivo->getRealPath();
        $metadatos = self::metadatos($origen);

        $resultado = class_exists(\Intervention\Image\Laravel\Facades\Image::class)
            ? self::conIntervention($origen, $dir, $nombre)
            : self::conGd($origen, $dir, $nombre);

        return [
            'ruta' => $carpeta.'/'.$resultado['archivo'],
            'ancho' => $resultado['ancho'],
            'alto' => $resultado['alto'],
            'peso_bytes' => (int) filesize($dir.'/'.$resultado['archivo']),
            'peso_original' => (int) filesize($origen),
        ] + $metadatos;
    }

    /**
     * Lee cámara, fecha de captura y si la foto traía ubicación GPS (solo JPEG trae EXIF).
     *
     * @param  string  $ruta  Ruta del archivo.
     * @return array{camara:?string, fecha_toma:?string, tenia_gps:bool}
     */
    public static function metadatos(string $ruta): array
    {
        $exif = self::leerExif($ruta);

        $camara = trim(($exif['Make'] ?? '').' '.($exif['Model'] ?? ''));

        return [
            'camara' => $camara !== '' ? Str::limit($camara, 80, '') : null,
            'fecha_toma' => isset($exif['DateTimeOriginal']) ? Str::limit((string) $exif['DateTimeOriginal'], 30, '') : null,
            'tenia_gps' => isset($exif['GPSLatitude']) || isset($exif['GPSLongitude']),
        ];
    }

    /**
     * Devuelve el EXIF del archivo o un arreglo vacío (si no es JPEG o falta la extensión exif).
     *
     * @param  string  $ruta
     * @return array<string, mixed>
     */
    private static function leerExif(string $ruta): array
    {
        if (! function_exists('exif_read_data')) {
            return [];
        }
        $tipo = @exif_imagetype($ruta);
        if (! in_array($tipo, [IMAGETYPE_JPEG, IMAGETYPE_TIFF_II, IMAGETYPE_TIFF_MM], true)) {
            return [];
        }
        $datos = @exif_read_data($ruta);

        return is_array($datos) ? $datos : [];
    }

    /**
     * Procesa la imagen con Intervention Image v3.
     *
     * @return array{archivo:string, ancho:int, alto:int}
     */
    private static function conIntervention(string $origen, string $dir, string $nombre): array
    {
        $imagen = \Intervention\Image\Laravel\Facades\Image::read($origen);
        $imagen->scaleDown(width: self::ANCHO_MAX);

        $archivo = $nombre.'.webp';
        $imagen->toWebp(self::CALIDAD)->save($dir.'/'.$archivo);

        return ['archivo' => $archivo, 'ancho' => $imagen->width(), 'alto' => $imagen->height()];
    }

    /**
     * Procesa la imagen solo con GD (sin librerías externas).
     *
     * @return array{archivo:string, ancho:int, alto:int}
     */
    private static function conGd(string $origen, string $dir, string $nombre): array
    {
        $imagen = @imagecreatefromstring((string) file_get_contents($origen));
        if ($imagen === false) {
            throw new RuntimeException('No se pudo leer la imagen.');
        }

        // Enderezar fotos de celular según la orientación EXIF
        $orientacion = (int) (self::leerExif($origen)['Orientation'] ?? 1);
        $grados = [3 => 180, 6 => -90, 8 => 90][$orientacion] ?? 0;
        if ($grados !== 0) {
            $rotada = imagerotate($imagen, $grados, 0);
            imagedestroy($imagen);
            $imagen = $rotada;
        }

        if (! imageistruecolor($imagen)) {
            imagepalettetotruecolor($imagen);
        }

        // Reducir (nunca agrandar)
        if (imagesx($imagen) > self::ANCHO_MAX) {
            $reducida = imagescale($imagen, self::ANCHO_MAX, -1, IMG_BICUBIC);
            imagedestroy($imagen);
            $imagen = $reducida;
        }

        imagealphablending($imagen, false);
        imagesavealpha($imagen, true);

        // GD nunca escribe EXIF: la imagen resultante sale sin metadatos ni GPS
        if (function_exists('imagewebp') && (gd_info()['WebP Support'] ?? false)) {
            $archivo = $nombre.'.webp';
            imagewebp($imagen, $dir.'/'.$archivo, self::CALIDAD);
        } else {
            $archivo = $nombre.'.jpg';
            imagejpeg($imagen, $dir.'/'.$archivo, self::CALIDAD);
        }

        $resultado = ['archivo' => $archivo, 'ancho' => imagesx($imagen), 'alto' => imagesy($imagen)];
        imagedestroy($imagen);

        return $resultado;
    }
}

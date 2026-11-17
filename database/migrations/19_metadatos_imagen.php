<?php

/**
 * Raiders Paradise - Tienda en línea de accesorios premium para motociclistas.
 *
 * Migración: metadatos de las fotos de galería (dimensiones, peso, cámara y fecha de captura).
 *
 * @package   RaidersParadise
 * @author    Equipo de desarrollo Raiders Paradise (ADSO - SENA)
 * @copyright 2026 Raiders Paradise
 * @license   MIT
 * @version   2.3.0
 * @since     2026-11-07
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Los datos se llenan al subir la foto (App\Support\ImagenOptimizada).
 * El archivo guardado ya no contiene EXIF ni GPS; aquí solo queda lo útil para la tienda.
 */
return new class () extends Migration {
    public function up(): void
    {
        Schema::table('producto_imagen', function (Blueprint $table) {
            $table->unsignedInteger('ancho')->nullable();
            $table->unsignedInteger('alto')->nullable();
            $table->unsignedInteger('peso_bytes')->nullable();
            $table->string('camara', 80)->nullable();
            $table->string('fecha_toma', 30)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('producto_imagen', function (Blueprint $table) {
            $table->dropColumn(['ancho', 'alto', 'peso_bytes', 'camara', 'fecha_toma']);
        });
    }
};

<?php

/**
 * Raiders Paradise - Tienda en línea de accesorios premium para motociclistas.
 *
 * Migración: etiquetas de productos para el buscador.
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
 * - etiqueta: palabras clave (ej. "impermeable", "cuero", "touring"); slug único.
 * - producto_etiqueta: relación muchos a muchos entre producto y etiqueta.
 */
return new class () extends Migration {
    public function up(): void
    {
        Schema::create('etiqueta', function (Blueprint $table) {
            $table->id('id_etiqueta');
            $table->string('nombre', 40);
            $table->string('slug', 50)->unique();
            $table->timestamps();
        });

        Schema::create('producto_etiqueta', function (Blueprint $table) {
            $table->unsignedBigInteger('id_producto');
            $table->unsignedBigInteger('id_etiqueta');
            $table->primary(['id_producto', 'id_etiqueta']);

            $table->foreign('id_producto')->references('id_producto')->on('producto')->cascadeOnDelete();
            $table->foreign('id_etiqueta')->references('id_etiqueta')->on('etiqueta')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('producto_etiqueta');
        Schema::dropIfExists('etiqueta');
    }
};

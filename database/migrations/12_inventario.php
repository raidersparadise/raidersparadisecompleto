<?php

/**
 * Raiders Paradise - Tienda en línea de accesorios premium para motociclistas.
 *
 * Migración de la tabla "inventario".
 *
 * @package   RaidersParadise
 * @author    Equipo de desarrollo Raiders Paradise (ADSO - SENA)
 * @copyright 2026 Raiders Paradise
 * @license   MIT
 * @version   2.0.0
 * @since     2026-10-29
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabla inventario: existencias por producto.
 *
 * Columnas: id_inventario (PK), id_producto (FK, único), cantidad_disponible,
 * cantidad_minima (umbral de alerta), timestamps.
 */
return new class () extends Migration {
    /**
     * Ejecuta la migración (php artisan migrate).
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('inventario', function (Blueprint $table) {
            $table->id('id_inventario');
            $table->unsignedBigInteger('id_producto')->unique(); // un registro de stock por producto
            $table->unsignedInteger('cantidad_disponible')->default(0);
            $table->unsignedInteger('cantidad_minima')->default(3);
            $table->timestamps();

            $table->foreign('id_producto')->references('id_producto')->on('producto')->cascadeOnDelete();
        });
    }

    /**
     * Revierte la migración (php artisan migrate:rollback).
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('inventario');
    }
};

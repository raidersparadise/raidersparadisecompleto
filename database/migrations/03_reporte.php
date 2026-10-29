<?php

/**
 * Raiders Paradise - Tienda en línea de accesorios premium para motociclistas.
 *
 * Migración de la tabla "reporte".
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
 * Tabla reporte: historial de reportes generados por el administrador.
 *
 * Columnas: id_reporte (PK), id_usuario (FK usuario, cascada), tipo_reporte,
 * formato (pantalla|pdf|csv), fecha_desde, fecha_hasta, fecha_generacion, timestamps.
 */
return new class () extends Migration {
    /**
     * Ejecuta la migración (php artisan migrate).
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('reporte', function (Blueprint $table) {
            $table->id('id_reporte');
            $table->unsignedBigInteger('id_usuario');
            $table->string('tipo_reporte', 100);
            $table->string('formato', 20)->default('pantalla');
            $table->date('fecha_desde')->nullable();
            $table->date('fecha_hasta')->nullable();
            $table->dateTime('fecha_generacion');
            $table->timestamps();

            $table->foreign('id_usuario')->references('id_usuario')->on('usuario')->cascadeOnDelete();
        });
    }

    /**
     * Revierte la migración (php artisan migrate:rollback).
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('reporte');
    }
};

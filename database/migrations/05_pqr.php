<?php

/**
 * Raiders Paradise - Tienda en línea de accesorios premium para motociclistas.
 *
 * Migración de la tabla "pqr".
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
 * Tabla pqr: peticiones, quejas, reclamos y sugerencias.
 *
 * Columnas: id_pqr (PK), id_usuario (FK usuario, responsable opcional), id_cliente
 * (FK cliente), tipo, asunto, descripcion_pqr, estado, respuesta, fecha,
 * fecha_cierre, timestamps.
 */
return new class () extends Migration {
    /**
     * Ejecuta la migración (php artisan migrate).
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('pqr', function (Blueprint $table) {
            $table->id('id_pqr');
            // Usuario (empleado) responsable de atenderla: se asigna después, por eso es opcional
            $table->unsignedBigInteger('id_usuario')->nullable();
            $table->unsignedBigInteger('id_cliente');
            $table->enum('tipo', ['peticion', 'queja', 'reclamo', 'sugerencia'])->default('peticion');
            $table->string('asunto', 120);
            $table->string('descripcion_pqr', 1000);
            $table->enum('estado', ['recibida', 'asignada', 'en proceso', 'requerido', 'cerrada', 'rechazada'])->default('recibida');
            $table->text('respuesta')->nullable();
            $table->date('fecha');
            $table->dateTime('fecha_cierre')->nullable();
            $table->timestamps();

            $table->foreign('id_usuario')->references('id_usuario')->on('usuario')->nullOnDelete();
            $table->foreign('id_cliente')->references('id_cliente')->on('cliente')->cascadeOnDelete();
        });
    }

    /**
     * Revierte la migración (php artisan migrate:rollback).
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('pqr');
    }
};

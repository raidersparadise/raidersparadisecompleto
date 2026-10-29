<?php

/**
 * Raiders Paradise - Tienda en línea de accesorios premium para motociclistas.
 *
 * Migración de la tabla "cliente".
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
 * Tabla cliente: compradores de la tienda.
 *
 * Columnas: id_cliente (PK), id_usuario (FK usuario, opcional y único), documento,
 * nombre_cliente, apellido_cliente, email (único), telefono, direccion, ciudad, timestamps.
 */
return new class () extends Migration {
    /**
     * Ejecuta la migración (php artisan migrate).
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('cliente', function (Blueprint $table) {
            $table->id('id_cliente');
            // Enlace con la cuenta de acceso (un cliente puede comprar con su usuario)
            $table->unsignedBigInteger('id_usuario')->nullable()->unique();
            $table->string('documento', 20)->nullable();
            $table->string('nombre_cliente', 40);
            $table->string('apellido_cliente', 40);
            $table->string('email', 150)->unique();
            $table->string('telefono', 20)->nullable();   // corregido: decía ->nullabel()
            $table->string('direccion', 100)->nullable(); // corregido: decía ->nullabel()
            $table->string('ciudad', 60)->nullable();
            $table->timestamps();

            $table->foreign('id_usuario')->references('id_usuario')->on('usuario')->nullOnDelete();
        });
    }

    /**
     * Revierte la migración (php artisan migrate:rollback).
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('cliente');
    }
};

<?php

/**
 * Raiders Paradise - Tienda en línea de accesorios premium para motociclistas.
 *
 * Migración de la tabla "proveedor".
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
 * Tabla proveedor: proveedores de mercancía.
 *
 * Columnas: id_proveedor (PK), nit, nombre_proveedor, telefono_proveedor (texto),
 * direccion_proveedor, email_proveedor, timestamps.
 */
return new class () extends Migration {
    /**
     * Ejecuta la migración (php artisan migrate).
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('proveedor', function (Blueprint $table) {
            $table->id('id_proveedor');
            $table->string('nit', 20)->nullable();
            $table->string('nombre_proveedor', 80);
            // corregido: era integer; un celular de 10 dígitos (3001234567) desborda un INT
            $table->string('telefono_proveedor', 20)->nullable();
            $table->string('direccion_proveedor', 100)->nullable();
            $table->string('email_proveedor', 100)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Revierte la migración (php artisan migrate:rollback).
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('proveedor');
    }
};

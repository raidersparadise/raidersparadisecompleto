<?php

/**
 * Raiders Paradise - Tienda en línea de accesorios premium para motociclistas.
 *
 * Migración de la tabla "producto".
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
 * Tabla producto: catálogo de la tienda.
 *
 * Columnas: id_producto (PK), id_categoria, id_marca, id_proveedor (FK), sku (único),
 * nombre_producto, descripcion_producto, precio_producto (COP con IVA),
 * estado_producto (activo|inactivo), destacado, imagen_producto, comentario_producto, timestamps.
 */
return new class () extends Migration {
    /**
     * Ejecuta la migración (php artisan migrate).
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('producto', function (Blueprint $table) {
            $table->id('id_producto');
            $table->unsignedBigInteger('id_categoria');
            $table->unsignedBigInteger('id_marca');
            $table->unsignedBigInteger('id_proveedor');

            $table->string('sku', 30)->unique();
            $table->string('nombre_producto', 120);
            $table->string('descripcion_producto', 1000)->nullable();
            $table->decimal('precio_producto', 12, 2);
            $table->string('estado_producto', 20)->default('activo'); // activo | inactivo
            $table->boolean('destacado')->default(false);
            $table->string('imagen_producto', 255)->nullable();
            $table->string('comentario_producto', 255)->nullable();
            $table->timestamps();

            $table->foreign('id_categoria')->references('id_categoria')->on('categoria');
            $table->foreign('id_marca')->references('id_marca')->on('marca');
            $table->foreign('id_proveedor')->references('id_proveedor')->on('proveedor');
        });
    }

    /**
     * Revierte la migración (php artisan migrate:rollback).
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('producto');
    }
};

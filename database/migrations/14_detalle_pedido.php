<?php

/**
 * Raiders Paradise - Tienda en línea de accesorios premium para motociclistas.
 *
 * Migración de la tabla "detalle_pedido".
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
 * Tabla detalle_pedido: productos vendidos en cada pedido.
 *
 * Columnas: id_detalle_pedido (PK), id_pedido (FK, cascada), id_producto (FK, restringida
 * para no perder el historial de ventas), cantidad, precio_unitario, sub_total, timestamps.
 */
return new class () extends Migration {
    /**
     * Ejecuta la migración (php artisan migrate).
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('detalle_pedido', function (Blueprint $table) {
            $table->id('id_detalle_pedido');
            $table->unsignedBigInteger('id_pedido');
            $table->unsignedBigInteger('id_producto');
            $table->unsignedInteger('cantidad');
            $table->decimal('precio_unitario', 12, 2);
            $table->decimal('sub_total', 12, 2);
            $table->timestamps();

            $table->foreign('id_pedido')->references('id_pedido')->on('pedido')->cascadeOnDelete();
            // corregido: antes era cascade; borrar un producto eliminaba el historial de ventas
            $table->foreign('id_producto')->references('id_producto')->on('producto')->restrictOnDelete();
        });
    }

    /**
     * Revierte la migración (php artisan migrate:rollback).
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('detalle_pedido');
    }
};

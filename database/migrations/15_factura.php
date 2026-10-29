<?php

/**
 * Raiders Paradise - Tienda en línea de accesorios premium para motociclistas.
 *
 * Migración de la tabla "factura".
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
 * Tabla factura: una factura por pedido.
 *
 * Columnas: id_factura (PK), id_pedido (FK, único), numero_factura (único), fecha_factura,
 * subtotal, impuesto (IVA 19 %), total_factura, estado_factura, pago, metodo_pago, timestamps.
 */
return new class () extends Migration {
    /**
     * Ejecuta la migración (php artisan migrate).
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('factura', function (Blueprint $table) {
            $table->id('id_factura');
            $table->unsignedBigInteger('id_pedido')->unique();
            $table->string('numero_factura', 20)->unique();
            $table->dateTime('fecha_factura');
            $table->decimal('subtotal', 12, 2);
            $table->decimal('impuesto', 12, 2);
            $table->decimal('total_factura', 12, 2);
            $table->string('estado_factura', 20); // pagada | pendiente | anulada
            $table->decimal('pago', 12, 2);
            $table->string('metodo_pago', 50);
            $table->timestamps();

            $table->foreign('id_pedido')->references('id_pedido')->on('pedido')->cascadeOnDelete();
        });
    }

    /**
     * Revierte la migración (php artisan migrate:rollback).
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('factura');
    }
};

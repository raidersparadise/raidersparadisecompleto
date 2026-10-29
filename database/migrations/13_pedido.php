<?php

/**
 * Raiders Paradise - Tienda en línea de accesorios premium para motociclistas.
 *
 * Migración de la tabla "pedido".
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
 * Tabla pedido: compras de los clientes.
 *
 * Columnas: id_pedido (PK), id_cliente (FK), fecha, estado (pendiente | programado |
 * en_curso | entregado | cancelado), total, direccion_envio, ciudad_envio, notas, timestamps.
 */
return new class () extends Migration {
    /**
     * Ejecuta la migración (php artisan migrate).
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('pedido', function (Blueprint $table) {
            $table->id('id_pedido');
            $table->unsignedBigInteger('id_cliente');
            $table->dateTime('fecha');
            // corregido: se unificó en minúsculas (antes mezclaba "Entregado" con "en_curso")
            $table->enum('estado', ['pendiente', 'programado', 'en_curso', 'entregado', 'cancelado'])->default('pendiente');
            $table->decimal('total', 12, 2);
            $table->string('direccion_envio', 150)->nullable();
            $table->string('ciudad_envio', 60)->nullable();
            $table->string('notas', 255)->nullable();
            $table->timestamps();

            $table->index('fecha');
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
        Schema::dropIfExists('pedido');
    }
};

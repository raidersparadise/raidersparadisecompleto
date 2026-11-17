<?php

/**
 * Raiders Paradise - Tienda en línea de accesorios premium para motociclistas.
 *
 * Migración: vista 360° y ampliación del catálogo.
 *
 * @package   RaidersParadise
 * @author    Equipo de desarrollo Raiders Paradise (ADSO - SENA)
 * @copyright 2026 Raiders Paradise
 * @license   MIT
 * @version   2.3.0
 * @since     2026-11-04
 */

use App\Models\Producto;
use App\Support\CatalogoNuevo;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * - producto_imagen.tipo: "galeria" (fotos normales) o "360" (cuadros de la vista 360°,
 *   en el orden de giro).
 * - Agrega los 11 productos nuevos de App\Support\CatalogoNuevo (ocultos hasta tener foto).
 */
return new class () extends Migration {
    /**
     * Ejecuta la migración (php artisan migrate).
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('producto_imagen', function (Blueprint $table) {
            $table->string('tipo', 10)->default('galeria')->after('ruta');
            $table->index(['id_producto', 'tipo']);
        });

        CatalogoNuevo::registrar();
    }

    /**
     * Revierte la migración (php artisan migrate:rollback).
     * Los productos nuevos que ya tengan ventas no se borran.
     *
     * @return void
     */
    public function down(): void
    {
        $skus = array_column(CatalogoNuevo::PRODUCTOS, 0);
        Producto::whereIn('sku', $skus)->doesntHave('detallesPedido')->delete();

        Schema::table('producto_imagen', function (Blueprint $table) {
            $table->dropIndex(['id_producto', 'tipo']);
            $table->dropColumn('tipo');
        });
    }
};

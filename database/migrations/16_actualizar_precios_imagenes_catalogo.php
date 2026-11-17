<?php

/**
 * Raiders Paradise - Tienda en línea de accesorios premium para motociclistas.
 *
 * Migración de datos: actualiza precios e imágenes del catálogo.
 *
 * @package   RaidersParadise
 * @author    Equipo de desarrollo Raiders Paradise (ADSO - SENA)
 * @copyright 2026 Raiders Paradise
 * @license   MIT
 * @version   2.2.0
 * @since     2026-11-04
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Actualiza, por SKU, el precio (COP, IVA incluido) y la imagen de los 24 productos base:
 * - Precios ajustados a valores de referencia del mercado colombiano (noviembre de 2026).
 * - Imágenes: fotos reales de cada producto en public/images/productos (reemplazan los SVG genéricos).
 *
 * No toca pedidos ni facturas: el detalle de cada pedido guarda su propio precio_unitario.
 * Productos creados después por el administrador no se modifican.
 */
return new class () extends Migration {
    /** @var array<string, array{0:int,1:string,2:int,3:string}> sku => [precio nuevo, imagen nueva, precio anterior, imagen anterior] */
    private array $catalogo = [
        'CAS-SHO-001' => [4290000, 'images/productos/23.webp', 3890000, 'images/productos/casco-negro.svg'],
        'CAS-AGV-002' => [2990000, 'images/productos/1.webp', 3250000, 'images/productos/casco-indigo.svg'],
        'CAS-ARA-003' => [4490000, 'images/productos/7.webp', 4600000, 'images/productos/casco-naranja.svg'],
        'CAS-LS2-004' => [1690000, 'images/productos/15.webp', 1290000, 'images/productos/casco-indigo.svg'],
        'CAS-SHA-005' => [2490000, 'images/productos/22.webp', 2150000, 'images/productos/casco-negro.svg'],
        'GUA-ALP-006' => [1590000, 'images/productos/16.jpg', 890000, 'images/productos/guantes-negro.svg'],
        'GUA-DAI-007' => [1890000, 'images/productos/9.webp', 780000, 'images/productos/guantes-naranja.svg'],
        'GUA-REV-008' => [749000, 'images/productos/19.webp', 420000, 'images/productos/guantes-indigo.svg'],
        'GUA-ALP-009' => [389000, 'images/productos/4.webp', 260000, 'images/productos/guantes-negro.svg'],
        'CHA-DAI-010' => [4590000, 'images/productos/11.webp', 3900000, 'images/productos/chaqueta-negro.svg'],
        'CHA-ALP-011' => [1390000, 'images/productos/6.webp', 1150000, 'images/productos/chaqueta-indigo.svg'],
        'CHA-REV-012' => [1999000, 'images/productos/20.webp', 1980000, 'images/productos/chaqueta-naranja.svg'],
        'CHA-DAI-013' => [2190000, 'images/productos/8.webp', 2450000, 'images/productos/chaqueta-indigo.svg'],
        'IMP-GIV-014' => [389000, 'images/productos/13.webp', 320000, 'images/productos/impermeable-naranja.svg'],
        'IMP-ALP-015' => [489000, 'images/productos/3.webp', 450000, 'images/productos/impermeable-negro.svg'],
        'IMP-REV-016' => [589000, 'images/productos/acid-4-h2o-pantalon.webp', 520000, 'images/productos/impermeable-indigo.svg'],
        'IMP-DAI-017' => [790000, 'images/productos/10.webp', 690000, 'images/productos/impermeable-negro.svg'],
        'BOT-ALP-018' => [1690000, 'images/productos/5.webp', 1250000, 'images/productos/botas-negro.svg'],
        'BOT-DAI-019' => [2390000, 'images/productos/12.webp', 1690000, 'images/productos/botas-indigo.svg'],
        'BOT-REV-020' => [2590000, 'images/productos/18.webp', 1150000, 'images/productos/botas-naranja.svg'],
        'ACC-SEN-021' => [1890000, 'images/productos/21.webp', 1450000, 'images/productos/intercom-negro.svg'],
        'ACC-GIV-022' => [2190000, 'images/productos/14.webp', 1890000, 'images/productos/baul-negro.svg'],
        'ACC-PIN-023' => [189000, 'images/productos/17.webp', 190000, 'images/productos/visor-indigo.svg'],
        'ACC-SHO-024' => [690000, 'images/productos/24.webp', 480000, 'images/productos/visor-naranja.svg'],
    ];

    /**
     * Aplica los nuevos precios e imágenes (php artisan migrate).
     *
     * @return void
     */
    public function up(): void
    {
        foreach ($this->catalogo as $sku => [$precio, $imagen]) {
            DB::table('producto')->where('sku', $sku)->update([
                'precio_producto' => $precio,
                'imagen_producto' => $imagen,
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Restaura los precios e imágenes anteriores (php artisan migrate:rollback).
     *
     * @return void
     */
    public function down(): void
    {
        foreach ($this->catalogo as $sku => [, , $precio, $imagen]) {
            DB::table('producto')->where('sku', $sku)->update([
                'precio_producto' => $precio,
                'imagen_producto' => $imagen,
                'updated_at' => now(),
            ]);
        }
    }
};

<?php

/**
 * Raiders Paradise - Tienda en línea de accesorios premium para motociclistas.
 *
 * Migración: tallas por producto y galería de fotos.
 *
 * @package   RaidersParadise
 * @author    Equipo de desarrollo Raiders Paradise (ADSO - SENA)
 * @copyright 2026 Raiders Paradise
 * @license   MIT
 * @version   2.2.0
 * @since     2026-11-04
 */

use App\Support\Tallas;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * - producto_talla: existencias por talla (S, M, L, XL; "Única" para accesorios).
 *   La suma de las tallas es inventario.cantidad_disponible.
 * - producto_imagen: fotos adicionales de la galería de cada producto.
 * - detalle_carrito.talla y detalle_pedido.talla: talla elegida por el cliente.
 *
 * Al migrar, el stock actual de cada producto se reparte entre sus tallas
 * y se registran las fotos de galería incluidas en public/images/productos/galeria.
 */
return new class () extends Migration {
    /** @var array<string, array<int, string>> sku => fotos adicionales */
    private array $galeria = [
        'CAS-SHO-001' => ['images/productos/galeria/cas-sho-001-detalle-1.webp', 'images/productos/galeria/cas-sho-001-detalle-2.webp'],
        'CAS-AGV-002' => ['images/productos/galeria/cas-agv-002-detalle-1.webp', 'images/productos/galeria/cas-agv-002-detalle-2.webp'],
        'CAS-ARA-003' => ['images/productos/galeria/cas-ara-003-detalle-1.webp', 'images/productos/galeria/cas-ara-003-detalle-2.webp'],
        'CAS-LS2-004' => ['images/productos/galeria/cas-ls2-004-detalle-1.webp', 'images/productos/galeria/cas-ls2-004-detalle-2.webp'],
        'CAS-SHA-005' => ['images/productos/galeria/cas-sha-005-detalle-1.webp', 'images/productos/galeria/cas-sha-005-detalle-2.webp'],
        'GUA-ALP-006' => ['images/productos/galeria/gua-alp-006-detalle-1.webp', 'images/productos/galeria/gua-alp-006-detalle-2.webp'],
        'GUA-DAI-007' => ['images/productos/galeria/gua-dai-007-detalle-1.webp', 'images/productos/galeria/gua-dai-007-detalle-2.webp'],
        'GUA-REV-008' => ['images/productos/galeria/gua-rev-008-detalle-1.webp', 'images/productos/galeria/gua-rev-008-detalle-2.webp'],
        'GUA-ALP-009' => ['images/productos/galeria/gua-alp-009-detalle-1.webp', 'images/productos/galeria/gua-alp-009-detalle-2.webp'],
        'CHA-DAI-010' => ['images/productos/galeria/cha-dai-010-detalle-1.webp', 'images/productos/galeria/cha-dai-010-detalle-2.webp'],
        'CHA-ALP-011' => ['images/productos/galeria/cha-alp-011-detalle-1.webp', 'images/productos/galeria/cha-alp-011-detalle-2.webp'],
        'CHA-REV-012' => ['images/productos/galeria/cha-rev-012-detalle-1.webp', 'images/productos/galeria/cha-rev-012-detalle-2.webp'],
        'CHA-DAI-013' => ['images/productos/galeria/cha-dai-013-detalle-1.webp', 'images/productos/galeria/cha-dai-013-detalle-2.webp'],
        'IMP-GIV-014' => ['images/productos/galeria/imp-giv-014-detalle-1.webp', 'images/productos/galeria/imp-giv-014-detalle-2.webp'],
        'IMP-ALP-015' => ['images/productos/galeria/imp-alp-015-detalle-1.webp', 'images/productos/galeria/imp-alp-015-detalle-2.webp'],
        'IMP-REV-016' => ['images/productos/galeria/imp-rev-016-detalle-1.webp', 'images/productos/galeria/imp-rev-016-detalle-2.webp'],
        'IMP-DAI-017' => ['images/productos/galeria/imp-dai-017-detalle-1.webp', 'images/productos/galeria/imp-dai-017-detalle-2.webp'],
        'BOT-ALP-018' => ['images/productos/galeria/bot-alp-018-detalle-1.webp', 'images/productos/galeria/bot-alp-018-detalle-2.webp'],
        'BOT-DAI-019' => ['images/productos/galeria/bot-dai-019-vista-12.webp', 'images/productos/galeria/bot-dai-019-detalle-1.webp', 'images/productos/galeria/bot-dai-019-detalle-2.webp'],
        'BOT-REV-020' => ['images/productos/galeria/bot-rev-020-vista-18.webp', 'images/productos/galeria/bot-rev-020-detalle-1.webp', 'images/productos/galeria/bot-rev-020-detalle-2.webp'],
        'ACC-SEN-021' => ['images/productos/galeria/acc-sen-021-detalle-1.webp', 'images/productos/galeria/acc-sen-021-detalle-2.webp'],
        'ACC-GIV-022' => ['images/productos/galeria/acc-giv-022-detalle-1.webp', 'images/productos/galeria/acc-giv-022-detalle-2.webp'],
        'ACC-PIN-023' => ['images/productos/galeria/acc-pin-023-detalle-1.webp', 'images/productos/galeria/acc-pin-023-detalle-2.webp'],
        'ACC-SHO-024' => ['images/productos/galeria/acc-sho-024-detalle-1.webp', 'images/productos/galeria/acc-sho-024-detalle-2.webp'],
    ];

    /**
     * Ejecuta la migración (php artisan migrate).
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('producto_talla', function (Blueprint $table) {
            $table->id('id_producto_talla');
            $table->unsignedBigInteger('id_producto');
            $table->string('talla', 10);
            $table->unsignedInteger('cantidad_disponible')->default(0);
            $table->unsignedTinyInteger('orden')->default(0);
            $table->timestamps();

            $table->unique(['id_producto', 'talla']);
            $table->foreign('id_producto')->references('id_producto')->on('producto')->cascadeOnDelete();
        });

        Schema::create('producto_imagen', function (Blueprint $table) {
            $table->id('id_producto_imagen');
            $table->unsignedBigInteger('id_producto');
            $table->string('ruta', 255);
            $table->unsignedTinyInteger('orden')->default(0);
            $table->timestamps();

            $table->foreign('id_producto')->references('id_producto')->on('producto')->cascadeOnDelete();
        });

        // La misma referencia puede estar en el carrito en varias tallas
        Schema::table('detalle_carrito', function (Blueprint $table) {
            $table->string('talla', 10)->nullable()->after('id_producto');
            $table->unique(['id_carrito', 'id_producto', 'talla'], 'detalle_carrito_producto_talla_unique');
        });
        Schema::table('detalle_carrito', function (Blueprint $table) {
            $table->dropUnique('detalle_carrito_id_carrito_id_producto_unique');
        });

        Schema::table('detalle_pedido', function (Blueprint $table) {
            $table->string('talla', 10)->nullable()->after('id_producto');
        });

        $this->poblarDatos();
    }

    /**
     * Reparte el stock actual entre tallas y registra las fotos de galería.
     *
     * @return void
     */
    private function poblarDatos(): void
    {
        $productos = DB::table('producto')
            ->join('categoria', 'categoria.id_categoria', '=', 'producto.id_categoria')
            ->leftJoin('inventario', 'inventario.id_producto', '=', 'producto.id_producto')
            ->select('producto.id_producto', 'producto.sku', 'categoria.nombre_categoria', 'inventario.cantidad_disponible')
            ->get();

        $ahora = now();
        foreach ($productos as $p) {
            $total = (int) ($p->cantidad_disponible ?? 0);
            $reparto = Tallas::categoriaUsaTallas($p->nombre_categoria) ? Tallas::repartir($total) : [Tallas::UNICA => $total];
            $orden = 0;
            foreach ($reparto as $talla => $cantidad) {
                DB::table('producto_talla')->insert([
                    'id_producto' => $p->id_producto, 'talla' => $talla, 'cantidad_disponible' => $cantidad,
                    'orden' => $orden++, 'created_at' => $ahora, 'updated_at' => $ahora,
                ]);
            }

            foreach ($this->galeria[$p->sku] ?? [] as $i => $ruta) {
                if (is_file(public_path($ruta))) {
                    DB::table('producto_imagen')->insert([
                        'id_producto' => $p->id_producto, 'ruta' => $ruta, 'orden' => $i + 1,
                        'created_at' => $ahora, 'updated_at' => $ahora,
                    ]);
                }
            }
        }
    }

    /**
     * Revierte la migración (php artisan migrate:rollback).
     *
     * @return void
     */
    public function down(): void
    {
        DB::table('detalle_carrito')->delete(); // evita duplicados al volver a la llave sin talla
        Schema::table('detalle_carrito', function (Blueprint $table) {
            $table->unique(['id_carrito', 'id_producto']);
        });
        Schema::table('detalle_carrito', function (Blueprint $table) {
            $table->dropUnique('detalle_carrito_producto_talla_unique');
            $table->dropColumn('talla');
        });
        Schema::table('detalle_pedido', function (Blueprint $table) {
            $table->dropColumn('talla');
        });
        Schema::dropIfExists('producto_imagen');
        Schema::dropIfExists('producto_talla');
    }
};

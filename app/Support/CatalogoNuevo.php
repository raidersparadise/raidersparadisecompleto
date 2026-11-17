<?php

/**
 * Raiders Paradise - Tienda en línea de accesorios premium para motociclistas.
 *
 * Ampliación del catálogo (noviembre de 2026).
 *
 * @package   RaidersParadise
 * @author    Equipo de desarrollo Raiders Paradise (ADSO - SENA)
 * @copyright 2026 Raiders Paradise
 * @license   MIT
 * @version   2.3.0
 * @since     2026-11-04
 */

namespace App\Support;

use App\Models\Categoria;
use App\Models\Inventario;
use App\Models\Marca;
use App\Models\Producto;
use App\Models\ProductoTalla;
use App\Models\Proveedor;

/**
 * Productos nuevos del catálogo, con los mismos parámetros de los 24 originales.
 *
 * Precios de referencia en COP con IVA (precio oficial de la marca convertido con el mismo
 * factor que usan las tiendas colombianas consultadas).
 *
 * Cada producto queda INACTIVO (oculto en la tienda) hasta que tenga foto principal:
 * se deja en public/images/productos/nuevos/<SKU>.webp (o .jpg / .png) y se ejecuta
 * "php artisan productos:importar-fotos", que la registra y lo publica.
 */
final class CatalogoNuevo
{
    /** Marca que se escribe en la nota interna mientras falta la foto. */
    public const PENDIENTE = 'Pendiente de foto principal';

    /**
     * [sku, nombre, categoría, marca, precio, destacado, stock, mínimo, descripción]
     *
     * @var array<int, array{0:string,1:string,2:string,3:string,4:int,5:bool,6:int,7:int,8:string}>
     */
    public const PRODUCTOS = [
        ['CAS-SHO-025', 'Shoei NXR2 Negro Brillante', 'Cascos', 'Shoei', 2990000, true, 6, 2, 'Integral deportivo-touring con calota AIM, visor CWR-F2 con Pinlock EVO y certificación ECE 22.06.'],
        ['CAS-AGV-026', 'AGV K3 Mono Negro Mate', 'Cascos', 'AGV', 1490000, false, 10, 3, 'Integral de calota termoplástica HIR-TH con visera solar interna, Pinlock incluido y forro 2Dry lavable.'],
        ['GUA-ALP-027', 'Alpinestars SP-8 v3', 'Guantes', 'Alpinestars', 489000, false, 12, 4, 'Guante deportivo corto en cuero con protección de nudillos en TPU y palma reforzada.'],
        ['GUA-DAI-028', 'Dainese Carbon 4 Short', 'Guantes', 'Dainese', 1090000, false, 8, 3, 'Guante corto de cuero de cabra con nudillos en fibra de carbono y deslizador de palma.'],
        ['CHA-REV-029', "Rev'It Eclipse 2", 'Chaquetas', "Rev'It", 990000, false, 10, 3, 'Chaqueta de verano en malla con protecciones SEESMART CE nivel 1 en hombros y codos.'],
        ['CHA-DAI-030', 'Dainese Racing 4 Cuero', 'Chaquetas', 'Dainese', 3490000, true, 4, 2, 'Chaqueta deportiva de cuero bovino con protecciones Pro-Armor y deslizadores en hombros.'],
        ['IMP-REV-031', "Rev'It Acid 4 H2O Chaqueta", 'Impermeables', "Rev'It", 649000, false, 8, 3, 'Chaqueta impermeable con membrana Hydratex, paneles reflectivos y bolsa de transporte.'],
        ['BOT-ALP-032', 'Alpinestars SMX Plus v2', 'Botas', 'Alpinestars', 2690000, false, 4, 2, 'Bota de pista con sistema MLC antitorsión del tobillo y deslizadores reemplazables.'],
        ['BOT-DAI-033', 'Dainese Nexus 2', 'Botas', 'Dainese', 1790000, false, 6, 2, 'Bota deportiva con sistema Axial Distortion Control y deslizadores termoplásticos reemplazables.'],
        ['ACC-SEN-034', 'Sena 60S Intercomunicador', 'Accesorios', 'Sena', 2190000, true, 6, 2, 'Intercomunicador Mesh 3.0 + Bluetooth 5.2, sonido Harman Kardon y comandos de voz.'],
        ['ACC-GIV-035', 'Givi Trekker Alaska 56L', 'Accesorios', 'Givi', 2490000, false, 3, 2, 'Baúl de aluminio de 56 litros con cierre Monokey, resistente al agua y con capacidad para dos cascos.'],
    ];

    /**
     * Crea (o actualiza) los productos nuevos con su inventario por talla.
     * Quedan activos solo si ya tienen foto principal en images/productos/nuevos/.
     *
     * @return int Cantidad de productos procesados.
     */
    public static function registrar(): int
    {
        $categorias = Categoria::pluck('id_categoria', 'nombre_categoria');
        $marcas = Marca::pluck('id_marca', 'nombre_marca');
        $proveedores = Proveedor::orderBy('id_proveedor')->pluck('id_proveedor')->values();
        if ($proveedores->isEmpty() || $categorias->isEmpty()) {
            return 0; // base vacía: el seeder los crea después
        }

        foreach (self::PRODUCTOS as $i => [$sku, $nombre, $cat, $marca, $precio, $destacado, $stock, $minimo, $descripcion]) {
            if (! isset($categorias[$cat], $marcas[$marca])) {
                continue;
            }
            $foto = self::fotoPrincipal($sku);
            $existente = Producto::where('sku', $sku)->first();

            $producto = Producto::updateOrCreate(['sku' => $sku], [
                'id_categoria' => $categorias[$cat],
                'id_marca' => $marcas[$marca],
                'id_proveedor' => $existente->id_proveedor ?? $proveedores[$i % $proveedores->count()],
                'nombre_producto' => $nombre,
                'descripcion_producto' => $descripcion,
                'precio_producto' => $precio,
                'destacado' => $destacado,
                'estado_producto' => $foto ? 'activo' : ($existente->estado_producto ?? 'inactivo'),
                'imagen_producto' => $foto ?? $existente?->imagen_producto,
                'comentario_producto' => $foto ? null : self::PENDIENTE,
            ]);

            if (! $existente) {
                Inventario::create(['id_producto' => $producto->id_producto, 'cantidad_disponible' => $stock, 'cantidad_minima' => $minimo]);
                $reparto = Tallas::categoriaUsaTallas($cat) ? Tallas::repartir($stock) : [Tallas::UNICA => $stock];
                $orden = 0;
                foreach ($reparto as $talla => $cantidad) {
                    ProductoTalla::create(['id_producto' => $producto->id_producto, 'talla' => $talla, 'cantidad_disponible' => $cantidad, 'orden' => $orden++]);
                }
            }
        }

        return count(self::PRODUCTOS);
    }

    /**
     * Ruta relativa de la foto principal de un SKU nuevo, si existe.
     *
     * @param  string  $sku
     * @return string|null Ej.: images/productos/nuevos/CAS-SHO-025.webp
     */
    public static function fotoPrincipal(string $sku): ?string
    {
        foreach ([$sku, strtolower($sku)] as $nombre) {
            foreach (['webp', 'jpg', 'jpeg', 'png', 'avif'] as $ext) {
                $ruta = "images/productos/nuevos/{$nombre}.{$ext}";
                if (is_file(public_path($ruta))) {
                    return $ruta;
                }
            }
        }

        return null;
    }
}

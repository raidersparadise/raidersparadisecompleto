<?php

/**
 * Raiders Paradise - Tienda en línea de accesorios premium para motociclistas.
 *
 * Seeder del catálogo de demostración.
 *
 * @package   RaidersParadise
 * @author    Equipo de desarrollo Raiders Paradise (ADSO - SENA)
 * @copyright 2026 Raiders Paradise
 * @license   MIT
 * @version   2.0.0
 * @since     2026-10-29
 */

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Inventario;
use App\Models\Marca;
use App\Models\Producto;
use App\Models\ProductoImagen;
use App\Models\ProductoTalla;
use App\Models\Proveedor;
use App\Support\CatalogoNuevo;
use App\Support\Tallas;
use Illuminate\Database\Seeder;

/**
 * Crea 6 categorías, 11 marcas, 3 proveedores, 24 productos y su inventario.
 * Incluye productos con stock bajo y agotados para probar las alertas.
 */
class CatalogoSeeder extends Seeder
{
    /**
     * Inserta el catálogo (idempotente por nombre y SKU).
     *
     * @return void
     */
    public function run(): void
    {
        $categorias = [
            'Cascos' => 'Cascos integrales, modulares y deportivos con certificación ECE 22.06 / DOT.',
            'Guantes' => 'Guantes de pista, turismo y urbanos con protecciones y materiales técnicos.',
            'Chaquetas' => 'Chaquetas de cuero y textiles con protecciones CE nivel 2.',
            'Impermeables' => 'Trajes y prendas impermeables para rodar bajo la lluvia.',
            'Botas' => 'Botas de pista, touring y urbanas con refuerzos de tobillo.',
            'Accesorios' => 'Intercomunicadores, baúles, visores y complementos premium.',
        ];
        foreach ($categorias as $n => $d) {
            Categoria::updateOrCreate(['nombre_categoria' => $n], ['descripcion_categoria' => $d]);
        }

        $marcas = [
            'Shoei' => 'Cascos japoneses de gama alta.', 'AGV' => 'Cascos italianos de competición.',
            'Arai' => 'Cascos artesanales japoneses.', 'LS2' => 'Cascos con excelente relación calidad-precio.',
            'Shark' => 'Cascos franceses de diseño deportivo.', 'Alpinestars' => 'Indumentaria técnica de competición.',
            'Dainese' => 'Protección italiana para motociclistas.', "Rev'It" => 'Equipamiento holandés touring y urbano.',
            'Givi' => 'Baúles, maletas y accesorios de viaje.', 'Sena' => 'Intercomunicadores Bluetooth.',
            'Pinlock' => 'Sistemas antiempañante para visores.',
        ];
        foreach ($marcas as $n => $d) {
            Marca::updateOrCreate(['nombre_marca' => $n], ['descripcion_marca' => $d]);
        }

        $proveedores = [
            ['900123456-1', 'MotoImport Colombia S.A.S.', '6017451200', 'Av. Boyacá # 64-20, Bogotá', 'compras@motoimport.co'],
            ['901987654-3', 'Racing Gear Andina S.A.S.', '6044448899', 'Cra. 48 # 10-45, Medellín', 'ventas@racinggear.co'],
            ['900555777-2', 'Distribuciones Ruta Premium Ltda.', '6023337766', 'Calle 5 # 38-12, Cali', 'pedidos@rutapremium.co'],
        ];
        foreach ($proveedores as [$nit, $n, $t, $dir, $mail]) {
            Proveedor::updateOrCreate(['nit' => $nit], ['nombre_proveedor' => $n, 'telefono_proveedor' => $t, 'direccion_proveedor' => $dir, 'email_proveedor' => $mail]);
        }

        $cat = Categoria::pluck('id_categoria', 'nombre_categoria');
        $mar = Marca::pluck('id_marca', 'nombre_marca');
        $prov = Proveedor::pluck('id_proveedor', 'nit')->values();

        // [sku, nombre, categoría, marca, precio, imagen, destacado, stock, mínimo, descripción]
        // Precios de referencia del mercado colombiano (COP, IVA incluido) actualizados a noviembre de 2026.
        // Imágenes: fotos reales en public/images/productos.
        $productos = [
            ['CAS-SHO-001', 'Shoei GT-Air 3 Negro Mate', 'Cascos', 'Shoei', 4290000, '23.webp', true, 6, 2, 'Casco integral touring con visera solar interna, sistema Pinlock y preinstalación para intercomunicador. Certificación ECE 22.06.'],
            ['CAS-AGV-002', 'AGV K6 S Carbon', 'Cascos', 'AGV', 2990000, '1.webp', true, 4, 2, 'Casco deportivo ultraliviano (1.255 g) en fibra de carbono y aramida. Ideal para pista y carretera.'],
            ['CAS-ARA-003', 'Arai Quantic Frost', 'Cascos', 'Arai', 4490000, '7.webp', true, 2, 2, 'Fabricado a mano en Japón. Calota redonda R75 que desvía impactos y máxima ventilación.'],
            ['CAS-LS2-004', 'LS2 Valiant II Modular', 'Cascos', 'LS2', 1690000, '15.webp', false, 12, 3, 'Modular con mentonera abatible 180°, doble homologación P/J y visor solar.'],
            ['CAS-SHA-005', 'Shark Spartan RS Carbon', 'Cascos', 'Shark', 2490000, '22.webp', false, 5, 2, 'Integral deportivo con calota de carbono, visor antirrayas y forro desmontable lavable.'],
            ['GUA-ALP-006', 'Alpinestars GP Pro R4', 'Guantes', 'Alpinestars', 1590000, '16.jpg', true, 10, 3, 'Guante de competición en cuero de canguro con protección de nudillos en carbono.'],
            ['GUA-DAI-007', 'Dainese Carbon 4 Long', 'Guantes', 'Dainese', 1890000, '9.webp', false, 7, 3, 'Guante largo de cuero de cabra con insertos de carbono y refuerzos en palma.'],
            ['GUA-REV-008', "Rev'It Sand 4 H2O", 'Guantes', "Rev'It", 749000, '19.webp', false, 0, 3, 'Guante touring impermeable con membrana Hydratex y compatibilidad táctil.'],
            ['GUA-ALP-009', 'Alpinestars SMX-1 Air v2', 'Guantes', 'Alpinestars', 389000, '4.webp', false, 15, 4, 'Guante urbano ventilado en malla y cuero sintético, ideal para clima cálido.'],
            ['CHA-DAI-010', 'Dainese Super Speed 4 Cuero', 'Chaquetas', 'Dainese', 4590000, '11.webp', true, 3, 2, 'Chaqueta de cuero bovino con protecciones Pro-Shape 2.0 en hombros y codos.'],
            ['CHA-ALP-011', 'Alpinestars T-GP Plus R v4 Air', 'Chaquetas', 'Alpinestars', 1390000, '6.webp', false, 8, 3, 'Chaqueta textil ventilada con protecciones CE nivel 1 y bolsillo para espaldar.'],
            ['CHA-REV-012', "Rev'It Tornado 4 H2O", 'Chaquetas', "Rev'It", 1999000, '20.webp', true, 5, 2, 'Chaqueta adventure 3 en 1 con membrana impermeable y forro térmico desmontable.'],
            ['CHA-DAI-013', 'Dainese Avro 5 Textil', 'Chaquetas', 'Dainese', 2190000, '8.webp', false, 2, 2, 'Chaqueta deportiva textil con elásticos S1 y preparación para airbag.'],
            ['IMP-GIV-014', 'Givi Traje Impermeable CRS01', 'Impermeables', 'Givi', 389000, '13.webp', false, 20, 5, 'Conjunto chaqueta y pantalón 100% impermeable con cintas reflectivas.'],
            ['IMP-ALP-015', 'Alpinestars Hurricane Rain', 'Impermeables', 'Alpinestars', 489000, '3.webp', true, 9, 4, 'Chaqueta impermeable compacta para llevar sobre la chaqueta de protección.'],
            ['IMP-REV-016', "Rev'It Acid 4 H2O Pantalón", 'Impermeables', "Rev'It", 589000, 'acid-4-h2o-pantalon.webp', false, 4, 4, 'Pantalón impermeable con cremalleras laterales completas para ponerlo con botas.'],
            ['IMP-DAI-017', 'Dainese Storm 2 Rain Suit', 'Impermeables', 'Dainese', 790000, '10.webp', false, 6, 3, 'Traje enterizo impermeable con bolsa de transporte y paneles de alta visibilidad.'],
            ['BOT-ALP-018', 'Alpinestars SMX-6 v3', 'Botas', 'Alpinestars', 1690000, '5.webp', true, 6, 2, 'Bota deportiva con sistema de soporte de tobillo y puntera reemplazable.'],
            ['BOT-DAI-019', 'Dainese Torque 3 Out', 'Botas', 'Dainese', 2390000, '12.webp', false, 1, 2, 'Bota de pista con sistema D-Axial y deslizadores de magnesio.'],
            ['BOT-REV-020', "Rev'It Discovery H2O", 'Botas', "Rev'It", 2590000, '18.webp', false, 4, 2, 'Bota adventure impermeable con suela Vibram y cierres de aluminio.'],
            ['ACC-SEN-021', 'Sena 50S Intercomunicador', 'Accesorios', 'Sena', 1890000, '21.webp', true, 8, 3, 'Intercomunicador Mesh 2.0 + Bluetooth 5, sonido Harman Kardon y control por voz.'],
            ['ACC-GIV-022', 'Givi Trekker Outback 42L', 'Accesorios', 'Givi', 2190000, '14.webp', false, 3, 2, 'Baúl de aluminio de 42 litros, resistente al agua, con sistema Monokey.'],
            ['ACC-PIN-023', 'Pinlock 120 Max Vision', 'Accesorios', 'Pinlock', 189000, '17.webp', false, 25, 6, 'Lente antiempañante para visor. Compatible con cascos con pines Pinlock.'],
            ['ACC-SHO-024', 'Shoei Visor CWR-F2 Iridium', 'Accesorios', 'Shoei', 690000, '24.webp', false, 0, 2, 'Visor espejado Iridium con tratamiento antirrayas para cascos Shoei.'],
        ];

        foreach ($productos as $i => [$sku, $nombre, $c, $m, $precio, $img, $dest, $stock, $min, $desc]) {
            $p = Producto::updateOrCreate(['sku' => $sku], [
                'id_categoria' => $cat[$c],
                'id_marca' => $mar[$m],
                'id_proveedor' => $prov[$i % $prov->count()],
                'nombre_producto' => $nombre,
                'descripcion_producto' => $desc,
                'precio_producto' => $precio,
                'estado_producto' => 'activo',
                'destacado' => $dest,
                'imagen_producto' => "images/productos/{$img}",
            ]);
            Inventario::updateOrCreate(['id_producto' => $p->id_producto], ['cantidad_disponible' => $stock, 'cantidad_minima' => $min]);

            // Stock por talla (S–XL; talla única para accesorios). La suma es el inventario total.
            $reparto = Tallas::categoriaUsaTallas($c) ? Tallas::repartir($stock) : [Tallas::UNICA => $stock];
            $orden = 0;
            foreach ($reparto as $talla => $cantidad) {
                ProductoTalla::updateOrCreate(
                    ['id_producto' => $p->id_producto, 'talla' => $talla],
                    ['cantidad_disponible' => $cantidad, 'orden' => $orden++]
                );
            }

            // Fotos adicionales de la galería: public/images/productos/galeria/<sku>-*.webp
            $fotos = glob(public_path('images/productos/galeria/'.strtolower($sku).'-*.webp')) ?: [];
            usort($fotos, fn ($a, $b) => [! str_contains($a, '-vista-'), $a] <=> [! str_contains($b, '-vista-'), $b]);
            foreach ($fotos as $n => $ruta) {
                ProductoImagen::updateOrCreate(
                    ['id_producto' => $p->id_producto, 'ruta' => 'images/productos/galeria/'.basename($ruta)],
                    ['orden' => $n + 1]
                );
            }
        }
    
        // Ampliación del catálogo: ocultos hasta tener foto (ver App\Support\CatalogoNuevo)
        CatalogoNuevo::registrar();
    }
}

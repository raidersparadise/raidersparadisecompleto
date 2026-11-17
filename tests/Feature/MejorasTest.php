<?php

/**
 * Raiders Paradise - Tienda en línea de accesorios premium para motociclistas.
 *
 * Pruebas de las mejoras v2.3: compresión y metadatos de imágenes, etiquetas en el buscador
 * y alertas de stock bajo.
 *
 * @package   RaidersParadise
 * @author    Equipo de desarrollo Raiders Paradise (ADSO - SENA)
 * @copyright 2026 Raiders Paradise
 * @license   MIT
 * @version   2.3.0
 * @since     2026-11-07
 */

namespace Tests\Feature;

use App\Models\Etiqueta;
use App\Models\Inventario;
use App\Models\Producto;
use App\Models\Usuario;
use App\Notifications\StockBajo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Ejecutar con: php artisan test --filter=MejorasTest
 */
class MejorasTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    private function admin(): Usuario
    {
        return Usuario::where('email', 'admin@raidersparadise.co')->first();
    }

    private function crearProducto(array $extra = []): Producto
    {
        $this->actingAs($this->admin())->post('/admin/productos', $extra + [
            'sku' => 'CAS-TEST-777', 'nombre_producto' => 'Casco de prueba', 'precio_producto' => 500000,
            'id_categoria' => 1, 'id_marca' => 1, 'id_proveedor' => 1, 'estado_producto' => 'activo',
            'stock_tallas' => ['S' => 5, 'M' => 5, 'L' => 5, 'XL' => 5], 'cantidad_minima' => 3,
            'imagen' => UploadedFile::fake()->image('casco.jpg', 600, 600),
        ])->assertRedirect('/admin/productos');

        return Producto::where('sku', 'CAS-TEST-777')->firstOrFail();
    }

    private function borrarArchivos(Producto $p): void
    {
        @unlink(public_path($p->imagen_producto));
        foreach ($p->imagenes()->get() as $img) {
            @unlink(public_path($img->ruta));
        }
    }

    public function test_las_imagenes_se_comprimen_a_webp_y_guardan_metadatos(): void
    {
        $p = $this->crearProducto([
            'imagen' => UploadedFile::fake()->image('grande.jpg', 2400, 1600),
            'imagenes' => [UploadedFile::fake()->image('lado.png', 3000, 3000)],
        ]);

        $this->assertStringEndsWith('.webp', $p->imagen_producto);
        [$ancho, $alto] = getimagesize(public_path($p->imagen_producto));
        $this->assertSame(1200, $ancho);   // reducida al ancho máximo
        $this->assertSame(800, $alto);     // conserva la proporción

        $foto = $p->imagenes()->first();
        $this->assertStringEndsWith('.webp', $foto->ruta);
        $this->assertSame(1200, $foto->ancho);
        $this->assertSame(1200, $foto->alto);
        $this->assertGreaterThan(0, $foto->peso_bytes);

        $this->borrarArchivos($p);
    }

    public function test_imagen_pequena_no_se_agranda(): void
    {
        $p = $this->crearProducto(['imagen' => UploadedFile::fake()->image('mini.jpg', 400, 300)]);
        [$ancho] = getimagesize(public_path($p->imagen_producto));
        $this->assertSame(400, $ancho);
        $this->borrarArchivos($p);
    }

    public function test_etiquetas_se_guardan_y_se_encuentran_en_el_buscador(): void
    {
        $p = $this->crearProducto(['etiquetas' => 'Impermeable, touring , impermeable,  ']);

        $this->assertSame(['impermeable', 'touring'], $p->etiquetas()->orderBy('nombre')->pluck('nombre')->all());

        $this->get('/catalogo?q=tourin')->assertOk()->assertSee('Casco de prueba');
        $this->get('/catalogo?etiqueta=impermeable')->assertOk()->assertSee('Casco de prueba')->assertSee('#impermeable');

        // Ficha de un producto del catálogo con etiquetas
        $casco = Producto::where('sku', 'CAS-SHO-001')->first();
        $casco->etiquetas()->sync(Etiqueta::idsDesdeTexto('touring'));
        $this->get("/producto/{$casco->id_producto}")->assertOk()->assertSee('#touring');

        // Al editar se reemplazan las etiquetas
        $this->actingAs($this->admin())->put("/admin/productos/{$p->id_producto}", [
            'sku' => 'CAS-TEST-777', 'nombre_producto' => 'Casco de prueba', 'precio_producto' => 500000,
            'id_categoria' => 1, 'id_marca' => 1, 'id_proveedor' => 1, 'estado_producto' => 'activo',
            'stock_tallas' => ['S' => 5, 'M' => 5, 'L' => 5, 'XL' => 5], 'cantidad_minima' => 3,
            'etiquetas' => 'cuero',
        ])->assertRedirect('/admin/productos');
        $this->assertSame(['cuero'], $p->etiquetas()->pluck('nombre')->all());
        $this->assertSame(3, Etiqueta::count()); // impermeable, touring y cuero

        $this->borrarArchivos($p);
    }

    public function test_alerta_cuando_un_movimiento_deja_el_stock_bajo(): void
    {
        Notification::fake();
        $p = $this->crearProducto();   // 20 unidades, mínimo 3
        Notification::assertNothingSent();

        $inv = $p->inventario;
        $this->put("/admin/inventario/{$inv->id_inventario}", ['movimiento' => 'ajuste', 'talla' => 'S', 'cantidad' => 0, 'cantidad_minima' => 3]);
        $this->put("/admin/inventario/{$inv->id_inventario}", ['movimiento' => 'ajuste', 'talla' => 'M', 'cantidad' => 0, 'cantidad_minima' => 3]);
        $this->put("/admin/inventario/{$inv->id_inventario}", ['movimiento' => 'ajuste', 'talla' => 'L', 'cantidad' => 0, 'cantidad_minima' => 3]);
        Notification::assertNothingSent(); // aún quedan 5

        $this->put("/admin/inventario/{$inv->id_inventario}", ['movimiento' => 'ajuste', 'talla' => 'XL', 'cantidad' => 2, 'cantidad_minima' => 3]);
        Notification::assertSentTo($this->admin(), StockBajo::class, fn ($n) => $n->inventarios->first()->producto->sku === 'CAS-TEST-777');
        Notification::assertCount(Usuario::administradores()->count());

        // Seguir bajando no repite la alerta
        $this->put("/admin/inventario/{$inv->id_inventario}", ['movimiento' => 'salida', 'talla' => 'XL', 'cantidad' => 1, 'cantidad_minima' => 3]);
        Notification::assertCount(Usuario::administradores()->count());

        $this->borrarArchivos($p);
    }

    public function test_alerta_cuando_una_venta_deja_el_stock_bajo(): void
    {
        Notification::fake();
        $p = $this->crearProducto(['stock_tallas' => ['S' => 0, 'M' => 5, 'L' => 0, 'XL' => 0], 'cantidad_minima' => 3]);
        $cliente = Usuario::where('email', 'juan.perez@correo.co')->first();

        $this->actingAs($cliente)->post("/carrito/agregar/{$p->id_producto}", ['cantidad' => 2, 'talla' => 'M'])->assertSessionHas('ok');
        $this->actingAs($cliente)->post('/checkout', [
            'direccion_envio' => 'Calle 1 # 2-3', 'ciudad_envio' => 'Bogotá', 'telefono' => '3000000000', 'metodo_pago' => 'nequi',
        ]);

        $this->assertSame(3, $p->inventario->fresh()->cantidad_disponible);
        Notification::assertSentTo($this->admin(), StockBajo::class);

        $this->borrarArchivos($p);
    }

    public function test_comando_diario_y_campanita_del_panel(): void
    {
        $bajos = Inventario::whereColumn('cantidad_disponible', '<=', 'cantidad_minima')
            ->whereHas('producto', fn ($q) => $q->where('estado_producto', 'activo'))->count();
        $this->assertGreaterThan(0, $bajos, 'Los datos de prueba deberían tener productos en stock bajo');

        $this->artisan('inventario:alertar')->assertSuccessful()->expectsOutputToContain("{$bajos} producto(s)");

        $admin = $this->admin();
        $this->assertSame(1, $admin->unreadNotifications()->count());

        // La notificación por correo se arma bien (con el modo "array" de pruebas)
        $correo = (new StockBajo(Inventario::with('producto')->whereColumn('cantidad_disponible', '<=', 'cantidad_minima')->get()))->toMail($admin);
        $this->assertStringContainsString('Stock bajo', $correo->subject);

        $this->actingAs($admin)->get('/admin')->assertOk()->assertSee('alertas de stock sin leer');
        $this->actingAs($admin)->get('/admin/inventario?nivel=bajo')->assertOk();
        $this->assertSame(0, $admin->unreadNotifications()->count());
    }
}

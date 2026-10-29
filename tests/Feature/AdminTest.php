<?php

/**
 * Raiders Paradise - Tienda en línea de accesorios premium para motociclistas.
 *
 * Pruebas funcionales del panel administrativo y los reportes.
 *
 * @package   RaidersParadise
 * @author    Equipo de desarrollo Raiders Paradise (ADSO - SENA)
 * @copyright 2026 Raiders Paradise
 * @license   MIT
 * @version   2.0.0
 * @since     2026-10-29
 */

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Reporte;
use App\Models\Usuario;
use App\Services\ReporteService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

/**
 * Pruebas del panel: pantallas, reportes en sus 3 formatos, CRUD, inventario y usuarios.
 *
 * Ejecutar con: php artisan test --filter=AdminTest
 */
class AdminTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    private function admin(): Usuario
    {
        return Usuario::where('email', 'admin@raidersparadise.co')->first();
    }

    public function test_todas_las_pantallas_del_panel_cargan(): void
    {
        $this->actingAs($this->admin());
        foreach (['/admin', '/admin/productos', '/admin/productos/create', '/admin/productos/1/edit', '/admin/inventario',
            '/admin/inventario?nivel=bajo', '/admin/pedidos', '/admin/pedidos/1', '/admin/clientes', '/admin/clientes/1',
            '/admin/pqr', '/admin/pqr/1', '/admin/usuarios', '/admin/usuarios/create', '/admin/reportes',
            '/admin/maestros/categorias', '/admin/maestros/marcas', '/admin/maestros/proveedores', '/admin/maestros/marcas/crear'] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_todos_los_reportes_en_pantalla_pdf_y_excel(): void
    {
        $this->actingAs($this->admin());
        $q = '?desde='.now()->subDays(120)->toDateString().'&hasta='.now()->toDateString();

        foreach (array_keys(ReporteService::TIPOS) as $tipo) {
            $this->get("/admin/reportes/{$tipo}{$q}&formato=pantalla")->assertOk()->assertSee(ReporteService::TIPOS[$tipo]['titulo']);
            $this->get("/admin/reportes/{$tipo}{$q}&formato=pdf")->assertOk()->assertSee('Imprimir o guardar PDF');
            $csv = $this->get("/admin/reportes/{$tipo}{$q}&formato=csv");
            $csv->assertOk()->assertHeader('content-type', 'text/csv; charset=UTF-8');
            $this->assertStringContainsString(ReporteService::TIPOS[$tipo]['titulo'], $csv->streamedContent());
        }
        $this->assertSame(21, Reporte::count()); // queda trazabilidad de cada reporte generado
    }

    public function test_reporte_de_ventas_cuadra_con_los_pedidos(): void
    {
        $r = app(ReporteService::class)->generar('ventas', now()->subDays(200), now());
        $esperado = \App\Models\Pedido::where('estado', '!=', 'cancelado')->sum('total');
        $this->assertEqualsWithDelta($esperado, $r['kpis'][0][1], 0.01);
    }

    public function test_rango_de_fechas_invalido(): void
    {
        $this->actingAs($this->admin())
            ->get('/admin/reportes/ventas?desde=2026-10-10&hasta=2026-10-01')
            ->assertSessionHasErrors('hasta');
    }

    public function test_crear_producto_con_imagen_e_inventario(): void
    {
        $this->actingAs($this->admin())->post('/admin/productos', [
            'sku' => 'CAS-TEST-999', 'nombre_producto' => 'Casco de prueba', 'precio_producto' => 500000,
            'id_categoria' => 1, 'id_marca' => 1, 'id_proveedor' => 1, 'estado_producto' => 'activo',
            'cantidad_disponible' => 7, 'cantidad_minima' => 2,
            'imagen' => UploadedFile::fake()->image('casco.jpg', 600, 600),
        ])->assertRedirect('/admin/productos');

        $p = Producto::where('sku', 'CAS-TEST-999')->first();
        $this->assertSame(7, $p->stock);
        $this->assertStringStartsWith('uploads/productos/', $p->imagen_producto);
        $this->assertFileExists(public_path($p->imagen_producto));
        @unlink(public_path($p->imagen_producto));
    }

    public function test_producto_con_ventas_se_inactiva_en_vez_de_borrarse(): void
    {
        $p = Producto::has('detallesPedido')->first();
        $this->actingAs($this->admin())->delete("/admin/productos/{$p->id_producto}")->assertSessionHas('ok');
        $this->assertSame('inactivo', $p->fresh()->estado_producto);
    }

    public function test_crud_categoria_y_bloqueo_si_tiene_productos(): void
    {
        $this->actingAs($this->admin());
        $this->post('/admin/maestros/categorias', ['nombre_categoria' => 'Gafas'])->assertRedirect('/admin/maestros/categorias');
        $nueva = Categoria::where('nombre_categoria', 'Gafas')->first();
        $this->delete("/admin/maestros/categorias/{$nueva->id_categoria}")->assertSessionHas('ok');
        $this->delete('/admin/maestros/categorias/1')->assertSessionHas('error');
        $this->assertNotNull(Categoria::find(1));
    }

    public function test_movimiento_de_inventario(): void
    {
        $this->actingAs($this->admin());
        $inv = Producto::where('sku', 'GUA-REV-008')->first()->inventario; // 0 unidades
        $this->put("/admin/inventario/{$inv->id_inventario}", ['movimiento' => 'entrada', 'cantidad' => 10, 'cantidad_minima' => 3])->assertSessionHas('ok');
        $this->assertSame(10, $inv->fresh()->cantidad_disponible);
        $this->put("/admin/inventario/{$inv->id_inventario}", ['movimiento' => 'salida', 'cantidad' => 50, 'cantidad_minima' => 3])->assertSessionHas('error');
    }

    public function test_admin_crea_usuario_y_no_puede_desactivarse_a_si_mismo(): void
    {
        $this->actingAs($this->admin());
        $this->post('/admin/usuarios', [
            'nombre_usuario' => 'Luis', 'apellido_usuario' => 'Mora', 'email' => 'luis@raidersparadise.co',
            'id_rol' => 2, 'password' => 'Bodega2026', 'password_confirmation' => 'Bodega2026', 'activo' => '1',
        ])->assertRedirect('/admin/usuarios');
        $this->assertTrue(Usuario::where('email', 'luis@raidersparadise.co')->exists());

        $this->delete('/admin/usuarios/'.$this->admin()->id_usuario)->assertSessionHas('error');
        $this->assertTrue($this->admin()->fresh()->activo);
    }
}

<?php

/**
 * Raiders Paradise - Tienda en línea de accesorios premium para motociclistas.
 *
 * Pruebas funcionales de la tienda, la autenticación, las compras y las PQR.
 *
 * @package   RaidersParadise
 * @author    Equipo de desarrollo Raiders Paradise (ADSO - SENA)
 * @copyright 2026 Raiders Paradise
 * @license   MIT
 * @version   2.0.0
 * @since     2026-10-29
 */

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Inventario;
use App\Models\Pedido;
use App\Models\Pqr;
use App\Models\Producto;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Pruebas de extremo a extremo de la parte pública y del cliente.
 *
 * Cada prueba parte de una base SQLite en memoria con los datos de los seeders
 * (RefreshDatabase + $seed = true). Ejecutar con: php artisan test --filter=TiendaTest
 */
class TiendaTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    private function usuario(string $email): Usuario
    {
        return Usuario::where('email', $email)->firstOrFail();
    }

    public function test_paginas_publicas_cargan(): void
    {
        $this->get('/')->assertOk()->assertSee('Raiders Paradise');
        $this->get('/catalogo')->assertOk()->assertSee('<strong>24</strong> productos', false);
        $this->get('/catalogo?categoria=1')->assertOk()->assertSee('Shoei GT-Air 3');
        $this->get('/catalogo?q=dainese')->assertOk()->assertSee('Dainese')->assertDontSee('Shoei GT-Air 3');
        $this->get('/producto/1')->assertOk()->assertSee('CAS-SHO-001');
        $this->get('/pqr')->assertOk();
    }

    public function test_se_crearon_los_cinco_usuarios_de_prueba(): void
    {
        $this->assertSame(5, Usuario::count());
        foreach (['admin@raidersparadise.co', 'ventas@raidersparadise.co', 'juan.perez@correo.co', 'maria.torres@correo.co', 'santiago.lopez@correo.co'] as $email) {
            $this->post('/login', ['email' => $email, 'password' => 'Raiders2026*'])->assertRedirect();
            $this->assertAuthenticated();
            $this->post('/logout');
        }
    }

    public function test_login_incorrecto_muestra_error(): void
    {
        $this->post('/login', ['email' => 'admin@raidersparadise.co', 'password' => 'mala'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_usuario_inactivo_no_puede_ingresar(): void
    {
        $this->usuario('maria.torres@correo.co')->update(['activo' => false]);
        $this->post('/login', ['email' => 'maria.torres@correo.co', 'password' => 'Raiders2026*'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_registro_crea_usuario_cliente_enlazado(): void
    {
        $this->post('/registro', [
            'nombre' => 'Ana', 'apellido' => 'Ríos', 'email' => 'ana@correo.co',
            'password' => 'Moto2026x', 'password_confirmation' => 'Moto2026x', 'terminos' => '1',
        ])->assertRedirect();

        $u = $this->usuario('ana@correo.co');
        $this->assertTrue($u->tieneRol('Cliente'));
        $this->assertNotNull($u->cliente);
    }

    public function test_roles_y_permisos(): void
    {
        $this->get('/admin')->assertRedirect('/login');
        $this->actingAs($this->usuario('juan.perez@correo.co'))->get('/admin')->assertForbidden();
        $this->actingAs($this->usuario('ventas@raidersparadise.co'))->get('/admin')->assertOk();
        $this->actingAs($this->usuario('ventas@raidersparadise.co'))->get('/admin/reportes')->assertForbidden();
        $this->actingAs($this->usuario('ventas@raidersparadise.co'))->get('/admin/usuarios')->assertForbidden();
        $this->actingAs($this->usuario('admin@raidersparadise.co'))->get('/admin/reportes')->assertOk();
        $this->actingAs($this->usuario('admin@raidersparadise.co'))->get('/admin/usuarios')->assertOk();
    }

    public function test_compra_completa_descuenta_inventario_y_genera_factura(): void
    {
        $cliente = $this->usuario('juan.perez@correo.co');
        $producto = Producto::where('sku', 'GUA-ALP-006')->first();
        $stockInicial = $producto->inventario->cantidad_disponible;

        $this->actingAs($cliente)->post("/carrito/agregar/{$producto->id_producto}", ['cantidad' => 2])->assertSessionHas('ok');
        $this->actingAs($cliente)->get('/carrito')->assertOk()->assertSee($producto->nombre_producto);

        $res = $this->actingAs($cliente)->post('/checkout', [
            'direccion_envio' => 'Calle 1 # 2-3', 'ciudad_envio' => 'Bogotá', 'telefono' => '3000000000', 'metodo_pago' => 'nequi',
        ]);

        $pedido = Pedido::latest('id_pedido')->first();
        $res->assertRedirect(route('cuenta.pedido', $pedido));
        $this->assertEquals(1780000, (float) $pedido->total);
        $this->assertSame('pagada', $pedido->factura->estado_factura);
        $this->assertEqualsWithDelta(1780000, $pedido->factura->subtotal + $pedido->factura->impuesto, 0.01);
        $this->assertSame($stockInicial - 2, $producto->inventario->fresh()->cantidad_disponible);
        $this->assertSame(0, $cliente->cliente->carrito->detalles()->count());

        $this->actingAs($cliente)->get("/factura/{$pedido->id_pedido}")->assertOk()->assertSee($pedido->factura->numero_factura);
    }

    public function test_no_se_puede_comprar_mas_del_stock(): void
    {
        $producto = Producto::where('sku', 'BOT-DAI-019')->first(); // stock 1
        $this->actingAs($this->usuario('juan.perez@correo.co'))
            ->post("/carrito/agregar/{$producto->id_producto}", ['cantidad' => 5])
            ->assertSessionHas('error');
    }

    public function test_producto_agotado_no_se_agrega(): void
    {
        $producto = Producto::where('sku', 'GUA-REV-008')->first(); // stock 0
        $this->actingAs($this->usuario('juan.perez@correo.co'))
            ->post("/carrito/agregar/{$producto->id_producto}")
            ->assertSessionHas('error');
    }

    public function test_cliente_no_ve_pedidos_de_otro(): void
    {
        $ajeno = Pedido::where('id_cliente', '!=', $this->usuario('juan.perez@correo.co')->cliente->id_cliente)->first();
        $this->actingAs($this->usuario('juan.perez@correo.co'))->get("/mis-pedidos/{$ajeno->id_pedido}")->assertForbidden();
        $this->actingAs($this->usuario('juan.perez@correo.co'))->get("/factura/{$ajeno->id_pedido}")->assertForbidden();
    }

    public function test_cancelar_pedido_devuelve_inventario_y_anula_factura(): void
    {
        $pedido = Pedido::with('detalles')->where('estado', 'entregado')->first();
        $pedido->update(['estado' => 'pendiente']);
        $d = $pedido->detalles->first();
        $antes = Inventario::where('id_producto', $d->id_producto)->value('cantidad_disponible');

        $this->actingAs($this->usuario('ventas@raidersparadise.co'))
            ->put("/admin/pedidos/{$pedido->id_pedido}", ['estado' => 'cancelado'])->assertSessionHas('ok');

        $this->assertSame('cancelado', $pedido->fresh()->estado);
        $this->assertSame('anulada', $pedido->factura->fresh()->estado_factura);
        $this->assertGreaterThanOrEqual($antes + $d->cantidad, Inventario::where('id_producto', $d->id_producto)->value('cantidad_disponible'));
    }

    public function test_pqr_de_visitante_crea_cliente_y_radicado(): void
    {
        $this->post('/pqr', [
            'nombre' => 'Pedro', 'apellido' => 'Gil', 'email' => 'pedro@correo.co', 'tipo' => 'peticion',
            'asunto' => 'Tallas', 'descripcion' => '¿Tienen cascos talla XL?', 'terminos' => '1',
        ])->assertRedirect('/pqr')->assertSessionHas('ok');

        $this->assertTrue(Cliente::where('email', 'pedro@correo.co')->exists());
        $this->assertSame('recibida', Pqr::latest('id_pqr')->first()->estado);
    }

    public function test_cerrar_pqr_exige_respuesta(): void
    {
        $pqr = Pqr::where('estado', 'recibida')->first();
        $admin = $this->usuario('admin@raidersparadise.co');
        $this->actingAs($admin)->put("/admin/pqr/{$pqr->id_pqr}", ['estado' => 'cerrada'])->assertSessionHasErrors('respuesta');
        $this->actingAs($admin)->put("/admin/pqr/{$pqr->id_pqr}", ['estado' => 'cerrada', 'respuesta' => 'Listo, resuelto.'])->assertSessionHas('ok');
        $this->assertNotNull($pqr->fresh()->fecha_cierre);
    }
}

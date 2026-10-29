<?php

/**
 * Raiders Paradise - Tienda en línea de accesorios premium para motociclistas.
 *
 * Definición de todas las rutas web: tienda pública, autenticación, zona del cliente y panel
 * administrativo. Ver la lista completa con: php artisan route:list --except-vendor
 *
 * @package   RaidersParadise
 * @author    Equipo de desarrollo Raiders Paradise (ADSO - SENA)
 * @copyright 2026 Raiders Paradise
 * @license   MIT
 * @version   2.0.0
 * @since     2026-10-29
 */

use App\Http\Controllers\Admin;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CarritoController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CuentaController;
use App\Http\Controllers\PqrController;
use App\Http\Controllers\TiendaController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Tienda pública
|--------------------------------------------------------------------------
*/
Route::get('/', [TiendaController::class, 'home'])->name('home');
Route::get('/catalogo', [TiendaController::class, 'catalogo'])->name('catalogo');
Route::get('/producto/{producto}', [TiendaController::class, 'producto'])->name('producto');
Route::get('/pqr', [PqrController::class, 'create'])->name('pqr.create');
Route::post('/pqr', [PqrController::class, 'store'])->middleware('throttle:10,1')->name('pqr.store');

/*
|--------------------------------------------------------------------------
| Autenticación
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:6,1');
    Route::get('/registro', [AuthController::class, 'showRegistro'])->name('registro');
    Route::post('/registro', [AuthController::class, 'registro'])->middleware('throttle:6,1');
});
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

/*
|--------------------------------------------------------------------------
| Zona del cliente
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/mi-cuenta', [CuentaController::class, 'perfil'])->name('cuenta.perfil');
    Route::put('/mi-cuenta', [CuentaController::class, 'actualizarPerfil'])->name('cuenta.actualizar');
    Route::get('/factura/{pedido}', [CuentaController::class, 'factura'])->name('cuenta.factura');

    Route::middleware('rol:Cliente')->group(function () {
        Route::get('/carrito', [CarritoController::class, 'index'])->name('carrito');
        Route::post('/carrito/agregar/{producto}', [CarritoController::class, 'agregar'])->name('carrito.agregar');
        Route::patch('/carrito/{detalle}', [CarritoController::class, 'actualizar'])->name('carrito.actualizar');
        Route::delete('/carrito/{detalle}', [CarritoController::class, 'eliminar'])->name('carrito.eliminar');

        Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout');
        Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');

        Route::get('/mis-pedidos', [CuentaController::class, 'pedidos'])->name('cuenta.pedidos');
        Route::get('/mis-pedidos/{pedido}', [CuentaController::class, 'pedido'])->name('cuenta.pedido');
        Route::get('/mis-pqr', [CuentaController::class, 'pqrs'])->name('cuenta.pqrs');
    });
});

/*
|--------------------------------------------------------------------------
| Panel administrativo (Administrador y Vendedor)
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->middleware(['auth', 'rol:Administrador,Vendedor'])->group(function () {
    Route::get('/', [Admin\DashboardController::class, 'index'])->name('dashboard');

    Route::resource('productos', Admin\ProductoController::class)->except('show');

    Route::get('maestros/{tipo}', [Admin\MaestroController::class, 'index'])->name('maestros.index');
    Route::get('maestros/{tipo}/crear', [Admin\MaestroController::class, 'create'])->name('maestros.create');
    Route::post('maestros/{tipo}', [Admin\MaestroController::class, 'store'])->name('maestros.store');
    Route::get('maestros/{tipo}/{id}/editar', [Admin\MaestroController::class, 'edit'])->name('maestros.edit');
    Route::put('maestros/{tipo}/{id}', [Admin\MaestroController::class, 'update'])->name('maestros.update');
    Route::delete('maestros/{tipo}/{id}', [Admin\MaestroController::class, 'destroy'])->name('maestros.destroy');

    Route::get('inventario', [Admin\InventarioController::class, 'index'])->name('inventario.index');
    Route::put('inventario/{inventario}', [Admin\InventarioController::class, 'update'])->name('inventario.update');

    Route::get('pedidos', [Admin\PedidoController::class, 'index'])->name('pedidos.index');
    Route::get('pedidos/{pedido}', [Admin\PedidoController::class, 'show'])->name('pedidos.show');
    Route::put('pedidos/{pedido}', [Admin\PedidoController::class, 'update'])->name('pedidos.update');

    Route::get('clientes', [Admin\ClienteController::class, 'index'])->name('clientes.index');
    Route::get('clientes/{cliente}', [Admin\ClienteController::class, 'show'])->name('clientes.show');

    Route::get('pqr', [Admin\PqrController::class, 'index'])->name('pqr.index');
    Route::get('pqr/{pqr}', [Admin\PqrController::class, 'show'])->name('pqr.show');
    Route::put('pqr/{pqr}', [Admin\PqrController::class, 'update'])->name('pqr.update');

    // Solo administrador: reportes gerenciales y gestión de usuarios
    Route::middleware('rol:Administrador')->group(function () {
        Route::get('reportes', [Admin\ReporteController::class, 'index'])->name('reportes.index');
        Route::get('reportes/{tipo}', [Admin\ReporteController::class, 'generar'])->name('reportes.generar');
        Route::resource('usuarios', Admin\UsuarioController::class)->except('show');
    });
});

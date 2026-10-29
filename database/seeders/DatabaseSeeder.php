<?php

/**
 * Raiders Paradise - Tienda en línea de accesorios premium para motociclistas.
 *
 * Seeder principal que carga todos los datos de prueba.
 *
 * @package   RaidersParadise
 * @author    Equipo de desarrollo Raiders Paradise (ADSO - SENA)
 * @copyright 2026 Raiders Paradise
 * @license   MIT
 * @version   2.0.0
 * @since     2026-10-29
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Ejecuta los seeders en orden. Uso: php artisan migrate:fresh --seed
 */
class DatabaseSeeder extends Seeder
{
    /**
     * Llama a RolSeeder, UsuarioSeeder, CatalogoSeeder y DemoVentasSeeder.
     *
     * @return void
     */
    public function run(): void
    {
        $this->call([
            RolSeeder::class,
            UsuarioSeeder::class,   // 5 usuarios de prueba
            CatalogoSeeder::class,  // categorías, marcas, proveedores, productos e inventario
            DemoVentasSeeder::class, // pedidos, facturas y PQR de ejemplo para los reportes
        ]);
    }
}

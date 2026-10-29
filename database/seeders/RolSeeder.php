<?php

/**
 * Raiders Paradise - Tienda en línea de accesorios premium para motociclistas.
 *
 * Seeder de roles del sistema.
 *
 * @package   RaidersParadise
 * @author    Equipo de desarrollo Raiders Paradise (ADSO - SENA)
 * @copyright 2026 Raiders Paradise
 * @license   MIT
 * @version   2.0.0
 * @since     2026-10-29
 */

namespace Database\Seeders;

use App\Models\Rol;
use Illuminate\Database\Seeder;

/**
 * Crea o actualiza los tres roles: Administrador, Vendedor y Cliente.
 */
class RolSeeder extends Seeder
{
    /**
     * Inserta los roles (idempotente: se puede ejecutar varias veces).
     *
     * @return void
     */
    public function run(): void
    {
        $roles = [
            [Rol::ADMIN, 'Acceso total: catálogo, inventario, pedidos, PQR, usuarios y reportes gerenciales.'],
            [Rol::VENDEDOR, 'Gestiona catálogo, inventario, pedidos, clientes y PQR. No accede a reportes ni usuarios.'],
            [Rol::CLIENTE, 'Compra en la tienda, consulta sus pedidos y facturas y radica PQR.'],
        ];

        foreach ($roles as [$nombre, $descripcion]) {
            Rol::updateOrCreate(['nombre_rol' => $nombre], ['descripcion' => $descripcion]);
        }
    }
}

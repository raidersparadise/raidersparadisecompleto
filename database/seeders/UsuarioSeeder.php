<?php

/**
 * Raiders Paradise - Tienda en línea de accesorios premium para motociclistas.
 *
 * Seeder de los 5 usuarios de prueba.
 *
 * @package   RaidersParadise
 * @author    Equipo de desarrollo Raiders Paradise (ADSO - SENA)
 * @copyright 2026 Raiders Paradise
 * @license   MIT
 * @version   2.0.0
 * @since     2026-10-29
 */

namespace Database\Seeders;

use App\Models\Cliente;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Database\Seeder;

/**
 * Crea los 5 usuarios de prueba: 1 Administrador, 1 Vendedor y 3 Clientes.
 * Contraseña de todos: Raiders2026* (se guarda cifrada con bcrypt).
 */
class UsuarioSeeder extends Seeder
{
    /**
     * Contraseña común de los usuarios de prueba (solo para desarrollo).
     *
     * @var string
     */
    public const PASSWORD = 'Raiders2026*';

    /**
     * Inserta los usuarios y, para los clientes, su registro en la tabla cliente.
     *
     * @return void
     */
    public function run(): void
    {
        $roles = Rol::pluck('id_rol', 'nombre_rol');

        $usuarios = [
            ['Andrés', 'Rojas', 'admin@raidersparadise.co', '3001112233', Rol::ADMIN, null],
            ['Camila', 'Herrera', 'ventas@raidersparadise.co', '3002223344', Rol::VENDEDOR, null],
            ['Juan', 'Pérez', 'juan.perez@correo.co', '3105556677', Rol::CLIENTE, ['1019876543', 'Cra. 15 # 93-60, Apto 402', 'Bogotá']],
            ['María', 'Torres', 'maria.torres@correo.co', '3157778899', Rol::CLIENTE, ['52345678', 'Calle 10 # 43-21', 'Medellín']],
            ['Santiago', 'López', 'santiago.lopez@correo.co', '3204445566', Rol::CLIENTE, ['80123456', 'Av. 6N # 23-45', 'Cali']],
        ];

        foreach ($usuarios as [$nombre, $apellido, $email, $tel, $rol, $cliente]) {
            $usuario = Usuario::updateOrCreate(['email' => $email], [
                'id_rol' => $roles[$rol],
                'nombre_usuario' => $nombre,
                'apellido_usuario' => $apellido,
                'telefono' => $tel,
                'password' => self::PASSWORD,
                'activo' => true,
            ]);

            if ($cliente) {
                [$doc, $dir, $ciudad] = $cliente;
                Cliente::updateOrCreate(['email' => $email], [
                    'id_usuario' => $usuario->id_usuario,
                    'documento' => $doc,
                    'nombre_cliente' => $nombre,
                    'apellido_cliente' => $apellido,
                    'telefono' => $tel,
                    'direccion' => $dir,
                    'ciudad' => $ciudad,
                ]);
            }
        }
    }
}

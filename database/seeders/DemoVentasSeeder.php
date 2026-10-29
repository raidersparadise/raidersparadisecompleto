<?php

/**
 * Raiders Paradise - Tienda en línea de accesorios premium para motociclistas.
 *
 * Seeder de ventas y PQR de demostración.
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
use App\Models\Pedido;
use App\Models\Pqr;
use App\Models\Producto;
use App\Models\Usuario;
use App\Services\PedidoService;
use Illuminate\Database\Seeder;

/**
 * Genera 5 clientes adicionales, unos 85 pedidos con factura en los últimos 4 meses
 * y 7 PQR, para que el dashboard y los reportes tengan datos desde el inicio.
 * Usa una semilla aleatoria fija (2026) para que los datos sean reproducibles.
 * No descuenta inventario: el stock del CatalogoSeeder representa el saldo actual.
 */
class DemoVentasSeeder extends Seeder
{
    /**
     * Inserta clientes, pedidos, detalles, facturas y PQR de prueba.
     *
     * @return void
     */
    public function run(): void
    {
        mt_srand(2026);
        $servicio = new PedidoService();

        // Clientes adicionales (sin usuario) para que los reportes se vean realistas
        $extra = [
            ['Laura', 'Gómez', 'laura.gomez@correo.co', 'Bogotá'], ['Felipe', 'Castro', 'felipe.castro@correo.co', 'Bucaramanga'],
            ['Valentina', 'Ruiz', 'valentina.ruiz@correo.co', 'Barranquilla'], ['Daniel', 'Moreno', 'daniel.moreno@correo.co', 'Bogotá'],
            ['Natalia', 'Vargas', 'natalia.vargas@correo.co', 'Pereira'],
        ];
        foreach ($extra as [$n, $a, $e, $c]) {
            Cliente::firstOrCreate(['email' => $e], ['nombre_cliente' => $n, 'apellido_cliente' => $a, 'ciudad' => $c, 'direccion' => 'Calle '.mt_rand(10, 150).' # '.mt_rand(1, 99).'-'.mt_rand(10, 99), 'telefono' => '3'.mt_rand(100000000, 299999999)]);
        }

        $clientes = Cliente::all();
        $productos = Producto::all();
        $metodos = ['nequi', 'pse', 'tarjeta', 'tarjeta', 'contraentrega'];

        for ($i = 0; $i < 85; $i++) {
            $diasAtras = mt_rand(0, 120);
            $fecha = now()->subDays($diasAtras)->setTime(mt_rand(8, 21), mt_rand(0, 59));
            $cliente = $clientes[mt_rand(0, $clientes->count() - 1)];

            if ($diasAtras > 6) {
                $estado = mt_rand(1, 100) <= 8 ? 'cancelado' : 'entregado';
            } else {
                $estado = ['pendiente', 'programado', 'en_curso', 'entregado'][mt_rand(0, 3)];
            }

            $pedido = Pedido::create([
                'id_cliente' => $cliente->id_cliente, 'fecha' => $fecha, 'estado' => $estado, 'total' => 0,
                'direccion_envio' => $cliente->direccion, 'ciudad_envio' => $cliente->ciudad,
            ]);

            $total = 0;
            foreach ($productos->random(mt_rand(1, 3)) as $prod) {
                $cant = mt_rand(1, 10) > 8 ? 2 : 1;
                $sub = $cant * (float) $prod->precio_producto;
                $total += $sub;
                $pedido->detalles()->create(['id_producto' => $prod->id_producto, 'cantidad' => $cant, 'precio_unitario' => $prod->precio_producto, 'sub_total' => $sub]);
            }
            $pedido->update(['total' => $total]);

            $pedido->created_at = $fecha;
            $pedido->save();

            $metodo = $metodos[mt_rand(0, count($metodos) - 1)];
            $factura = $servicio->generarFactura($pedido, $metodo, $fecha);
            if ($estado === 'cancelado') {
                $factura->update(['estado_factura' => 'anulada']);
            } elseif ($estado === 'entregado' && $metodo === 'contraentrega') {
                $factura->update(['estado_factura' => 'pagada', 'pago' => $total]);
            }
        }

        $vendedor = Usuario::where('email', 'ventas@raidersparadise.co')->first();
        $pqrs = [
            ['peticion', '¿Tienen el AGV K6 S en talla XS?', 'Quisiera saber si pueden conseguir el casco AGV K6 S en talla XS y cuánto tardaría.', 'cerrada', 'Hola, sí lo podemos pedir. Llega en 8 días hábiles. Ya te enviamos la cotización al correo.', 20],
            ['queja', 'Demora en la entrega', 'Mi pedido tardó 6 días en llegar a Medellín y me habían dicho 3.', 'cerrada', 'Lamentamos la demora: la transportadora tuvo novedades en ruta. Te enviamos un bono de envío gratis.', 35],
            ['reclamo', 'Guante con costura suelta', 'El guante derecho llegó con una costura suelta en el dedo índice. Solicito cambio.', 'en proceso', null, 4],
            ['sugerencia', 'Más tallas de botas', 'Sería genial que manejaran tallas 46 y 47 en botas de pista.', 'asignada', null, 2],
            ['peticion', 'Factura a nombre de empresa', 'Necesito que la factura de mi último pedido salga a nombre de mi empresa con NIT.', 'recibida', null, 1],
            ['reclamo', 'Cobro duplicado PSE', 'Me debitaron dos veces el valor del pedido por PSE.', 'cerrada', 'Verificamos con el banco y el segundo cobro fue reversado. Se refleja en 3 días hábiles.', 50],
            ['queja', 'No responden el WhatsApp', 'Escribí al WhatsApp y nadie respondió en todo el día.', 'rechazada', 'Nuestro canal oficial de atención es este formulario de PQR y el correo. El WhatsApp es solo para ventas.', 15],
        ];
        foreach ($pqrs as $i => [$tipo, $asunto, $desc, $estado, $resp, $dias]) {
            $fecha = now()->subDays($dias);
            Pqr::create([
                'id_cliente' => $clientes[$i % $clientes->count()]->id_cliente,
                'id_usuario' => $estado === 'recibida' ? null : $vendedor?->id_usuario,
                'tipo' => $tipo, 'asunto' => $asunto, 'descripcion_pqr' => $desc, 'estado' => $estado,
                'respuesta' => $resp, 'fecha' => $fecha->toDateString(),
                'fecha_cierre' => in_array($estado, ['cerrada', 'rechazada']) ? $fecha->copy()->addDays(mt_rand(1, 5)) : null,
            ]);
        }
    }
}

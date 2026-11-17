<?php

/**
 * Raiders Paradise - Tienda en línea de accesorios premium para motociclistas.
 *
 * Notificación de stock bajo o agotado para los administradores (correo + campanita del panel).
 *
 * @package   RaidersParadise
 * @author    Equipo de desarrollo Raiders Paradise (ADSO - SENA)
 * @copyright 2026 Raiders Paradise
 * @license   MIT
 * @version   2.3.0
 * @since     2026-11-07
 */

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;

/**
 * Se envía desde Producto::sincronizarStock() (al pasar de "ok" a "bajo"/"agotado")
 * y desde el comando diario "php artisan inventario:alertar".
 */
class StockBajo extends Notification
{
    /**
     * @param  Collection<int, \App\Models\Inventario>  $inventarios  Inventarios con la relación producto cargada.
     */
    public function __construct(public Collection $inventarios)
    {
    }

    /**
     * Canales: correo (SMTP configurado en .env) y base de datos (tabla notifications).
     *
     * @param  object  $notifiable
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Correo con la lista de productos.
     *
     * @param  object  $notifiable
     * @return MailMessage
     */
    public function toMail(object $notifiable): MailMessage
    {
        $total = $this->inventarios->count();
        $correo = (new MailMessage)
            ->subject($total === 1
                ? 'Stock bajo: '.$this->inventarios->first()->producto->nombre_producto
                : "Stock bajo en {$total} productos – Raiders Paradise")
            ->greeting('Hola '.$notifiable->nombre_usuario.',')
            ->line('Estos productos están en su cantidad mínima o agotados:');

        foreach ($this->inventarios as $i) {
            $estado = $i->cantidad_disponible <= 0 ? 'AGOTADO' : "{$i->cantidad_disponible} und";
            $correo->line("• {$i->producto->sku} – {$i->producto->nombre_producto}: {$estado} (mínimo {$i->cantidad_minima})");
        }

        return $correo
            ->action('Ver inventario', route('admin.inventario.index', ['nivel' => 'bajo']))
            ->line('Recuerda solicitar la reposición al proveedor.');
    }

    /**
     * Datos guardados en la tabla notifications.
     *
     * @param  object  $notifiable
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'total' => $this->inventarios->count(),
            'productos' => $this->inventarios->map(fn ($i) => [
                'sku' => $i->producto->sku,
                'nombre' => $i->producto->nombre_producto,
                'disponible' => (int) $i->cantidad_disponible,
                'minimo' => (int) $i->cantidad_minima,
            ])->values()->all(),
        ];
    }
}

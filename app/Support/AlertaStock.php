<?php

/**
 * Raiders Paradise - Tienda en línea de accesorios premium para motociclistas.
 *
 * Envío de alertas de stock bajo a los administradores.
 *
 * @package   RaidersParadise
 * @author    Equipo de desarrollo Raiders Paradise (ADSO - SENA)
 * @copyright 2026 Raiders Paradise
 * @license   MIT
 * @version   2.3.0
 * @since     2026-11-07
 */

namespace App\Support;

use App\Models\Inventario;
use App\Models\Usuario;
use App\Notifications\StockBajo;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;

/**
 * Centraliza la regla "avisar solo cuando un producto pasa de ok a bajo/agotado"
 * para usarla desde el panel (Producto::sincronizarStock) y desde las ventas (PedidoService).
 */
class AlertaStock
{
    /**
     * Indica si el inventario acaba de cruzar el mínimo.
     *
     * @param  string|null  $nivelAntes  Nivel antes del cambio ("ok", "bajo" o "agotado").
     * @param  Inventario  $inventario  Inventario con la cantidad ya actualizada.
     * @return bool
     */
    public static function cruzoMinimo(?string $nivelAntes, Inventario $inventario): bool
    {
        return $nivelAntes === 'ok' && $inventario->nivel !== 'ok';
    }

    /**
     * Envía la notificación a los administradores activos.
     * Si el correo falla (SMTP mal configurado) se registra en el log y no interrumpe la operación.
     *
     * @param  Collection<int, Inventario>  $inventarios
     * @return int Administradores notificados (0 si no había a quién o si falló el envío).
     */
    public static function enviar(Collection $inventarios): int
    {
        if ($inventarios->isEmpty()) {
            return 0;
        }

        try {
            $admins = Usuario::administradores();
            if ($admins->isNotEmpty()) {
                $inventarios->each->loadMissing('producto');
                Notification::send($admins, new StockBajo($inventarios->values()));
            }

            return $admins->count();
        } catch (\Throwable $e) {
            report($e);

            return 0;
        }
    }
}

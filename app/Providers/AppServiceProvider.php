<?php

/**
 * Raiders Paradise - Tienda en línea de accesorios premium para motociclistas.
 *
 * Proveedor de servicios principal de la aplicación.
 *
 * @package   RaidersParadise
 * @author    Equipo de desarrollo Raiders Paradise (ADSO - SENA)
 * @copyright 2026 Raiders Paradise
 * @license   MIT
 * @version   2.0.0
 * @since     2026-10-29
 */

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

/**
 * Configuración global que se ejecuta al iniciar la aplicación.
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Registra servicios en el contenedor. No se usa actualmente.
     *
     * @return void
     */
    public function register(): void
    {
        //
    }

    /**
     * Configura la aplicación al arrancar:
     * - Paginación con la vista personalizada partials.paginacion.
     * - Directiva Blade @cop($valor) para mostrar pesos colombianos.
     * - Variable $carritoItems (unidades en el carrito) en el layout de la tienda.
     *
     * @return void
     */
    public function boot(): void
    {
        Paginator::defaultView('partials.paginacion');

        // @cop($valor) imprime un valor en pesos colombianos: $ 1.250.000
        Blade::directive('cop', fn ($exp) => "<?php echo e(\\App\\Support\\Formato::cop($exp)); ?>");

        // Contador del carrito disponible en todas las vistas de la tienda
        View::composer('layouts.tienda', function ($view) {
            $items = 0;
            $usuario = auth()->user();
            if ($usuario && $usuario->cliente && $usuario->cliente->carrito) {
                $items = (int) $usuario->cliente->carrito->detalles()->sum('cantidad');
            }
            $view->with('carritoItems', $items);
        });
    }
}

<?php

/**
 * Raiders Paradise - Tienda en línea de accesorios premium para motociclistas.
 *
 * Controlador del módulo de reportes gerenciales (solo Administrador).
 *
 * @package   RaidersParadise
 * @author    Equipo de desarrollo Raiders Paradise (ADSO - SENA)
 * @copyright 2026 Raiders Paradise
 * @license   MIT
 * @version   2.0.0
 * @since     2026-10-29
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reporte;
use App\Services\ReporteService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Genera los reportes en pantalla, PDF (vista imprimible) o CSV para Excel, y
 * registra cada generación en la tabla reporte.
 */
class ReporteController extends Controller
{
    /**
     * Pantalla de reportes con el catálogo y los últimos 12 generados.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index()
    {
        $historial = Reporte::with('usuario')->latest('fecha_generacion')->take(12)->get();

        return view('admin.reportes.index', ['tipos' => ReporteService::TIPOS, 'historial' => $historial]);
    }

    /**
     * Genera un reporte en el formato solicitado y deja registro en la tabla reporte.
     *
     * Parámetros de consulta: desde, hasta (por defecto, últimos 30 días; máximo 2 años)
     * y formato (pantalla | pdf | csv).
     *
     * @param \Illuminate\Http\Request $request Petición HTTP actual.
     * @param string $tipo Clave del reporte (ver {@see ReporteService::TIPOS}).
     * @param ReporteService $servicio Servicio de reportes (inyectado).
     * @return \Illuminate\Contracts\View\View|\Symfony\Component\HttpFoundation\StreamedResponse|\Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Validation\ValidationException Si los datos no cumplen las reglas de validación.
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException Si el tipo no existe.
     */
    public function generar(Request $request, string $tipo, ReporteService $servicio)
    {
        abort_unless(isset(ReporteService::TIPOS[$tipo]), 404);

        $datos = $request->validate([
            'desde' => ['nullable', 'date'],
            'hasta' => ['nullable', 'date', 'after_or_equal:desde'],
            'formato' => ['nullable', 'in:pantalla,pdf,csv'],
        ], ['hasta.after_or_equal' => 'La fecha final debe ser igual o posterior a la inicial.']);

        $hasta = isset($datos['hasta']) ? Carbon::parse($datos['hasta']) : now();
        $desde = isset($datos['desde']) ? Carbon::parse($datos['desde']) : $hasta->copy()->subDays(29);
        if ($desde->diffInDays($hasta) > 731) {
            return back()->withErrors(['desde' => 'El rango máximo es de 2 años.']);
        }
        $formato = $datos['formato'] ?? 'pantalla';

        $reporte = $servicio->generar($tipo, $desde, $hasta);

        Reporte::create([
            'id_usuario' => $request->user()->id_usuario,
            'tipo_reporte' => $reporte['titulo'],
            'formato' => $formato,
            'fecha_desde' => $reporte['usa_fechas'] ? $desde->toDateString() : null,
            'fecha_hasta' => $reporte['usa_fechas'] ? $hasta->toDateString() : null,
            'fecha_generacion' => now(),
        ]);

        return match ($formato) {
            'csv' => $this->csv($reporte),
            'pdf' => view('admin.reportes.imprimir', compact('reporte')),
            default => view('admin.reportes.ver', ['reporte' => $reporte, 'tipos' => ReporteService::TIPOS]),
        };
    }

    /**
     * Descarga el reporte como CSV compatible con Excel en español.
     *
     * Usa separador ";" y BOM UTF-8 para que Excel muestre bien columnas y tildes.
     *
     * @param array<string, mixed> $reporte Datos generados por ReporteService.
     * @return StreamedResponse
     */
    private function csv(array $reporte): StreamedResponse
    {
        $nombre = 'raiders-'.$reporte['tipo'].'-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($reporte) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Raiders Paradise — '.$reporte['titulo']], ';');
            if ($reporte['usa_fechas']) {
                fputcsv($out, ['Periodo', $reporte['desde']->format('d/m/Y').' a '.$reporte['hasta']->format('d/m/Y')], ';');
            }
            fputcsv($out, ['Generado', now()->format('d/m/Y H:i')], ';');
            fputcsv($out, [], ';');
            fputcsv($out, array_column($reporte['columnas'], 0), ';');
            $tipos = array_column($reporte['columnas'], 1);
            $fmt = function ($v, $i) use ($tipos) {
                return match ($tipos[$i] ?? 'text') {
                    'money' => is_numeric($v) ? number_format((float) $v, 0, ',', '') : $v,
                    'pct' => is_numeric($v) ? number_format($v * 100, 1, ',', '').'%' : $v,
                    default => $v,
                };
            };
            foreach ($reporte['filas'] as $fila) {
                fputcsv($out, array_map($fmt, $fila, array_keys($fila)), ';');
            }
            if (! empty($reporte['totales'])) {
                fputcsv($out, array_map($fmt, $reporte['totales'], array_keys($reporte['totales'])), ';');
            }
            fclose($out);
        }, $nombre, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}

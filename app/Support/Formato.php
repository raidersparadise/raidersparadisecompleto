<?php

/**
 * Raiders Paradise - Tienda en línea de accesorios premium para motociclistas.
 *
 * Utilidades de formato de valores para las vistas.
 *
 * @package   RaidersParadise
 * @author    Equipo de desarrollo Raiders Paradise (ADSO - SENA)
 * @copyright 2026 Raiders Paradise
 * @license   MIT
 * @version   2.0.0
 * @since     2026-10-29
 */

namespace App\Support;

/**
 * Funciones de formato con convenciones colombianas (punto de miles, coma decimal).
 *
 * En Blade se usa a través de la directiva @cop($valor), registrada en
 * {@see \App\Providers\AppServiceProvider::boot()}.
 */
class Formato
{
    /**
     * Formatea un valor como pesos colombianos sin decimales.
     *
     * Ejemplo: Formato::cop(1250000) devuelve "$ 1.250.000".
     *
     * @param float|int|string|null $valor Valor numérico; null se trata como 0.
     * @return string
     */
    public static function cop(float|int|string|null $valor): string
    {
        return '$ '.number_format((float) $valor, 0, ',', '.');
    }

    /**
     * Formatea un número con separador de miles "." y decimal ",".
     *
     * @param float|int|string|null $valor Valor numérico; null se trata como 0.
     * @param int $decimales Cantidad de decimales.
     * @return string
     */
    public static function numero(float|int|string|null $valor, int $decimales = 0): string
    {
        return number_format((float) $valor, $decimales, ',', '.');
    }
}

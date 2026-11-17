<?php

/**
 * Raiders Paradise - Tienda en línea de accesorios premium para motociclistas.
 *
 * Reglas de tallas del catálogo.
 *
 * @package   RaidersParadise
 * @author    Equipo de desarrollo Raiders Paradise (ADSO - SENA)
 * @copyright 2026 Raiders Paradise
 * @license   MIT
 * @version   2.2.0
 * @since     2026-11-04
 */

namespace App\Support;

/**
 * Define qué tallas maneja cada categoría y cómo repartir unidades entre ellas.
 *
 * - Cascos, guantes, chaquetas, impermeables y botas: S, M, L y XL.
 * - Accesorios (intercomunicadores, baúles, visores, Pinlock): talla única.
 */
final class Tallas
{
    /** Tallas de ropa y protección, en orden de menor a mayor. */
    public const TALLAS = ['S', 'M', 'L', 'XL'];

    /** Valor usado por los productos que no tienen talla. */
    public const UNICA = 'Única';

    /** Categorías cuyos productos no manejan talla. */
    public const CATEGORIAS_SIN_TALLA = ['Accesorios'];

    /** Equivalencias que se muestran en la guía de tallas. */
    public const GUIA = [
        'Cascos' => ['S' => '55–56 cm', 'M' => '57–58 cm', 'L' => '59–60 cm', 'XL' => '61–62 cm'],
        'Guantes' => ['S' => '8 – 8,5', 'M' => '9 – 9,5', 'L' => '10 – 10,5', 'XL' => '11'],
        'Chaquetas' => ['S' => '46 EU', 'M' => '48–50 EU', 'L' => '52 EU', 'XL' => '54–56 EU'],
        'Impermeables' => ['S' => '46 EU', 'M' => '48–50 EU', 'L' => '52 EU', 'XL' => '54–56 EU'],
        'Botas' => ['S' => '39–40', 'M' => '41–42', 'L' => '43–44', 'XL' => '45–46'],
    ];

    /** Medida que explica la guía de cada categoría. */
    public const MEDIDA = [
        'Cascos' => 'Contorno de cabeza',
        'Guantes' => 'Talla de mano',
        'Chaquetas' => 'Talla europea',
        'Impermeables' => 'Talla europea',
        'Botas' => 'Talla de calzado (EU)',
    ];

    /**
     * Indica si una categoría maneja tallas S–XL.
     *
     * @param  string|null  $categoria  Nombre de la categoría.
     * @return bool
     */
    public static function categoriaUsaTallas(?string $categoria): bool
    {
        return $categoria !== null && ! in_array($categoria, self::CATEGORIAS_SIN_TALLA, true);
    }

    /**
     * Lista de tallas que corresponden a una categoría.
     *
     * @param  string|null  $categoria  Nombre de la categoría.
     * @return array<int, string>
     */
    public static function paraCategoria(?string $categoria): array
    {
        return self::categoriaUsaTallas($categoria) ? self::TALLAS : [self::UNICA];
    }

    /**
     * Reparte un total de unidades entre las tallas S, M, L y XL
     * (las tallas M y L reciben el doble, porque son las que más se venden).
     *
     * @param  int  $total  Unidades a repartir.
     * @return array<string, int> talla => unidades
     */
    public static function repartir(int $total): array
    {
        $pesos = ['S' => 1, 'M' => 2, 'L' => 2, 'XL' => 1];
        $suma = array_sum($pesos);
        $base = [];
        $resto = [];
        foreach ($pesos as $talla => $peso) {
            $exacto = $total * $peso / $suma;
            $base[$talla] = (int) floor($exacto);
            $resto[$talla] = $exacto - $base[$talla];
        }
        arsort($resto);
        $faltan = $total - array_sum($base);
        foreach (array_keys($resto) as $talla) {
            if ($faltan-- <= 0) {
                break;
            }
            $base[$talla]++;
        }

        return $base;
    }
}

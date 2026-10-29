<?php

/**
 * Raiders Paradise - Tienda en línea de accesorios premium para motociclistas.
 *
 * Pruebas unitarias de la clase App\Support\Formato.
 *
 * @package   RaidersParadise
 * @author    Equipo de desarrollo Raiders Paradise (ADSO - SENA)
 * @copyright 2026 Raiders Paradise
 * @license   MIT
 * @version   2.0.0
 * @since     2026-10-29
 */

namespace Tests\Unit;

use App\Support\Formato;
use PHPUnit\Framework\TestCase;

/**
 * Verifica el formato de pesos colombianos.
 *
 * @covers \App\Support\Formato
 */
class FormatoTest extends TestCase
{
    public function test_formatea_pesos_colombianos(): void
    {
        $this->assertSame('$ 1.250.000', Formato::cop(1250000));
        $this->assertSame('$ 0', Formato::cop(null));
    }
}

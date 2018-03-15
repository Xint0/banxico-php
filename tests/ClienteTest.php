<?php

namespace Xint0\BanxicoPHP\Tests;

use Xint0\BanxicoPHP\Cliente;
use PHPUnit\Framework\TestCase;

final class BanxicoPHPTest extends TestCase
{
    public function testCanBeCreatedWithToken()
    {
        $cliente = new Cliente([
            'token' => $_ENV['BANXICO_TOKEN'],
        ]);

        $this->assertInstanceOf(Cliente::class, $cliente);

        return $cliente;
    }

    public function testCannotBeCreatedWithoutToken()
    {
        $this->expectException(\InvalidArgumentException::class);

        new Cliente();
    }

    /**
     * @depends testCanBeCreatedWithToken
     */
    public function testObtenerTipoDeCambioUSDPagosOportuno(Cliente $cliente)
    {
        $respuesta = $cliente->obtenerTipoDeCambioUSDPagos();
        $this->assertTrue($respuesta != '');
    }

    /**
     * @depends testCanBeCreatedWithToken
     */
    public function testObtenerTipoDeCambioUSDPagosFecha(Cliente $cliente)
    {
        $respuesta = $cliente->obtenerTipoDeCambioUSDPagos('2018-03-14');
        $this->assertTrue($respuesta != '');
    }
}

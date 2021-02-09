<?php

declare(strict_types=1);

namespace Xint0\BanxicoPHP\Tests;

use Psr\Http\Message\ResponseInterface;
use Xint0\BanxicoPHP\ClienteBanxicoException;
use Xint0\BanxicoPHP\ResponseParser;
use PHPUnit\Framework\TestCase;

class ResponseParserTest extends TestCase
{
    public function test_parse_method_throws_expected_exception_when_status_code_is_not_success(): void
    {
        $stub = $this->createStub(ResponseInterface::class);
        $stub->method('getStatusCode')->willReturn(500);
        $sut = new ResponseParser();
        $this->expectException(ClienteBanxicoException::class);
        $this->expectExceptionCode(500);
        $this->expectExceptionMessage('Response parse failed.');
        $sut->parse($stub);
    }
}
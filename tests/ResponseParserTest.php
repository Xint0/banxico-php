<?php

declare(strict_types=1);

namespace Xint0\BanxicoPHP\Tests;

use Xint0\BanxicoPHP\ClienteBanxicoException;
use JsonException;
use Psr\Http\Message\ResponseInterface;
use Xint0\BanxicoPHP\ResponseParser;
use RuntimeException;
use Psr\Http\Message\StreamInterface;
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
        $this->expectExceptionMessage('Request failed.');
        $sut->parse($stub);
    }

    public function test_parse_method_throws_expected_exception_when_stream_get_contents_fails(): void
    {
        $stubStream = $this->createStub(StreamInterface::class);
        $runtimeException = new RuntimeException('Could not read stream.');
        $stubStream->method('getContents')->willThrowException($runtimeException);
        $stubResponse = $this->createStub(ResponseInterface::class);
        $stubResponse->method('getStatusCode')->willReturn(200);
        $stubResponse->method('getBody')->willReturn($stubStream);
        $sut = new ResponseParser();
        $this->expectExceptionObject(new ClienteBanxicoException('Could not get response content.', 1, $runtimeException));
        $sut->parse($stubResponse);
    }

    public function test_parse_method_throws_expected_exception_when_stream_is_not_valid_json(): void
    {
        $stubStream = $this->createStub(StreamInterface::class);
        $stubStream->method('getContents')->willReturn('not valid json');
        $stubResponse = $this->createStub(ResponseInterface::class);
        $stubResponse->method('getStatusCode')->willReturn(200);
        $stubResponse->method('getBody')->willReturn($stubStream);
        $sut = new ResponseParser();
        $this->expectExceptionObject(new ClienteBanxicoException('Response parsing failed.', 2, new JsonException()));
        $sut->parse($stubResponse);
    }
}
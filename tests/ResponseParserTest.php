<?php

/**
 * xint0/banxico-php
 *
 * Client for the Banco de Mexico SIE REST API.
 *
 * @author Rogelio Jacinto <ego@rogeliojacinto.com>
 * @copyright 2021 Rogelio Jacinto
 * @license https://github.com/Xint0/banxico-php/blob/master/LICENSE MIT License
 */

declare(strict_types=1);

namespace Xint0\BanxicoPHP\Tests;

use Xint0\BanxicoPHP\ClienteBanxicoException;
use JsonException;
use Psr\Http\Message\ResponseInterface;
use Xint0\BanxicoPHP\ResponseParser;
use RuntimeException;
use Psr\Http\Message\StreamInterface;
use PHPUnit\Framework\TestCase;
use Xint0\BanxicoPHP\SieClientException;

class ResponseParserTest extends TestCase
{
    private const JSON_PATH_SF43718_DATE_RANGE = __DIR__ . '/data/SF43718_date_range.json';
    private const JSON_PATH_SF43718_LATEST = __DIR__ . '/data/SF43718_latest.json';
    private const JSON_PATH_SF60653_DATE_RANGE = __DIR__ . '/data/SF60653_date_range.json';
    private const JSON_PATH_SF60653_LATEST = __DIR__ . '/data/SF60653_latest.json';

    public function test_parse_method_throws_expected_exception_when_status_code_is_not_success(): void
    {
        $stub = $this->createStub(ResponseInterface::class);
        $stub->method('getStatusCode')->willReturn(500);
        $sut = new ResponseParser();
        $this->expectException(SieClientException::class);
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
        $this->expectExceptionObject(new SieClientException('Could not get response content.', 1, $runtimeException));
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
        $this->expectExceptionObject(new SieClientException('Response parsing failed.', 2, new JsonException()));
        $sut->parse($stubResponse);
    }

    public function responseProvider(): array
    {
        return [
            'SF43718 date range' => [
                'test_data' => [
                    'file_path' => self::JSON_PATH_SF43718_DATE_RANGE,
                ],
                'final_state' => [
                    'result' => [
                        '2020-11-26' => '20.0467',
                        '2020-11-27' => '20.0777',
                    ],
                ],
            ],
            'SF43718 latest' => [
                'test_data' => [
                    'file_path' => self::JSON_PATH_SF43718_LATEST,
                ],
                'final_state' => [
                    'result' => '20.0777',
                ],
            ],
            'SF60653 date range' => [
                'test_data' => [
                    'file_path' => self::JSON_PATH_SF60653_DATE_RANGE,
                ],
                'final_state' => [
                    'result' => [
                        '2020-11-26' => '20.0577',
                        '2020-11-27' => '20.0465',
                    ],
                ],
            ],
            'SF60653 latest' => [
                'test_data' => [
                    'file_path' => self::JSON_PATH_SF60653_LATEST,
                ],
                'final_state' => [
                    'result' => '20.0777',
                ],
            ],
        ];
    }

    /**
     * @dataProvider responseProvider
     *
     * @param  array  $test_data
     * @param  array  $final_state
     */
    public function test_parse_method_returns_expected_result(array $test_data, array $final_state): void
    {
        $stubStream = $this->createStub(StreamInterface::class);
        $stubStream->method('getContents')->willReturn(file_get_contents($test_data['file_path']));
        $stubResponse = $this->createStub(ResponseInterface::class);
        $stubResponse->method('getStatusCode')->willReturn(200);
        $stubResponse->method('getBody')->willReturn($stubStream);
        $sut = new ResponseParser();
        $result = $sut->parse($stubResponse);
        static::assertEquals($final_state['result'], $result);
    }
}

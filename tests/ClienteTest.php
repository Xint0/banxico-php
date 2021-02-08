<?php

declare(strict_types=1);

namespace Xint0\BanxicoPHP\Tests;

use Http\Client\Exception\NetworkException;
use Http\Discovery\ClassDiscovery;
use Psr\Http\Message\RequestInterface;
use Xint0\BanxicoPHP\Cliente;
use Psr\Http\Client\ClientInterface;
use Xint0\BanxicoPHP\HttpClientFactory;
use Http\Discovery\Strategy\MockClientStrategy;
use Http\Mock\Client as MockHttpClient;
use Http\Message\RequestMatcher\RequestMatcher;
use Psr\Http\Message\ResponseInterface;
use PHPUnit\Framework\TestCase;

final class ClienteTest extends TestCase
{
    private const JSON_PATH_SF43718_DATE_RANGE = __DIR__ . '/data/SF43718_date_range.json';
    private const JSON_PATH_SF43718_LATEST = __DIR__ . '/data/SF43718_latest.json';
    private const JSON_PATH_SF60653_DATE_RANGE = __DIR__ . '/data/SF60653_date_range.json';
    private const JSON_PATH_SF60653_LATEST = __DIR__ . '/data/SF60653_latest.json';

    protected function setUp(): void
    {
        parent::setUp();
        ClassDiscovery::prependStrategy(MockClientStrategy::class);
    }

    public function test_throws_exception_without_token(): void
    {
        $this->expectError();
        $this->expectErrorMessage('Undefined index: token');
        new Cliente();
    }

    public function test_can_be_created_with_token(): void
    {
        $cliente = new Cliente([ 'token' => 'test-token' ]);

        $this->assertInstanceOf(Cliente::class, $cliente);
    }

    public function expectedRequestProvider(): array
    {
        return [
            'tipo de cambio usd pagos' => [
                'testData' => [
                    'method' => 'obtenerTipoDeCambioUsdPagos',
                ],
                'finalState' => [
                    'series' => 'SF60653',
                    'uriSuffix' => 'oportuno',
                ],
            ],
            'tipo de cambio usd fix' => [
                'testData' => [
                    'method' => 'obtenerTipoDeCambioUsdFix',
                ],
                'finalState' => [
                    'series' => 'SF43718',
                    'uriSuffix' => 'oportuno',
                ],
            ],
            'tipo de cambio usd pagos rango de fechas' => [
                'testData' => [
                    'method' => 'obtenerTipoDeCambioUSDPagos',
                    'params' => [
                        '2020-11-26',
                        '2020-11-27',
                    ],
                ],
                'finalState' => [
                    'series' => 'SF60653',
                    'uriSuffix' => '2020-11-26/2020-11-27',
                ],
            ],
            'tipo de cambio usd fix rango de fechas' => [
                'testData' => [
                    'method' => 'obtenerTipoDeCambioUSDFix',
                    'params' => [
                        '2020-11-26',
                        '2020-11-27',
                    ],
                ],
                'finalState' => [
                    'series' => 'SF43718',
                    'uriSuffix' => '2020-11-26/2020-11-27',
                ],
            ],
            'tipo de cambio usd pagos un día' => [
                'testData' => [
                    'method' => 'obtenerTipoDeCambioUSDPagos',
                    'params' => [
                        '2020-12-01',
                    ],
                ],
                'finalState' => [
                    'series' => 'SF60653',
                    'uriSuffix' => '2020-12-01/2020-12-01',
                ],
            ],
            'tipo de cambio usd fix un día' => [
                'testData' => [
                    'method' => 'obtenerTipoDeCambioUSDFix',
                    'params' => [
                        '2020-11-27',
                    ],
                ],
                'finalState' => [
                    'series' => 'SF43718',
                    'uriSuffix' => '2020-11-27/2020-11-27',
                ],
            ],
            'obtener serie SF60653 oportuno' => [
                'testData' => [
                    'method' => 'obtenerSerie',
                    'params' => [
                        'SF60653',
                    ],
                ],
                'finalState' => [
                    'series' => 'SF60653',
                    'uriSuffix' => 'oportuno',
                ],
            ],
        ];
    }

    /**
     * @dataProvider expectedRequestProvider
     *
     * @param  array  $testData
     * @param  array  $finalState
     */
    public function test_makes_expected_request(array $testData, array $finalState): void
    {
        $expectedSeries = $finalState['series'];
        $expectedUriSuffix = $finalState['uriSuffix'] ?? 'oportuno';
        $expectedUri = "https://www.banxico.org.mx/SieAPIRest/service/v1/series/${expectedSeries}/datos/${expectedUriSuffix}";
        $expectedHeaders = [
            'User-Agent' => [ 'Xint0 BanxicoPHP/0.2.0' ],
            'Accept' => [ 'application/json' ],
            'Bmx-Token' => [ 'test-token' ],
            'Host' => [ 'www.banxico.org.mx' ],
        ];

        /** @var MockHttpClient $mockHttpClient */
        $mockHttpClient = $this->mockHttpClient();
        $httpClient = HttpClientFactory::create('test-token', [], $mockHttpClient);
        $sut = new Cliente([ 'token' => 'test-token' ], $httpClient);

        $method = $testData['method'];
        $params = $testData['params'] ?? [];
        $sut->{$method}(...$params);

        $requests = $mockHttpClient->getRequests();
        $this->assertCount(1, $requests);
        $request = $requests[0];
        $this->assertEquals('GET', $request->getMethod());
        $this->assertEquals($expectedUri, (string)$request->getUri());
        $this->assertEquals($expectedHeaders, $request->getHeaders());
    }

    public function obtenerTipoDeCambioUsdPagosProvider(): array
    {
        return [
            'oportuno' => [
                'testData' => [
                    'params' => [],
                ],
                'finalState' => [
                    'result' => '20.0777',
                ],
            ],
            'rango de fechas' => [
                'testData' => [
                    'params' => [
                        '2020-11-26',
                        '2020-11-27',
                    ],
                ],
                'finalState' => [
                    'result' => [
                        'SF60653' => [
                            '26/11/2020' => '20.0577',
                            '27/11/2020' => '20.0465',
                        ],
                    ],
                ],
            ],
            'un día' => [
                'testData' => [
                    'params' => [
                        '2020-12-01',
                    ],
                ],
                'finalState' => [
                    'result' => '20.0777',
                ],
            ],
        ];
    }

    /**
     * @dataProvider obtenerTipoDeCambioUsdPagosProvider
     *
     * @param  array  $testData
     * @param  array  $finalState
     */
    public function test_obtener_tipo_de_cambio_usd_pagos_method_returns_expected_result(array $testData, array $finalState): void
    {
        $mockHttpClient = $this->mockHttpClient();
        $httpClient = HttpClientFactory::create('test-token', [], $mockHttpClient);
        $sut = new Cliente([ 'token' => 'test-token' ], $httpClient);
        $params = $testData['params'] ?? [];
        $result = $sut->obtenerTipoDeCambioUsdPagos(...$params);
        $this->assertEquals($finalState['result'], $result);
    }

    public function obtenerTipoDeCambioUsdFixProvider(): array
    {
        return [
            'oportuno' => [
                'testData' => [
                    'params' => [],
                ],
                'finalState' => [
                    'result' => '20.0777',
                ],
            ],
            'rango de fechas' => [
                'testData' => [
                    'params' => [
                        '2020-11-26',
                        '2020-11-27',
                    ],
                ],
                'finalState' => [
                    'result' => [
                        'SF43718' => [
                            '26/11/2020' => '20.0467',
                            '27/11/2020' => '20.0777',
                        ],
                    ],
                ],
            ],
            'un día' => [
                'testData' => [
                    'params' => [
                        '2020-11-27',
                    ],
                ],
                'finalState' => [
                    'result' => '20.0777',
                ],
            ],
        ];
    }

    /**
     * @dataProvider obtenerTipoDeCambioUsdFixProvider
     *
     * @param  array  $testData
     * @param  array  $finalState
     */
    public function test_obtener_tipo_de_cambio_usd_fix_method_returns_expected_result(array $testData, array $finalState): void
    {
        $mockHttpClient = $this->mockHttpClient();
        $httpClient = HttpClientFactory::create('test-token', [], $mockHttpClient);
        $sut = new Cliente([ 'token' => 'test-token' ], $httpClient);
        $params = $testData['params'] ?? [];
        $result = $sut->obtenerTipoDeCambioUsdFix(...$params);
        $this->assertEquals($finalState['result'], $result);
    }

    public function test_throws_expected_exception_on_http_client_exception(): void
    {
        $this->expectException(BanxicoClienteException::class);
        $httpClient = HttpClientFactory::create('test-token', [], $this->mockHttpClient());
        $sut = new Cliente([ 'token' => 'test-token' ], $httpClient);
        $sut->obtenerTipoDeCambioUsdPagos('1700-01-01');
    }

    private function mockHttpClient(): ClientInterface
    {
        $mockHttpClient = new MockHttpClient();
        $this->mockHttpClientResponse(
            $mockHttpClient,
            [
                'series' => 'SF60653',
                'body' => file_get_contents(ClienteTest::JSON_PATH_SF60653_LATEST),
                'startDate' => 'oportuno',
            ]
        );
        $this->mockHttpClientResponse(
            $mockHttpClient,
            [
                'series' => 'SF43718',
                'body' => file_get_contents(ClienteTest::JSON_PATH_SF43718_LATEST),
                'startDate' => 'oportuno',
            ]
        );
        $this->mockHttpClientResponse(
            $mockHttpClient,
            [
                'series' => 'SF43718',
                'body' => file_get_contents(ClienteTest::JSON_PATH_SF43718_DATE_RANGE),
                'startDate' => '2020-11-26',
                'endDate' => '2020-11-27',
            ]
        );
        $this->mockHttpClientResponse(
            $mockHttpClient,
            [
                'series' => 'SF60653',
                'body' => file_get_contents(ClienteTest::JSON_PATH_SF60653_DATE_RANGE),
                'startDate' => '2020-11-26',
                'endDate' => '2020-11-27',
            ]
        );
        $this->mockHttpClientResponse(
            $mockHttpClient,
            [
                'series' => 'SF43718',
                'body' => file_get_contents(ClienteTest::JSON_PATH_SF43718_LATEST),
                'startDate' => '2020-11-27',
            ]
        );
        $this->mockHttpClientResponse(
            $mockHttpClient,
            [
                'series' => 'SF60653',
                'body' => file_get_contents(ClienteTest::JSON_PATH_SF60653_LATEST),
                'startDate' => '2020-12-01',
            ]
        );
        $this->mockHttpClientException($mockHttpClient);
        return $mockHttpClient;
    }

    private function mockHttpClientResponse(MockHttpClient $mockHttpClient, array $params): void
    {
        $series = $params['series'];
        $body = $params['body'] ?? '';
        $startDate = $params['startDate'] ?? 'oportuno';
        $endDate = $params['endDate'] ?? $startDate;
        $suffix = $startDate . ($startDate === 'oportuno' ? '' : ($endDate === 'oportuno' ? '' : "\/${endDate}"));
        $requestMatcher = new RequestMatcher(
            "\/SieAPIRest\/service\/v1\/series\/${series}\/datos\/${suffix}$",
            'www.banxico.org.mx',
            'GET',
            'https'
        );
        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockResponse->method('getBody')->willReturn($body);
        $mockResponse->method('getStatusCode')->willReturn(200);
        $mockHttpClient->on($requestMatcher, $mockResponse);
    }

    private function mockHttpClientException(MockHttpClient $mockHttpClient): void
    {
        $requestMatcher = new RequestMatcher(
            '/SieAPIRest/service/v1/series/.+/datos/1700-01-01',
            'www.banxico.org.mx',
            'GET',
            'https'
        );
        $mockException = new NetworkException('Network error', $this->createMock(RequestInterface::class));
        $mockHttpClient->on($requestMatcher, $mockException);
    }
}

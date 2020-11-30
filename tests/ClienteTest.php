<?php

declare(strict_types=1);

namespace Xint0\BanxicoPHP\Tests;

use Http\Discovery\ClassDiscovery;
use Xint0\BanxicoPHP\Cliente;
use Http\Client\HttpClient;
use Xint0\BanxicoPHP\HttpClientFactory;
use Http\Discovery\Strategy\MockClientStrategy;
use Http\Mock\Client as MockHttpClient;
use Http\Message\RequestMatcher\RequestMatcher;
use Psr\Http\Message\ResponseInterface;
use PHPUnit\Framework\TestCase;

final class ClienteTest extends TestCase
{
    private const JSON_PATH_SF60653_LATEST = __DIR__ . '/data/SF60653_latest.json';
    private const JSON_PATH_SF43718_LATEST = __DIR__ . '/data/SF43718_latest.json';

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
                    'result' => '20.0777',
                ],
            ],
            'tipo de cambio usd fix' => [
                'testData' => [
                    'method' => 'obtenerTipoDeCambioUsdFix',
                ],
                'finalState' => [
                    'series' => 'SF43718',
                    'result' => '20.0777',
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
        $expectedUri = "https://www.banxico.org.mx/SieAPIRest/service/v1/series/{$expectedSeries}/datos/oportuno";
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
        $result = call_user_func([$sut, $method], ...$params);

        $requests = $mockHttpClient->getRequests();
        $this->assertCount(1, $requests);
        $request = $requests[0];
        $this->assertEquals('GET', $request->getMethod());
        $this->assertEquals($expectedUri, (string)$request->getUri());
        $this->assertEquals($expectedHeaders, $request->getHeaders());
        $this->assertEquals($finalState['result'], $result);
    }

    private function mockHttpClient(): HttpClient
    {
        $mockHttpClient = new MockHttpClient();
        $this->mockHttpClientResponse(
            $mockHttpClient,
            'SF60653',
            file_get_contents(static::JSON_PATH_SF60653_LATEST)
        );
        $this->mockHttpClientResponse(
            $mockHttpClient,
            'SF43718',
            file_get_contents(static::JSON_PATH_SF43718_LATEST)
        );
        return $mockHttpClient;
    }

    private function mockHttpClientResponse(MockHttpClient $mockHttpClient, string $series, string $body): void
    {
        $requestMatcher = new RequestMatcher(
            "\/SieAPIRest\/service\/v1\/series\/${series}\/datos\/oportuno$",
            'www.banxico.org.mx',
            'GET',
            'https'
        );
        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockResponse->method('getBody')->willReturn($body);
        $mockResponse->method('getStatusCode')->willReturn(200);
        $mockHttpClient->on($requestMatcher, $mockResponse);
    }
}

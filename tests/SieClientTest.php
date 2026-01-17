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

use Http\Client\Exception\NetworkException;
use Http\Discovery\ClassDiscovery;
use Http\Discovery\Strategy\MockClientStrategy;
use Http\Message\RequestMatcher\RequestMatcher;
use Http\Mock\Client as MockHttpClient;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Xint0\BanxicoPHP\SieClient;
use Xint0\BanxicoPHP\SieClientException;

class SieClientTest extends TestCase
{
    private const JSON_PATH_SF43718_DATE_RANGE = __DIR__ . '/data/SF43718_date_range.json';
    private const JSON_PATH_SF43718_LATEST = __DIR__ . '/data/SF43718_latest.json';
    private const JSON_PATH_SF60653_DATE_RANGE = __DIR__ . '/data/SF60653_date_range.json';
    private const JSON_PATH_SF60653_LATEST = __DIR__ . '/data/SF60653_latest.json';

    protected function setUp(): void
    {
        ClassDiscovery::prependStrategy(MockClientStrategy::class);
    }

    /**
     * @return array<string, array<string, array<string, string|string[]>>>
     */
    public static function expectedRequestProvider(): array
    {
        return [
            'USD exchange rate liquidation date' => [
                'test_data' => [
                    'method' => 'exchangeRateUsdLiquidation',
                ],
                'final_state' => [
                    'series' => 'SF60653',
                    'uri_suffix' => 'oportuno',
                ],
            ],
            'USD exchange rate determination date' => [
                'test_data' => [
                    'method' => 'exchangeRateUsdDetermination',
                ],
                'final_state' => [
                    'series' => 'SF43718',
                    'uri_suffix' => 'oportuno',
                ],
            ],
            'USD exchange rate liquidation date, date range' => [
                'test_data' => [
                    'method' => 'exchangeRateUsdLiquidation',
                    'params' => [
                        '2020-11-26',
                        '2020-11-27',
                    ],
                ],
                'final_state' => [
                    'series' => 'SF60653',
                    'uri_suffix' => '2020-11-26/2020-11-27',
                ],
            ],
            'USD exchange rate determination date, date rage' => [
                'test_data' => [
                    'method' => 'exchangeRateUsdDetermination',
                    'params' => [
                        '2020-11-26',
                        '2020-11-27',
                    ],
                ],
                'final_state' => [
                    'series' => 'SF43718',
                    'uri_suffix' => '2020-11-26/2020-11-27',
                ],
            ],
            'USD exchange rate liquidation date, sigle day' => [
                'test_data' => [
                    'method' => 'exchangeRateUsdLiquidation',
                    'params' => [ '2020-12-01' ],
                ],
                'final_state' => [
                    'series' => 'SF60653',
                    'uri_suffix' => '2020-12-01/2020-12-01',
                ],
            ],
            'USD exchange rate determination date, sigle day' => [
                'test_data' => [
                    'method' => 'exchangeRateUsdDetermination',
                    'params' => [ '2020-11-27' ],
                ],
                'final_state' => [
                    'series' => 'SF43718',
                    'uri_suffix' => '2020-11-27/2020-11-27',
                ],
            ],
            'Fetch series SF60653, current' => [
                'test_data' => [
                    'method' => 'fetchSeries',
                    'params' => ['SF60653'],
                ],
                'final_state' => [
                    'series' => 'SF60653',
                    'uri_suffix' => 'oportuno',
                ],
            ],
            'Fetch series SF43718, date range' => [
                'test_data' => [
                    'method' => 'fetchSeries',
                    'params' => ['SF43718','2020-11-26','2020-11-27'],
                ],
                'final_state' => [
                    'series' => 'SF43718',
                    'uri_suffix' => '2020-11-26/2020-11-27',
                ],
            ],
            'Fetch series SF60653, single day' => [
                'test_data' => [
                    'method' => 'fetchSeries',
                    'params' => ['SF60653','2020-12-01'],
                ],
                'final_state' => [
                    'series' => 'SF60653',
                    'uri_suffix' => '2020-12-01/2020-12-01',
                ],
            ],
        ];
    }

    /**
     * @param array<string, string[]|string> $test_data
     * @param array<string, string> $final_state
     */
    #[DataProvider('expectedRequestProvider')]
    public function test_makes_expected_request(array $test_data, array $final_state): void
    {
        $expectedSeries = $final_state['series'];
        $expectedUriSuffix = $final_state['uri_suffix'];
        $expectedUri = "https://www.banxico.org.mx/SieAPIRest/service/v1/series/$expectedSeries/datos/$expectedUriSuffix";
        $expectedHeaders = [
            'User-Agent' => ['Xint0 BanxicoPHP/1.0.0'],
            'Accept' => ['application/json'],
            'Bmx-Token' => ['test-token'],
            'Host' => ['www.banxico.org.mx'],
            'Accept-Encoding' => ['gzip','deflate'],
            'TE' => ['gzip','deflate','chunked'],
        ];

        /** @var MockHttpClient $mockHttpClient */
        $mockHttpClient = $this->mockHttpClient();
        $sut = new SieClient('test-token', $mockHttpClient);

        $method = $test_data['method'];
        $params = $test_data['params'] ?? [];
        $sut->{$method}(...$params);

        $requests = $mockHttpClient->getRequests();
        static::assertCount(1, $requests);
        $request = $requests[0];
        static::assertEquals('GET', $request->getMethod());
        static::assertEquals($expectedUri, (string)$request->getUri());
        static::assertEquals($expectedHeaders, $request->getHeaders());
    }

    /**
     * @return array<string, array<string, string|string[]>>
     */
    public static function exchangeRateUsdLiquidationProvider(): array
    {
        return [
            'current' => [
                'params' => [],
                'expected_result' => '20.0777',
            ],
            'date range' => [
                'params' => [
                    '2020-11-26',
                    '2020-11-27',
                ],
                'expected_result' => [
                    '2020-11-26' => '20.0577',
                    '2020-11-27' => '20.0465',
                ],
            ],
            'single day' => [
                'params' => ['2020-12-01'],
                'expected_result' => '20.0777',
            ],
        ];
    }

    /**
     * @param string[] $params
     */
    #[DataProvider('exchangeRateUsdLiquidationProvider')]
    public function test_exchange_rate_usd_liquidation_method_returns_expected_result(
        array $params,
        string|array $expected_result
    ): void {
        $mockHttpClient = $this->mockHttpClient();
        $sut = new SieClient('test-token', $mockHttpClient);

        $result = $sut->exchangeRateUsdLiquidation(...$params);

        static::assertEquals($expected_result, $result);
    }

    /**
     * @return array<string, array<string, string|string[]>>
     */
    public static function exchangeRateUsdDeterminationProvider(): array
    {
        return [
            'current' => [
                'params' => [],
                'expected_result' => '20.0777',
            ],
            'date range' => [
                'params' => ['2020-11-26','2020-11-27'],
                'expected_result' => [
                    '2020-11-26' => '20.0467',
                    '2020-11-27' => '20.0777',
                ],
            ],
            'single day' => [
                'params' => ['2020-11-27'],
                'expected_result' => '20.0777',
            ],
        ];
    }

    /**
     * @param string[] $params
     */
    #[DataProvider('exchangeRateUsdDeterminationProvider')]
    public function test_exchange_rate_usd_determination_method_returns_expected_result(
        array $params,
        string|array $expected_result
    ): void {
        $mockHttpClient = $this->mockHttpClient();
        $sut = new SieClient('test-token', $mockHttpClient);

        $result = $sut->exchangeRateUsdDetermination(...$params);

        static::assertEquals($expected_result, $result);
    }

    public function test_exchange_rate_usd_determination_method_throws_expected_exception_on_http_client_exception(): void
    {
        $this->expectException(SieClientException::class);
        $sut = new SieClient('test-token', $this->mockHttpClient());
        $sut->exchangeRateUsdDetermination('1700-01-01');
    }

    public function test_exchange_rate_usd_liquidation_method_throws_expected_exception_on_http_client_exception(): void
    {
        $this->expectException(SieClientException::class);
        $sut = new SieClient('test-token', $this->mockHttpClient());
        $sut->exchangeRateUsdLiquidation('1700-01-01');
    }

    public function test_fetch_series_method_throws_expected_exception_on_http_client_exception(): void
    {
        $this->expectException(SieClientException::class);
        $sut = new SieClient('test-token', $this->mockHttpClient());
        $sut->fetchSeries(SieClient::SERIES_USD_EXCHANGE_RATE_DETERMINATION, '1700-01-01');
    }

    private function mockHttpClient(): ClientInterface
    {
        $mockHttpClient = new MockHttpClient();
        $streamSF60653LatestStub = $this->createStub(StreamInterface::class);
        $streamSF60653LatestStub->method('getContents')->willReturn(file_get_contents(self::JSON_PATH_SF60653_LATEST));
        $streamSF60653DateRangeStub = $this->createStub(StreamInterface::class);
        $streamSF60653DateRangeStub->method('getContents')->willReturn(file_get_contents(self::JSON_PATH_SF60653_DATE_RANGE));
        $streamSF43718LatestStub = $this->createStub(StreamInterface::class);
        $streamSF43718LatestStub->method('getContents')->willReturn(file_get_contents(self::JSON_PATH_SF43718_LATEST));
        $streamSF43718DateRangeStub = $this->createStub(StreamInterface::class);
        $streamSF43718DateRangeStub->method('getContents')->willReturn(file_get_contents(self::JSON_PATH_SF43718_DATE_RANGE));
        $this->mockHttpClientResponse(
            $mockHttpClient,
            [
                'series' => 'SF60653',
                'body' => $streamSF60653LatestStub,
                'startDate' => 'oportuno',
            ]
        );
        $this->mockHttpClientResponse(
            $mockHttpClient,
            [
                'series' => 'SF43718',
                'body' => $streamSF43718LatestStub,
                'startDate' => 'oportuno',
            ]
        );
        $this->mockHttpClientResponse(
            $mockHttpClient,
            [
                'series' => 'SF43718',
                'body' => $streamSF43718DateRangeStub,
                'startDate' => '2020-11-26',
                'endDate' => '2020-11-27',
            ]
        );
        $this->mockHttpClientResponse(
            $mockHttpClient,
            [
                'series' => 'SF60653',
                'body' => $streamSF60653DateRangeStub,
                'startDate' => '2020-11-26',
                'endDate' => '2020-11-27',
            ]
        );
        $this->mockHttpClientResponse(
            $mockHttpClient,
            [
                'series' => 'SF43718',
                'body' => $streamSF43718LatestStub,
                'startDate' => '2020-11-27',
            ]
        );
        $this->mockHttpClientResponse(
            $mockHttpClient,
            [
                'series' => 'SF60653',
                'body' => $streamSF60653LatestStub,
                'startDate' => '2020-12-01',
            ]
        );
        $this->mockHttpClientException($mockHttpClient);
        return $mockHttpClient;
    }

    /**
     * @param array<string, mixed> $params
     */
    private function mockHttpClientResponse(MockHttpClient $mockHttpClient, array $params): void
    {
        $series = $params['series'];
        $body = $params['body'] ?? '';
        $startDate = $params['startDate'] ?? 'oportuno';
        $endDate = $params['endDate'] ?? $startDate;
        $suffix = $startDate . ($startDate === 'oportuno' ? '' : ($endDate === 'oportuno' ? '' : "\/$endDate"));
        $requestMatcher = new RequestMatcher(
            "\/SieAPIRest\/service\/v1\/series\/$series\/datos\/$suffix$",
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

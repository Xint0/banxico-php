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

namespace Xint0\BanxicoPHP;

use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Xint0\BanxicoPHP\Factories\HttpClientFactory;
use Xint0\BanxicoPHP\Factories\RequestFactory;

/**
 * Banxico SIE REST API client
 */
class SieClient
{
    public const SERIES_USD_EXCHANGE_RATE_DETERMINATION = 'SF43718';

    public const SERIES_USD_EXCHANGE_RATE_LIQUIDATION = 'SF60653';

    private const DEFAULT_PARAMS = ['base_uri' => 'https://www.banxico.org.mx/SieAPIRest/service/v1'];

    private readonly ClientInterface $httpClient;

    private readonly RequestFactory $requestFactory;

    private readonly ResponseParser $responseParser;

    /**
     * @var array<string, mixed>
     */
    private array $params;

    /**
     * @param  string  $token
     * @param  ClientInterface|null  $httpClient
     * @param  array<string, mixed>  $params
     */
    public function __construct(string $token, ?ClientInterface $httpClient = null, array $params = [])
    {
        $this->params = self::DEFAULT_PARAMS + $params;
        $this->httpClient = HttpClientFactory::create($token, [], $httpClient);
        $this->requestFactory = new RequestFactory($this->baseUri());
        $this->responseParser = new ResponseParser();
    }

    /**
     * @param  string  $series  The data series ID.
     * @param  string|null  $start_date  The start date in YYYY-MM-DD format, optional.
     * @param  string|null  $end_date  The end date in YYYY-MM-DD format, optional.
     *
     * @return array|mixed
     */
    public function fetchSeries(string $series, ?string $start_date = null, ?string $end_date = null)
    {
        $request = $this->requestFactory->createRequest($series, $start_date, $end_date);
        try {
            $response = $this->httpClient->sendRequest($request);
        } catch (ClientExceptionInterface $clientException) {
            throw new SieClientException('HTTP request failed', 0, $clientException);
        }

        return $this->responseParser->parse($response);
    }

    /**
     * @param  string|null  $start_date  The start date in YYYY-MM-DD format, optional.
     * @param  string|null  $end_date  The end date in YYYY-MM-DD format, optional.
     *
     * @return array|mixed
     */
    public function exchangeRateUsdDetermination(?string $start_date = null, ?string $end_date = null)
    {
        return $this->fetchSeries(self::SERIES_USD_EXCHANGE_RATE_DETERMINATION, $start_date, $end_date);
    }

    /**
     * @param  string|null  $start_date  The start date in YYYY-MM-DD format, optional.
     * @param  string|null  $end_date  The end date in YYYY-MM-DD format, optional.
     *
     * @return array|mixed
     */
    public function exchangeRateUsdLiquidation(?string $start_date = null, ?string $end_date = null)
    {
        return $this->fetchSeries(self::SERIES_USD_EXCHANGE_RATE_LIQUIDATION, $start_date, $end_date);
    }

    private function baseUri(): string
    {
        return $this->params['base_uri'];
    }
}

<?php

declare(strict_types=1);

namespace Xint0\BanxicoPHP;

use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Exception;
use Http\Discovery\Psr17FactoryDiscovery;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriFactoryInterface;

class Cliente
{
    private const SERIES_FIX_USD_EXCHANGE_RATE = 'SF43718';
    private const SERIES_PAYMENTS_USD_EXCHANGE_RATE = 'SF60653';

    private array $config;
    private ClientInterface $client;
    private RequestFactoryInterface $requestFactory;
    private UriFactoryInterface $uriFactory;

    /**
     * Crea una instancia de la clase.
     *
     * @param  array  $config  Opciones de configuración.
     * @param  ClientInterface|null  $cliente  El cliente HTTP.
     */
    public function __construct($config = [], ?ClientInterface $cliente = null)
    {
        $this->config = $this->initialConfiguration($config);
        $this->client = $cliente ?? HttpClientFactory::create($config['token']);
        $this->requestFactory = Psr17FactoryDiscovery::findRequestFactory();
        $this->uriFactory = Psr17FactoryDiscovery::findUriFactory();
    }

    /**
     * Devuelve la serie de datos indicada.
     *
     * @param  string  $series
     * @param  string|null  $startDate
     * @param  string|null  $endDate
     *
     * @return array|false|null
     *
     * @throws ClienteBanxicoException
     */
    public function obtenerSerie(string $series, ?string $startDate = null, ?string $endDate = null)
    {
        $normalizedStartDate = self::normalizeDate($startDate) ?? 'oportuno';
        $normalizedEndDate = self::normalizeDate($endDate) ?? 'oportuno';
        try {
            return self::processResponse($this->sendRequest($series, $normalizedStartDate, $normalizedEndDate));
        } catch (ClientExceptionInterface $clientException) {
            throw new ClienteBanxicoException('HTTP request failed.', 0, $clientException);
        }
    }

    /**
     * Devuelve el tipo de cambio para pagos denominados en dólares estadounidenses.
     *
     * @param  string|null  $fechaInicio
     * @param  string|null  $fechaFinal
     *
     * @return array|false|null
     *
     * @throws ClienteBanxicoException
     */
    public function obtenerTipoDeCambioUsdPagos(?string $fechaInicio = null, ?string $fechaFinal = null)
    {
        return $this->obtenerSerie(self::SERIES_PAYMENTS_USD_EXCHANGE_RATE, $fechaInicio, $fechaFinal);
    }

    /**
     * Devuelve el tipo de cambio fix pesos por dólar estadounidense.
     *
     * @param  string|null  $fechaInicio
     * @param  string|null  $fechaFinal
     *
     * @return array|false|null
     *
     * @throws ClienteBanxicoException
     */
    public function obtenerTipoDeCambioUsdFix(?string $fechaInicio = null, ?string $fechaFinal = null)
    {
        return $this->obtenerSerie(self::SERIES_FIX_USD_EXCHANGE_RATE, $fechaInicio, $fechaFinal);
    }

    /**
     * Configura las opciones del cliente.
     *
     * @param  array  $config
     *
     * @return array
     */
    private function initialConfiguration(array $config): array
    {
        $defaults = [
            'url' => 'https://www.banxico.org.mx/SieAPIRest/service/v1/'
        ];

        return $config + $defaults;
    }

    /**
     * Sends the HTTP request to the SIE API end-point.
     *
     * @param  string  $series
     * @param  string  $startDate
     * @param  string  $endDate
     *
     * @return ResponseInterface
     *
     * @throws ClientExceptionInterface
     */
    private function sendRequest(string $series, string $startDate, string $endDate): ResponseInterface
    {
        $uri = "series/{$series}/datos/{$startDate}" . ($startDate != 'oportuno' ? ($endDate == 'oportuno' ? "/$startDate" : "/$endDate") : '');
        $uri = $this->uriFactory->createUri($this->config['url'] . $uri);
        $request = $this->requestFactory->createRequest('GET', $uri);
        return $this->client->sendRequest($request);
    }

    private static function processResponse(ResponseInterface $response)
    {
        $statusCode = $response->getStatusCode();

        if ($statusCode == 200) {
            $body = $response->getBody();
            return self::parseResponseBody((string)$body);
        } else {
            return false;
        }
    }

    private static function parseResponseBody(string $body)
    {
        try
        {
            $data = json_decode($body);
            $series = [];
            $itemCount = 0;
            $lastItem = null;
            foreach($data->bmx->series as $serie) {
                $series[$serie->idSerie] = [];
                foreach($serie->datos as $dato) {
                    $series[$serie->idSerie][$dato->fecha] = $dato->dato;
                    $lastItem = $dato->dato;
                    $itemCount++;
                }
            }

            if ($itemCount == 1) {
                return $lastItem;
            }

            return $series;
        }
        catch(Exception $e)
        {
            return false;
        }
    }

    /**
     * Normalize input string as date using `YYYY-MM-DD` format. If parsing fails returns `null`.
     *
     * @param  string|null  $input
     *
     * @return string|null
     */
    private static function normalizeDate(?string $input): ?string
    {
        if ($input === null) {
            return null;
        }

        $date = date_create($input);
        return $date !== false ? date_format($date, 'Y-m-d') : null;
    }
}

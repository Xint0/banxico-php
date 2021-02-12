<?php

declare(strict_types=1);

namespace Xint0\BanxicoPHP;

use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Xint0\BanxicoPHP\Factories\HttpClientFactory;
use Xint0\BanxicoPHP\Factories\RequestFactory;

class Cliente
{
    private const SERIES_FIX_USD_EXCHANGE_RATE = 'SF43718';
    private const SERIES_PAYMENTS_USD_EXCHANGE_RATE = 'SF60653';

    private ClientInterface $client;
    private RequestFactory $requestFactory;
    private ResponseParser $responseParser;

    /**
     * Crea una instancia de la clase.
     *
     * @param  array  $config  Opciones de configuración.
     * @param  ClientInterface|null  $cliente  El cliente HTTP.
     */
    public function __construct($config = [], ?ClientInterface $cliente = null)
    {
        $initialConfig = $this->initialConfiguration($config);
        $this->client = $cliente ?? HttpClientFactory::create($initialConfig['token']);
        $this->requestFactory = new RequestFactory($initialConfig['url']);
        $this->responseParser = new ResponseParser();
    }

    /**
     * Devuelve la serie de datos indicada.
     *
     * @param  string  $series
     * @param  string|null  $startDate
     * @param  string|null  $endDate
     *
     * @return array<string, array<string, string>>
     *
     * @throws ClienteBanxicoException
     */
    public function obtenerSerie(string $series, ?string $startDate = null, ?string $endDate = null): array
    {
        $request = $this->requestFactory->createRequest($series, $startDate, $endDate);
        try {
            $response = $this->client->sendRequest($request);
        } catch (ClientExceptionInterface $clientException) {
            throw new ClienteBanxicoException('HTTP request failed.', 0, $clientException);
        }

        return $this->responseParser->parse($response);
    }

    /**
     * Devuelve el tipo de cambio para pagos denominados en dólares estadounidenses.
     *
     * @param  string|null  $fechaInicio
     * @param  string|null  $fechaFinal
     *
     * @return array|string|false
     *
     * @throws ClienteBanxicoException
     */
    public function obtenerTipoDeCambioUsdPagos(?string $fechaInicio = null, ?string $fechaFinal = null)
    {
        return $this->obtenerYProcesarSerie(self::SERIES_PAYMENTS_USD_EXCHANGE_RATE, $fechaInicio, $fechaFinal);
    }

    /**
     * Devuelve el tipo de cambio fix pesos por dólar estadounidense.
     *
     * @param  string|null  $fechaInicio
     * @param  string|null  $fechaFinal
     *
     * @return array|string|false
     *
     * @throws ClienteBanxicoException
     */
    public function obtenerTipoDeCambioUsdFix(?string $fechaInicio = null, ?string $fechaFinal = null)
    {
        return $this->obtenerYProcesarSerie(self::SERIES_FIX_USD_EXCHANGE_RATE, $fechaInicio, $fechaFinal);
    }

    /**
     * @param  string  $serie
     * @param  string|null  $fechaInicio
     * @param  string|null  $fechaFinal
     *
     * @return array|false|string
     *
     * @throws ClienteBanxicoException
     */
    private function obtenerYProcesarSerie(string $serie, ?string $fechaInicio = null, ?string $fechaFinal = null)
    {
        try {
            $result = $this->obtenerSerie($serie, $fechaInicio, $fechaFinal);
        } catch (ClienteBanxicoException $clienteBanxicoException) {
            if ($clienteBanxicoException->getCode() > 200) {
                return false;
            }

            throw $clienteBanxicoException;
        }

        $firstKeyValue = $result[array_key_first($result)];
        if (count($result) === 1 && count($firstKeyValue) === 1) {
            return $firstKeyValue[array_key_first($firstKeyValue)];
        }

        return $result;
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
            'url' => 'https://www.banxico.org.mx/SieAPIRest/service/v1'
        ];

        return $config + $defaults;
    }
}

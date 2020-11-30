<?php

namespace Xint0\BanxicoPHP;

use Exception;
use Http\Client\HttpClient;
use InvalidArgumentException;
use Http\Discovery\Psr17FactoryDiscovery;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use Psr\Http\Message\UriFactoryInterface;

/**
 * @method string obtenerTipoDeCambioUsdPagos(string $fechaInicio = null, string $fechaFinal = null)
 * @method string obtenerTipoDeCambioUsdFix(string $fechaInicio = null, string $fechaFinal = null)
 */
class Cliente
{
    private const MAPA_SERIES = [
        'TipoDeCambioUsdPagos' => 'SF60653',
        'TipoDeCambioUsdFix' => 'SF43718'
    ];

    private array $config;
    private HttpClient $client;
    private RequestFactoryInterface $requestFactory;
    private UriFactoryInterface $uriFactory;

    /**
     * Crea una instancia de la clase.
     *
     * @param  array  $config  Opciones de configuración.
     * @param  HttpClient|null  $cliente  El cliente HTTP.
     */
    public function __construct($config = [], ?HttpClient $cliente = null)
    {
        $this->configurarOpciones($config);
        $this->client = $cliente ?? HttpClientFactory::create($config['token']);
        $this->requestFactory = Psr17FactoryDiscovery::findRequestFactory();
        $this->uriFactory = Psr17FactoryDiscovery::findUriFactory();
    }

    public function __call($method, $args)
    {
        if (substr($method, 0, 7) === 'obtener') {
            $series = self::getSeries(substr($method, 7));
        } else {
            throw new RuntimeException("El método '{$method}' no existe.");
        }

        $parameterCount = count($args);
        $startDate = null;
        $endDate = null;
        if ($parameterCount < 1) {
            $startDate = 'oportuno';
            $endDate = 'oportuno';
        } elseif ($parameterCount == 1) {
            $startDate = self::normalizeDate($args[0]);
            $endDate = 'oportuno';
        } else {
            $startDate = self::normalizeDate($args[0]);
            $endDate = self::normalizeDate($args[1]);
        }

        return self::processResponse($this->sendRequest($series, $startDate, $endDate));
    }

    /**
     * Configura las opciones del cliente.
     *
     * @param array $config
     */
    private function configurarOpciones(array $config)
    {
        $defaults = [
            'url' => 'https://www.banxico.org.mx/SieAPIRest/service/v1/'
        ];

        $this->config = $config + $defaults;
    }

    /**
     * Sends the HTTP request to the SIE API end-point.
     *
     * @param string $series
     * @param string $startDate
     * @param string $endDate
     * @return ResponseInterface
     * @throws \Http\Client\Exception
     */
    private function sendRequest(string $series, string $startDate, string $endDate)
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
            return self::parseResponseBody($body);
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
     * Interpreta una cadena de caracteres como una fecha y devuelve la fecha
     * en formato `AAAA-MM-DD`
     *
     * @param string $cadena
     * @return string
     */
    private static function normalizeDate(string $cadena)
    {
        $result = 'oportuno';
        $fecha = date_create($cadena);
        if ($fecha !== false) {
            $result = date_format($fecha, 'Y-m-d');
        }

        return $result;
    }

    /**
     * Obtiene el identificador de la serie a partir del nombre.
     *
     * @param string $nombre
     * @return string
     */
    private static function getSeries(string $nombre)
    {
        if (!array_key_exists($nombre, self::MAPA_SERIES)) {
            throw new InvalidArgumentException("La serie '{$nombre}' no está definida");
        }

        return self::MAPA_SERIES[$nombre];
    }
}

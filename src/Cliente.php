<?php
namespace Xint0\BanxicoPHP;

use GuzzleHttp\Client;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;

/**
 * @method string obtenerTipoDeCambioUSDPagos(string $fechaInicio = null, string $fechaFinal = null)
 * @method string obtenerTipoDeCambioUSDFix(string $fechaInicio = null, string $fechaFinal = null)
 */
class Cliente
{
    /** @var array Series */
    const MAPA_SERIES = [
        'TipoDeCambioUSDPagos' => 'SF60653',
        'TipoDeCambioUSDFix' => 'SF43718'
    ];

    /** @var array Opciones */
    private $config;

    /** @var \GuzzleHttp\Client El cliente HTTP */
    private $client;

    /**
     * Crea una instancia de la clase.
     *
     * @param array $config Opciones de configuración.
     */
    public function __construct(array $config = [])
    {
        if (!isset($config['token'])) {
            throw new \InvalidArgumentException('Se debe indicar el token');
        }

        $this->configurarOpciones($config);
        $this->client = new Client([
            'base_uri' => $this->config['url'],
            'headers' => [
                'User-Agent' => 'Xint0\BanxicoPHP 0.1.0',
                'Accept' => 'application/json',
                'Bmx-Token' => $this->config['token'],
            ],
            'timeout' => 2.0,
        ]);
    }

    public function __call($method, $args)
    {
        if (substr($method, 0, 7) === 'obtener') {
            $series = self::getSeries(substr($method, 7));
        } else {
            throw new \RuntimeException("El método '{$method}' no existe.");
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
     * @return string
     */
    private function sendRequest(string $series, string $startDate, string $endDate)
    {
        $uri = "series/{$series}/datos/{$startDate}" . ($startDate != 'oportuno' ? ($endDate == 'oportuno' ? "/$startDate" : "/$endDate") : '');
        $request = new Request('GET', $uri);
        return $this->client->send($request, [
            'debug' => false
        ]);
    }

    private static function processResponse(\Psr\Http\Message\ResponseInterface $response)
    {
        $statusCode = $response->getStatusCode();
        $reasonPhrase = $response->getReasonPhrase();
        $protocolVersion = $response->getProtocolVersion();

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
     * en formato AAAA-MM-DD
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
            throw new \InvalidArgumentException("La serie '{$nombre}' no está definida");
        }

        return self::MAPA_SERIES[$nombre];
    }
}

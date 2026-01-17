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

use JsonException;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;

/**
 * Class ResponseParser
 *
 * @package Xint0\BanxicoPHP
 */
class ResponseParser
{
    private const HTTP_STATUS_SUCCESS = 200;

    /**
     * @param  ResponseInterface  $response  The HTTP response.
     *
     * @return array|mixed
     */
    public function parse(ResponseInterface $response)
    {
        $statusCode = $response->getStatusCode();
        if ($statusCode !== self::HTTP_STATUS_SUCCESS) {
            throw new SieClientException('Request failed.', $statusCode);
        }

        try {
            $contents = $response->getBody()->getContents();
        } catch (RuntimeException $runtimeException) {
            throw new SieClientException('Could not get response content.', 1, $runtimeException);
        }

        try {
            $jsonValue = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $jsonException) {
            throw new SieClientException('Response parsing failed.', 2, $jsonException);
        }

        return $this->transformJson($jsonValue);
    }

    /**
     * @param array<string, mixed> $json The decoded JSON array
     *
     * @return array|mixed
     */
    private function transformJson(array $json)
    {
        $result = [];
        foreach ($json['bmx']['series'] as $series) {
            $seriesId = $series['idSerie'];
            $result[$seriesId] = [];
            foreach ($series['datos'] as $record) {
                $date_key = date_create_immutable_from_format('d/m/Y', $record['fecha'])->format('Y-m-d');
                $result[$seriesId][$date_key] = $record['dato'];
            }
        }

        if (count($result) === 1) {
            $result = array_values($result)[0];
        }

        if (count($result) === 1) {
            $result = array_values($result)[0];
        }

        return $result;
    }
}

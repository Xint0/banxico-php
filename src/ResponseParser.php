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

use DateTimeImmutable;
use JsonException;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use ValueError;

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
     * @return array<string, array<string, numeric-string>>|array<string, numeric-string>|numeric-string
     */
    public function parse(ResponseInterface $response): string | array
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

        if (! is_array($jsonValue)) {
            throw new SieClientException('Response parsing failed.', 3);
        }

        if (
            ! array_key_exists('bmx', $jsonValue) ||
            ! is_array($jsonValue['bmx']) ||
            ! array_key_exists('series', $jsonValue['bmx']) ||
            ! is_array($jsonValue['bmx']['series'])
        ) {
            throw new SieClientException('Response parsing failed.', 4);
        }

        return $this->processSeries($jsonValue['bmx']['series']);
    }

    /**
     * @param  array<mixed,mixed>  $series
     *
     * @return array<string, array<string, numeric-string>>|array<string, numeric-string>|numeric-string
     */
    private function processSeries(array $series): array | string
    {
        $mapped = array_map(function (mixed $serie): array | false {
            if (
                is_array($serie) && array_key_exists('idSerie', $serie) && is_string($serie['idSerie']) &&
                array_key_exists('datos', $serie) && is_array($serie['datos'])
            ) {
                return $this->processSeriesData([
                    'idSerie' => $serie['idSerie'],
                    'datos' => $serie['datos'],
                ]);
            }

            return false;
        }, $series);

        $filtered = array_filter($mapped);

        $result = [];
        foreach ($filtered as $serie) {
            $result[$serie['id']] = $serie['data'];
        }

        if (count($result) === 1) {
            $result = array_values($result)[0];
        }

        if (count($result) === 1) {
            $result = array_values($result)[0];
            if (! is_numeric($result)) {
                throw new SieClientException('Response parsing failed.', 11);
            }

            return (string)$result;
        }

        return $result;
    }

    /**
     * @param  array<string,mixed>  $series
     *
     * @return array{id:string,data:array<string,numeric-string>}
     */
    private function processSeriesData(array $series): array
    {
        $filtered = array_filter(
            is_array($series['datos']) ? $series['datos'] : [],
            fn(mixed $dato): bool => is_array($dato) && array_key_exists('fecha', $dato) &&
                array_key_exists('dato', $dato) &&
                is_string($dato['fecha']) && is_numeric($dato['dato'])
        );
        return [
            'id' => is_string($series['idSerie']) ? $series['idSerie'] : '',
            'data' => array_combine(
                array_map(fn (array $dato): string => $this->normalizeDateString($dato['fecha']), $filtered),
                array_map(fn (array $dato): string => (string)$dato['dato'], $filtered),
            ),
        ];
    }

    private function normalizeDateString(string $dateString): string
    {
        try {
            $dateValue = DateTimeImmutable::createFromFormat('d/m/Y', $dateString);
            if ($dateValue === false) {
                throw new SieClientException('Invalid date format.');
            }

            return $dateValue->format('Y-m-d');
        } catch (ValueError $valueError) {
            throw new SieClientException('Invalid date format.', 0, $valueError);
        }
    }
}

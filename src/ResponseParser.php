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

        if (! is_array($jsonValue)) {
            throw new SieClientException('Response parsing failed.', 3);
        }

        return $this->transformJson($jsonValue);
    }

    /**
     * @param  array<mixed, mixed>  $json  The decoded JSON array
     *
     * @return array<string, mixed>|mixed
     */
    private function transformJson(array $json)
    {
        if (! is_array($json['bmx']) || ! is_array($json['bmx']['series'])) {
            throw new SieClientException('Response parsing failed.', 4);
        }
        $result = [];
        foreach ($json['bmx']['series'] as $series) {
            if (! is_array($series)) {
                throw new SieClientException('Response parsing failed.', 5);
            }
            $seriesId = $series['idSerie'];
            if (! is_string($seriesId)) {
                throw new SieClientException('Response parsing failed.', 6);
            }
            $result[$seriesId] = [];
            if (! is_array($series['datos'])) {
                throw new SieClientException('Response parsing failed.', 7);
            }
            foreach ($series['datos'] as $record) {
                if (! is_array($record)) {
                    throw new SieClientException('Response parsing failed.', 8);
                }
                if (! is_string($record['fecha'])) {
                    throw new SieClientException('Response parsing failed.', 9);
                }
                $date_key = $this->normalizeDateString($record['fecha']);
                $result[$seriesId][$date_key] = $record['dato'];
            }
        }

        if (count($result) === 1) {
            $result = array_values($result)[0];
        }

        if (count($result) === 1) {
            return array_values($result)[0];
        }

        return $result;
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

<?php

declare(strict_types=1);

namespace Xint0\BanxicoPHP;

use JsonException;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;

class ResponseParser
{
    private const HTTP_STATUS_SUCCESS = 200;

    public function parse(ResponseInterface $response): array
    {
        $statusCode = $response->getStatusCode();
        if ($statusCode !== self::HTTP_STATUS_SUCCESS) {
            throw new ClienteBanxicoException('Request failed.', $statusCode);
        }

        try {
            $contents = $response->getBody()->getContents();
        } catch (RuntimeException $runtimeException) {
            throw new ClienteBanxicoException('Could not get response content.', 1, $runtimeException);
        }

        try {
            $json = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $jsonException) {
            throw new ClienteBanxicoException('Response parsing failed.', 2, $jsonException);
        }

        return $this->transformJson($json);
    }

    private function transformJson(array $json): array
    {
        $result = [];
        foreach ($json['bmx']['series'] as $series) {
            $seriesId = $series['idSerie'];
            $result[$seriesId] = [];
            foreach ($series['datos'] as $data) {
                $result[$seriesId][$data['fecha']] = $data['dato'];
            }
        }

        return $result;
    }
}
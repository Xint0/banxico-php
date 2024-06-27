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

namespace Xint0\BanxicoPHP\Tests\Factories;

use PHPUnit\Framework\Attributes\DataProvider;
use Xint0\BanxicoPHP\Factories\RequestFactory;
use PHPUnit\Framework\TestCase;

class RequestFactoryTest extends TestCase
{
    public static function createRequestProvider(): array
    {
        return [
            'oportuno' => [
                'initial_state' => [],
                'params' => [
                    'SF43718',
                ],
                'final_state' => [
                    'expected_scheme' => 'https',
                    'expected_host' => 'www.banxico.org.mx',
                    'expected_path' => '/SieAPIRest/service/v1/series/SF43718/datos/oportuno',
                ],
            ],
            'single date' => [
                'initial_state' => [
                    'http://www.example.com',
                ],
                'params' => [
                    'SF60653',
                    '2020-12-01',
                ],
                'final_state' => [
                    'expected_scheme' => 'http',
                    'expected_host' => 'www.example.com',
                    'expected_path' => '/series/SF60653/datos/2020-12-01/2020-12-01',
                ],
            ],
            'date range' => [
                'initial_state' => [],
                'params' => [
                    'SF60653',
                    '2020-11-26',
                    '2020-11-27',
                ],
                'final_state' => [
                    'expected_scheme' => 'https',
                    'expected_host' => 'www.banxico.org.mx',
                    'expected_path' => '/SieAPIRest/service/v1/series/SF60653/datos/2020-11-26/2020-11-27',
                ],
            ],
        ];
    }

    #[DataProvider('createRequestProvider')]
    public function test_returns_expected_request(array $initial_state, array $params, array $final_state): void
    {
        $sut = new RequestFactory(...$initial_state);
        $result = $sut->createRequest(...$params);
        static::assertEquals('GET', $result->getMethod());
        $resultUri = $result->getUri();
        static::assertEquals($final_state['expected_scheme'], $resultUri->getScheme());
        static::assertEquals($final_state['expected_host'], $resultUri->getHost());
        static::assertEquals($final_state['expected_path'], $resultUri->getPath());
    }
}

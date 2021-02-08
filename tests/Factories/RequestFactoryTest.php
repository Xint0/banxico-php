<?php

declare(strict_types=1);

namespace Xint0\BanxicoPHP\Tests\Factories;

use Xint0\BanxicoPHP\Factories\RequestFactory;
use PHPUnit\Framework\TestCase;

class RequestFactoryTest extends TestCase
{
    public function createRequestProvider(): array
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
                    'http://www.example.com/',
                ],
                'params' => [
                    'SF60653',
                    '2020-12-01',
                ],
                'final_state' => [
                    'expected_scheme' => 'http',
                    'expected_host' => 'www.example.com',
                    'expected_path' => '/SF60653/datos/2020-12-01/2020-12-01',
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

    /**
     * @dataProvider createRequestProvider
     *
     * @param  array  $initial_state
     * @param  array  $params
     * @param  array  $final_state
     */
    public function test_returns_expected_request(array $initial_state, array $params, array $final_state): void
    {
        $sut = new RequestFactory(...$initial_state);
        $result = $sut->createRequest(...$params);
        $this->assertEquals('GET', $result->getMethod());
        $resultUri = $result->getUri();
        $this->assertEquals($final_state['expected_scheme'], $resultUri->getScheme());
        $this->assertEquals($final_state['expected_host'], $resultUri->getHost());
        $this->assertEquals($final_state['expected_path'], $resultUri->getPath());
    }
}
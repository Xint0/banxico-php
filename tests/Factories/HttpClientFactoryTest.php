<?php

/**
 * xint0/banxico-php
 *
 * Client for the Banco de Mexico SIE REST API.
 *
 * @author Rogelio Jacinto <ego@rogeliojacinto.com>
 * @copyright 2020,2021 Rogelio Jacinto
 * @license https://github.com/Xint0/banxico-php/blob/master/LICENSE MIT License
 */

declare(strict_types=1);

namespace Xint0\BanxicoPHP\Tests\Factories;

use Http\Client\HttpClient;
use Http\Discovery\ClassDiscovery;
use Http\Discovery\Strategy\MockClientStrategy;
use Xint0\BanxicoPHP\Factories\HttpClientFactory;
use PHPUnit\Framework\TestCase;

class HttpClientFactoryTest extends TestCase
{
    public function test_creates_http_client(): void
    {
        ClassDiscovery::appendStrategy(MockClientStrategy::class);
        $token = 'test-token';
        $httpClient = HttpClientFactory::create($token);
        static::assertNotNull($httpClient);
        static::assertInstanceOf(HttpClient::class, $httpClient);
    }
}

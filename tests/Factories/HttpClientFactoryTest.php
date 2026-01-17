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

use Http\Client\Common\PluginClient;
use Http\Discovery\ClassDiscovery;
use Http\Discovery\Strategy\MockClientStrategy;
use PHPUnit\Framework\TestCase;
use Xint0\BanxicoPHP\Factories\HttpClientFactory;

final class HttpClientFactoryTest extends TestCase
{
    public function test_creates_http_client(): void
    {
        ClassDiscovery::appendStrategy(MockClientStrategy::class);
        $token = 'test-token';
        $httpClient = HttpClientFactory::create($token);
        $this->assertInstanceOf(PluginClient::class, $httpClient);
    }
}

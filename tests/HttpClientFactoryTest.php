<?php

namespace Xint0\BanxicoPHP\Tests;

use Http\Client\HttpClient;
use Http\Discovery\ClassDiscovery;
use Http\Discovery\Strategy\MockClientStrategy;
use Xint0\BanxicoPHP\HttpClientFactory;
use PHPUnit\Framework\TestCase;

class HttpClientFactoryTest extends TestCase
{
    public function test_creates_http_client(): void
    {
        ClassDiscovery::appendStrategy(MockClientStrategy::class);
        $token = 'test-token';
        $httpClient = HttpClientFactory::create($token);
        $this->assertNotNull($httpClient);
        $this->assertInstanceOf(HttpClient::class, $httpClient);
    }
}

<?php

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

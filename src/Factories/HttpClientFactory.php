<?php

declare(strict_types=1);

namespace Xint0\BanxicoPHP\Factories;

use Http\Client\Common\Plugin\AuthenticationPlugin;
use Psr\Http\Client\ClientInterface;
use Http\Message\Authentication\Header;
use Http\Client\Common\Plugin\HeaderSetPlugin;
use Http\Discovery\HttpClientDiscovery;
use Http\Client\Common\PluginClient;

/**
 * Class HttpClientFactory
 *
 * @package Xint0\BanxicoPHP\Factories
 */
class HttpClientFactory
{
    /**
     * Crear el cliente HTTP para utilizar el cliente de la API Banxico.
     *
     * @param  string  $token  Token de autenticación de la API REST Banxico.
     * @param  array  $plugins  Lista de plugins del cliente HTTP.
     * @param  ClientInterface|null  $httpClient  El cliente HTTP base.
     *
     * @return ClientInterface
     */
    public static function create(string $token, array $plugins = [], ClientInterface $httpClient = null): ClientInterface
    {
        if (! $httpClient) {
            $httpClient = HttpClientDiscovery::find();
        }
        $plugins[] = new HeaderSetPlugin([
            'User-Agent' => 'Xint0 BanxicoPHP/0.2.0',
            'Accept' => 'application/json',
        ]);
        $plugins[] = new AuthenticationPlugin(new Header('Bmx-Token', $token));
        return new PluginClient($httpClient, $plugins);
    }
}

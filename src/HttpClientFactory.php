<?php

namespace Xint0\BanxicoPHP;

use Http\Client\Common\Plugin\AuthenticationPlugin;
use Http\Message\Authentication\Header;
use Http\Client\Common\Plugin\HeaderSetPlugin;
use Http\Client\HttpClient;
use Http\Discovery\HttpClientDiscovery;
use Http\Client\Common\PluginClient;

class HttpClientFactory
{
    /**
     * Crear el cliente HTTP para utilizar el cliente de la API Banxico.
     *
     * @param  string  $token  Token de autenticación de la API REST Banxico.
     * @param  array  $plugins  Lista de plugins del cliente HTTP.
     * @param  HttpClient|null  $httpClient  El cliente HTTP base.
     *
     * @return HttpClient
     */
    public static function create(string $token, array $plugins = [], HttpClient $httpClient = null): HttpClient
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
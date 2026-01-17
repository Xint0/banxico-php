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

namespace Xint0\BanxicoPHP\Factories;

use Http\Client\Common\Plugin;
use Http\Client\Common\Plugin\AuthenticationPlugin;
use Http\Client\Common\Plugin\DecoderPlugin;
use Http\Discovery\Psr18ClientDiscovery;
use Psr\Http\Client\ClientInterface;
use Http\Message\Authentication\Header;
use Http\Client\Common\Plugin\HeaderSetPlugin;
use Http\Client\Common\PluginClient;

/**
 * Creates the HTTP client
 */
class HttpClientFactory
{
    /**
     * Crear el cliente HTTP para utilizar el cliente de la API Banxico.
     *
     * @param  string  $token  Token de autenticación de la API REST Banxico.
     * @param  Plugin[]  $plugins  Lista de plugins del cliente HTTP.
     * @param  ClientInterface|null  $httpClient  El cliente HTTP base.
     */
    public static function create(
        string $token,
        array $plugins = [],
        ClientInterface $httpClient = null,
    ): ClientInterface {
        if (! $httpClient instanceof ClientInterface) {
            $httpClient = Psr18ClientDiscovery::find();
        }

        $plugins[] = new HeaderSetPlugin([
            'User-Agent' => 'Xint0 BanxicoPHP/1.0.0',
            'Accept' => 'application/json',
        ]);
        $plugins[] = new AuthenticationPlugin(new Header('Bmx-Token', $token));
        $plugins[] = new DecoderPlugin();
        return new PluginClient($httpClient, $plugins);
    }
}

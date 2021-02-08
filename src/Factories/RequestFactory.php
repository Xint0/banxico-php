<?php

declare(strict_types=1);

namespace Xint0\BanxicoPHP\Factories;

use Http\Discovery\Psr17FactoryDiscovery;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriFactoryInterface;

class RequestFactory
{
    private string $baseUri;
    private RequestFactoryInterface $psrRequestFactory;
    private UriFactoryInterface $psrUriFactory;

    /**
     * RequestFactory constructor.
     *
     * If `$baseUri` is not specified the default value is `https://www.banxico.org.mx/SieAPIRest/service/v1`.
     *
     * @param  string|null  $baseUri
     */
    public function __construct(?string $baseUri = null)
    {
        $this->baseUri = $baseUri ?? 'https://www.banxico.org.mx/SieAPIRest/service/v1';
        $this->psrRequestFactory = Psr17FactoryDiscovery::findRequestFactory();
        $this->psrUriFactory = Psr17FactoryDiscovery::findUriFactory();
    }

    /**
     * @param  string  $series
     * @param  string|null  $startDate
     * @param  string|null  $endDate
     *
     * @return RequestInterface
     */
    public function createRequest(string $series, ?string $startDate = null, ?string $endDate = null): RequestInterface
    {
        $normalizedStartDate = self::normalizeDate($startDate);
        $normalizedEndDate = self::normalizeDate($endDate);
        $suffix = 'oportuno';
        if ($normalizedStartDate !== null) {
            $suffix = $normalizedStartDate . (
                    $normalizedEndDate === null ? "/${normalizedStartDate}" : "/${normalizedEndDate}"
                );
        }

        $uri = $this->psrUriFactory->createUri("{$this->baseUri}/series/${series}/datos/${suffix}");
        return $this->psrRequestFactory->createRequest('GET', $uri);
    }

    /**
     * Normalize input string as date using `YYYY-MM-DD` format. If parsing fails returns `null`.
     *
     * @param  string|null  $input
     *
     * @return string|null
     */
    private static function normalizeDate(?string $input): ?string
    {
        if ($input === null) {
            return null;
        }

        $date = date_create($input);
        return $date !== false ? date_format($date, 'Y-m-d') : null;
    }
}
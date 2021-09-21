# banxico-php

Cliente PHP para la API REST del Sistema de Información Económica (SIE) del Banco de México (Banxico).

## Instalación

### Requisitos

- PHP `7.4` o más reciente.
- Cliente HTTP conforme a [PSR-18], cualquiera de la [lista de clientes y adaptadores] de [php-http.org].

### Utilizar Composer

Instalar con el adaptador para Guzzle 7 por ejemplo:

```bash
composer require xint0/banxico-php php-http/guzzle7-adapter
```

Si ya se tiene definido un cliente [PSR-18] en el proyecto, se puede instalar solo el cliente:

```bash
composer require xint0/banxico-php
```

## Cómo usar

### Token de consulta

Se debe obtener un _token_ de consulta a través de la [página de la API REST] del SIE del Banxico.

### Obtener el tipo de cambio peso - dólar

```php
<?php

use Xint0\BanxicoPHP\SieClient;

/*
 * Indicar el token de consulta en el constructor de la clase `SieClient`
 */
$cliente = new SieClient('e3980208bf01ec653aba9aee3c2d6f70f6ae8b066d2545e379b9e0ef92e9de25');

/*
 * Tipo de cambio MXN-USD fecha liquidación más reciente disponible
 * Devuelve una cadena de caracteres con el monto por ejemplo: '19.7930'
 */
$tipo_de_cambio = $cliente->exchangeRateUsdLiquidation();

/*
 * Tipo de cambio MXN-USD fecha determinación (Fix) más reciente disponible:
 */
$tipo_de_cambio_fix = $cliente->exchangeRateUsdDetermination();

/*
 *  Tipo de cambio MXN-USD fecha liquidación de un día específico:
 */
$tipo_de_cambio_2021_09_16 = $cliente->exchangeRateUsdLiquidation('2021-09-16');

/*
 *  Arreglo con los tipos de cambio MXN-USD fecha liquidación de un rango de fechas.
 * 
 * Cuando se indica un rango de fechas, el método devuelve un arreglo con las fechas como llaves y el tipo de cambio
 * como valor:
 * [
 *     '2021-08-01' => '19.9999',
 *     '2021-08-02' => '19.9999',
 *     ...
 * ];
 */
$tipo_de_cambio_agosto_2021 = $cliente->exchangeRateUsdLiquidation('2021-08-01', '2021-08-31');

```

[PSR-18]:(https://www.php-fig.org/psr/psr-18/)
[php-http.org]:(https://php-http.org)
[lista de clientes y adaptadores]:(https://docs.php-http.org/en/latest/clients.html)
[página de la API REST]:(https://www.banxico.org.mx/SieAPIRest/service/v1/token)
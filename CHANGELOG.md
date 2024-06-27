# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased](https://github.com/Xint0/banxico-php/compare/2.0.0...master)

## [2.0.0](https://github.com/Xint0/banxico-php/compare/1.0.3...2.0.0) 2024-06-27

### Changed

- Ignore custom PHP results cache directory `.phpunit.cache`.
- Set PHP 8.1 as minimum supported version.
- Upgrade PHPUnit to 10.5.
- Make test data provider methods `static`.
- Set PHPStorm PHPCS timeout to 60 seconds.

## [1.0.3](https://github.com/Xint0/banxico-php/compare/1.0.2...1.0.3) 2023-08-02

### Changed

- Fix deprecated string interpolation syntax.
- Upgrade PHPUnit to 9.6.10.
- Set default PHP version to 8.2 in docker images.
- Include PHPUnit phar in phpstan configuration.

## [1.0.2](https://github.com/Xint0/banxico-php/compare/1.0.1...1.0.2) 2023-04-05

### Changed

- Use Psr18ClientDiscovery instead of HttpClientDiscovery in HttpClientFactory.
- Upgrade PHPUnit, PHP Code Sniffer.
- Add PHPStan configuration.

## [1.0.1](https://github.com/Xint0/banxico-php/compare/1.0.0...1.0.1) 2023-02-04

### Changed

- Use phive to install PHPUnit, PHP Code Sniffer, and PHP Code Beautifier and Fixer.
- Fix GitHub actions workflow to use composer.json hash since we don't commit composer.lock.
- Use tools/phpunit in composer test script.
- Add docker images for PHP 7.4, and PHPStan.
- Use PHPUnit 9.6 schema for phpunit.xml configuration file.
- Ignore development only files and directories in git export.

## [1.0.0](https://github.com/Xint0/banxico-php/compare/v0.3.1...1.0.0)

### Changed

- Replace `Cliente` with `SieClient` class. **Breaking**
- `fetchSeries`, `exchangeRateUsdDetermination`, and `exchangeRateUsdLiquidation` return single result as single string,
single series with multiple values as array keyed by date in `YYYY-MM-DD`, multiple series keyed by series code.

## [v0.3.1](https://github.com/Xint0/banxico-php/compare/v0.3.0...v0.3.1)

### Changed

- Do not force exclusion of `symfony/polyfill-php73`.

## [v0.3.0](https://github.com/Xint0/banxico-php/compare/v0.2.0...v0.3.0)

### Changed

- Require `psr/http-client-implementation` instead of `guzzlehttp/guzzle`; `PHP` > `7.4`; `ext-json`.
- Throw `ClienteBanxicoException` on client errors.

## [v0.2.0](https://github.com/Xint0/banxico-php/compare/v0.1.0...v0.2.0)

### Changed

- Upgrade `guzzlehttp/guzzle` to `v7`.

## [v0.1.0](https://github.com/Xint0/banxico-php/tree/v0.1.0)

- Initial version.
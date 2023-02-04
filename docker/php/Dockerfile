ARG PHP_VERSION=7.4
FROM mlocati/php-extension-installer as php-extension-installer
FROM php:${PHP_VERSION}-cli-alpine
COPY --from=php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions \
    @composer \
    xdebug \
    zip && \
    apk add gpg gnupg-dirmngr gpg-agent && \
    wget -O phive.phar https://phar.io/releases/phive.phar && \
    wget -O phive.phar.asc https://phar.io/releases/phive.phar.asc && \
    gpg --keyserver hkps://keys.openpgp.org --recv-keys 0x9D8A98B29B2D5D79 && \
    gpg --verify phive.phar.asc phive.phar && \
    chmod +x phive.phar && \
    mv phive.phar /usr/local/bin/phive
VOLUME /usr/src/app
WORKDIR /usr/src/app
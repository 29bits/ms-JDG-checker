FROM php:8.1-fpm-alpine3.16 as php
RUN apk add --virtual .build-deps $PHPIZE_DEPS \
    && apk add \
    bash \
    vim \
    mc \
    && apk del .build-deps $PHPIZE_DEPS \
    && docker-php-source delete \
ENV COMPOSER_HOME /.composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY php.ini $PHP_INI_DIR/php.ini
WORKDIR /app

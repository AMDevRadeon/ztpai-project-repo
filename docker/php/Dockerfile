FROM php:8.2.11-fpm-alpine3.18


ENV RUN_DEPS \
    zlib \
    libzip \
    libpng \
    libjpeg-turbo \
    postgresql-libs

ENV BUILD_DEPS \
    zlib-dev \
    libzip-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    postgresql-dev

ENV PHP_EXTENSIONS \
    opcache \
    zip \
    gd \
    bcmath \
    pgsql \
    pdo_pgsql

RUN apk add --no-cache --virtual .build-deps $BUILD_DEPS \
    && docker-php-ext-configure gd --with-jpeg \
    && docker-php-ext-install -j "$(nproc)" $PHP_EXTENSIONS \
    && apk del .build-deps

RUN apk add --no-cache --virtual .run-deps $RUN_DEPS

ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

RUN chmod +x /usr/local/bin/install-php-extensions && sync && \
    install-php-extensions amqp

# Multi stage build
COPY --from=composer/composer:latest-bin /composer /usr/bin/composer
RUN composer self-update

COPY ./docker/php/phpSettings.conf /usr/local/etc/php-fpm.d/zzz-phpSettings.conf
COPY ./docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini
# COPY ./data/app/ /var/www/html/

# VOLUME ["/var/www/html/"]
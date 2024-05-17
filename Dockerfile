ARG PHP_VERSION
FROM php:${PHP_VERSION}-fpm

RUN apt-get update
RUN apt-get install -y git zip unzip libzip-dev libmcrypt-dev --no-install-recommends

# See: https://github.com/mlocati/docker-php-extension-installer
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN chmod +x /usr/local/bin/install-php-extensions && sync
RUN install-php-extensions \
    @composer xdebug zip

COPY config/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini
RUN rm /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
RUN touch /var/log/xdebug.log && chmod a+rw /var/log/xdebug.log

WORKDIR /app/

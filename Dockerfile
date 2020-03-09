FROM php:7.4.3-fpm

RUN apt-get update
RUN apt-get install -y git zip unzip libzip-dev libmcrypt-dev --no-install-recommends
RUN docker-php-ext-install zip
RUN docker-php-ext-configure zip
RUN curl -o /tmp/composer-setup.php https://getcomposer.org/installer \
    && curl -o /tmp/composer-setup.sig https://composer.github.io/installer.sig \
    && php -r "if (hash('SHA384', file_get_contents('/tmp/composer-setup.php')) !== trim(file_get_contents('/tmp/composer-setup.sig'))) { unlink('/tmp/composer-setup.php'); echo 'Invalid installer' . PHP_EOL; exit(1); }" \
    && php /tmp/composer-setup.php --no-ansi --install-dir=/usr/local/bin --filename=composer \
    && rm -f /tmp/composer-setup.*

WORKDIR /app/

FROM composer:2.4 as build
COPY . /app/
RUN MT_BUILD=1 composer install --prefer-dist --no-dev --optimize-autoloader --no-interaction --ignore-platform-reqs

FROM php:8.1-apache-buster as dev

ENV APP_ENV=dev
ENV APP_DEBUG=false
ENV COMPOSER_ALLOW_SUPERUSER=1

RUN apt update && apt install -y zip
RUN docker-php-ext-install pdo pdo_mysql && docker-php-ext-install sockets
RUN pecl install redis && docker-php-ext-enable redis

COPY . /var/www/html/
COPY --from=build /usr/bin/composer /usr/bin/composer
RUN MT_BUILD=1 composer install --prefer-dist --optimize-autoloader --no-interaction --ignore-platform-reqs

RUN MT_BUILD=1 php artisan route:cache && \
    chmod 777 -R /var/www/html/storage/ && \
    chown -R www-data:www-data /var/www/ && \
    a2enmod rewrite


FROM php:8.1-apache-buster as production

ENV APP_ENV=production
ENV APP_DEBUG=false

RUN docker-php-ext-configure opcache --enable-opcache && \
    docker-php-ext-install pdo pdo_mysql && docker-php-ext-install sockets
RUN pecl install redis && docker-php-ext-enable redis
COPY docker/php/conf.d/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

COPY --from=build /app /var/www/html
COPY docker/apache/000-default.conf /etc/apache2/sites-available/000-default.conf

RUN MT_BUILD=1 php artisan config:clear && \
    chmod 777 -R /var/www/html/storage/ && \
    chown -R www-data:www-data /var/www/ && \
    a2enmod rewrite

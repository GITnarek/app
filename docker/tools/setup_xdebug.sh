#!/bin/bash

INSTALLED=$(php -m | grep xdebug)

if [ -z "$INSTALLED" ]; then
    pecl install xdebug
    docker-php-ext-enable xdebug
fi

echo "zend_extension=xdebug" > /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

{
    echo "[Xdebug]"
    echo "xdebug.mode=${XDEBUG_MODE}"
    echo "xdebug.idekey=${XDEBUG_IDEKEY}"
    echo "xdebug.max_nesting_level=100"
    echo "xdebug.client_host=${XDEBUG_HOST}"
    echo "xdebug.client_port=${XDEBUG_PORT}"
} >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

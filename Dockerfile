FROM php:8.2-fpm-alpine

RUN apk update && \
    apk --no-cache add --virtual .build-deps $PHPIZE_DEPS

RUN apk add tzdata && \
    cp /usr/share/zoneinfo/Asia/Tokyo /etc/localtime && \
    apk del tzdata

RUN apk add --update linux-headers icu-dev
RUN pecl install xdebug-3.2.2
RUN docker-php-ext-enable xdebug
RUN docker-php-ext-install intl

RUN curl -sS https://getcomposer.org/installer | \
    php -- --install-dir=/usr/bin/ --filename=composer

RUN apk add git

COPY php.ini /usr/local/etc/php/

RUN mkdir /code
WORKDIR /code

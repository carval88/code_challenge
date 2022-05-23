FROM php:7.4.24-apache

RUN usermod -u 1001 www-data
RUN groupmod -g 1006 www-data

RUN apt-get update && apt-get install -y libmagickwand-dev --no-install-recommends && rm -rf /var/lib/apt/lists/*
RUN docker-php-ext-configure gd --with-freetype=/usr/include/ --with-jpeg=/usr/include/ \
    && docker-php-ext-install gd

RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli
RUN a2enmod rewrite
RUN apt update -y
RUN apt upgrade -y

RUN mkdir -p /var/lib/php/sessions
RUN chown -R 1001:1006 /var/lib/php/sessions
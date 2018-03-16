FROM php:7.2-alpine

RUN apk update
RUN apk add autoconf build-base git zip unzip
RUN pecl install mongodb
RUN echo "extension=mongodb.so" >> /usr/local/etc/php/conf.d/mongodb.ini

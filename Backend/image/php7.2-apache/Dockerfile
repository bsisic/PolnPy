FROM php:7.2-apache

RUN apt-get update
RUN apt-get install -y autoconf build-essential git zip unzip
RUN pecl install mongodb
RUN echo "extension=mongodb.so" >> /usr/local/etc/php/conf.d/mongodb.ini

RUN if [ ! -d "/var/www/html/app/cache" ]; then mkdir -p "/var/www/html/app/cache"; fi
RUN if [ ! -d "/var/www/html/app/log" ]; then mkdir -p "/var/www/html/app/log"; fi

RUN chown www-data:www-data /var/www/html/app/cache
RUN chmod 744 /var/www/html/app/cache
RUN chown www-data:www-data /var/www/html/app/log
RUN chmod 744 /var/www/html/app/log

COPY apache.conf /etc/apache2/sites-available/000-default.conf
RUN a2enmod rewrite

RUN apt-get install -y libsodium-dev libicu-dev
RUN docker-php-ext-install sodium opcache mbstring intl

VOLUME ["/var/www/html/app/cache", "/var/www/html/app/log"]

WORKDIR /var/www/html

RUN useradd -p dl -s /bin/bash user

RUN apt-get install -y python3 python3-pip mongodb-clients
RUN pip3 install pandas scikit-learn scipy numpy

RUN mkdir /usr/local/src/predict
COPY Archive.zip /usr/local/src/predict/
WORKDIR /usr/local/src/predict
RUN unzip Archive.zip
RUN rm Archive.zip

WORKDIR /var/www/html

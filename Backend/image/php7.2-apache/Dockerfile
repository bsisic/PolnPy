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

ENV APP_ENV=prod
ENV APP_DEBUG=0

WORKDIR /var/www/html
RUN git clone https://github.com/kairosProject/chronos.git
WORKDIR /usr/bin
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
RUN php -r "if (hash_file('SHA384', 'composer-setup.php') === '544e09ee996cdf60ece3804abc52599c22b1f40f4323403c44d44fdfdd586475ca9813a858088ffbc1f233e9b180f061') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
RUN php composer-setup.php
RUN php -r "unlink('composer-setup.php');"
RUN mv composer.phar composer
WORKDIR /var/www/html/chronos
RUN composer install --no-dev --optimize-autoloader 

VOLUME ["/var/www/html/chronos/app/cache", "/var/www/html/chronos/app/log"]

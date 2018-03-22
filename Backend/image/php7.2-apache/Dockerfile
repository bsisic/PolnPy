FROM php:7.2-apache

RUN apt-get update
RUN apt-get install -y autoconf build-essential git zip unzip libsodium-dev libicu-dev python3 python3-pip mongodb-clients
RUN pecl install mongodb
RUN echo "extension=mongodb.so" >> /usr/local/etc/php/conf.d/mongodb.ini

RUN docker-php-ext-install sodium opcache mbstring intl
RUN pip3 install pandas scikit-learn scipy numpy keras tensorflow h5py

COPY apache.conf /etc/apache2/sites-available/000-default.conf
RUN a2enmod rewrite

RUN mkdir /usr/local/src/predict
COPY Archive.zip /usr/local/src/predict/
WORKDIR /usr/local/src/predict
RUN unzip Archive.zip
RUN rm Archive.zip

WORKDIR /var/www/
RUN git clone https://github.com/bsisic/PolnPy.git
RUN mv PolnPy/Backend/polnpy .
RUN rm -Rf html
RUN mv polnpy html

WORKDIR /var/www/html
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
RUN php -r "if (hash_file('SHA384', 'composer-setup.php') === '544e09ee996cdf60ece3804abc52599c22b1f40f4323403c44d44fdfdd586475ca9813a858088ffbc1f233e9b180f061') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
RUN php composer-setup.php
RUN php -r "unlink('composer-setup.php');"
RUN php composer.phar install
RUN rm composer.phar

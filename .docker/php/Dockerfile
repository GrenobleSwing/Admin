FROM php:5.6-apache

RUN apt-get update
RUN apt-get install -y apt-utils
RUN apt-get update

RUN apt-get install libxml2 libxml2-dev xml-core

# Install PDO MySQL driver
# See https://github.com/docker-library/php/issues/62
RUN docker-php-ext-install pdo pdo_mysql mysql mysqli
RUN docker-php-ext-install ctype
RUN docker-php-ext-install iconv
RUN docker-php-ext-install json
RUN docker-php-ext-install tokenizer
RUN docker-php-ext-install xml

RUN apt-get install -y libmcrypt-dev
RUN docker-php-ext-install mcrypt

RUN docker-php-ext-install dom

RUN apt-get install -y imagemagick

RUN apt-get install -y libcurl4-openssl-dev
RUN docker-php-ext-install curl

RUN apt-get install -y libicu-dev

RUN docker-php-ext-install intl
RUN docker-php-ext-install opcache
RUN docker-php-ext-install mbstring

RUN apt-get install -y libpng12-dev
RUN docker-php-ext-install gd

RUN apt-get install -y php5-apcu

# Set timezone
RUN rm /etc/localtime
RUN ln -s /usr/share/zoneinfo/Europe/Paris /etc/localtime
RUN "date"
COPY symfony.ini /usr/local/etc/php/conf.d/

RUN usermod -u 1000 www-data

# Install zip/unzip: mandatory for Composer
RUN apt-get install -y zip

# Install composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
RUN php -r "if (hash_file('SHA384', 'composer-setup.php') === '669656bab3166a7aff8a7506b8cb2d1c292f042046c5a994c43155c0be6190fa0355160742ab2e1c88d40d5be660b410') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
RUN php composer-setup.php
RUN php -r "unlink('composer-setup.php');"
RUN mv composer.phar /usr/local/bin/composer

# Install git
RUN apt-get install -y git

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Intall wkhtmltopdf
RUN apt-get install -y wget
RUN wget https://downloads.wkhtmltopdf.org/0.12/0.12.4/wkhtmltox-0.12.4_linux-generic-amd64.tar.xz -O/var/local/wkhtmltox.tar
RUN tar -xf /var/local/wkhtmltox.tar -C /var/local/
RUN chown -R www-data:www-data /var/local/wkhtmltox
RUN chmod -R ug+x /var/local/wkhtmltox/bin

EXPOSE 80


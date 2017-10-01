FROM php:5.6-apache

RUN apt-get update
RUN apt-get install -y apt-utils
RUN apt-get update && apt-get install -y \
    libxml2 libxml2-dev xml-core \
    libmcrypt-dev \
    imagemagick \
    libcurl4-openssl-dev \
    libicu-dev \
    libpng12-dev \
    php5-apcu \
    zip \
    git \
    wget

# Install PDO MySQL driver
# See https://github.com/docker-library/php/issues/62
RUN docker-php-ext-install pdo pdo_mysql mysql mysqli
RUN docker-php-ext-install ctype
RUN docker-php-ext-install iconv
RUN docker-php-ext-install json
RUN docker-php-ext-install tokenizer
RUN docker-php-ext-install xml
RUN docker-php-ext-install mcrypt
RUN docker-php-ext-install dom
RUN docker-php-ext-install curl
RUN docker-php-ext-install intl
RUN docker-php-ext-install opcache
RUN docker-php-ext-install mbstring
RUN docker-php-ext-install gd

# Set timezone
RUN rm /etc/localtime
RUN ln -s /usr/share/zoneinfo/Europe/Paris /etc/localtime
RUN "date"
COPY symfony.ini /usr/local/etc/php/conf.d/

RUN usermod -u 1000 www-data

# Install composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
RUN php composer-setup.php
RUN php -r "unlink('composer-setup.php');"
RUN mv composer.phar /usr/local/bin/composer

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Intall wkhtmltopdf
RUN wget https://downloads.wkhtmltopdf.org/0.12/0.12.4/wkhtmltox-0.12.4_linux-generic-amd64.tar.xz -O/var/local/wkhtmltox.tar
RUN tar -xf /var/local/wkhtmltox.tar -C /var/local/
RUN chown -R www-data:www-data /var/local/wkhtmltox
RUN chmod -R ug+x /var/local/wkhtmltox/bin

EXPOSE 80


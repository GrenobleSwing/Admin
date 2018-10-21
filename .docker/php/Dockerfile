FROM php:5.6-apache
#FROM php:7.2-apache

RUN apt-get update && apt-get install -y apt-utils
RUN apt-get update && apt-get install -y \
    libxml2 libxml2-dev xml-core \
    libmcrypt-dev \
    imagemagick \
    libcurl4-openssl-dev \
    libicu-dev \
    libpng-dev \
    zip \
    git \
    wget

#RUN pecl install apcu-5.1.12 \
    # Install PDO MySQL driver
    # See https://github.com/docker-library/php/issues/62
#    && docker-php-ext-enable apcu \
RUN pecl install apcu-4.0.11 \
    # Install PDO MySQL driver
    # See https://github.com/docker-library/php/issues/62
    && docker-php-ext-enable apcu \
    && docker-php-ext-install pdo pdo_mysql mysqli \
    && docker-php-ext-install ctype \
    && docker-php-ext-install iconv \
    && docker-php-ext-install json \
    && docker-php-ext-install tokenizer \
    && docker-php-ext-install xml \
    && docker-php-ext-install dom \
    && docker-php-ext-install curl \
    && docker-php-ext-install intl \
    && docker-php-ext-install opcache \
    && docker-php-ext-install mbstring \
    && docker-php-ext-install gd

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


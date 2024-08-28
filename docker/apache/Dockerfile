# use php 8.2 apache image
FROM php:8.2-apache
# Update it
RUN apt-get update

# install necessary libraries
RUN apt-get install -y \
    git \
    zip \
    curl \
    sudo \
    unzip \
    libicu-dev \
    libbz2-dev \
    libpng-dev \
    libjpeg-dev \
    libmcrypt-dev \
    libreadline-dev \
    libfreetype6-dev \
    g++

# install docker extensions
RUN docker-php-ext-install \
    bz2 \
    intl \
    bcmath \
    opcache \
    calendar \
    pdo_mysql \
    mysqli

# set up document root for apache
COPY docker/apache/000-default.conf /etc/apache2/sites-available/000-default.conf

# mod_rewrite for URL rewrite and mod_headers for .htaccess extra headers like Access-Control-Allow-Origin-
RUN a2enmod rewrite headers

# start with base php config, then add extensions
RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"

# composer
RUN curl -sS https://getcomposer.org/installer | php
RUN mv composer.phar /usr/local/bin/composer
RUN chmod +x /usr/local/bin/composer
RUN composer self-update

COPY . /var/www/html/
# Install PHP dependencies, generate keys, run migrations, and cache config
RUN composer install && \
    php artisan key:generate --force && \
    php artisan passport:keys --force && \
    php artisan migrate_in_order && \
    php artisan storage:link --force && \
    php artisan config:cache
	
RUN chown -R www-data:www-data /var/www/html && \
    chmod -R 755 /var/www/html

EXPOSE 80
# # Use the official PHP 8.0 image as the base image
# FROM php:8.1-apache

# # Set the working directory in the container
# WORKDIR /var/www/html

# # Install system dependencies
# RUN apt-get update && apt-get install -y \
#     libzip-dev \
#     zip \
#     unzip \
#     && docker-php-ext-install zip

# # Install PHP extensions required by Laravel
# RUN docker-php-ext-install pdo_mysql

# # Copy composer.lock and composer.json
# COPY composer.lock composer.json ./

# # Install composer dependencies
# RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
# RUN composer install --no-scripts --no-autoloader

# # Copy the rest of the application code
# COPY . .

# # Generate the autoload files
# RUN composer dump-autoload

# # Set the document root
# RUN sed -ri -e 's!/var/www/html/public!/var/www/html/public!g' /etc/apache2/sites-available/*.conf
# RUN sed -ri -e 's!/var/www/html/public!/var/www/html/public!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# # Enable Apache modules
# RUN a2enmod rewrite

# # Expose port 8002
# EXPOSE 8002

# # Start Apache server
# CMD ["apache2-foreground"]

FROM php:8.1 as php

RUN apt-get update -y
RUN apt-get install -y unzip libpq-dev libcurl4-gnutls-dev
RUN docker-php-ext-install pdo pdo_mysql bcmath

RUN pecl install -o -f redis \
    && rm -rf /tmp/pear \
    && docker-php-ext-enable redis

WORKDIR /var/www
COPY . .

COPY --from=composer:2.3.5 /usr/bin/composer /usr/bin/composer

ENV PORT=8000
ENTRYPOINT [ "docker/entrypoint.sh" ]

# ==============================================================================
#  node
FROM node:14-alpine as node

WORKDIR /var/www
COPY . .

RUN npm install --global cross-env
RUN npm install

VOLUME /var/www/node_modules

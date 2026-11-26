# PHP + Apache image that can run this app on Render (or any Docker host)
FROM php:8.2-apache

RUN apt-get update \
 && apt-get install -y --no-install-recommends unzip ca-certificates \
 && rm -rf /var/lib/apt/lists/* \
 && docker-php-ext-install mysqli

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY composer.json ./
RUN composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader

COPY . .
RUN chown -R www-data:www-data /var/www/html

# Render tells the app which port to use through $PORT
ENV PORT=80
CMD sed -i "s/^Listen .*/Listen ${PORT}/" /etc/apache2/ports.conf \
 && sed -i "s/<VirtualHost \*:[0-9]*>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf \
 && apache2-foreground

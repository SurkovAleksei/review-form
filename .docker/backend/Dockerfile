FROM php:8.4-fpm

RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/backend

COPY backend/ /var/www/backend

RUN rm -f composer.lock && composer install --no-interaction --optimize-autoloader --no-dev

RUN chown -R www-data:www-data /var/www/backend/storage /var/www/backend/bootstrap/cache
RUN chmod -R 775 /var/www/backend/storage /var/www/backend/bootstrap/cache

EXPOSE 8000

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
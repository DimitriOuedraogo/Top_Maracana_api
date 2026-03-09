FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    git unzip curl libzip-dev libonig-dev \
    libpng-dev libjpeg-dev libfreetype6-dev \
    zip vim libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql mbstring zip exif pcntl bcmath gd

COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www
COPY . .

RUN composer install --optimize-autoloader --no-dev

RUN php artisan vendor:publish --provider="L5Swagger\L5SwaggerServiceProvider"

RUN chown -R www-data:www-data /var/www \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache

EXPOSE 10000

CMD php artisan optimize:clear && \
    php artisan l5-swagger:generate && \
    php artisan migrate --force && \
    php artisan serve --host=0.0.0.0 --port=$PORT
FROM php:8.2-cli

WORKDIR /app

# installer dépendances système
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    && docker-php-ext-install zip

# installer composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# copier le projet
COPY . .

# installer dépendances Laravel
RUN composer install --no-dev --optimize-autoloader

# exposer le port
EXPOSE 10000

# démarrer le serveur
CMD php -S 0.0.0.0:10000 -t public
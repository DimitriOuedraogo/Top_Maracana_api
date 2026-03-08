# 1. Image de base PHP avec extensions nécessaires
FROM php:8.2-fpm

# 2. Installer les dépendances système nécessaires
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    curl \
    libzip-dev \
    libonig-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    vim \
    && docker-php-ext-install pdo_mysql mbstring zip exif pcntl bcmath gd

# 3. Installer Composer
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

# 4. Copier le projet dans le container
WORKDIR /var/www
COPY . .

# 5. Installer les dépendances PHP via Composer
RUN composer install --optimize-autoloader --no-dev

# 6. Copier le fichier .env si nécessaire
# (Render peut gérer les variables d'environnement à part, donc optionnel)
# COPY .env .env

# 7. Donner les droits pour storage et bootstrap/cache
RUN chown -R www-data:www-data /var/www \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# 8. Exposer le port sur lequel Laravel va écouter
EXPOSE 8000

# 9. Commande pour lancer Laravel
CMD php artisan serve --host=0.0.0.0 --port=8000
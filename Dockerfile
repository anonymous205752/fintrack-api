# 1. Use official PHP image with Apache
FROM php:8.2-apache

# 2. Set working directory
WORKDIR /var/www/html

# 3. Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpq-dev \
    libzip-dev \
    zip \
    && docker-php-ext-install pdo pdo_pgsql zip

# 4. Enable Apache mod_rewrite
RUN a2enmod rewrite

# 5. Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# 6. Copy app files
COPY . .

# 7. Install PHP dependencies
RUN composer install --optimize-autoloader --no-dev

# 8. Set permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# 9. Generate app key (optional if you already have APP_KEY)
RUN php artisan key:generate --force

# 10. Run migrations and seeders during build
RUN php artisan migrate --force && php artisan db:seed --force

# 11. Expose port
EXPOSE 10000

# 12. Start Apache
CMD ["apache2-foreground"]

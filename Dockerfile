# 1. Use official PHP image with necessary extensions
FROM php:8.2-fpm

# 2. Set working directory
WORKDIR /var/www/html

# 3. Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libonig-dev \
    libpng-dev \
    curl \
    && docker-php-ext-install pdo pdo_mysql zip mbstring gd

# 4. Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 5. Copy project files
COPY . .

# 6. Copy example env and install PHP dependencies
RUN cp .env.example .env
RUN composer install --no-dev --optimize-autoloader

# 7. Set permissions
RUN chown -R www-data:www-data storage bootstrap/cache

# 8. Generate APP_KEY (optional, can skip if using Render APP_KEY env var)
# RUN php artisan key:generate --force

# 9. Run migrations and seeders
RUN php artisan migrate --force
RUN php artisan db:seed --force

# 10. Expose port
EXPOSE 8000

# 11. Start the Laravel server
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]

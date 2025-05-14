# Stage 1: Build assets and install PHP dependencies
FROM composer:2.5 AS build-backend

WORKDIR /var/www

# Copy composer files and install PHP dependencies
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader

# Copy the rest of the application code
COPY . .

# Stage 2: Build frontend assets
FROM node:20 AS build-frontend
WORKDIR /app
COPY --from=build-backend /var/www /app
RUN npm install && npm run build

# Stage 3: Production image
FROM php:8.2-fpm

# Install system dependencies and PHP extensions
RUN apt-get update \
    && apt-get install -y libpng-dev libonig-dev libxml2-dev zip unzip git curl \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Set working directory
WORKDIR /var/www

# Copy built application and assets from previous stages
COPY --from=build-backend /var/www /var/www
COPY --from=build-frontend /app/public/build /var/www/public/build

# Copy entrypoint script (optional, for permissions)
# COPY docker-entrypoint.sh /usr/local/bin/
# RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Set permissions for Laravel
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# Expose port 8000
EXPOSE 8000

# Start Laravel's built-in server (for production, use Nginx or Caddy)
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]

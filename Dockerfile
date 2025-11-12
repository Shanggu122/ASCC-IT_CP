# Use the official PHP image with necessary extensions
FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git curl libpng-dev libjpeg-dev libfreetype6-dev zip unzip

# Install PHP extensions required by Laravel
RUN docker-php-ext-install pdo pdo_mysql gd

# Set working directory
WORKDIR /var/www/html

# Copy composer from the official image
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

# Copy the application files
COPY . .

# Install dependencies
RUN composer install --no-dev --optimize-autoloader

# Expose port 9000 for PHP-FPM
EXPOSE 9000

# Start PHP-FPM server
CMD ["php-fpm"]
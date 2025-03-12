# Use the official PHP-FPM image as the base image
FROM php:8.2-fpm

# Install any needed packages specified in composer.json
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libicu-dev \
    libzip-dev \
    unzip \
    nginx \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd \
    && docker-php-ext-install intl \
    && docker-php-ext-install zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy the current directory contents into the container at /var/www/html
COPY . /var/www/html

# Set the correct permissions for the web directory
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Install application dependencies
RUN composer install

# Copy Nginx configuration file
COPY nginx.conf /etc/nginx/nginx.conf

# Expose port 80 to the outside world
EXPOSE 80

# Start Nginx and PHP-FPM
CMD ["sh", "-c", "php-fpm -D && nginx -g 'daemon off;'"]
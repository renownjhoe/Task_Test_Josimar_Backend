#PHP-FPM base image
FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    sqlite3 \
    libsqlite3-dev \
    libicu-dev \
    zip \
    unzip

# Install PHP extensions # Include intl because of the complaint from composer
RUN docker-php-ext-install intl pdo_sqlite pdo_mysql mbstring exif pcntl bcmath gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy existing application directory contents
COPY . .

#Install application dependencies and # Fix permissions and Git safe directory first
RUN git config --global --add safe.directory /var/www/html && \
    chown -R www-data:www-data /var/www/html/storage && \
    chmod -R 775 /var/www/html/storage && \
    touch database/database.sqlite && \
    chmod 755 database/database.sqlite

# Install dependencies with platform checks seperately
RUN composer install --optimize-autoloader --no-dev --ignore-platform-reqs


#Some artisan commands
COPY .env.docker .env

RUN php artisan key:generate
RUN php artisan migrate
RUN php artisan optimize:clear

# Expose port 9000 and start php-fpm server
EXPOSE 9000

#Starts the PHP-FPM server
CMD ["php-fpm", "-F"]
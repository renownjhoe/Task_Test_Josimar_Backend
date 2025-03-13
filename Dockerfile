FROM php:8.2-fpm

# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    libzip-dev \
    unzip \
    libicu-dev \
    sqlite3 \
    libsqlite3-dev \
    && docker-php-ext-configure zip \
    && docker-php-ext-install zip intl pdo pdo_sqlite

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy application files
COPY . /var/www

# Install PHP dependencies via Composer
RUN composer install

EXPOSE 9000

CMD ["php-fpm"]

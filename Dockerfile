# Stage 1: Install dependencies
FROM php:8.2-fpm AS dependencies

# Install system dependencies for intl, zip, and other required extensions
RUN apt-get update && apt-get install -y \
    libicu-dev \
    libzip-dev \
    unzip \
    git \
    curl \
    && docker-php-ext-install intl zip

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /app

# Copy only necessary files for composer install
COPY composer.json composer.lock artisan ./
COPY app/ app/
COPY config/ config/
COPY database/ database/
COPY public/ public/
COPY resources/ resources/
COPY routes/ routes/
COPY storage/ storage/
COPY .env.example .env
COPY bootstrap/ bootstrap/

# Set cache directory
ENV COMPOSE_CACHE_DIR=/app/bootstrap/cache

# Run Composer install
RUN composer install --optimize-autoloader --no-dev --prefer-dist

# Stage 2: Build application
FROM php:8.2-fpm

WORKDIR /app

# Copy vendor folder from dependencies stage
COPY --from=dependencies /app/vendor ./vendor

# Copy the rest of your application files
COPY . .
COPY database/ database/
COPY public/ public/
COPY resources/ resources/
COPY routes/ routes/
COPY storage/ storage/
COPY app/ app/
COPY config/ config/
COPY .env.docker .env

# Ensure proper permissions
RUN apt-get update && apt-get install -y git && git config --global --add safe.directory /app && \
    chown -R www-data:www-data /app/storage && \
    chmod -R 775 /app/storage && \
    touch database/database.sqlite && \
    chmod 755 database/database.sqlite

# Run Laravel setup commands
RUN php artisan key:generate
RUN php artisan migrate
RUN php artisan optimize:clear

EXPOSE 9000

CMD ["php-fpm", "-F"]
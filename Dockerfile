# Stage 1: Build stage for PHP dependencies and Node.js assets
FROM php:8.2-fpm-alpine AS build

# Install system dependencies for PHP extensions
RUN apk add --no-cache \
    git \
    unzip \
    curl \
    libzip-dev \
    libpng-dev \
    libpq-dev \
    build-base \
    nodejs \
    npm \
    # Additional dependencies for specific extensions
    icu-dev \
    freetype-dev \
    libjpeg-turbo-dev

# Install and configure PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-configure intl && \
    docker-php-ext-install \
        pdo_mysql \
        pdo_pgsql \
        zip \
        gd \
        intl

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Set working directory for the build
WORKDIR /app

COPY . /app

# Install Node.js dependencies and build assets
RUN npm install && npm run build

# Install PHP dependencies
RUN composer install --optimize-autoloader --no-dev

# Stage 2: Runtime stage
FROM php:8.2-fpm-alpine

# Install runtime dependencies for PHP extensions
RUN apk add --no-cache \
    libpq \
    libpng \
    libzip \
    icu-libs \
    nodejs \
    npm \
    # Additional dependencies for specific extensions
    freetype \
    libjpeg-turbo

# Install and configure PHP extensions in the runtime stage
# We need to install these again in the runtime stage
RUN apk add --no-cache --virtual .build-deps \
    libzip-dev \
    libpng-dev \
    libpq-dev \
    icu-dev \
    freetype-dev \
    libjpeg-turbo-dev \
    build-base && \
    docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-configure intl && \
    docker-php-ext-install \
        pdo_mysql \
        pdo_pgsql \
        zip \
        gd \
        intl && \
    apk del .build-deps

# Copy Composer from the build stage
COPY --from=build /usr/local/bin/composer /usr/local/bin/composer

# Set working directory
WORKDIR /app

# Copy application files from the build stage
COPY --from=build /app /app

# Ensure the directories exist before setting permissions
RUN mkdir -p /app/storage /app/bootstrap/cache

# Set appropriate permissions for Laravel folders
RUN chmod -R 775 /app/storage /app/bootstrap/cache && \
    chown -R www-data:www-data /app/storage /app/bootstrap/cache

# Expose PHP-FPM port
EXPOSE 9000

# Start the application
CMD ["sh", "-c", "php artisan key:generate && php artisan migrate && php artisan db:seed && php-fpm"]
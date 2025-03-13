# Stage 1: Build stage for PHP dependencies and Node.js assets
FROM php:8.2-fpm-alpine AS build

# Install build dependencies for PHP extensions and Node.js
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

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Set working directory for the build
WORKDIR /app

COPY . /app

# Install Node.js globally in the build stage (optional)
RUN npm install -g npm

# Clean up build dependencies to reduce image size
RUN apk del .build-deps

# Stage 2: Runtime stage
FROM php:8.2-fpm-alpine

# Install only runtime dependencies
RUN apk add --no-cache \
    libpq \
    libpng \
    libzip \
    nodejs \
    npm

# Copy Composer from the build stage
COPY --from=build /usr/local/bin/composer /usr/local/bin/composer

# Set working directory
WORKDIR /app

# Copy PHP runtime extensions and configuration from the build stage
COPY --from=build /usr/local/lib/php/extensions /usr/local/lib/php/extensions
COPY --from=build /usr/local/etc/php/conf.d /usr/local/etc/php/conf.d

# Copy application files from the build stage
COPY --from=build /app /app

# Ensure the directories exist before setting permissions
RUN mkdir -p /app/storage /app/bootstrap/cache

# Set appropriate permissions for Laravel folders

RUN echo "changing mode and owner"
RUN chmod -R 775 /app/storage /app/bootstrap/cache
RUN chown -R www-data:www-data /app/storage /app/bootstrap/cache


RUN echo "starting application"
# Expose port and set CMD
EXPOSE 9000
CMD ["sh", "-c", "composer install && npm install && npm run dev && php artisan key:generate && php artisan migrate && php artisan db:seed && php-fpm -F"]
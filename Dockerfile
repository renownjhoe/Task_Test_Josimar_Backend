# Stage 1: Build stage for PHP dependencies and Node.js assets
FROM php:8.2-fpm-alpine AS build

# Install build dependencies for PHP extensions and Node.js
RUN apk add --no-cache --virtual .build-deps \
    git \
    unzip \
    curl \
    libzip-dev \
    libpng-dev \
    libpq-dev \
    icu-dev \
    build-base \
    nodejs \
    npm && \
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

# Copy the .env.example to .env
COPY .env.example .env

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
    icu \
    nodejs \
    npm \
    mysql-client \
    && docker-php-ext-install pdo_mysql

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
RUN mkdir -p /app/storage /app/bootstrap/cache && \
    chmod -R 775 /app/storage /app/bootstrap/cache && \
    chown -R www-data:www-data /app/storage /app/bootstrap/cache

    
# Change ownership of the database directory
# RUN chown www-data:www-data /app/database

# # Create the database file
# RUN touch /app/database/database.sqlite && \
#     chmod 775 /app/database/database.sqlite && \
#     chown www-data:www-data /app/database/database.sqlite

RUN echo "Export Port"
# Expose port and set CMD
EXPOSE 9000

RUN echo "Run command"

CMD ["sh", "-c", "composer install && npm install && npm run build && php artisan key:generate && php artisan migrate --force && php-fpm -F"]
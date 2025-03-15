# Stage 2: Runtime stage
FROM php:8.2-fpm-alpine

# Install only runtime dependencies
RUN apk add --no-cache \
        libpq \
        libpng \
        libzip \
        icu \
        nodejs \
        npm

# Copy Composer from the build stage
COPY --from=build /usr/local/bin/composer /usr/local/bin/composer

# Set working directory
WORKDIR /app

COPY composer.json composer.lock ./

COPY . /app

# Copy PHP runtime extensions and configuration from the build stage
COPY --from=build /usr/local/lib/php/extensions /usr/local/lib/php/extensions
COPY --from=build /usr/local/etc/php/conf.d /usr/local/etc/php/conf.d

# Ensure the directories exist before setting permissions
RUN mkdir -p /app/storage /app/bootstrap/cache && \
        chmod -R 770 /app/storage /app/bootstrap/cache && \
        chown -R www-data:www-data /app/storage /app/bootstrap/cache

# Change ownership of the database directory
RUN chown www-data:www-data /app/database

# Create the database file
RUN touch /app/database/database.sqlite

# Change ownership of the database.sqlite file
RUN chown www-data:www-data /app/database/database.sqlite

RUN echo "Export Port"
# Expose port and set CMD
EXPOSE 9000

RUN echo "Run command"

CMD ["sh", "-c", "composer install && npm install && npm run build && php artisan key:generate && php artisan migrate --force && php-fpm -F"]
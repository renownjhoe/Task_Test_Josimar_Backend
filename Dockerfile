# Stage 1: Install dependencies
FROM php:8.2-fpm AS dependencies

WORKDIR /app

COPY composer.json composer.lock ./

RUN composer install --optimize-autoloader --no-dev --prefer-dist

# Stage 2: Build application
FROM php:8.2-fpm

WORKDIR /app

COPY --from=dependencies /app/vendor ./vendor

COPY . .

RUN git config --global --add safe.directory /app && \
    chown -R www-data:www-data /app/storage && \
    chmod -R 775 /app/storage && \
    touch database/database.sqlite && \
    chmod 755 database/database.sqlite

COPY credentials .env

RUN php artisan key:generate
RUN php artisan migrate
RUN php artisan optimize:clear

EXPOSE 9000

CMD ["php-fpm", "-F"]
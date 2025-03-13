#!/bin/sh
set -e

# Run application setup tasks
composer install
npm install
npm run dev
php artisan key:generate
php artisan migrate

# Ensure proper permissions
chmod -R 775 /app/storage /app/bootstrap/cache
chown -R www-data:www-data /app/storage /app/bootstrap/cache

# Start PHP-FPM
exec php-fpm
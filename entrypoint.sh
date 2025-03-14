#!/bin/sh
set -e

# Run application setup
composer install
npm install

# Start Vite in the background
npm run dev &

# Generate application key
php artisan key:generate

# Ensure proper permissions
chmod -R 775 /app/storage /app/bootstrap/cache
chown -R www-data:www-data /app/storage /app/bootstrap/cache

# Start PHP-FPM in the foreground
exec php-fpm
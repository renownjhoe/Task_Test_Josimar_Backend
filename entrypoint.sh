cat > fix-entrypoint.sh << 'EOF'

#!/bin/sh
set -e

# Run application setup
composer install
npm install

# Start Vite in the background
nohup npm run dev & /dev/null 2>&1 &

# Generate application key
php artisan key:generate --force

# Ensure proper permissions
chmod -R 775 /app/storage /app/bootstrap/cache
chown -R www-data:www-data /app/storage /app/bootstrap/cache

# Start PHP-FPM in the foreground
exec php-fpm

EOF
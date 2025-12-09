#!/bin/bash
set -e

echo "Setting permissions..."
chown -R www-data:www-data /var/www/html
chmod -R 755 /var/www/html/storage
chmod -R 755 /var/www/html/bootstrap/cache

echo "Installing composer dependencies..."
composer install --no-dev --optimize-autoloader

echo "Running migrations..."
php artisan migrate --force

# Optional: seed database if needed
# echo "Seeding database..."
# php artisan db:seed --force

echo "Starting Apache..."
apache2-foreground

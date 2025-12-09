#!/bin/bash
set -e

# Ensure correct permissions
chown -R www-data:www-data /var/www/html
chmod -R 755 /var/www/html/storage
chmod -R 755 /var/www/html/bootstrap/cache

# Install composer dependencies (optional if you want fresh install every deploy)
composer install --no-dev --optimize-autoloader

# Run migrations
php artisan migrate --force

# Optional: seed database
# php artisan db:seed --force

# Start Apache in foreground
apache2-foreground

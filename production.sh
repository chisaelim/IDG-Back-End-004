#!/bin/bash
set -e

chown -R www-data:www-data /var/www/html/bootstrap/cache
chown -R www-data:www-data /var/www/html/storage
chown -R www-data:www-data /var/www/html/storage/app
chown -R www-data:www-data /var/www/html/storage/app/public
chown -R www-data:www-data /var/www/html/storage/app/private
chmod -R g+s /var/www/html/storage

# Install dependencies without production optimizations
composer install
wait $!

# Generate key and run migrations first
php artisan key:generate --force
php artisan migrate --force
wait $!

# THEN clear caches (this preserves the built assets)
php artisan optimize:clear

# Laravel optimizations
php artisan config:cache    
php artisan route:cache       
php artisan view:cache

# Create storage link if needed
php artisan storage:link

# Retry failed jobs
php artisan queue:retry all

# Start services
apache2-foreground &
php artisan reverb:start &
# php artisan schedule:work &
php artisan queue:work --tries=3 --timeout=600
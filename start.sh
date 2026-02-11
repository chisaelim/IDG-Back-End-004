#!/bin/bash
set -e

chown -R www-data:www-data /var/www/html/bootstrap/cache
chown -R www-data:www-data /var/www/html/storage
chown -R www-data:www-data /var/www/html/storage/app #optinal
chown -R www-data:www-data /var/www/html/storage/app/public #optinal
chown -R www-data:www-data /var/www/html/storage/app/private #optinal
chmod -R g+s /var/www/html/storage

composer install
wait $!

# Generate key and run migrations first
php artisan key:generate --force
php artisan migrate --force
wait $!

# Clear and optimize caches
php artisan optimize:clear

# Create storage link if needed
php artisan storage:link

# Retry failed jobs
php artisan queue:retry all

# Start services
apache2-foreground &
php artisan reverb:start &
# php artisan schedule:work &
php artisan queue:work

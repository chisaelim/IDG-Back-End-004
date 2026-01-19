chown -R www-data:www-data /var/www/html/bootstrap/cache
chown -R www-data:www-data /var/www/html/storage

# Install dependencies without production optimizations
composer install &
wait $!

# Generate key and run migrations first
php artisan key:generate --force
php artisan migrate --force

# Run specific migrations for cache and jobs tables
# php artisan migrate --path=database/migrations/0001_01_01_000001_create_cache_table.php --force
# php artisan migrate --path=database/migrations/0001_01_01_000002_create_jobs_table.php --force

# Clear and optimize caches
php artisan optimize:clear

# Retry failed jobs
php artisan queue:retry all

# Start services
apache2-foreground &
# php artisan reverb:start &
php artisan queue:work

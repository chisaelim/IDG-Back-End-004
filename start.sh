# Install dependencies without production optimizations
composer install &
wait $!

# Generate key and run migrations first
php artisan key:generate --force
php artisan migrate --force

# Run specific migrations for cache and jobs tables
# php artisan migrate --path=database/migrations/0001_01_01_000001_create_cache_table.php --force
# php artisan migrate --path=database/migrations/0001_01_01_000002_create_jobs_table.php --force

# Restart Reverb to apply any changes
# php artisan reverb:restart

# Retry failed jobs
php artisan queue:retry all

# Start services
apache2-foreground &
# php artisan reverb:start &
php artisan queue:work

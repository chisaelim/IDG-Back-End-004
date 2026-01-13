# Run specific migrations
php artisan migrate --path=database/migrations/0001_01_01_000001_create_cache_table.php --force
php artisan migrate --path=database/migrations/0001_01_01_000002_create_jobs_table.php --force

# Start Apache in the foreground (main process)
apache2-foreground &

# Start the queue worker in the background
php artisan queue:work
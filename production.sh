#!/bin/bash
set -e

# Install dependencies without production optimizations
composer install
npm install

# Generate key and run migrations first
php artisan key:generate --force
php artisan migrate --force

# Build frontend assets FIRST
npm run build

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
php artisan queue:work --tries=3 --timeout=600 &

wait
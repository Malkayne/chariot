#!/bin/sh
set -e

# Cache Laravel configuration, routes, and views for production optimization
echo "Caching Laravel configuration, routes, and views..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations automatically
echo "Running database migrations..."
php artisan migrate --force

# Check if RUN_SEEDER is set to true and run database seeders
if [ "$RUN_SEEDER" = "true" ]; then
    echo "Running database seeders..."
    php artisan db:seed --force
fi

# Execute the default Apache command to keep the container running
echo "Starting Apache Web Server..."
exec apache2-foreground

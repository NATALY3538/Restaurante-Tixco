#!/bin/sh
set -e

# Run database migrations automatically on container start
echo "Running database migrations..."
php artisan migrate --force

# Optimize Laravel caching for production
echo "Caching Laravel configuration, routes, and views..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Determine port from Render $PORT env or default to 10000
PORT=${PORT:-10000}
echo "Starting Laravel Web Server listening on 0.0.0.0:$PORT..."

exec php artisan serve --host=0.0.0.0 --port=$PORT

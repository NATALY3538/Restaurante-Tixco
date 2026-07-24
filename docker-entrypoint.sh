#!/bin/sh
set -e

# Check if database connection is configured and reachable
echo "Checking database connection..."
if php artisan db:monitor > /dev/null 2>&1 || php artisan migrate:status > /dev/null 2>&1; then
    echo "Database connection successful. Running migrations..."
    php artisan migrate --force
else
    echo "⚠️ ADVERTENCIA: La base de datos no está disponible o las credenciales (DB_HOST) siguen con texto de plantilla. Se omiten las migraciones temporales para permitir el arranque."
fi

# Optimize Laravel caching for production
echo "Caching Laravel configuration, routes, and views..."
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

# Determine port from Render $PORT env or default to 10000
PORT=${PORT:-10000}
echo "Starting Laravel Web Server listening on 0.0.0.0:$PORT..."

exec php artisan serve --host=0.0.0.0 --port=$PORT

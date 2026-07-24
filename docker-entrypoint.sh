#!/bin/sh
set -e

echo "=== INITIALIZING RESTAURANTE TIXCO CONTAINER ==="

# Check database configuration
if [ "$DB_CONNECTION" = "mysql" ] && [ "$DB_HOST" != "127.0.0.1" ] && [ "$DB_HOST" != "tu_host_de_bd.render.com" ] && [ -n "$DB_HOST" ]; then
    echo "Using configured MySQL database at $DB_HOST..."
    if php artisan db:monitor > /dev/null 2>&1 || php artisan migrate:status > /dev/null 2>&1; then
        echo "MySQL Connection successful!"
        php artisan migrate --force
        php artisan db:seed --class=RoleSeeder --force || true
        php artisan db:seed --class=SucursalSeeder --force || true
        php artisan db:seed --class=ReservationSeeder --force || true
    else
        echo "⚠️ Could not connect to MySQL at $DB_HOST. Falling back to SQLite."
        USE_SQLITE=1
    fi
else
    echo "No external MySQL configured or placeholder detected. Initializing standalone SQLite database."
    USE_SQLITE=1
fi

if [ "$USE_SQLITE" = "1" ]; then
    export DB_CONNECTION=sqlite
    export DB_DATABASE="/var/www/html/database/database.sqlite"
    
    mkdir -p /var/www/html/database
    touch /var/www/html/database/database.sqlite
    chown -R www-data:www-data /var/www/html/database
    chmod -R 777 /var/www/html/database

    echo "Running SQLite migrations & initial seeders..."
    php artisan migrate --force
    php artisan db:seed --class=RoleSeeder --force || true
    php artisan db:seed --class=SucursalSeeder --force || true
    php artisan db:seed --class=ReservationSeeder --force || true
fi

# Optimize Laravel caching for production
echo "Caching Laravel configuration, routes, and views..."
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

# Determine port from Render $PORT env or default to 10000
PORT=${PORT:-10000}
echo "🚀 Starting Restaurante Tixco Web Server on 0.0.0.0:$PORT..."

exec php artisan serve --host=0.0.0.0 --port=$PORT

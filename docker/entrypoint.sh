#!/bin/sh
set -e

# Cache configuration, routes, views if in production
if [ "$APP_ENV" = "production" ]; then
    echo "Caching Laravel configuration, routes, and views..."
    php /var/www/html/artisan config:cache || true
    php /var/www/html/artisan route:cache || true
    php /var/www/html/artisan view:cache || true
fi

# Ensure storage link exists
php /var/www/html/artisan storage:link || true

# Set storage and bootstrap/cache permissions
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

exec "$@"

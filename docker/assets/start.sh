#!/bin/bash
set -e

# Transform the nginx configuration
node /assets/scripts/prestart.mjs /assets/nginx.template.conf /etc/nginx.conf

# Ensure Laravel dirs exist when using Docker volumes (empty volumes hide container content)
mkdir -p /app/storage/framework/cache/data \
         /app/storage/framework/sessions \
         /app/storage/framework/views \
         /app/storage/logs \
         /app/bootstrap/cache
chown -R www-data:www-data /app/storage /app/bootstrap/cache
chmod -R 775 /app/storage /app/bootstrap/cache

# Set APP_URL to HTTPS in production if not already set
if [ "$APP_ENV" = "production" ] && ([ -z "$APP_URL" ] || [[ "$APP_URL" == http://* ]]); then
    export APP_URL="https://${SERVER_NAME:-localhost}"
fi

# Run migrations if database is available
php /app/artisan migrate --force || true

# Clear and cache config
php /app/artisan config:clear || true
php /app/artisan config:cache || true

# Storage Link
php /app/artisan storage:link || true

# Start supervisor
supervisord -c /assets/supervisord.conf -n


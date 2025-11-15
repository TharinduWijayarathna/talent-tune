#!/bin/bash

# Transform the nginx configuration
node /assets/scripts/prestart.mjs /assets/nginx.template.conf /etc/nginx.conf

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


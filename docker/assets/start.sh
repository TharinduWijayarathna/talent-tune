#!/bin/bash
set -e

# Transform the nginx configuration (substitute PORT)
export PORT="${PORT:-80}"
sed "s/\${PORT}/$PORT/g" /assets/nginx.template.conf > /etc/nginx.conf

# Set APP_URL to HTTPS in production if not already set
if [ "$APP_ENV" = "production" ] && ([ -z "$APP_URL" ] || [[ "$APP_URL" == http://* ]]); then
    export APP_URL="https://talenttune.site"
fi

# Run migrations if database is available
php /app/artisan migrate --force

# Clear and cache config
php /app/artisan config:clear
php /app/artisan config:cache

# Clear cache (after DB exists from migrate above)
php /app/artisan cache:clear || true

# Storage Link
php /app/artisan storage:link

# Start supervisor
exec supervisord -c /etc/supervisord.conf -n

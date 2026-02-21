#!/bin/sh
set -e

# Transform the nginx configuration (substitute PORT)
export PORT="${PORT:-80}"
sed "s/\${PORT}/$PORT/g" /assets/nginx.template.conf > /etc/nginx.conf

# Set APP_URL to HTTPS in production if not already set
if [ "$APP_ENV" = "production" ]; then
  case "${APP_URL:-}" in ""|http://*) export APP_URL="https://talenttune.site";; esac
fi

# These can fail if DB/env not ready; don't exit the container
php /app/artisan migrate --force || true
php /app/artisan config:clear || true
php /app/artisan config:cache || true
php /app/artisan cache:clear || true
php /app/artisan storage:link || true

# Ensure storage and bootstrap/cache are writable by www-data (PHP-FPM).
# Artisan commands above run as root and may create files owned by root.
chown -R www-data:www-data /app/storage /app/bootstrap/cache

# Start supervisor (must not exit)
exec supervisord -c /etc/supervisord.conf -n

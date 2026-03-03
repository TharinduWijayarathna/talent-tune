#!/bin/sh
set -e

# Transform the nginx configuration (substitute PORT)
export PORT="${PORT:-80}"
sed "s/\${PORT}/$PORT/g" /assets/nginx.template.conf > /etc/nginx.conf

# Set APP_URL to HTTPS in production if not already set
if [ "$APP_ENV" = "production" ]; then
  case "${APP_URL:-}" in ""|http://*) export APP_URL="https://talenttune.site";; esac
fi

# Ensure storage directory structure exists first (needed when volume mount overwrites /app/storage).
# Create log file so it exists before any process writes; avoids permission issues when volume is mounted.
mkdir -p /app/storage/logs \
  /app/storage/framework/cache/data \
  /app/storage/framework/sessions \
  /app/storage/framework/views \
  /app/storage/app/public \
  /app/bootstrap/cache
touch /app/storage/logs/laravel.log

# Fix ownership so PHP-FPM (www-data) can write. Critical when using volume mounts (e.g. ./storage:/app/storage).
if chown -R www-data:www-data /app/storage /app/bootstrap/cache 2>/dev/null; then
  chmod -R 775 /app/storage /app/bootstrap/cache
else
  # Fallback when running as non-root (e.g. restricted K8s): allow all to write so at least logging works
  chmod -R 777 /app/storage /app/bootstrap/cache
fi

# These can fail if DB/env not ready; don't exit the container
php /app/artisan migrate --force || true
php /app/artisan config:clear || true
php /app/artisan config:cache || true
php /app/artisan cache:clear || true
php /app/artisan storage:link || true

# Re-apply ownership after artisan (root may have created files in storage/bootstrap)
if chown -R www-data:www-data /app/storage /app/bootstrap/cache 2>/dev/null; then
  chmod -R 775 /app/storage /app/bootstrap/cache
else
  chmod -R 777 /app/storage /app/bootstrap/cache
fi

# Start supervisor (must not exit)
exec supervisord -c /etc/supervisord.conf -n

# Multi-stage: build frontend with Node 22, run with PHP 8.2 + nginx + supervisor
# ----------------------------
# Stage 1: PHP + Composer (deps + app)
# ----------------------------
FROM php:8.2-cli-bookworm AS php-base
RUN apt-get update && apt-get install -y --no-install-recommends \
    git unzip libzip-dev libpng-dev libonig-dev libxml2-dev \
    && docker-php-ext-install zip pdo_mysql mbstring exif pcntl bcmath gd \
    && apt-get clean && rm -rf /var/lib/apt/lists/*
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /app
COPY composer.json composer.lock* ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist
COPY . .
RUN composer dump-autoload --optimize

# ----------------------------
# Stage 2: Vite build (needs PHP for wayfinder:generate)
# ----------------------------
FROM php-base AS frontend
# Install Node 22 (Wayfinder plugin runs "php artisan wayfinder:generate" during build)
RUN curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get install -y --no-install-recommends nodejs \
    && apt-get clean && rm -rf /var/lib/apt/lists/*
WORKDIR /app
RUN npm ci && npm run build

# ----------------------------
# Stage 3: Runtime (PHP-FPM + nginx + supervisor)
# ----------------------------
FROM php:8.2-fpm-bookworm AS runtime
RUN apt-get update && apt-get install -y --no-install-recommends \
    nginx supervisor \
    libzip-dev libpng-dev libonig-dev libxml2-dev \
    && docker-php-ext-install zip pdo_mysql mbstring exif pcntl bcmath gd \
    && pecl install redis && docker-php-ext-enable redis \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# App and logs
RUN mkdir -p /var/log \
    && touch /var/log/nginx-access.log /var/log/nginx-error.log \
    && chown -R www-data:www-data /var/log

WORKDIR /app
COPY --from=php-base /app /app
COPY --from=frontend /app/public/build /app/public/build

# Runtime assets (start script, configs)
COPY docker/assets/start.sh /assets/start.sh
COPY docker/assets/nginx.template.conf /assets/nginx.template.conf
COPY docker/assets/supervisord.conf /assets/supervisord.conf
COPY docker/assets/php-fpm.conf /assets/php-fpm.conf
COPY docker/assets/worker-nginx.conf /assets/worker-nginx.conf
COPY docker/assets/worker-phpfpm.conf /assets/worker-phpfpm.conf
COPY docker/assets/worker-laravel.conf /assets/worker-laravel.conf

RUN mkdir -p /etc/supervisor/conf.d \
    && cp /assets/worker-*.conf /etc/supervisor/conf.d/ \
    && cp /assets/supervisord.conf /etc/supervisord.conf \
    && chown -R www-data:www-data /app \
    && chmod -R 755 /app \
    && chmod -R 775 /app/storage \
    && chmod +x /assets/start.sh

EXPOSE 80
ENV PORT=80
ENTRYPOINT ["/assets/start.sh"]

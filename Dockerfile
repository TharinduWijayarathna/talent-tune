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
RUN curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get install -y --no-install-recommends nodejs \
    && apt-get clean && rm -rf /var/lib/apt/lists/*
WORKDIR /app
RUN npm ci && npm run build

# ----------------------------
# Stage 3: Runtime (Alpine = smaller image)
# ----------------------------
FROM php:8.2-fpm-alpine3.20 AS runtime
# PHP extensions: install build deps, build, then remove build deps
RUN apk add --no-cache \
    nginx supervisor \
    libzip libpng libxml2 libjpeg-turbo freetype oniguruma \
    && apk add --no-cache --virtual .build-deps \
    $PHPIZE_DEPS libzip-dev libpng-dev libxml2-dev libjpeg-turbo-dev freetype-dev oniguruma-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) zip pdo_mysql mbstring exif pcntl bcmath gd \
    && pecl install redis && docker-php-ext-enable redis \
    && apk del .build-deps

RUN mkdir -p /var/log \
    && touch /var/log/nginx-access.log /var/log/nginx-error.log \
    && chown -R www-data:www-data /var/log

WORKDIR /app
COPY --from=php-base /app /app
COPY --from=frontend /app/public/build /app/public/build

# Remove unneeded files from final image
RUN rm -rf /app/node_modules /app/tests /app/.phpunit.result.cache \
    /app/storage/framework/cache/data/* /app/storage/framework/sessions/* \
    /app/storage/framework/views/* /app/storage/logs/* \
    /app/bootstrap/cache/* 2>/dev/null; true

COPY docker/assets/start.sh /assets/start.sh
COPY docker/assets/start-nginx.sh /assets/start-nginx.sh
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
    && chmod +x /assets/start.sh /assets/start-nginx.sh

EXPOSE 80
ENV PORT=80
ENTRYPOINT ["/assets/start.sh"]

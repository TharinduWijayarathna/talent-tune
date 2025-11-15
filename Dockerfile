# Multi-stage build for Laravel + Vue + Inertia application

# Stage 1: Build frontend assets
FROM node:22-alpine AS node-builder

WORKDIR /app

# Install PHP and required extensions for wayfinder plugin
RUN apk add --no-cache \
    php82 \
    php82-common \
    php82-cli \
    php82-openssl \
    php82-phar \
    php82-mbstring \
    php82-xml \
    php82-tokenizer \
    php82-json \
    php82-pdo \
    php82-pdo_sqlite \
    sqlite \
    && ln -s /usr/bin/php82 /usr/bin/php

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy package files
COPY package*.json ./

# Install Node.js dependencies
RUN npm ci --only=production=false

# Copy all application files (needed for wayfinder plugin)
COPY . .

# Install PHP dependencies for wayfinder generation
# Using --ignore-platform-reqs to handle potential platform mismatches in Alpine
# If lock file is incompatible, update dependencies (acceptable in build stage)
RUN set -e; \
    if ! composer install --no-interaction --prefer-dist --no-scripts --ignore-platform-reqs; then \
        echo "Lock file incompatible, updating dependencies for build stage..."; \
        composer update --no-interaction --prefer-dist --no-scripts --ignore-platform-reqs; \
    fi

# Set up minimal Laravel environment for wayfinder
# Create .env file with minimal required configuration
RUN if [ ! -f .env ]; then \
        echo "APP_NAME=Laravel" > .env && \
        echo "APP_ENV=local" >> .env && \
        echo "APP_KEY=" >> .env && \
        echo "APP_DEBUG=true" >> .env && \
        echo "APP_URL=http://localhost" >> .env && \
        echo "LOG_CHANNEL=stack" >> .env && \
        echo "LOG_LEVEL=debug" >> .env && \
        echo "DB_CONNECTION=sqlite" >> .env && \
        echo "DB_DATABASE=/tmp/database.sqlite" >> .env && \
        echo "CACHE_STORE=file" >> .env && \
        echo "SESSION_DRIVER=file" >> .env && \
        echo "QUEUE_CONNECTION=sync" >> .env && \
        echo "BROADCAST_CONNECTION=log" >> .env && \
        echo "FILESYSTEM_DISK=local" >> .env && \
        echo "MAIL_MAILER=log" >> .env; \
    fi

# Ensure bootstrap/cache and storage directories exist and are writable
RUN mkdir -p bootstrap/cache \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    /tmp \
    && chmod -R 775 bootstrap/cache storage /tmp

# Create temporary SQLite database for Laravel bootstrap
RUN touch /tmp/database.sqlite && chmod 666 /tmp/database.sqlite

# Run composer scripts to set up Laravel (package discovery, etc.)
RUN composer dump-autoload --optimize --classmap-authoritative || true

# Generate APP_KEY using PHP (more reliable in Alpine)
RUN php -r "\$env = file_get_contents('.env'); \$key = 'base64:' . base64_encode(random_bytes(32)); \$env = preg_replace('/^APP_KEY=.*/m', 'APP_KEY=' . \$key, \$env); file_put_contents('.env', \$env);" 2>&1 || true
RUN php artisan key:generate --ansi 2>&1 || true

# Clear any cached config to ensure fresh bootstrap
RUN php artisan config:clear 2>&1 || true

# Discover packages (needed for wayfinder to work)
RUN php artisan package:discover --ansi 2>&1 || true

# Create wayfinder output directories
RUN mkdir -p resources/js/wayfinder resources/js/routes resources/js/actions

# Pre-generate wayfinder types to ensure it works before vite build
# This helps identify issues early and ensures the command works during vite build
RUN echo "=== Pre-generating wayfinder types ===" && \
    php artisan wayfinder:generate --with-form 2>&1 && \
    echo "=== Wayfinder generation completed successfully ===" || \
    (echo "=== ERROR: wayfinder:generate failed ===" && \
     php artisan wayfinder:generate --with-form 2>&1 && \
     exit 1)

# Build frontend assets (wayfinder plugin will regenerate, but should work now)
RUN echo "=== Starting Vite build ===" && \
    npm run build 2>&1

# Stage 2: PHP production image with Nginx and PHP-FPM
FROM php:8.2-fpm

# Set working directory
WORKDIR /app

# Install system dependencies and PHP extensions required by Laravel
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    nginx \
    supervisor \
    nodejs \
    npm \
    && docker-php-ext-install pdo_mysql pdo mbstring exif pcntl bcmath gd zip \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy composer files first for better layer caching
COPY --chown=www-data:www-data composer.json composer.lock ./

# Install PHP dependencies (production only)
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist --no-scripts

# Copy application files
COPY --chown=www-data:www-data . .

# Copy built assets from node-builder stage
COPY --from=node-builder --chown=www-data:www-data /app/public/build ./public/build

# Run post-install scripts and optimize
RUN composer dump-autoload --optimize --classmap-authoritative \
    && php artisan package:discover --ansi

# Copy supervisor and nginx configuration files
COPY --chown=root:root docker/assets/ /assets/

# Set up directories and permissions
RUN mkdir -p /var/log \
    && touch /var/log/nginx-access.log /var/log/nginx-error.log \
    && mkdir -p /etc/supervisor/conf.d/ \
    && chown -R www-data:www-data /var/log \
    && chown -R www-data:www-data /app \
    && chmod -R 755 /app \
    && chmod -R 775 /app/storage \
    && chmod -R 775 /app/bootstrap/cache \
    && chmod +x /assets/start.sh \
    && cp /assets/worker-*.conf /etc/supervisor/conf.d/

# Expose port (default 80, but will use PORT env var)
EXPOSE 80

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=40s --retries=3 \
    CMD curl -f http://localhost:${PORT:-80}/ || exit 1

# Start supervisor
CMD ["/assets/start.sh"]

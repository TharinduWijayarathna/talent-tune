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

# Build frontend assets
RUN npm run build

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

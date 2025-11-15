# Single-stage build for Laravel + Vue + Inertia application
FROM ubuntu:24.04

WORKDIR /app

# Set environment variables
ENV DEBIAN_FRONTEND=noninteractive
ENV TZ=UTC
ENV PHP_VERSION=8.2
ENV NODE_VERSION=22

# Set timezone
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

# Configure apt for better caching
RUN echo "Acquire::http::Pipeline-Depth 0;" > /etc/apt/apt.conf.d/99custom && \
    echo "Acquire::http::No-Cache true;" >> /etc/apt/apt.conf.d/99custom && \
    echo "Acquire::BrokenProxy true;" >> /etc/apt/apt.conf.d/99custom

# Install system dependencies, PHP, Node.js, and Nginx
RUN apt-get update && apt-get upgrade -y && \
    mkdir -p /etc/apt/keyrings && \
    apt-get install -y gnupg gosu curl ca-certificates zip unzip git supervisor sqlite3 \
        libcap2-bin libpng-dev nginx nano && \
    curl -sS 'https://keyserver.ubuntu.com/pks/lookup?op=get&search=0xb8dc7e53946656efbce4c1dd71daeaab4ad4cab6' | \
        gpg --dearmor | tee /etc/apt/keyrings/ppa_ondrej_php.gpg > /dev/null && \
    echo "deb [signed-by=/etc/apt/keyrings/ppa_ondrej_php.gpg] https://ppa.launchpadcontent.net/ondrej/php/ubuntu noble main" > \
        /etc/apt/sources.list.d/ppa_ondrej_php.list && \
    apt-get update && \
    apt-get install -y \
        php8.2-fpm php8.2-cli php8.2-dev \
        php8.2-pgsql php8.2-sqlite3 php8.2-gd \
        php8.2-curl php8.2-mysql php8.2-mbstring \
        php8.2-xml php8.2-zip php8.2-bcmath \
        php8.2-intl php8.2-readline php8.2-dom \
        php8.2-redis php8.2-pcov && \
    curl -sLS https://getcomposer.org/installer | php -- --install-dir=/usr/bin/ --filename=composer && \
    curl -fsSL https://deb.nodesource.com/gpgkey/nodesource-repo.gpg.key | gpg --dearmor -o /etc/apt/keyrings/nodesource.gpg && \
    echo "deb [signed-by=/etc/apt/keyrings/nodesource.gpg] https://deb.nodesource.com/node_$NODE_VERSION.x nodistro main" > /etc/apt/sources.list.d/nodesource.list && \
    apt-get update && \
    apt-get install -y nodejs && \
    npm install -g npm && \
    apt-get -y autoremove && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# Configure PHP
RUN echo "post_max_size = 100M" > /etc/php/8.2/fpm/conf.d/99-custom.ini && \
    echo "upload_max_filesize = 100M" >> /etc/php/8.2/fpm/conf.d/99-custom.ini && \
    echo "variables_order = EGPCS" >> /etc/php/8.2/fpm/conf.d/99-custom.ini && \
    echo "post_max_size = 100M" > /etc/php/8.2/cli/conf.d/99-custom.ini && \
    echo "upload_max_filesize = 100M" >> /etc/php/8.2/cli/conf.d/99-custom.ini && \
    echo "variables_order = EGPCS" >> /etc/php/8.2/cli/conf.d/99-custom.ini

# Copy application files
COPY --chown=www-data:www-data . .

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist --no-scripts

# Set up Laravel environment (copy from .env.example if .env doesn't exist)
RUN if [ ! -f .env ] && [ -f .env.example ]; then \
        cp .env.example .env; \
    fi

# Create required directories
RUN mkdir -p bootstrap/cache \
    storage/framework/{cache/data,sessions,views} \
    storage/logs \
    resources/js/{wayfinder,routes,actions} \
    && chmod -R 775 bootstrap/cache storage

# Discover packages and optimize
RUN php artisan package:discover --ansi && \
    composer dump-autoload --optimize --classmap-authoritative

# Install Node.js dependencies and build frontend assets
RUN npm ci --only=production=false && \
    npm run build && \
    npm cache clean --force

# Copy supervisor and nginx configuration files
COPY --chown=root:root docker/assets/ /assets/

# Set up directories and permissions
RUN mkdir -p /var/log /etc/supervisor/conf.d/ && \
    touch /var/log/nginx-access.log /var/log/nginx-error.log && \
    chown -R www-data:www-data /var/log /app && \
    chmod -R 755 /app && \
    chmod -R 775 /app/storage /app/bootstrap/cache && \
    chmod +x /assets/start.sh && \
    cp /assets/worker-*.conf /etc/supervisor/conf.d/

EXPOSE 80

HEALTHCHECK --interval=30s --timeout=3s --start-period=40s --retries=3 \
    CMD curl -f http://localhost:${PORT:-80}/ || exit 1

CMD ["/assets/start.sh"]

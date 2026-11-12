# ==============================================================================
# Stage 1: Build PHP dependencies & Frontend Assets
# ==============================================================================
FROM php:8.3-cli-bookworm AS builder

# Install system build dependencies, Node.js 20, and Composer
RUN apt-get update && apt-get install -y --no-install-recommends \
    curl \
    git \
    unzip \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    && docker-php-ext-install pdo_mysql bcmath zip pcntl \
    && curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Install Composer dependencies
COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-scripts --no-autoloader --no-interaction

# Install NPM dependencies
COPY package.json package-lock.json* ./
RUN npm ci || npm install

# Copy application source
COPY . .

# Generate autoload & Wayfinder routes
RUN composer dump-autoload --optimize --no-dev

# Build Vite & Inertia assets
RUN npm run build \
    && rm -rf node_modules \
    && rm -rf tests

# ==============================================================================
# Stage 2: Production Runtime
# ==============================================================================
FROM php:8.3-fpm-bookworm AS production

ENV APP_ENV=production
ENV DEBIAN_FRONTEND=noninteractive

# Install runtime dependencies (Nginx, Supervisor, PHP extensions)
RUN apt-get update && apt-get install -y --no-install-recommends \
    nginx \
    supervisor \
    curl \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libzip-dev \
    libonig-dev \
    libicu-dev \
    locales \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        opcache \
        intl \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

# Copy application from builder stage (already cleaned up of dev artifacts)
COPY --from=builder /var/www/html /var/www/html

# Copy configuration files
COPY docker/nginx/default.conf /etc/nginx/sites-available/default
COPY docker/php/php.ini /usr/local/etc/php/conf.d/custom-php.ini
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh

# Permissions
RUN chmod +x /usr/local/bin/entrypoint.sh \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]

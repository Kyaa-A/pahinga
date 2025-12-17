# Stage 1: Build assets
FROM node:20-alpine AS node-builder
WORKDIR /app
COPY package*.json ./
RUN npm ci
COPY resources ./resources
COPY vite.config.js ./
COPY public ./public
RUN npm run build

# Stage 2: Install PHP dependencies
FROM composer:latest AS composer-builder
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist
COPY . .
RUN composer dump-autoload --optimize --classmap-authoritative

# Stage 3: Production image
FROM php:8.3-cli-alpine

# Install system dependencies
RUN apk add --no-cache \
    libpq \
    libpng \
    libjpeg-turbo \
    freetype \
    oniguruma

# Install build dependencies, PHP extensions, then clean up
RUN apk add --no-cache --virtual .build-deps \
    postgresql-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    oniguruma-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) pdo pdo_pgsql pgsql mbstring exif pcntl bcmath gd opcache \
    && apk del .build-deps

# Copy OPcache configuration
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

# Set working directory
WORKDIR /app

# Copy application from composer stage
COPY --from=composer-builder /app/vendor ./vendor
COPY . .

# Copy built assets from node stage
COPY --from=node-builder /app/public/build ./public/build

# Set permissions
RUN chown -R www-data:www-data /app/storage /app/bootstrap/cache \
    && chmod -R 775 /app/storage /app/bootstrap/cache

# Expose port
EXPOSE 10000

# Start script
CMD php artisan config:clear && \
    php artisan cache:clear && \
    php artisan config:cache && \
    php artisan route:cache && \
    php artisan view:cache && \
    php artisan event:cache && \
    php artisan migrate --force && \
    php artisan serve --host=0.0.0.0 --port=${PORT:-10000}

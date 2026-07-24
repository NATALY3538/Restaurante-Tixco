# ═════════════════════════════════════════════════════════════════════════════
# STAGE 1: Build Frontend Assets (Vite & Tailwind CSS)
# ═════════════════════════════════════════════════════════════════════════════
FROM node:20-alpine AS node_builder
WORKDIR /app
COPY package*.json ./
RUN npm ci || npm install
COPY . .
RUN npm run build

# ═════════════════════════════════════════════════════════════════════════════
# STAGE 2: PHP 8.3 Production Environment
# ═════════════════════════════════════════════════════════════════════════════
FROM php:8.3-cli-alpine

# Install System Dependencies & Libraries
RUN apk add --no-cache \
    bash \
    curl \
    sqlite-dev \
    sqlite \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    oniguruma-dev \
    icu-dev

# Install required PHP Extensions for Laravel 11
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_mysql \
        pdo_sqlite \
        mbstring \
        gd \
        xml \
        zip \
        bcmath \
        intl

# Install Composer globally
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . /var/www/html

# Copy compiled assets from node_builder stage
COPY --from=node_builder /app/public/build /var/www/html/public/build

# Install PHP dependencies without dev dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Set permissions for Storage & Cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Expose Render default port
EXPOSE 10000

# Copy and setup entrypoint script
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

ENTRYPOINT ["docker-entrypoint.sh"]

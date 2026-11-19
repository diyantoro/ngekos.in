# syntax=docker/dockerfile:1

# ---------------------------------------------------------------------------
# Tahap 1: build aset frontend (Vite + Tailwind) dengan Node.js
# ---------------------------------------------------------------------------
FROM node:22-alpine AS assets

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci

COPY vite.config.js tailwind.config.js postcss.config.js ./
COPY resources ./resources
RUN npm run build

# ---------------------------------------------------------------------------
# Tahap 2: image aplikasi Laravel (PHP-FPM)
# ---------------------------------------------------------------------------
FROM php:8.3-fpm-alpine AS app

RUN apk add --no-cache \
        freetype-dev \
        icu-dev \
        libjpeg-turbo-dev \
        libpng-dev \
        libzip-dev \
        oniguruma-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        bcmath \
        exif \
        gd \
        intl \
        mbstring \
        opcache \
        pcntl \
        pdo_mysql \
        zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Cache dependency PHP lebih dulu agar rebuild cepat.
COPY composer.json composer.lock ./
RUN composer install \
        --no-interaction \
        --prefer-dist \
        --no-scripts \
        --no-autoloader \
        --optimize-autoloader

# Salin source aplikasi (vendor/node_modules/.env dikecualikan via .dockerignore).
COPY . .
COPY --from=assets /app/public/build ./public/build
COPY docker/php/php.ini /usr/local/etc/php/conf.d/zz-ngekos.ini

RUN composer dump-autoload --optimize --no-interaction \
    && mkdir -p \
        storage/app/public \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && cp -a public /opt/public-template

COPY docker/entrypoint.sh /usr/local/bin/entrypoint
RUN chmod +x /usr/local/bin/entrypoint

EXPOSE 9000

ENTRYPOINT ["entrypoint"]
CMD ["php-fpm"]

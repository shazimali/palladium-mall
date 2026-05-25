# ==================================================
# Stage 1: Base PHP Image
# ==================================================
FROM php:8.3-apache AS base

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    curl \
    libzip-dev \
    libonig-dev \
    libxml2-dev \
    libicu-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libxslt-dev \
    libfontconfig1 \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
    pdo_mysql \
    mbstring \
    zip \
    intl \
    gd \
    bcmath \
    exif \
    xsl \
    && a2enmod rewrite \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/palladium_mall

# ==================================================
# Stage 2: Composer Dependencies (CACHEABLE)
# ==================================================
FROM composer:2 AS vendor

WORKDIR /app

ENV COMPOSER_MEMORY_LIMIT=-1

COPY composer.json composer.lock ./

RUN composer validate --no-check-publish

RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader \
    --no-scripts \
    --ignore-platform-reqs

# ==================================================
# Stage 3: Node Build (Vite + Tailwind)
# ==================================================
FROM node:22-alpine AS node-builder

WORKDIR /app

COPY package.json package-lock.json* ./
RUN npm ci

COPY resources/ resources/
COPY vite.config.js* ./
COPY tailwind.config.* ./
COPY postcss.config.* ./

RUN npm run build

# Stage 4: Final Runtime Image
FROM base

COPY --from=vendor /app/vendor ./vendor
COPY --from=node-builder /app/public/build ./public/build

# Copy source — .dockerignore prevents vendor/node_modules/build overwrite
COPY . .

RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf
COPY docker/apache/000-default.conf /etc/apache2/sites-available/000-default.conf

COPY docker/startup.sh /usr/local/bin/startup.sh
RUN chmod +x /usr/local/bin/startup.sh

RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 80
CMD ["/usr/local/bin/startup.sh"]
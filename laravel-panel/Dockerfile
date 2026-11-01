FROM node:22-alpine AS assets
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci --no-audit
COPY vite.config.js ./
COPY resources ./resources
COPY public ./public
RUN npm run build

FROM php:8.3-cli AS app
RUN apt-get update && apt-get install -y --no-install-recommends \
    libonig-dev libpq-dev libzip-dev unzip \
    && docker-php-ext-install mbstring opcache pdo_pgsql zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /var/www/html
COPY . .
RUN composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader \
    && mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache
COPY --from=assets --chown=www-data:www-data /app/public/build ./public/build
USER www-data
EXPOSE 8000
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]

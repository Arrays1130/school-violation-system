FROM php:8.2-cli

# Install system dependencies (libpq-dev for PostgreSQL, build tools for phpredis)
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libicu-dev \
    libpq-dev \
    zip \
    unzip \
    nodejs \
    npm \
    $PHPIZE_DEPS \
    && docker-php-ext-install pdo_mysql pdo_pgsql mbstring exif pcntl bcmath gd zip intl \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apt-get purge -y --auto-remove -o APT::AutoRemove::RecommendsImportant=false $PHPIZE_DEPS \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY . /var/www

# Dean mobile web app: pre-built in public/dean-app (run scripts/build-dean-web.ps1 before deploy)

ENV COMPOSER_MEMORY_LIMIT=-1
ENV COMPOSER_PROCESS_TIMEOUT=600
RUN composer config --global github-protocols https \
    && composer install --no-dev --optimize-autoloader --prefer-dist --no-progress --no-interaction

RUN npm install && npm run build

RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

EXPOSE 8000

# Migrate on boot, seed if empty, index AI knowledge if empty (background), then serve (Render sets PORT)
CMD ["/bin/sh", "-c", "php artisan migrate --force && php artisan app:seed-if-empty && php artisan queue:work --queue=notifications,default --sleep=3 --tries=4 --backoff=30 --max-time=3600 & (php artisan ai:index-if-empty &) && php artisan serve --host=0.0.0.0 --port=${PORT:-8000}"]

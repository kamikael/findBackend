FROM php:8.2-cli

COPY --from=mlocati/php-extension-installer:2 /usr/bin/install-php-extensions /usr/local/bin/
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    curl \
    libsqlite3-dev \
    pkg-config \
    libssl-dev \
    libzip-dev \
    libicu-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libxml2-dev \
    libonig-dev \
    && install-php-extensions \
        bcmath \
        curl \
        gd \
        intl \
        mbstring \
        mongodb \
        pdo_sqlite \
        zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www

COPY . .

RUN php --ri gd
RUN php --ri mongodb
RUN composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction

RUN mkdir -p database storage/framework/cache storage/framework/sessions storage/framework/views bootstrap/cache \
    && touch database/database.sqlite \
    && chown -R www-data:www-data storage bootstrap/cache database

EXPOSE 10000

CMD ["sh", "-c", "php artisan config:clear && php artisan route:clear && php artisan view:clear && (php artisan storage:link || true) && php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=${PORT:-10000}"]

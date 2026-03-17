FROM php:8.2-cli

COPY --from=mlocati/php-extension-installer:2 /usr/bin/install-php-extensions /usr/local/bin/
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    curl \
    autoconf \
    g++ \
    make \
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
    zlib1g-dev \
    && install-php-extensions \
        bcmath \
        curl \
        dom \
        gd \
        intl \
        mbstring \
        pdo_sqlite \
        simplexml \
        xml \
        xmlreader \
        xmlwriter \
        zip \
    && pecl channel-update pecl.php.net \
    && pecl install mongodb-1.21.3 \
    && docker-php-ext-enable mongodb \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www

COPY . .

RUN php --ri gd
RUN php --ri mongodb
ENV COMPOSER_ALLOW_SUPERUSER=1

RUN composer install -vvv --no-dev --prefer-dist --optimize-autoloader --no-interaction --no-scripts

RUN mkdir -p database storage/framework/cache storage/framework/sessions storage/framework/views bootstrap/cache \
    && touch database/database.sqlite \
    && chown -R www-data:www-data storage bootstrap/cache database

EXPOSE 10000

CMD ["sh", "-c", "php artisan package:discover --ansi && php artisan config:clear && php artisan route:clear && php artisan view:clear && (php artisan storage:link || true) && php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=${PORT:-10000}"]

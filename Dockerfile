FROM php:8.2-cli

# ── Outils tiers ──────────────────────────────────────────────────────────────
COPY --from=mlocati/php-extension-installer:2 /usr/bin/install-php-extensions /usr/local/bin/
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# ── Dépendances système ───────────────────────────────────────────────────────
RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    unzip \
    curl \
    autoconf \
    g++ \
    make \
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
    libsqlite3-dev \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# ── Extensions PHP via install-php-extensions ────────────────────────────────
# pdo_pgsql est ajouté pour Render PostgreSQL (adapter selon votre DB)
# Retirer pdo_sqlite si vous n'utilisez pas SQLite en prod
RUN install-php-extensions \
    bcmath \
    curl \
    dom \
    gd \
    intl \
    mbstring \
    opcache \
    pdo_sqlite \
    pdo_pgsql \
    simplexml \
    xml \
    xmlreader \
    xmlwriter \
    zip \
    openssl

# ── MongoDB via PECL ──────────────────────────────────────────────────────────
RUN pecl channel-update pecl.php.net \
    && pecl install mongodb \
    && docker-php-ext-enable mongodb

# ── Vérification des extensions critiques ────────────────────────────────────
RUN php --ri gd && php --ri mongodb && php --ri openssl

# ── Répertoire de travail ─────────────────────────────────────────────────────
WORKDIR /var/www

# ── Copie du code source ──────────────────────────────────────────────────────
# Assurez-vous d'avoir un .dockerignore excluant vendor/, node_modules/, .env
COPY . .

# ── Variables Composer ────────────────────────────────────────────────────────
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV COMPOSER_MEMORY_LIMIT=-1

# ── Installation des dépendances PHP ─────────────────────────────────────────
RUN composer install \
    --no-dev \
    --prefer-dist \
    --optimize-autoloader \
    --no-interaction \
    --no-scripts \
    --no-progress

# ── Préparation des répertoires et permissions ────────────────────────────────
RUN mkdir -p \
        database \
        storage/framework/cache \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        storage/app/public \
        bootstrap/cache \
    && touch database/database.sqlite \
    && chmod -R 775 storage bootstrap/cache database \
    && chown -R root:root storage bootstrap/cache database

# ── Symlink storage (au build, pas au runtime) ────────────────────────────────
RUN php artisan storage:link || true

# ── OPcache config pour artisan serve (optionnel mais utile) ──────────────────
RUN echo "opcache.enable_cli=1" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.jit_buffer_size=100M" >> /usr/local/etc/php/conf.d/opcache.ini

# ── Port exposé (Render utilise $PORT, défaut 10000) ─────────────────────────
EXPOSE 10000

# ── Commande de démarrage ─────────────────────────────────────────────────────
CMD ["sh", "-c", "\
    php artisan package:discover --ansi && \
    php artisan config:clear && \
    php artisan config:cache && \
    php artisan route:clear && \
    php artisan route:cache && \
    php artisan view:clear && \
    php artisan view:cache && \
    php artisan migrate --force && \
    php artisan serve --host=0.0.0.0 --port=${PORT:-10000} \
"]
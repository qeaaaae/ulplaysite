#!/bin/bash
set -e

cd /var/www/html

if [ -f docker/env.docker ]; then
    echo "Applying Docker .env..."
    cp docker/env.docker .env
fi
if [ ! -f .env ]; then
    cp .env.example .env 2>/dev/null || true
    sed -i 's/DB_CONNECTION=sqlite/DB_CONNECTION=mysql/' .env 2>/dev/null || true
    sed -i 's/# DB_HOST=127.0.0.1/DB_HOST=mysql/' .env 2>/dev/null || true
fi

if ! grep -q 'APP_KEY=base64:' .env 2>/dev/null; then
    php artisan key:generate --no-interaction
fi

mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache storage/logs
chmod -R 775 storage bootstrap/cache 2>/dev/null || true

if [ ! -d vendor ] || [ ! -f vendor/autoload.php ]; then
    echo "Installing Composer dependencies..."
    composer install --no-interaction --prefer-dist --ignore-platform-reqs
fi
if [ -f vendor/composer/platform_check.php ]; then
    rm -f vendor/composer/platform_check.php
    sed -i '/platform_check/d' vendor/composer/autoload_real.php
fi

if [ ! -d node_modules ]; then
    echo "Installing npm dependencies..."
    npm install
fi

if [ ! -f public/build/manifest.json ]; then
    echo "Building assets..."
    npm run build
fi

exec "$@"

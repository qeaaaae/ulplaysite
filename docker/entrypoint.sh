#!/bin/bash
set -e

cd /var/www/html

if [ ! -f .env ]; then
    if [ -f docker/env.docker ]; then
        echo "Creating .env from docker/env.docker..."
        cp docker/env.docker .env
    else
        cp .env.example .env 2>/dev/null || true
    fi
fi

if ! grep -q 'APP_KEY=base64:' .env 2>/dev/null; then
    php artisan key:generate --no-interaction
fi

mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache storage/logs
chmod -R 775 storage bootstrap/cache 2>/dev/null || true

if [ ! -d vendor ] || [ ! -f vendor/autoload.php ]; then
    echo "Installing Composer dependencies..."
    composer install --no-interaction --prefer-dist
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

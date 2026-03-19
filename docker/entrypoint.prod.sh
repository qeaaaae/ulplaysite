#!/bin/bash
set -e

cd /var/www/html

mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache storage/logs
chmod -R 775 storage bootstrap/cache 2>/dev/null || true

exec "$@"

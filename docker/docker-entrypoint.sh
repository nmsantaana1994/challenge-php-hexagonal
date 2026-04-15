#!/bin/sh
set -e

cd /var/www/html

mkdir -p \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache

chown -R www-data:www-data storage bootstrap/cache
chmod -R ug+rwX storage bootstrap/cache

if [ -f storage/oauth-private.key ]; then
    chown www-data:www-data storage/oauth-private.key
    chmod 640 storage/oauth-private.key
fi

if [ -f storage/oauth-public.key ]; then
    chown www-data:www-data storage/oauth-public.key
    chmod 640 storage/oauth-public.key
fi

exec "$@"

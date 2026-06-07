#!/bin/sh
set -e

cd /var/www/html

if [ ! -f .env ] && [ -f .env.docker ]; then
    cp .env.docker .env
fi

mkdir -p \
    storage/app/public \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/testing \
    storage/framework/views \
    storage/logs \
    bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

if [ "${DB_CONNECTION:-}" = "mysql" ]; then
    echo "Waiting for MySQL at ${DB_HOST:-mysql}:${DB_PORT:-3306}..."
    until php -r "exit(@fsockopen(getenv('DB_HOST') ?: 'mysql', (int) (getenv('DB_PORT') ?: 3306)) ? 0 : 1);"; do
        sleep 2
    done
fi

if [ -z "${APP_KEY:-}" ]; then
    if [ "${APP_ENV:-production}" = "production" ]; then
        echo "APP_KEY must be set in the production environment." >&2
        exit 1
    fi

    if ! grep -Eq '^APP_KEY=base64:.{40,}$' .env; then
        php artisan key:generate --force
    fi

    unset APP_KEY
fi

php artisan config:clear

if [ "${DOCKER_RUN_MIGRATIONS:-true}" = "true" ]; then
    php artisan migrate --force
fi

php artisan storage:link --force

if [ "${DOCKER_OPTIMIZE:-false}" = "true" ]; then
    php artisan optimize
fi

exec "$@"

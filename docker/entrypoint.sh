#!/bin/sh
set -e

cd /var/www/html

if [ ! -f .env ] && [ -f .env.docker ]; then
    cp .env.docker .env
fi

if [ "${DB_CONNECTION:-}" = "mysql" ]; then
    echo "Waiting for MySQL at ${DB_HOST:-mysql}:${DB_PORT:-3306}..."
    until php -r "exit(@fsockopen(getenv('DB_HOST') ?: 'mysql', (int) (getenv('DB_PORT') ?: 3306)) ? 0 : 1);"; do
        sleep 2
    done
fi

if [ -z "${APP_KEY:-}" ] && ! grep -Eq '^APP_KEY=base64:.{40,}$' .env; then
    php artisan key:generate --force
fi

unset APP_KEY

php artisan config:clear

if [ "${DOCKER_RUN_MIGRATIONS:-true}" = "true" ]; then
    php artisan migrate --force
fi

exec "$@"

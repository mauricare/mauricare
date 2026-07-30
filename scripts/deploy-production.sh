#!/usr/bin/env bash

set -euo pipefail

project_dir="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
encrypted_env="$project_dir/deploy/.env.production.enc"

if ! command -v sops >/dev/null 2>&1; then
    echo "sops is required to deploy the encrypted production environment." >&2
    exit 1
fi

if ! command -v php >/dev/null 2>&1; then
    echo "PHP is required on the production server." >&2
    exit 1
fi

if ! command -v composer >/dev/null 2>&1; then
    echo "Composer is required on the production server." >&2
    exit 1
fi

if [[ ! -f "$encrypted_env" ]]; then
    echo "Encrypted production environment not found: $encrypted_env" >&2
    echo "Run scripts/encrypt-production-env.sh first." >&2
    exit 1
fi

if [[ -z "${SOPS_AGE_KEY_FILE:-}" && -z "${SOPS_AGE_KEY:-}" ]]; then
    echo "Set SOPS_AGE_KEY_FILE or SOPS_AGE_KEY before deploying." >&2
    exit 1
fi

umask 077
decrypted_env="$(mktemp "$project_dir/.env.production.XXXXXX")"
trap 'rm -f -- "$decrypted_env"' EXIT

sops \
    --decrypt \
    --input-type dotenv \
    --output-type dotenv \
    --output "$decrypted_env" \
    "$encrypted_env"

chmod 600 "$decrypted_env"
mv -f -- "$decrypted_env" "$project_dir/.env"
trap - EXIT

cd "$project_dir"

composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --classmap-authoritative \
    --optimize-autoloader

if command -v npm >/dev/null 2>&1; then
    npm ci
    npm run build
else
    echo "npm is unavailable; existing frontend assets were not rebuilt." >&2
fi

php artisan storage:link --no-interaction
php artisan migrate --force --no-interaction
php artisan optimize
php artisan queue:restart

echo "Mauricare production deployment completed."

#!/usr/bin/env bash

set -euo pipefail

project_dir="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
source_env="${1:-$project_dir/.env.production}"
encrypted_env="$project_dir/deploy/.env.production.enc"

if ! command -v sops >/dev/null 2>&1; then
    echo "sops is required to encrypt the production environment." >&2
    exit 1
fi

if [[ ! -f "$source_env" ]]; then
    echo "Production environment not found: $source_env" >&2
    echo "Create it from .env.production.example, then run this command again." >&2
    exit 1
fi

required_values=(
    APP_KEY
    DB_PASSWORD
    MYSQL_ROOT_PASSWORD
)

for key in "${required_values[@]}"; do
    value="$(sed -n "s/^${key}=//p" "$source_env" | tail -n 1)"
    if [[ -z "$value" || "$value" == *REPLACE_WITH* ]]; then
        echo "$key must contain a real production value before encryption." >&2
        exit 1
    fi
done

if ! grep -q '^APP_ENV=production$' "$source_env"; then
    echo "APP_ENV must be production." >&2
    exit 1
fi

if ! grep -q '^APP_URL=https://mauricare\.mu$' "$source_env"; then
    echo "APP_URL must be https://mauricare.mu." >&2
    exit 1
fi

if ! grep -q '^SESSION_SECURE_COOKIE=true$' "$source_env"; then
    echo "SESSION_SECURE_COOKIE must be true." >&2
    exit 1
fi

mkdir -p "$(dirname "$encrypted_env")"
sops \
    --config "$project_dir/.sops.yaml" \
    --encrypt \
    --input-type dotenv \
    --output-type dotenv \
    --output "$encrypted_env" \
    "$source_env"

echo "Encrypted production environment written to deploy/.env.production.enc"
echo "Only the encrypted file should be committed."

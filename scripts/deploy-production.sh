#!/usr/bin/env bash

set -euo pipefail

project_dir="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
encrypted_env="$project_dir/deploy/.env.production.enc"

if ! command -v sops >/dev/null 2>&1; then
    echo "sops is required to deploy the encrypted production environment." >&2
    exit 1
fi

if ! command -v docker >/dev/null 2>&1; then
    echo "Docker with the Compose plugin is required." >&2
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

sops exec-file \
    --no-fifo \
    --input-type dotenv \
    --output-type dotenv \
    --filename .env.production \
    "$encrypted_env" \
    "$project_dir/scripts/run-production-compose.sh {}"

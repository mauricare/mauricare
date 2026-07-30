#!/usr/bin/env bash

set -euo pipefail

project_dir="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
encrypted_env="$project_dir/deploy/.env.production.enc"

if [[ $# -eq 0 ]]; then
    echo "Usage: $0 <docker compose arguments...>" >&2
    exit 1
fi

if [[ ! -f "$encrypted_env" ]]; then
    echo "Encrypted production environment not found: $encrypted_env" >&2
    exit 1
fi

if [[ -z "${SOPS_AGE_KEY_FILE:-}" && -z "${SOPS_AGE_KEY:-}" ]]; then
    default_key_file="/etc/mauricare/age-key.txt"
    if [[ -f "$default_key_file" ]]; then
        export SOPS_AGE_KEY_FILE="$default_key_file"
    else
        echo "Set SOPS_AGE_KEY_FILE or SOPS_AGE_KEY." >&2
        exit 1
    fi
fi

umask 077
decrypted_env="$(mktemp)"
trap 'rm -f -- "$decrypted_env"' EXIT

sops \
    --decrypt \
    --input-type dotenv \
    --output-type dotenv \
    --output "$decrypted_env" \
    "$encrypted_env"

export ENV_FILE="$decrypted_env"
docker compose \
    --env-file "$decrypted_env" \
    -f "$project_dir/docker-compose.prod.yml" \
    "$@"

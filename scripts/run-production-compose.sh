#!/usr/bin/env bash

set -euo pipefail

if [[ $# -ne 1 ]]; then
    echo "Usage: $0 <decrypted-production-env>" >&2
    exit 1
fi

project_dir="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
decrypted_env="$1"

if [[ ! -f "$decrypted_env" ]]; then
    echo "Decrypted production environment is unavailable." >&2
    exit 1
fi

export ENV_FILE="$decrypted_env"

docker compose \
    --env-file "$decrypted_env" \
    -f "$project_dir/docker-compose.prod.yml" \
    config --quiet

docker compose \
    --env-file "$decrypted_env" \
    -f "$project_dir/docker-compose.prod.yml" \
    up -d --build --remove-orphans

docker compose \
    --env-file "$decrypted_env" \
    -f "$project_dir/docker-compose.prod.yml" \
    ps

#!/usr/bin/env bash

set -euo pipefail

project_dir="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
age_key_file="${SOPS_AGE_KEY_FILE:-/etc/mauricare/age-key.txt}"

if [[ ! -f "$age_key_file" ]]; then
    echo "Age private key not found: $age_key_file" >&2
    echo "Install it with mode 0600 before enabling automatic deployment." >&2
    exit 1
fi

if [[ ! -f "$project_dir/deploy/.env.production.enc" ]]; then
    echo "deploy/.env.production.enc is missing." >&2
    echo "Encrypt and commit the production environment first." >&2
    exit 1
fi

git -C "$project_dir" config core.hooksPath .githooks
chmod +x "$project_dir/.githooks/post-merge"

echo "Automatic production deployment enabled."
echo "Future git pull operations will deploy the updated application."

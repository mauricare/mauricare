#!/usr/bin/env bash

set -euo pipefail

project_dir="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
backup_dir="$project_dir/database/backups"
backup_file="$backup_dir/mauricare-$(date +%Y%m%d-%H%M%S).sql"

mkdir -p "$backup_dir"
umask 077

docker exec mariadb sh -c \
    'exec mariadb-dump -uroot -p"$MYSQL_ROOT_PASSWORD" --single-transaction --routines --triggers mauricare' \
    > "$backup_file"

if [[ ! -s "$backup_file" ]]; then
    echo "Database backup failed: $backup_file is empty." >&2
    exit 1
fi

echo "$backup_file"

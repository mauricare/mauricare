#!/usr/bin/env bash

set -euo pipefail

project_dir="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"

docker exec -i mariadb sh -c \
    'exec mariadb -uroot -p"$MYSQL_ROOT_PASSWORD"' \
    < "$project_dir/database/legacy_import_cleanup.sql"

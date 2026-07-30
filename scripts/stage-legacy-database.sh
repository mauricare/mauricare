#!/usr/bin/env bash

set -euo pipefail

if [[ $# -ne 1 ]]; then
    echo "Usage: $0 <legacy-sql-dump>" >&2
    exit 1
fi

legacy_dump="$(realpath "$1")"

if [[ ! -s "$legacy_dump" ]]; then
    echo "Legacy SQL dump is missing or empty: $legacy_dump" >&2
    exit 1
fi

docker exec mariadb sh -c \
    'exec mariadb -uroot -p"$MYSQL_ROOT_PASSWORD" -e "
        DROP DATABASE IF EXISTS mauricare_legacy_import;
        CREATE DATABASE mauricare_legacy_import CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
    "'

docker exec -i mariadb sh -c \
    'exec mariadb -uroot -p"$MYSQL_ROOT_PASSWORD" mauricare_legacy_import' \
    < "$legacy_dump"

docker exec mariadb sh -c \
    'exec mariadb -uroot -p"$MYSQL_ROOT_PASSWORD" -N -e "
        SELECT table_name, table_rows
        FROM information_schema.tables
        WHERE table_schema = '\''mauricare_legacy_import'\''
        ORDER BY table_name;
    "'

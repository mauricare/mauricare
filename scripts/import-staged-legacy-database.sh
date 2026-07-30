#!/usr/bin/env bash

set -euo pipefail

project_dir="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
import_sql="$project_dir/database/legacy_import.sql"

staged_users="$(docker exec mariadb sh -c \
    'exec mariadb -uroot -p"$MYSQL_ROOT_PASSWORD" -N -e "
        SELECT COUNT(*) FROM mauricare_legacy_import.users;
    "')"

if [[ "$staged_users" -eq 0 ]]; then
    echo "The staged legacy database is missing or contains no users." >&2
    exit 1
fi

existing_rows="$(docker exec mariadb sh -c \
    'exec mariadb -uroot -p"$MYSQL_ROOT_PASSWORD" -N -e "
        SELECT
            (SELECT COUNT(*) FROM mauricare.users)
          + (SELECT COUNT(*) FROM mauricare.roles)
          + (SELECT COUNT(*) FROM mauricare.user_profiles)
          + (SELECT COUNT(*) FROM mauricare.care_giver_profiles)
          + (SELECT COUNT(*) FROM mauricare.care_seeker_profiles)
          + (SELECT COUNT(*) FROM mauricare.agency_profiles)
          + (SELECT COUNT(*) FROM mauricare.documents)
          + (SELECT COUNT(*) FROM mauricare.model_has_roles);
    "')"

if [[ "$existing_rows" -ne 0 ]]; then
    echo "Import aborted: destination identity/profile tables are not empty." >&2
    exit 1
fi

"$project_dir/scripts/backup-local-database.sh" >/dev/null

docker exec -i mariadb sh -c \
    'exec mariadb -uroot -p"$MYSQL_ROOT_PASSWORD"' \
    < "$import_sql"

docker exec mariadb sh -c \
    'exec mariadb -uroot -p"$MYSQL_ROOT_PASSWORD" -N -e "
        SELECT '\''users'\'', COUNT(*) FROM mauricare.users
        UNION ALL SELECT '\''user_profiles'\'', COUNT(*) FROM mauricare.user_profiles
        UNION ALL SELECT '\''care_giver_profiles'\'', COUNT(*) FROM mauricare.care_giver_profiles
        UNION ALL SELECT '\''care_seeker_profiles'\'', COUNT(*) FROM mauricare.care_seeker_profiles
        UNION ALL SELECT '\''agency_profiles'\'', COUNT(*) FROM mauricare.agency_profiles
        UNION ALL SELECT '\''documents'\'', COUNT(*) FROM mauricare.documents
        UNION ALL SELECT '\''roles'\'', COUNT(*) FROM mauricare.roles
        UNION ALL SELECT '\''model_has_roles'\'', COUNT(*) FROM mauricare.model_has_roles;

        SELECT '\''orphaned_profile_users'\'', COUNT(*)
        FROM (
            SELECT user_id FROM mauricare.user_profiles
            UNION ALL SELECT user_id FROM mauricare.care_giver_profiles
            UNION ALL SELECT user_id FROM mauricare.care_seeker_profiles
            UNION ALL SELECT user_id FROM mauricare.agency_profiles
            UNION ALL SELECT user_id FROM mauricare.documents
        ) profiles
        LEFT JOIN mauricare.users ON users.id = profiles.user_id
        WHERE users.id IS NULL;

        SELECT '\''orphaned_role_assignments'\'', COUNT(*)
        FROM mauricare.model_has_roles assignments
        LEFT JOIN mauricare.users ON users.id = assignments.model_id
        LEFT JOIN mauricare.roles ON roles.id = assignments.role_id
        WHERE users.id IS NULL OR roles.id IS NULL;
    "'

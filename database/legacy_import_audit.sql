SELECT
    assignments.role_id,
    assignments.model_type,
    assignments.model_id,
    users.id IS NULL AS missing_user,
    roles.id IS NULL AS missing_role
FROM mauricare.model_has_roles assignments
LEFT JOIN mauricare.users ON users.id = assignments.model_id
LEFT JOIN mauricare.roles ON roles.id = assignments.role_id
WHERE users.id IS NULL OR roles.id IS NULL;

SELECT 'users', COUNT(*) FROM mauricare.users
UNION ALL SELECT 'user_profiles', COUNT(*) FROM mauricare.user_profiles
UNION ALL SELECT 'care_giver_profiles', COUNT(*) FROM mauricare.care_giver_profiles
UNION ALL SELECT 'active_care_giver_profiles', COUNT(*) FROM mauricare.care_giver_profiles WHERE is_active = TRUE
UNION ALL SELECT 'care_seeker_profiles', COUNT(*) FROM mauricare.care_seeker_profiles
UNION ALL SELECT 'active_care_seeker_profiles', COUNT(*) FROM mauricare.care_seeker_profiles WHERE is_active = TRUE
UNION ALL SELECT 'agency_profiles', COUNT(*) FROM mauricare.agency_profiles
UNION ALL SELECT 'documents', COUNT(*) FROM mauricare.documents
UNION ALL SELECT 'roles', COUNT(*) FROM mauricare.roles
UNION ALL SELECT 'model_has_roles', COUNT(*) FROM mauricare.model_has_roles;

SELECT roles.name, COUNT(*)
FROM mauricare.model_has_roles assignments
INNER JOIN mauricare.roles ON roles.id = assignments.role_id
GROUP BY roles.id, roles.name
ORDER BY roles.name;

SELECT 'duplicate_user_emails', COUNT(*)
FROM (
    SELECT email
    FROM mauricare.users
    GROUP BY email
    HAVING COUNT(*) > 1
) duplicate_emails
UNION ALL
SELECT 'users_without_password_hash', COUNT(*)
FROM mauricare.users
WHERE password IS NULL OR password = ''
UNION ALL
SELECT 'orphaned_profile_or_document_users', COUNT(*)
FROM (
    SELECT user_id FROM mauricare.user_profiles
    UNION ALL SELECT user_id FROM mauricare.care_giver_profiles
    UNION ALL SELECT user_id FROM mauricare.care_seeker_profiles
    UNION ALL SELECT user_id FROM mauricare.agency_profiles
    UNION ALL SELECT user_id FROM mauricare.documents
) profile_records
LEFT JOIN mauricare.users ON users.id = profile_records.user_id
WHERE users.id IS NULL;

START TRANSACTION;

INSERT INTO mauricare.roles (
    id, name, guard_name, created_at, updated_at
)
SELECT
    id, name, guard_name, created_at, updated_at
FROM mauricare_legacy_import.roles;

INSERT INTO mauricare.users (
    id, name, email, email_verified_at, password, remember_token, created_at, updated_at
)
SELECT
    id, name, email, email_verified_at, password, remember_token, created_at, updated_at
FROM mauricare_legacy_import.users;

INSERT INTO mauricare.user_profiles (
    id, user_id, first_name, last_name, age, phone, address, city, created_at, updated_at
)
SELECT
    id, user_id, first_name, last_name, age, phone, address, city, created_at, updated_at
FROM mauricare_legacy_import.user_profiles;

INSERT INTO mauricare.care_giver_profiles (
    id, user_id, type, is_active, created_at, updated_at
)
SELECT
    id, user_id, type, TRUE, created_at, updated_at
FROM mauricare_legacy_import.care_giver_profiles;

INSERT INTO mauricare.care_seeker_profiles (
    id, user_id, care_for, care_needs, preferred_contact_method,
    emergency_contact_name, emergency_contact_phone, mobility_level,
    medical_notes, is_active, created_at, updated_at
)
SELECT
    id, user_id, care_for, care_needs, preferred_contact_method,
    emergency_contact_name, emergency_contact_phone, mobility_level,
    medical_notes, TRUE, created_at, updated_at
FROM mauricare_legacy_import.care_seeker_profiles;

INSERT INTO mauricare.agency_profiles (
    id, user_id, agency_name, contact_person, agency_address,
    services_offered, created_at, updated_at
)
SELECT
    id, user_id, agency_name, contact_person, agency_address,
    services_offered, created_at, updated_at
FROM mauricare_legacy_import.agency_profiles;

INSERT INTO mauricare.documents (
    id, user_id, type, disk, path, original_name, mime_type, size,
    created_at, updated_at
)
SELECT
    id, user_id, type, disk, path, original_name, mime_type, size,
    created_at, updated_at
FROM mauricare_legacy_import.documents;

INSERT INTO mauricare.model_has_roles (
    role_id, model_type, model_id
)
SELECT
    assignments.role_id, assignments.model_type, assignments.model_id
FROM mauricare_legacy_import.model_has_roles assignments
INNER JOIN mauricare_legacy_import.users
    ON users.id = assignments.model_id
INNER JOIN mauricare_legacy_import.roles
    ON roles.id = assignments.role_id;

COMMIT;

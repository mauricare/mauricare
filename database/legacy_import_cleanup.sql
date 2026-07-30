START TRANSACTION;

USE mauricare;

DELETE assignments
FROM model_has_roles assignments
LEFT JOIN users
    ON users.id = assignments.model_id
LEFT JOIN roles
    ON roles.id = assignments.role_id
WHERE users.id IS NULL OR roles.id IS NULL;

COMMIT;

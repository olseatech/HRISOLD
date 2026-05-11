-- Appointments Permissions Seed
USE hris_db1;

INSERT INTO hris_permissions (permission_key, module, description)
SELECT 'appointments.view', 'appointments', 'View appointments'
WHERE NOT EXISTS (SELECT 1 FROM hris_permissions WHERE permission_key = 'appointments.view');

INSERT INTO hris_permissions (permission_key, module, description)
SELECT 'appointments.create', 'appointments', 'Create appointments'
WHERE NOT EXISTS (SELECT 1 FROM hris_permissions WHERE permission_key = 'appointments.create');

INSERT INTO hris_permissions (permission_key, module, description)
SELECT 'appointments.update', 'appointments', 'Update appointments'
WHERE NOT EXISTS (SELECT 1 FROM hris_permissions WHERE permission_key = 'appointments.update');

INSERT INTO hris_permissions (permission_key, module, description)
SELECT 'appointments.delete', 'appointments', 'Delete appointments'
WHERE NOT EXISTS (SELECT 1 FROM hris_permissions WHERE permission_key = 'appointments.delete');

-- Idempotent updates
UPDATE hris_permissions SET module = 'appointments', description = 'View appointments'   WHERE permission_key = 'appointments.view';
UPDATE hris_permissions SET module = 'appointments', description = 'Create appointments' WHERE permission_key = 'appointments.create';
UPDATE hris_permissions SET module = 'appointments', description = 'Update appointments' WHERE permission_key = 'appointments.update';
UPDATE hris_permissions SET module = 'appointments', description = 'Delete appointments' WHERE permission_key = 'appointments.delete';

-- Super Admin: all 4
INSERT INTO hris_role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM hris_roles r
CROSS JOIN hris_permissions p
WHERE r.role_name = 'Super Admin'
  AND p.permission_key IN ('appointments.view','appointments.create','appointments.update','appointments.delete')
  AND NOT EXISTS (SELECT 1 FROM hris_role_permissions rp WHERE rp.role_id = r.id AND rp.permission_id = p.id);

-- HR Admin: view + create + update
INSERT INTO hris_role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM hris_roles r
JOIN hris_permissions p ON p.permission_key IN ('appointments.view','appointments.create','appointments.update')
WHERE r.role_name = 'HR Admin'
  AND NOT EXISTS (SELECT 1 FROM hris_role_permissions rp WHERE rp.role_id = r.id AND rp.permission_id = p.id);

-- Manager: view only
INSERT INTO hris_role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM hris_roles r
JOIN hris_permissions p ON p.permission_key = 'appointments.view'
WHERE r.role_name = 'Manager'
  AND NOT EXISTS (SELECT 1 FROM hris_role_permissions rp WHERE rp.role_id = r.id AND rp.permission_id = p.id);

-- Employee: view only
INSERT INTO hris_role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM hris_roles r
JOIN hris_permissions p ON p.permission_key = 'appointments.view'
WHERE r.role_name = 'Employee'
  AND NOT EXISTS (SELECT 1 FROM hris_role_permissions rp WHERE rp.role_id = r.id AND rp.permission_id = p.id);

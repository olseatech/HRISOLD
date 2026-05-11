-- PDS Permissions Seed

INSERT INTO hris_permissions (permission_key, module, description)
SELECT 'pds.view', 'pds', 'View Personal Data Sheet records'
WHERE NOT EXISTS (SELECT 1 FROM hris_permissions WHERE permission_key = 'pds.view');

INSERT INTO hris_permissions (permission_key, module, description)
SELECT 'pds.create', 'pds', 'Create Personal Data Sheet records'
WHERE NOT EXISTS (SELECT 1 FROM hris_permissions WHERE permission_key = 'pds.create');

INSERT INTO hris_permissions (permission_key, module, description)
SELECT 'pds.update', 'pds', 'Update Personal Data Sheet records'
WHERE NOT EXISTS (SELECT 1 FROM hris_permissions WHERE permission_key = 'pds.update');

INSERT INTO hris_permissions (permission_key, module, description)
SELECT 'pds.delete', 'pds', 'Delete Personal Data Sheet records'
WHERE NOT EXISTS (SELECT 1 FROM hris_permissions WHERE permission_key = 'pds.delete');

-- Update descriptions (idempotent)
UPDATE hris_permissions SET module = 'pds', description = 'View Personal Data Sheet records' WHERE permission_key = 'pds.view';
UPDATE hris_permissions SET module = 'pds', description = 'Create Personal Data Sheet records' WHERE permission_key = 'pds.create';
UPDATE hris_permissions SET module = 'pds', description = 'Update Personal Data Sheet records' WHERE permission_key = 'pds.update';
UPDATE hris_permissions SET module = 'pds', description = 'Delete Personal Data Sheet records' WHERE permission_key = 'pds.delete';

-- Grant all 4 PDS permissions to Super Admin
INSERT INTO hris_role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM hris_roles r
CROSS JOIN hris_permissions p
WHERE r.role_name = 'Super Admin'
  AND p.permission_key IN ('pds.view','pds.create','pds.update','pds.delete')
  AND NOT EXISTS (
      SELECT 1 FROM hris_role_permissions rp
      WHERE rp.role_id = r.id AND rp.permission_id = p.id
  );

-- Grant view/create/update to HR Admin
INSERT INTO hris_role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM hris_roles r
JOIN hris_permissions p ON p.permission_key IN ('pds.view','pds.create','pds.update')
WHERE r.role_name = 'HR Admin'
  AND NOT EXISTS (
      SELECT 1 FROM hris_role_permissions rp
      WHERE rp.role_id = r.id AND rp.permission_id = p.id
  );

-- Grant view only to Manager
INSERT INTO hris_role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM hris_roles r
JOIN hris_permissions p ON p.permission_key = 'pds.view'
WHERE r.role_name = 'Manager'
  AND NOT EXISTS (
      SELECT 1 FROM hris_role_permissions rp
      WHERE rp.role_id = r.id AND rp.permission_id = p.id
  );

-- Grant view only to Employee
INSERT INTO hris_role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM hris_roles r
JOIN hris_permissions p ON p.permission_key = 'pds.view'
WHERE r.role_name = 'Employee'
  AND NOT EXISTS (
      SELECT 1 FROM hris_role_permissions rp
      WHERE rp.role_id = r.id AND rp.permission_id = p.id
  );

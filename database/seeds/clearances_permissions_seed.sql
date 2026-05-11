-- Clearance Permissions Seed
USE hris_db1;

INSERT INTO hris_permissions (permission_key, module, description)
SELECT 'clearances.view', 'clearances', 'View clearance requests'
WHERE NOT EXISTS (SELECT 1 FROM hris_permissions WHERE permission_key = 'clearances.view');

INSERT INTO hris_permissions (permission_key, module, description)
SELECT 'clearances.create', 'clearances', 'Create clearance requests'
WHERE NOT EXISTS (SELECT 1 FROM hris_permissions WHERE permission_key = 'clearances.create');

INSERT INTO hris_permissions (permission_key, module, description)
SELECT 'clearances.update', 'clearances', 'Update clearance requests'
WHERE NOT EXISTS (SELECT 1 FROM hris_permissions WHERE permission_key = 'clearances.update');

INSERT INTO hris_permissions (permission_key, module, description)
SELECT 'clearances.delete', 'clearances', 'Delete clearance requests'
WHERE NOT EXISTS (SELECT 1 FROM hris_permissions WHERE permission_key = 'clearances.delete');

INSERT INTO hris_permissions (permission_key, module, description)
SELECT 'clearances.approve', 'clearances', 'Approve or reject clearance requests'
WHERE NOT EXISTS (SELECT 1 FROM hris_permissions WHERE permission_key = 'clearances.approve');

-- Idempotent updates
UPDATE hris_permissions SET module = 'clearances', description = 'View clearance requests'             WHERE permission_key = 'clearances.view';
UPDATE hris_permissions SET module = 'clearances', description = 'Create clearance requests'           WHERE permission_key = 'clearances.create';
UPDATE hris_permissions SET module = 'clearances', description = 'Update clearance requests'           WHERE permission_key = 'clearances.update';
UPDATE hris_permissions SET module = 'clearances', description = 'Delete clearance requests'           WHERE permission_key = 'clearances.delete';
UPDATE hris_permissions SET module = 'clearances', description = 'Approve or reject clearance requests' WHERE permission_key = 'clearances.approve';

-- Super Admin: all 5
INSERT INTO hris_role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM hris_roles r
CROSS JOIN hris_permissions p
WHERE r.role_name = 'Super Admin'
  AND p.permission_key IN ('clearances.view','clearances.create','clearances.update','clearances.delete','clearances.approve')
  AND NOT EXISTS (SELECT 1 FROM hris_role_permissions rp WHERE rp.role_id = r.id AND rp.permission_id = p.id);

-- HR Admin: view + create + update + approve
INSERT INTO hris_role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM hris_roles r
JOIN hris_permissions p ON p.permission_key IN ('clearances.view','clearances.create','clearances.update','clearances.approve')
WHERE r.role_name = 'HR Admin'
  AND NOT EXISTS (SELECT 1 FROM hris_role_permissions rp WHERE rp.role_id = r.id AND rp.permission_id = p.id);

-- Manager: view + approve
INSERT INTO hris_role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM hris_roles r
JOIN hris_permissions p ON p.permission_key IN ('clearances.view','clearances.approve')
WHERE r.role_name = 'Manager'
  AND NOT EXISTS (SELECT 1 FROM hris_role_permissions rp WHERE rp.role_id = r.id AND rp.permission_id = p.id);

-- Employee: view only
INSERT INTO hris_role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM hris_roles r
JOIN hris_permissions p ON p.permission_key = 'clearances.view'
WHERE r.role_name = 'Employee'
  AND NOT EXISTS (SELECT 1 FROM hris_role_permissions rp WHERE rp.role_id = r.id AND rp.permission_id = p.id);

-- Service Records Permissions Seed

INSERT INTO hris_permissions (permission_key, module, description)
SELECT 'service_records.view', 'service_records', 'View service records'
WHERE NOT EXISTS (SELECT 1 FROM hris_permissions WHERE permission_key = 'service_records.view');

INSERT INTO hris_permissions (permission_key, module, description)
SELECT 'service_records.create', 'service_records', 'Create service records'
WHERE NOT EXISTS (SELECT 1 FROM hris_permissions WHERE permission_key = 'service_records.create');

INSERT INTO hris_permissions (permission_key, module, description)
SELECT 'service_records.update', 'service_records', 'Update service records'
WHERE NOT EXISTS (SELECT 1 FROM hris_permissions WHERE permission_key = 'service_records.update');

INSERT INTO hris_permissions (permission_key, module, description)
SELECT 'service_records.delete', 'service_records', 'Delete service records'
WHERE NOT EXISTS (SELECT 1 FROM hris_permissions WHERE permission_key = 'service_records.delete');

-- Idempotent updates
UPDATE hris_permissions SET module = 'service_records', description = 'View service records'   WHERE permission_key = 'service_records.view';
UPDATE hris_permissions SET module = 'service_records', description = 'Create service records' WHERE permission_key = 'service_records.create';
UPDATE hris_permissions SET module = 'service_records', description = 'Update service records' WHERE permission_key = 'service_records.update';
UPDATE hris_permissions SET module = 'service_records', description = 'Delete service records' WHERE permission_key = 'service_records.delete';

-- Super Admin: all 4
INSERT INTO hris_role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM hris_roles r
CROSS JOIN hris_permissions p
WHERE r.role_name = 'Super Admin'
  AND p.permission_key IN ('service_records.view','service_records.create','service_records.update','service_records.delete')
  AND NOT EXISTS (SELECT 1 FROM hris_role_permissions rp WHERE rp.role_id = r.id AND rp.permission_id = p.id);

-- HR Admin: view + create + update
INSERT INTO hris_role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM hris_roles r
JOIN hris_permissions p ON p.permission_key IN ('service_records.view','service_records.create','service_records.update')
WHERE r.role_name = 'HR Admin'
  AND NOT EXISTS (SELECT 1 FROM hris_role_permissions rp WHERE rp.role_id = r.id AND rp.permission_id = p.id);

-- Manager: view only
INSERT INTO hris_role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM hris_roles r
JOIN hris_permissions p ON p.permission_key = 'service_records.view'
WHERE r.role_name = 'Manager'
  AND NOT EXISTS (SELECT 1 FROM hris_role_permissions rp WHERE rp.role_id = r.id AND rp.permission_id = p.id);

-- Employee: view only
INSERT INTO hris_role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM hris_roles r
JOIN hris_permissions p ON p.permission_key = 'service_records.view'
WHERE r.role_name = 'Employee'
  AND NOT EXISTS (SELECT 1 FROM hris_role_permissions rp WHERE rp.role_id = r.id AND rp.permission_id = p.id);

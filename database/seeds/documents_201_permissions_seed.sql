-- 201 Documents Permissions Seed
USE hris_db1;

INSERT INTO hris_permissions (permission_key, module, description)
SELECT 'documents.view', 'documents', 'View 201 documents'
WHERE NOT EXISTS (SELECT 1 FROM hris_permissions WHERE permission_key = 'documents.view');

INSERT INTO hris_permissions (permission_key, module, description)
SELECT 'documents.manage', 'documents', 'Upload and manage 201 documents'
WHERE NOT EXISTS (SELECT 1 FROM hris_permissions WHERE permission_key = 'documents.manage');

INSERT INTO hris_permissions (permission_key, module, description)
SELECT 'documents.delete', 'documents', 'Delete 201 documents'
WHERE NOT EXISTS (SELECT 1 FROM hris_permissions WHERE permission_key = 'documents.delete');

-- Idempotent updates
UPDATE hris_permissions SET module = 'documents', description = 'View 201 documents'               WHERE permission_key = 'documents.view';
UPDATE hris_permissions SET module = 'documents', description = 'Upload and manage 201 documents'  WHERE permission_key = 'documents.manage';
UPDATE hris_permissions SET module = 'documents', description = 'Delete 201 documents'             WHERE permission_key = 'documents.delete';

-- Super Admin: all 3
INSERT INTO hris_role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM hris_roles r
CROSS JOIN hris_permissions p
WHERE r.role_name = 'Super Admin'
  AND p.permission_key IN ('documents.view','documents.manage','documents.delete')
  AND NOT EXISTS (SELECT 1 FROM hris_role_permissions rp WHERE rp.role_id = r.id AND rp.permission_id = p.id);

-- HR Admin: view + manage + delete
INSERT INTO hris_role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM hris_roles r
JOIN hris_permissions p ON p.permission_key IN ('documents.view','documents.manage','documents.delete')
WHERE r.role_name = 'HR Admin'
  AND NOT EXISTS (SELECT 1 FROM hris_role_permissions rp WHERE rp.role_id = r.id AND rp.permission_id = p.id);

-- Manager: view only
INSERT INTO hris_role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM hris_roles r
JOIN hris_permissions p ON p.permission_key = 'documents.view'
WHERE r.role_name = 'Manager'
  AND NOT EXISTS (SELECT 1 FROM hris_role_permissions rp WHERE rp.role_id = r.id AND rp.permission_id = p.id);

-- Employee: view only
INSERT INTO hris_role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM hris_roles r
JOIN hris_permissions p ON p.permission_key = 'documents.view'
WHERE r.role_name = 'Employee'
  AND NOT EXISTS (SELECT 1 FROM hris_role_permissions rp WHERE rp.role_id = r.id AND rp.permission_id = p.id);

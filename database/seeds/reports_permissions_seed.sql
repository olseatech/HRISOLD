-- Reports permission seed
USE hris_db1;

INSERT INTO hris_permissions (permission_name, module, description)
SELECT 'reports.view', 'reports', 'View and print reports'
WHERE NOT EXISTS (SELECT 1 FROM hris_permissions WHERE permission_name = 'reports.view');

-- Grant to Super Admin
INSERT INTO hris_role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM hris_roles r
CROSS JOIN hris_permissions p
WHERE r.role_name = 'Super Admin'
  AND p.permission_name = 'reports.view'
  AND NOT EXISTS (
    SELECT 1 FROM hris_role_permissions rp2
    WHERE rp2.role_id = r.id AND rp2.permission_id = p.id
  );

-- Grant to HR Admin
INSERT INTO hris_role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM hris_roles r
CROSS JOIN hris_permissions p
WHERE r.role_name = 'HR Admin'
  AND p.permission_name = 'reports.view'
  AND NOT EXISTS (
    SELECT 1 FROM hris_role_permissions rp2
    WHERE rp2.role_id = r.id AND rp2.permission_id = p.id
  );

-- Grant to Manager
INSERT INTO hris_role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM hris_roles r
CROSS JOIN hris_permissions p
WHERE r.role_name = 'Manager'
  AND p.permission_name = 'reports.view'
  AND NOT EXISTS (
    SELECT 1 FROM hris_role_permissions rp2
    WHERE rp2.role_id = r.id AND rp2.permission_id = p.id
  );

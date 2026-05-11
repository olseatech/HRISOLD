-- Leave Enhancement Permissions Seed
USE hris_db1;

-- ── Insert new permissions ─────────────────────────────────────────────────────

INSERT INTO hris_permissions (permission_key, module, description)
SELECT 'holidays.manage', 'holidays', 'Manage public holidays'
WHERE NOT EXISTS (SELECT 1 FROM hris_permissions WHERE permission_key = 'holidays.manage');

INSERT INTO hris_permissions (permission_key, module, description)
SELECT 'leave.my_leave', 'leave', 'View own leave requests (self-service)'
WHERE NOT EXISTS (SELECT 1 FROM hris_permissions WHERE permission_key = 'leave.my_leave');

-- Idempotent updates
UPDATE hris_permissions SET module = 'holidays', description = 'Manage public holidays'                    WHERE permission_key = 'holidays.manage';
UPDATE hris_permissions SET module = 'leave',    description = 'View own leave requests (self-service)'   WHERE permission_key = 'leave.my_leave';

-- ── holidays.manage: Super Admin + HR Admin ───────────────────────────────────

INSERT INTO hris_role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM hris_roles r
CROSS JOIN hris_permissions p
WHERE r.role_name IN ('Super Admin', 'HR Admin')
  AND p.permission_key = 'holidays.manage'
  AND NOT EXISTS (SELECT 1 FROM hris_role_permissions rp WHERE rp.role_id = r.id AND rp.permission_id = p.id);

-- ── leave.my_leave: all roles ─────────────────────────────────────────────────

INSERT INTO hris_role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM hris_roles r
CROSS JOIN hris_permissions p
WHERE p.permission_key = 'leave.my_leave'
  AND NOT EXISTS (SELECT 1 FROM hris_role_permissions rp WHERE rp.role_id = r.id AND rp.permission_id = p.id);

-- ── leave.request: grant to HR Admin and Manager (they are also employees) ────

INSERT INTO hris_role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM hris_roles r
JOIN hris_permissions p ON p.permission_key = 'leave.request'
WHERE r.role_name IN ('HR Admin', 'Manager')
  AND NOT EXISTS (SELECT 1 FROM hris_role_permissions rp WHERE rp.role_id = r.id AND rp.permission_id = p.id);

-- HRIS v1 Roles, Permissions, and Role-Permission Mapping Seed

INSERT INTO hris_roles (role_name, description, is_active)
SELECT 'Super Admin', 'Full system access', 1
WHERE NOT EXISTS (
    SELECT 1 FROM hris_roles WHERE role_name = 'Super Admin'
);

INSERT INTO hris_roles (role_name, description, is_active)
SELECT 'HR Admin', 'HR module administration', 0
WHERE NOT EXISTS (
    SELECT 1 FROM hris_roles WHERE role_name = 'HR Admin'
);

INSERT INTO hris_roles (role_name, description, is_active)
SELECT 'Manager', 'Department and team approvals', 0
WHERE NOT EXISTS (
    SELECT 1 FROM hris_roles WHERE role_name = 'Manager'
);

INSERT INTO hris_roles (role_name, description, is_active)
SELECT 'Employee', 'Self-service access', 0
WHERE NOT EXISTS (
    SELECT 1 FROM hris_roles WHERE role_name = 'Employee'
);

UPDATE hris_roles SET description = 'Full system access', is_active = 1 WHERE role_name = 'Super Admin';
UPDATE hris_roles SET description = 'HR module administration', is_active = 0 WHERE role_name = 'HR Admin';
UPDATE hris_roles SET description = 'Department and team approvals', is_active = 0 WHERE role_name = 'Manager';
UPDATE hris_roles SET description = 'Self-service access', is_active = 0 WHERE role_name = 'Employee';

INSERT INTO hris_permissions (permission_key, module, description)
SELECT 'dashboard.view', 'dashboard', 'View dashboard and widgets'
WHERE NOT EXISTS (
    SELECT 1 FROM hris_permissions WHERE permission_key = 'dashboard.view'
);

INSERT INTO hris_permissions (permission_key, module, description)
SELECT 'employees.view', 'employees', 'View employee records'
WHERE NOT EXISTS (
    SELECT 1 FROM hris_permissions WHERE permission_key = 'employees.view'
);

INSERT INTO hris_permissions (permission_key, module, description)
SELECT 'employees.create', 'employees', 'Create employee records'
WHERE NOT EXISTS (
    SELECT 1 FROM hris_permissions WHERE permission_key = 'employees.create'
);

INSERT INTO hris_permissions (permission_key, module, description)
SELECT 'employees.update', 'employees', 'Update employee records'
WHERE NOT EXISTS (
    SELECT 1 FROM hris_permissions WHERE permission_key = 'employees.update'
);

INSERT INTO hris_permissions (permission_key, module, description)
SELECT 'employees.delete', 'employees', 'Delete employee records'
WHERE NOT EXISTS (
    SELECT 1 FROM hris_permissions WHERE permission_key = 'employees.delete'
);

INSERT INTO hris_permissions (permission_key, module, description)
SELECT 'attendance.view', 'attendance', 'View attendance records'
WHERE NOT EXISTS (
    SELECT 1 FROM hris_permissions WHERE permission_key = 'attendance.view'
);

INSERT INTO hris_permissions (permission_key, module, description)
SELECT 'attendance.manage', 'attendance', 'Manage attendance records'
WHERE NOT EXISTS (
    SELECT 1 FROM hris_permissions WHERE permission_key = 'attendance.manage'
);

INSERT INTO hris_permissions (permission_key, module, description)
SELECT 'leave.view', 'leave', 'View leave requests and balances'
WHERE NOT EXISTS (
    SELECT 1 FROM hris_permissions WHERE permission_key = 'leave.view'
);

INSERT INTO hris_permissions (permission_key, module, description)
SELECT 'leave.request', 'leave', 'Request leave'
WHERE NOT EXISTS (
    SELECT 1 FROM hris_permissions WHERE permission_key = 'leave.request'
);

INSERT INTO hris_permissions (permission_key, module, description)
SELECT 'leave.approve', 'leave', 'Approve or reject leave requests'
WHERE NOT EXISTS (
    SELECT 1 FROM hris_permissions WHERE permission_key = 'leave.approve'
);

INSERT INTO hris_permissions (permission_key, module, description)
SELECT 'payroll.view', 'payroll', 'View payroll structures and records'
WHERE NOT EXISTS (
    SELECT 1 FROM hris_permissions WHERE permission_key = 'payroll.view'
);

INSERT INTO hris_permissions (permission_key, module, description)
SELECT 'settings.manage', 'settings', 'Manage global system settings'
WHERE NOT EXISTS (
    SELECT 1 FROM hris_permissions WHERE permission_key = 'settings.manage'
);

INSERT INTO hris_permissions (permission_key, module, description)
SELECT 'billing.view', 'billing', 'View billing status and plan catalog'
WHERE NOT EXISTS (
    SELECT 1 FROM hris_permissions WHERE permission_key = 'billing.view'
);

INSERT INTO hris_permissions (permission_key, module, description)
SELECT 'billing.manage', 'billing', 'Manage subscriptions and billing actions'
WHERE NOT EXISTS (
    SELECT 1 FROM hris_permissions WHERE permission_key = 'billing.manage'
);

INSERT INTO hris_permissions (permission_key, module, description)
SELECT 'users.manage', 'auth', 'Manage user accounts and access'
WHERE NOT EXISTS (
    SELECT 1 FROM hris_permissions WHERE permission_key = 'users.manage'
);

UPDATE hris_permissions SET module = 'dashboard', description = 'View dashboard and widgets' WHERE permission_key = 'dashboard.view';
UPDATE hris_permissions SET module = 'employees', description = 'View employee records' WHERE permission_key = 'employees.view';
UPDATE hris_permissions SET module = 'employees', description = 'Create employee records' WHERE permission_key = 'employees.create';
UPDATE hris_permissions SET module = 'employees', description = 'Update employee records' WHERE permission_key = 'employees.update';
UPDATE hris_permissions SET module = 'employees', description = 'Delete employee records' WHERE permission_key = 'employees.delete';
UPDATE hris_permissions SET module = 'attendance', description = 'View attendance records' WHERE permission_key = 'attendance.view';
UPDATE hris_permissions SET module = 'attendance', description = 'Manage attendance records' WHERE permission_key = 'attendance.manage';
UPDATE hris_permissions SET module = 'leave', description = 'View leave requests and balances' WHERE permission_key = 'leave.view';
UPDATE hris_permissions SET module = 'leave', description = 'Request leave' WHERE permission_key = 'leave.request';
UPDATE hris_permissions SET module = 'leave', description = 'Approve or reject leave requests' WHERE permission_key = 'leave.approve';
UPDATE hris_permissions SET module = 'payroll', description = 'View payroll structures and records' WHERE permission_key = 'payroll.view';
UPDATE hris_permissions SET module = 'settings', description = 'Manage global system settings' WHERE permission_key = 'settings.manage';
UPDATE hris_permissions SET module = 'billing', description = 'View billing status and plan catalog' WHERE permission_key = 'billing.view';
UPDATE hris_permissions SET module = 'billing', description = 'Manage subscriptions and billing actions' WHERE permission_key = 'billing.manage';
UPDATE hris_permissions SET module = 'auth', description = 'Manage user accounts and access' WHERE permission_key = 'users.manage';

INSERT INTO hris_role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM hris_roles r
CROSS JOIN hris_permissions p
WHERE r.role_name = 'Super Admin'
  AND NOT EXISTS (
      SELECT 1
      FROM hris_role_permissions rp
      WHERE rp.role_id = r.id AND rp.permission_id = p.id
  );

INSERT INTO hris_role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM hris_roles r
JOIN hris_permissions p ON p.permission_key IN (
    'dashboard.view',
    'employees.view', 'employees.create', 'employees.update',
    'attendance.view', 'attendance.manage',
    'leave.view', 'leave.approve',
    'payroll.view',
    'billing.view', 'billing.manage'
)
WHERE r.role_name = 'HR Admin'
  AND NOT EXISTS (
      SELECT 1
      FROM hris_role_permissions rp
      WHERE rp.role_id = r.id AND rp.permission_id = p.id
  );

INSERT INTO hris_role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM hris_roles r
JOIN hris_permissions p ON p.permission_key IN (
    'dashboard.view',
    'employees.view',
    'attendance.view',
    'leave.view', 'leave.approve'
)
WHERE r.role_name = 'Manager'
  AND NOT EXISTS (
      SELECT 1
      FROM hris_role_permissions rp
      WHERE rp.role_id = r.id AND rp.permission_id = p.id
  );

INSERT INTO hris_role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM hris_roles r
JOIN hris_permissions p ON p.permission_key IN (
    'dashboard.view',
    'leave.view', 'leave.request',
    'attendance.view'
)
WHERE r.role_name = 'Employee'
  AND NOT EXISTS (
      SELECT 1
      FROM hris_role_permissions rp
      WHERE rp.role_id = r.id AND rp.permission_id = p.id
  );

-- HRIS account recovery seed
-- Creates/updates Super Admin credentials and keeps other actor records soft-disabled.
-- All seeded passwords use: Admin@123

SET NAMES utf8mb4;
START TRANSACTION;

SET @pwd_hash = '$2y$12$VevcIIsG56vvKpem0eRI1ORK.W3EuA/WD29H3qrEyqVgiwU3vFI6e';

-- Ensure core roles exist
INSERT INTO hris_roles (role_name, description, is_active)
VALUES
    ('Super Admin', 'Full system access', 1),
    ('HR Admin', 'HR module administration', 0),
    ('Manager', 'Department and team approvals', 0),
    ('Employee', 'Self-service access', 0)
ON DUPLICATE KEY UPDATE
    description = VALUES(description),
    is_active = CASE WHEN role_name = 'Super Admin' THEN 1 ELSE 0 END;

-- Ensure permission catalog exists
INSERT INTO hris_permissions (permission_key, module, description)
VALUES
    ('dashboard.view', 'dashboard', 'View dashboard and widgets'),
    ('employees.view', 'employees', 'View employee records'),
    ('employees.create', 'employees', 'Create employee records'),
    ('employees.update', 'employees', 'Update employee records'),
    ('employees.delete', 'employees', 'Delete employee records'),
    ('attendance.view', 'attendance', 'View attendance records'),
    ('attendance.manage', 'attendance', 'Manage attendance records'),
    ('leave.view', 'leave', 'View leave requests and balances'),
    ('leave.request', 'leave', 'Request leave'),
    ('leave.approve', 'leave', 'Approve or reject leave requests'),
    ('payroll.view', 'payroll', 'View payroll structures and records'),
    ('settings.manage', 'settings', 'Manage global system settings'),
    ('billing.view', 'billing', 'View billing status and plan catalog'),
    ('billing.manage', 'billing', 'Manage subscriptions and billing actions'),
    ('users.manage', 'auth', 'Manage user accounts and access')
ON DUPLICATE KEY UPDATE
    module = VALUES(module),
    description = VALUES(description);

-- Ensure role-permission mapping exists
SET @super_admin_role_id = (SELECT id FROM hris_roles WHERE role_name = 'Super Admin' LIMIT 1);
SET @hr_admin_role_id = (SELECT id FROM hris_roles WHERE role_name = 'HR Admin' LIMIT 1);
SET @manager_role_id = (SELECT id FROM hris_roles WHERE role_name = 'Manager' LIMIT 1);
SET @employee_role_id = (SELECT id FROM hris_roles WHERE role_name = 'Employee' LIMIT 1);

INSERT INTO hris_role_permissions (role_id, permission_id)
SELECT @super_admin_role_id, p.id
FROM hris_permissions p
WHERE NOT EXISTS (
    SELECT 1
    FROM hris_role_permissions rp
    WHERE rp.role_id = @super_admin_role_id
      AND rp.permission_id = p.id
);

INSERT INTO hris_role_permissions (role_id, permission_id)
SELECT @hr_admin_role_id, p.id
FROM hris_permissions p
WHERE p.permission_key IN (
    'dashboard.view',
    'employees.view', 'employees.create', 'employees.update',
    'attendance.view', 'attendance.manage',
    'leave.view', 'leave.approve',
    'payroll.view',
    'billing.view'
)
AND NOT EXISTS (
    SELECT 1
    FROM hris_role_permissions rp
    WHERE rp.role_id = @hr_admin_role_id
      AND rp.permission_id = p.id
);

INSERT INTO hris_role_permissions (role_id, permission_id)
SELECT @manager_role_id, p.id
FROM hris_permissions p
WHERE p.permission_key IN (
    'dashboard.view',
    'employees.view',
    'attendance.view',
    'leave.view', 'leave.approve'
)
AND NOT EXISTS (
    SELECT 1
    FROM hris_role_permissions rp
    WHERE rp.role_id = @manager_role_id
      AND rp.permission_id = p.id
);

INSERT INTO hris_role_permissions (role_id, permission_id)
SELECT @employee_role_id, p.id
FROM hris_permissions p
WHERE p.permission_key IN (
    'dashboard.view',
    'leave.view', 'leave.request',
    'attendance.view'
)
AND NOT EXISTS (
    SELECT 1
    FROM hris_role_permissions rp
    WHERE rp.role_id = @employee_role_id
      AND rp.permission_id = p.id
);

-- Ensure company and org structure exists
INSERT INTO hris_companies (company_name, address, phone, email, website)
SELECT 'Acme HR Solutions', '123 Main Street, Metro City', '+63 900 000 0000', 'hello@acmehr.local', 'https://acmehr.local'
WHERE NOT EXISTS (
    SELECT 1 FROM hris_companies WHERE company_name = 'Acme HR Solutions'
);

SET @company_id = COALESCE(
    (SELECT id FROM hris_companies WHERE company_name = 'Acme HR Solutions' ORDER BY id ASC LIMIT 1),
    (SELECT MIN(id) FROM hris_companies)
);

INSERT INTO hris_branches (company_id, branch_name, address, phone, is_active)
SELECT @company_id, 'Head Office', '123 Main Street, Metro City', '+63 900 000 0001', 1
WHERE NOT EXISTS (
    SELECT 1 FROM hris_branches WHERE company_id = @company_id AND branch_name = 'Head Office'
);

SET @branch_id = (
    SELECT id
    FROM hris_branches
    WHERE company_id = @company_id AND branch_name = 'Head Office'
    ORDER BY id ASC
    LIMIT 1
);

INSERT INTO hris_departments (branch_id, department_name, is_active)
SELECT @branch_id, 'Human Resources', 1
WHERE NOT EXISTS (
    SELECT 1 FROM hris_departments WHERE branch_id = @branch_id AND department_name = 'Human Resources'
);

INSERT INTO hris_departments (branch_id, department_name, is_active)
SELECT @branch_id, 'Operations', 1
WHERE NOT EXISTS (
    SELECT 1 FROM hris_departments WHERE branch_id = @branch_id AND department_name = 'Operations'
);

SET @hr_dept_id = (
    SELECT id
    FROM hris_departments
    WHERE branch_id = @branch_id AND department_name = 'Human Resources'
    ORDER BY id ASC
    LIMIT 1
);

SET @ops_dept_id = COALESCE(
    (
        SELECT id
        FROM hris_departments
        WHERE branch_id = @branch_id AND department_name = 'Operations'
        ORDER BY id ASC
        LIMIT 1
    ),
    @hr_dept_id
);

-- Ensure designations exist
INSERT INTO hris_designations (designation_name, description)
VALUES
    ('System Administrator', 'Maintains HRIS platform'),
    ('HR Officer', 'Handles HR operations'),
    ('Department Manager', 'Manages department operations and approvals'),
    ('Staff Employee', 'General employee role for self-service modules')
ON DUPLICATE KEY UPDATE
    description = VALUES(description);

SET @sysadmin_designation_id = (SELECT id FROM hris_designations WHERE designation_name = 'System Administrator' LIMIT 1);
SET @hrofficer_designation_id = (SELECT id FROM hris_designations WHERE designation_name = 'HR Officer' LIMIT 1);
SET @manager_designation_id = (SELECT id FROM hris_designations WHERE designation_name = 'Department Manager' LIMIT 1);
SET @employee_designation_id = (SELECT id FROM hris_designations WHERE designation_name = 'Staff Employee' LIMIT 1);

-- Ensure employees for accounts exist
INSERT INTO hris_employees (
    employee_code, first_name, last_name, gender, date_of_birth,
    department_id, designation_id, employment_type, employment_status,
    date_hired, email
)
VALUES (
    'EMP-0001', 'System', 'Administrator', 'Other', '1990-01-01',
    @hr_dept_id, @sysadmin_designation_id, 'Full-Time', 'Active',
    CURRENT_DATE, 'admin@hris.local'
)
ON DUPLICATE KEY UPDATE
    first_name = VALUES(first_name),
    last_name = VALUES(last_name),
    department_id = VALUES(department_id),
    designation_id = VALUES(designation_id),
    employment_status = 'Active',
    email = VALUES(email);

INSERT INTO hris_employees (
    employee_code, first_name, last_name, gender, date_of_birth,
    department_id, designation_id, employment_type, employment_status,
    date_hired, email
)
VALUES (
    'EMP-0004', 'Hanna', 'Hradmin', 'Female', '1993-04-08',
    @hr_dept_id, @hrofficer_designation_id, 'Full-Time', 'Active',
    CURRENT_DATE, 'hradmin@hris.local'
)
ON DUPLICATE KEY UPDATE
    first_name = VALUES(first_name),
    last_name = VALUES(last_name),
    department_id = VALUES(department_id),
    designation_id = VALUES(designation_id),
    employment_status = 'Active',
    email = VALUES(email);

INSERT INTO hris_employees (
    employee_code, first_name, last_name, gender, date_of_birth,
    department_id, designation_id, employment_type, employment_status,
    date_hired, email
)
VALUES (
    'EMP-0002', 'Mark', 'Manager', 'Male', '1992-05-12',
    @ops_dept_id, @manager_designation_id, 'Full-Time', 'Active',
    CURRENT_DATE, 'manager@hris.local'
)
ON DUPLICATE KEY UPDATE
    first_name = VALUES(first_name),
    last_name = VALUES(last_name),
    department_id = VALUES(department_id),
    designation_id = VALUES(designation_id),
    employment_status = 'Active',
    email = VALUES(email);

SET @manager_employee_id = (SELECT id FROM hris_employees WHERE employee_code = 'EMP-0002' LIMIT 1);

INSERT INTO hris_employees (
    employee_code, first_name, last_name, gender, date_of_birth,
    department_id, designation_id, employment_type, employment_status,
    date_hired, email, supervisor_id
)
VALUES (
    'EMP-0003', 'Ella', 'Employee', 'Female', '1998-09-21',
    @ops_dept_id, @employee_designation_id, 'Full-Time', 'Active',
    CURRENT_DATE, 'employee@hris.local', @manager_employee_id
)
ON DUPLICATE KEY UPDATE
    first_name = VALUES(first_name),
    last_name = VALUES(last_name),
    department_id = VALUES(department_id),
    designation_id = VALUES(designation_id),
    employment_status = 'Active',
    email = VALUES(email),
    supervisor_id = VALUES(supervisor_id);

SET @admin_employee_id = (SELECT id FROM hris_employees WHERE employee_code = 'EMP-0001' LIMIT 1);
SET @hradmin_employee_id = (SELECT id FROM hris_employees WHERE employee_code = 'EMP-0004' LIMIT 1);
SET @employee_employee_id = (SELECT id FROM hris_employees WHERE employee_code = 'EMP-0003' LIMIT 1);

-- Keep HR department head aligned
UPDATE hris_departments
SET head_employee_id = @admin_employee_id
WHERE id = @hr_dept_id;

-- Create/update login accounts
INSERT INTO hris_users (
    username, email, password_hash, role_id, employee_id,
    is_active, failed_attempts, locked_until
)
VALUES
    ('superadmin', 'admin@hris.local', @pwd_hash, @super_admin_role_id, @admin_employee_id, 1, 0, NULL),
    ('hradmin1', 'hradmin@hris.local', @pwd_hash, @hr_admin_role_id, @hradmin_employee_id, 0, 0, NULL),
    ('manager1', 'manager@hris.local', @pwd_hash, @manager_role_id, @manager_employee_id, 0, 0, NULL),
    ('employee1', 'employee@hris.local', @pwd_hash, @employee_role_id, @employee_employee_id, 0, 0, NULL)
ON DUPLICATE KEY UPDATE
    email = VALUES(email),
    password_hash = VALUES(password_hash),
    role_id = VALUES(role_id),
    employee_id = VALUES(employee_id),
    is_active = VALUES(is_active),
    failed_attempts = 0,
    locked_until = NULL;

-- Enforce Super Admin-only sign-in posture while preserving actor records
UPDATE hris_users u
INNER JOIN hris_roles r ON r.id = u.role_id
SET u.is_active = CASE WHEN r.role_name = 'Super Admin' THEN 1 ELSE 0 END,
    u.failed_attempts = 0,
    u.locked_until = NULL
WHERE u.username IN ('superadmin', 'hradmin1', 'manager1', 'employee1');

COMMIT;

-- Verification
SELECT username, email, is_active
FROM hris_users
WHERE username IN ('superadmin', 'hradmin1', 'manager1', 'employee1')
ORDER BY username;

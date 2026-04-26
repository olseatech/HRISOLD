-- HRIS v1 Demo and Initial Super Admin Seed
-- Default super admin credentials:
-- Username: superadmin
-- Email: admin@hris.local
-- Password: Admin@123

INSERT INTO hris_companies (company_name, address, phone, email, website)
SELECT 'Acme HR Solutions', '123 Main Street, Metro City', '+63 900 000 0000', 'hello@acmehr.local', 'https://acmehr.local'
WHERE NOT EXISTS (
    SELECT 1 FROM hris_companies WHERE company_name = 'Acme HR Solutions'
);

SET @company_id = (SELECT MIN(id) FROM hris_companies WHERE company_name = 'Acme HR Solutions');

INSERT INTO hris_branches (company_id, branch_name, address, phone, is_active)
SELECT @company_id, 'Head Office', '123 Main Street, Metro City', '+63 900 000 0001', 1
WHERE NOT EXISTS (
    SELECT 1 FROM hris_branches WHERE company_id = @company_id AND branch_name = 'Head Office'
);

SET @branch_id = (SELECT MIN(id) FROM hris_branches WHERE company_id = @company_id AND branch_name = 'Head Office');

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

INSERT INTO hris_departments (branch_id, department_name, is_active)
SELECT @branch_id, 'Finance', 1
WHERE NOT EXISTS (
    SELECT 1 FROM hris_departments WHERE branch_id = @branch_id AND department_name = 'Finance'
);

INSERT INTO hris_designations (designation_name, description)
SELECT 'HR Director', 'Leads the HR department'
WHERE NOT EXISTS (
    SELECT 1 FROM hris_designations WHERE designation_name = 'HR Director'
);

INSERT INTO hris_designations (designation_name, description)
SELECT 'HR Officer', 'Handles HR operations'
WHERE NOT EXISTS (
    SELECT 1 FROM hris_designations WHERE designation_name = 'HR Officer'
);

INSERT INTO hris_designations (designation_name, description)
SELECT 'System Administrator', 'Maintains HRIS platform'
WHERE NOT EXISTS (
    SELECT 1 FROM hris_designations WHERE designation_name = 'System Administrator'
);

INSERT INTO hris_leave_types (type_name, description, default_days, is_paid, is_active)
SELECT 'Vacation', 'Annual vacation leave', 10, 1, 1
WHERE NOT EXISTS (
    SELECT 1 FROM hris_leave_types WHERE type_name = 'Vacation'
);

INSERT INTO hris_leave_types (type_name, description, default_days, is_paid, is_active)
SELECT 'Sick', 'Sick leave entitlement', 10, 1, 1
WHERE NOT EXISTS (
    SELECT 1 FROM hris_leave_types WHERE type_name = 'Sick'
);

INSERT INTO hris_leave_types (type_name, description, default_days, is_paid, is_active)
SELECT 'Emergency', 'Emergency leave', 5, 1, 1
WHERE NOT EXISTS (
    SELECT 1 FROM hris_leave_types WHERE type_name = 'Emergency'
);

SET @hr_dept_id = (SELECT MIN(id) FROM hris_departments WHERE department_name = 'Human Resources');
SET @sysadmin_designation_id = (SELECT MIN(id) FROM hris_designations WHERE designation_name = 'System Administrator');

INSERT INTO hris_employees (
    employee_code, first_name, last_name, gender, date_of_birth,
    department_id, designation_id, employment_type, employment_status, date_hired, email
)
SELECT
    'EMP-0001', 'System', 'Administrator', 'Other', '1990-01-01',
    @hr_dept_id, @sysadmin_designation_id, 'Full-Time', 'Active', CURRENT_DATE, 'admin@hris.local'
WHERE NOT EXISTS (
    SELECT 1 FROM hris_employees WHERE employee_code = 'EMP-0001'
);

SET @admin_employee_id = (SELECT MIN(id) FROM hris_employees WHERE employee_code = 'EMP-0001');
SET @super_admin_role_id = (SELECT MIN(id) FROM hris_roles WHERE role_name = 'Super Admin');
SET @manager_role_id = (SELECT MIN(id) FROM hris_roles WHERE role_name = 'Manager');
SET @employee_role_id = (SELECT MIN(id) FROM hris_roles WHERE role_name = 'Employee');
SET @operations_dept_id = COALESCE((SELECT MIN(id) FROM hris_departments WHERE department_name = 'Operations'), @hr_dept_id);

INSERT INTO hris_designations (designation_name, description)
SELECT 'Department Manager', 'Manages department operations and approvals'
WHERE NOT EXISTS (
    SELECT 1 FROM hris_designations WHERE designation_name = 'Department Manager'
);

INSERT INTO hris_designations (designation_name, description)
SELECT 'Staff Employee', 'General employee role for self-service modules'
WHERE NOT EXISTS (
    SELECT 1 FROM hris_designations WHERE designation_name = 'Staff Employee'
);

SET @manager_designation_id = (SELECT MIN(id) FROM hris_designations WHERE designation_name = 'Department Manager');
SET @employee_designation_id = (SELECT MIN(id) FROM hris_designations WHERE designation_name = 'Staff Employee');

INSERT INTO hris_users (
    username, email, password_hash, role_id, employee_id, is_active, failed_attempts
)
SELECT
    'superadmin',
    'admin@hris.local',
    '$2y$12$VevcIIsG56vvKpem0eRI1ORK.W3EuA/WD29H3qrEyqVgiwU3vFI6e',
    @super_admin_role_id,
    @admin_employee_id,
    1,
    0
WHERE NOT EXISTS (
    SELECT 1 FROM hris_users WHERE username = 'superadmin' OR email = 'admin@hris.local'
);

-- Manager test credentials (created for data relationships, inactive in Super Admin-only mode):
-- Username: manager1
-- Email: manager@hris.local
-- Password: Admin@123

INSERT INTO hris_employees (
    employee_code, first_name, last_name, gender, date_of_birth,
    department_id, designation_id, employment_type, employment_status, date_hired, email
)
SELECT
    'EMP-0002', 'Mark', 'Manager', 'Male', '1992-05-12',
    @operations_dept_id, @manager_designation_id, 'Full-Time', 'Active', CURRENT_DATE, 'manager@hris.local'
WHERE NOT EXISTS (
    SELECT 1 FROM hris_employees WHERE employee_code = 'EMP-0002'
);

SET @manager_employee_id = (SELECT MIN(id) FROM hris_employees WHERE employee_code = 'EMP-0002');

INSERT INTO hris_users (
    username, email, password_hash, role_id, employee_id, is_active, failed_attempts
)
SELECT
    'manager1',
    'manager@hris.local',
    '$2y$12$VevcIIsG56vvKpem0eRI1ORK.W3EuA/WD29H3qrEyqVgiwU3vFI6e',
    @manager_role_id,
    @manager_employee_id,
    1,
    0
WHERE NOT EXISTS (
    SELECT 1 FROM hris_users WHERE username = 'manager1' OR email = 'manager@hris.local'
);

-- Employee test credentials (created for data relationships, inactive in Super Admin-only mode):
-- Username: employee1
-- Email: employee@hris.local
-- Password: Admin@123

INSERT INTO hris_employees (
    employee_code, first_name, last_name, gender, date_of_birth,
    department_id, designation_id, employment_type, employment_status, date_hired, email, supervisor_id
)
SELECT
    'EMP-0003', 'Ella', 'Employee', 'Female', '1998-09-21',
    @operations_dept_id, @employee_designation_id, 'Full-Time', 'Active', CURRENT_DATE, 'employee@hris.local', @manager_employee_id
WHERE NOT EXISTS (
    SELECT 1 FROM hris_employees WHERE employee_code = 'EMP-0003'
);

SET @employee_employee_id = (SELECT MIN(id) FROM hris_employees WHERE employee_code = 'EMP-0003');

INSERT INTO hris_users (
    username, email, password_hash, role_id, employee_id, is_active, failed_attempts
)
SELECT
    'employee1',
    'employee@hris.local',
    '$2y$12$VevcIIsG56vvKpem0eRI1ORK.W3EuA/WD29H3qrEyqVgiwU3vFI6e',
    @employee_role_id,
    @employee_employee_id,
    1,
    0
WHERE NOT EXISTS (
    SELECT 1 FROM hris_users WHERE username = 'employee1' OR email = 'employee@hris.local'
);

UPDATE hris_departments
SET head_employee_id = @admin_employee_id
WHERE id = @hr_dept_id;

-- Quarterly subscription plans for testing
INSERT INTO hris_subscription_plans (
    plan_code, plan_name, description, billing_cycle, interval_months,
    price_amount, currency, employee_limit, is_contact_only, feature_flags, is_active, sort_order
)
SELECT
    'STARTER-Q', 'Starter', 'Core HR workflow for small teams', 'quarterly', 3,
    2999.00, 'PHP', 25, 0,
    JSON_ARRAY('employees', 'attendance', 'leave'), 1, 10
WHERE NOT EXISTS (
    SELECT 1 FROM hris_subscription_plans WHERE plan_code = 'STARTER-Q'
);

INSERT INTO hris_subscription_plans (
    plan_code, plan_name, description, billing_cycle, interval_months,
    price_amount, currency, employee_limit, is_contact_only, feature_flags, is_active, sort_order
)
SELECT
    'GROWTH-Q', 'Growth', 'Expanded HR operations with payroll and admin controls', 'quarterly', 3,
    6999.00, 'PHP', 200, 0,
    JSON_ARRAY('employees', 'attendance', 'leave', 'payroll', 'settings'), 1, 20
WHERE NOT EXISTS (
    SELECT 1 FROM hris_subscription_plans WHERE plan_code = 'GROWTH-Q'
);

INSERT INTO hris_subscription_plans (
    plan_code, plan_name, description, billing_cycle, interval_months,
    price_amount, currency, employee_limit, is_contact_only, feature_flags, is_active, sort_order
)
SELECT
    'ENTERPRISE-Q', 'Enterprise', 'Custom enterprise setup with dedicated support', 'quarterly', 3,
    19999.00, 'PHP', NULL, 1,
    JSON_ARRAY('employees', 'attendance', 'leave', 'payroll', 'settings', 'priority_support'), 1, 30
WHERE NOT EXISTS (
    SELECT 1 FROM hris_subscription_plans WHERE plan_code = 'ENTERPRISE-Q'
);

UPDATE hris_subscription_plans
SET plan_name = 'Starter',
    description = 'Core HR workflow for small teams',
    billing_cycle = 'quarterly',
    interval_months = 3,
    price_amount = 2999.00,
    currency = 'PHP',
    employee_limit = 25,
    is_contact_only = 0,
    feature_flags = JSON_ARRAY('employees', 'attendance', 'leave'),
    is_active = 1,
    sort_order = 10
WHERE plan_code = 'STARTER-Q';

UPDATE hris_subscription_plans
SET plan_name = 'Growth',
    description = 'Expanded HR operations with payroll and admin controls',
    billing_cycle = 'quarterly',
    interval_months = 3,
    price_amount = 6999.00,
    currency = 'PHP',
    employee_limit = 200,
    is_contact_only = 0,
    feature_flags = JSON_ARRAY('employees', 'attendance', 'leave', 'payroll', 'settings'),
    is_active = 1,
    sort_order = 20
WHERE plan_code = 'GROWTH-Q';

UPDATE hris_subscription_plans
SET plan_name = 'Enterprise',
    description = 'Custom enterprise setup with dedicated support',
    billing_cycle = 'quarterly',
    interval_months = 3,
    price_amount = 19999.00,
    currency = 'PHP',
    employee_limit = NULL,
    is_contact_only = 1,
    feature_flags = JSON_ARRAY('employees', 'attendance', 'leave', 'payroll', 'settings', 'priority_support'),
    is_active = 1,
    sort_order = 30
WHERE plan_code = 'ENTERPRISE-Q';

SET @starter_plan_id = (SELECT MIN(id) FROM hris_subscription_plans WHERE plan_code = 'STARTER-Q');
SET @growth_plan_id = (SELECT MIN(id) FROM hris_subscription_plans WHERE plan_code = 'GROWTH-Q');

INSERT INTO hris_company_subscriptions (
    company_id, plan_id, billing_cycle, status,
    starts_at, ends_at, trial_ends_at, activated_at, metadata
)
SELECT
    @company_id,
    @starter_plan_id,
    'quarterly',
    'trialing',
    CURRENT_DATE,
    DATE_ADD(CURRENT_DATE, INTERVAL 3 MONTH),
    DATE_ADD(CURRENT_DATE, INTERVAL 14 DAY),
    NULL,
    JSON_OBJECT('source', 'demo_seed', 'note', 'initial trial subscription')
WHERE NOT EXISTS (
    SELECT 1
    FROM hris_company_subscriptions
    WHERE company_id = @company_id
      AND status IN ('trialing', 'active')
);

SET @company_subscription_id = (
    SELECT id
    FROM hris_company_subscriptions
    WHERE company_id = @company_id
      AND status IN ('trialing', 'active')
    ORDER BY id DESC
    LIMIT 1
);

INSERT INTO hris_subscription_transactions (
    company_subscription_id, company_id, plan_id, provider, test_mode, status,
    amount, currency, reference_code, notes, payload, processed_at
)
SELECT
    @company_subscription_id,
    @company_id,
    @starter_plan_id,
    'test',
    'test_success',
    'success',
    2999.00,
    'PHP',
    'TXN-TEST-SUCCESS',
    'Seeded successful test checkout',
    JSON_OBJECT('seed', true, 'result', 'success'),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM hris_subscription_transactions WHERE reference_code = 'TXN-TEST-SUCCESS'
);

INSERT INTO hris_subscription_transactions (
    company_subscription_id, company_id, plan_id, provider, test_mode, status,
    amount, currency, reference_code, notes, payload, processed_at
)
SELECT
    @company_subscription_id,
    @company_id,
    @growth_plan_id,
    'test',
    'test_pending',
    'pending',
    6999.00,
    'PHP',
    'TXN-TEST-PENDING',
    'Seeded pending test checkout',
    JSON_OBJECT('seed', true, 'result', 'pending'),
    NULL
WHERE NOT EXISTS (
    SELECT 1 FROM hris_subscription_transactions WHERE reference_code = 'TXN-TEST-PENDING'
);

INSERT INTO hris_subscription_transactions (
    company_subscription_id, company_id, plan_id, provider, test_mode, status,
    amount, currency, reference_code, notes, payload, processed_at
)
SELECT
    @company_subscription_id,
    @company_id,
    @growth_plan_id,
    'test',
    'test_fail',
    'failed',
    6999.00,
    'PHP',
    'TXN-TEST-FAIL',
    'Seeded failed test checkout',
    JSON_OBJECT('seed', true, 'result', 'failed'),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM hris_subscription_transactions WHERE reference_code = 'TXN-TEST-FAIL'
);

-- Super Admin-only mode: keep additional actors for data relationships but disable their sign-in
UPDATE hris_users u
INNER JOIN hris_roles r ON r.id = u.role_id
SET u.is_active = CASE WHEN r.role_name = 'Super Admin' THEN 1 ELSE 0 END,
    u.failed_attempts = 0,
    u.locked_until = NULL
WHERE u.username IN ('superadmin', 'manager1', 'employee1', 'hradmin1');

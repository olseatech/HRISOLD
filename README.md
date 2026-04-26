# HRIS v1

Custom Human Resources Information System built with a lightweight PHP MVC architecture, MySQL, and role-based access control.

## Technology Stack
- PHP 8.1+
- MySQL 8+
- Apache (recommended) with mod_rewrite
- Composer + vlucas/phpdotenv
- Vanilla JavaScript + modular CSS

## Implemented Features

### Authentication and Access Control
- Login using username or email.
- Session-based authentication with secure cookie options (HttpOnly, SameSite, configurable lifetime).
- Session ID regeneration on login/logout.
- Account lockout protection after configurable failed attempts.
- Role and permission-based authorization via route middleware.
- Billing-first redirect after login (`/billing`) before entering protected modules.
- Role-based in-app continuation paths: Super Admin -> Settings, HR Admin -> Employees, Manager -> Leave, Employee -> Attendance.
- Automatic redirect fallback to first allowed module if preferred path is not accessible.

### Public Landing and Subscription Billing (Quarterly Test Mode)
- Public marketing landing page at `/` for unauthenticated visitors.
- Dedicated public pricing page at `/pricing` with quarterly plans.
- Plan selection from landing/pricing with CSRF-protected submission (`/subscribe`).
- Quarterly plan catalog with Starter, Growth, and Enterprise (contact-sales only for now).
- Billing workspace at `/billing` for authenticated users.
- Subscription statuses: `trialing`, `active`, `past_due`, `canceled`, `expired`.
- Test checkout simulation modes: `test_success`, `test_pending`, `test_fail`.
- Subscription guard middleware blocks protected modules unless subscription is valid.
- No role bypass when subscription is invalid; users are redirected to `/billing`.
- Admin billing action to cancel current subscription (`billing.manage` permission).

### Security
- CSRF token generation and verification for mutating requests.
- Input validation utilities for required fields, email, date format, date order, enum lists, and numeric minimums.
- Permission guardrails on all protected modules.

### Dashboard
- KPI cards: total employees, present today, pending leave requests.
- Attendance trend chart for the last 7 days.
- Dashboard visibility controlled by authentication and permissions.

### Employee Management
- Employee listing with pagination.
- Search by employee code, name, email, department, and designation.
- Filter by employment status.
- Create employee profiles with comprehensive personal and employment details.
- View employee profile details.
- Edit employee records.
- Delete employee records (with constraint-safe error handling).
- Automatic employee code generation in EMP-0001 format.
- Department, designation, and supervisor lookups in forms.

### Attendance Management
- Daily attendance board by selected date.
- Search and status filtering with pagination.
- Attendance statuses: Present, Absent, Late, Half-Day, Holiday, Rest Day.
- Attendance upsert (insert/update) per employee per date.
- Clock-in and clock-out support with automatic hours calculation.
- Overtime tracking and remarks.
- Employee self-service scope (employees can only view their own attendance).

### Leave Management
- Leave request listing with pagination, status filter, and search.
- Leave request submission with date and numeric validation.
- Overlap detection to prevent conflicting pending/approved leave periods.
- Leave approval and rejection workflows with reviewer remarks.
- Status transition enforcement (only pending requests are reviewable).
- Employee self-service scope for leave visibility and submission.

### Payroll Management
- Salary grade management (create/update by grade name).
- Salary grade validation with min/max boundary checks.
- Employee salary assignment with effective date.
- Current salary flag handling with transactional update to keep consistency.
- Salary records list with search and pagination.

### Settings and Administration
- Company profile management: name, address, phone, email, website, logo path.
- System settings management: timezone, date format, default currency.
- System settings persisted to storage/cache/system-settings.json.
- Role administration with active/inactive toggle.
- Role listing with permission-count visibility.

### Audit and Traceability
- Audit logging for key actions (auth, employee, attendance, leave, payroll, settings).
- Captures module, action, user, record ID, before/after payload snapshots, and IP address.
- Audit failures are isolated so core business actions do not fail.

### Seeded Roles, Permissions, and Data
- Seeded roles: Super Admin, HR Admin, Manager, Employee.
- Seeded permission matrix for dashboard, employees, attendance, leave, payroll, settings, and billing.
- Seeded baseline organization data: company, branch, departments, designations, leave types.
- Seeded demo users and linked employee records.
- Seeded quarterly subscription plans and checkout transaction scenarios.
- Idempotent-style seed scripts designed to be safely re-run.

### Architecture Highlights
- Custom MVC structure with controllers, models, middleware, and views.
- Route pattern matching with dynamic parameters (example: /employees/{id}/edit).
- Middleware chain support (auth, guest, csrf, permission, subscription).
- Centralized session and helper utilities.
- Modular CSS organization per domain module.

## Role Access Summary

| Role | Typical Landing | Access Scope |
| --- | --- | --- |
| Super Admin | /billing -> /settings | Full access to all seeded permissions, including billing management |
| HR Admin | /billing -> /employees | Employee CRUD, attendance management, leave approvals, payroll view |
| Manager | /billing -> /leave | Team-oriented visibility with leave approvals |
| Employee | /billing -> /attendance | Self-service attendance and leave requests |

## Schema-Ready Modules (Database Foundation Exists)

The schema also includes tables that are ready for future UI/API expansion:
- Employee contacts
- Employee documents
- Timesheets
- Leave balances
- Allowances
- Deductions
- Announcements
- User session records

The schema now also includes implemented subscription tables:
- `hris_subscription_plans`
- `hris_company_subscriptions`
- `hris_subscription_transactions`

## Quick Start
1. Copy .env.example to .env.
2. Configure database credentials in .env.
3. Run composer install.
4. Create the database (default: hris_db).
5. Import database/migrations/001_initial_schema.sql.
6. Run database/seeds/roles_seed.sql.
7. Run database/seeds/demo_data.sql.
8. Serve the app (Apache vhost or PHP built-in server with document root set to public).
9. Open `/` for the public landing page, then use `/login` to enter the billing flow.

Example built-in server command:

```bash
php -S 127.0.0.1:8000 -t public
```

## Environment Variables

Default values from .env.example:

```dotenv
APP_NAME=HRIS v1
APP_ENV=development
APP_DEBUG=true
APP_URL=http://hris.test
APP_TIMEZONE=Asia/Manila

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hris_db
DB_USERNAME=root
DB_PASSWORD=

SESSION_NAME=hris_session
SESSION_LIFETIME=120
SESSION_SECURE=false
SESSION_SAMESITE=Strict

AUTH_MAX_ATTEMPTS=5
AUTH_LOCK_MINUTES=15
```

## Seeded Test Accounts

All seeded test users use password: Admin@123

| Role | Username | Email |
| --- | --- | --- |
| Super Admin | superadmin | admin@hris.local |
| Manager | manager1 | manager@hris.local |
| Employee | employee1 | employee@hris.local |

## Subscription Test Flow

1. Visit `/` and open `/pricing`.
2. Select a plan (Enterprise routes to contact-sales messaging).
3. Sign in using a seeded account.
4. You will be redirected to `/billing`.
5. Choose a plan and run checkout with one of the test modes:
	- `test_success`: activates subscription and restores module access.
	- `test_pending`: records pending checkout and keeps access gated.
	- `test_fail`: records failed checkout and keeps access gated.
6. Use **Continue to workspace** once subscription status is active or trialing.

<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Audit;
use App\Core\Controller;
use App\Core\CSRF;
use App\Core\Session;
use App\Core\Validator;
use App\Models\Settings;

final class SettingsController extends Controller
{
    /** Allowed date format strings — whitelist only */
    private const VALID_DATE_FORMATS = ['Y-m-d', 'd/m/Y', 'm/d/Y', 'd-m-Y', 'F j, Y', 'j F Y'];

    /** Allowed ISO 4217 currency codes */
    private const VALID_CURRENCIES = ['PHP', 'USD', 'EUR', 'GBP', 'JPY', 'SGD', 'AUD', 'CAD', 'HKD', 'MYR'];

    private Settings $settings;

    public function __construct()
    {
        $this->settings = new Settings();
    }

    public function index(): void
    {
        $this->view('settings/index', [
            'title'   => 'Settings',
            'csrf'    => CSRF::token(),
            'company' => $this->settings->companyProfile(),
            'roles'   => $this->settings->rolesWithPermissionCount(),
            'system'  => $this->settings->loadSystemSettings(),
            'success' => Session::pullFlash('success'),
            'error'   => Session::pullFlash('error'),
            'errors'  => Session::pullFlash('errors', []),
        ]);
    }

    public function saveCompany(): void
    {
        // CSRF verification
        $token = $_POST['_csrf'] ?? null;
        if (!CSRF::verify(is_string($token) ? $token : null)) {
            Session::flash('error', 'Your session token is invalid. Please try again.');
            $this->redirect('/settings');
        }

        $data = [
            'company_name' => mb_substr(trim((string) ($_POST['company_name'] ?? '')), 0, 200),
            'address'      => mb_substr(trim((string) ($_POST['address'] ?? '')), 0, 500),
            'phone'        => mb_substr(trim((string) ($_POST['phone'] ?? '')), 0, 50),
            'email'        => mb_substr(trim((string) ($_POST['email'] ?? '')), 0, 150),
            'website'      => mb_substr(trim((string) ($_POST['website'] ?? '')), 0, 250),
            'logo_path'    => mb_substr(trim((string) ($_POST['logo_path'] ?? '')), 0, 500),
        ];

        $errors = Validator::required($data, ['company_name']);
        $errors = array_merge($errors, Validator::email($data, 'email'));

        // Validate website URL format if provided
        if ($data['website'] !== '' && !filter_var($data['website'], FILTER_VALIDATE_URL)) {
            $errors['website'] = 'Website must be a valid URL.';
        }

        if ($errors !== []) {
            Session::flash('errors', $errors);
            Session::flash('error', 'Please fix company settings errors.');
            $this->redirect('/settings');
        }

        $companyId = $this->settings->saveCompany($data);
        Audit::log('settings', 'UPDATE', $companyId, null, $data);

        Session::flash('success', 'Company settings saved.');
        $this->redirect('/settings');
    }

    public function saveSystem(): void
    {
        // CSRF verification
        $token = $_POST['_csrf'] ?? null;
        if (!CSRF::verify(is_string($token) ? $token : null)) {
            Session::flash('error', 'Your session token is invalid. Please try again.');
            $this->redirect('/settings');
        }

        $data = [
            'timezone'         => trim((string) ($_POST['timezone'] ?? 'Asia/Manila')),
            'date_format'      => trim((string) ($_POST['date_format'] ?? 'Y-m-d')),
            'default_currency' => trim((string) ($_POST['default_currency'] ?? 'PHP')),
        ];

        $errors = Validator::required($data, ['timezone', 'date_format', 'default_currency']);

        // ── Whitelist validation ──────────────────────────────────
        if ($errors === [] || !isset($errors['timezone'])) {
            if (!in_array($data['timezone'], timezone_identifiers_list(), true)) {
                $errors['timezone'] = 'Invalid timezone selected.';
            }
        }

        if ($errors === [] || !isset($errors['date_format'])) {
            if (!in_array($data['date_format'], self::VALID_DATE_FORMATS, true)) {
                $errors['date_format'] = 'Invalid date format selected.';
            }
        }

        if ($errors === [] || !isset($errors['default_currency'])) {
            if (!in_array($data['default_currency'], self::VALID_CURRENCIES, true)) {
                $errors['default_currency'] = 'Invalid currency code.';
            }
        }

        if ($errors !== []) {
            Session::flash('errors', $errors);
            Session::flash('error', 'Please fix system settings errors.');
            $this->redirect('/settings');
        }

        if (!$this->settings->saveSystemSettings($data)) {
            Session::flash('error', 'Unable to save system settings.');
            $this->redirect('/settings');
        }

        Audit::log('settings', 'UPDATE', null, null, $data);

        Session::flash('success', 'System settings saved.');
        $this->redirect('/settings');
    }

    public function toggleRole(string $id): void
    {
        if (super_admin_only_mode_enabled()) {
            Session::flash('error', 'Role updates are disabled while Super Admin-only mode is enabled.');
            $this->redirect('/settings');
        }

        $roleId = (int) $id;
        $before = $this->settings->findRole($roleId);

        if (!$before) {
            Session::flash('error', 'Role not found.');
            $this->redirect('/settings');
        }

        $this->settings->toggleRoleActive($roleId);
        $after = $this->settings->findRole($roleId);
        Audit::log('settings', 'UPDATE', $roleId, $before, $after);

        Session::flash('success', 'Role status updated.');
        $this->redirect('/settings');
    }

    // ── Departments ────────────────────────────────────────────────────────────

    public function departments(): void
    {
        $this->view('settings/departments', [
            'title'   => 'Departments',
            'csrf'    => CSRF::token(),
            'rows'    => $this->settings->allDepartments(),
            'success' => Session::pullFlash('success'),
            'error'   => Session::pullFlash('error'),
            'errors'  => Session::pullFlash('errors', []),
            'old'     => Session::pullFlash('old', []),
        ]);
    }

    public function storeDepartment(): void
    {
        $this->verifyCsrf('/settings/departments');
        $data   = ['department_name' => trim((string) ($_POST['department_name'] ?? '')), 'is_active' => $_POST['is_active'] ?? null];
        $errors = Validator::required($data, ['department_name']);
        if ($errors !== []) {
            Session::flash('errors', $errors);
            Session::flash('old', $data);
            $this->redirect('/settings/departments');
        }
        $id = $this->settings->createDepartment($data);
        Audit::log('settings', 'CREATE', $id, null, $data);
        Session::flash('success', 'Department added.');
        $this->redirect('/settings/departments');
    }

    public function updateDepartment(string $id): void
    {
        $this->verifyCsrf('/settings/departments');
        $recId  = (int) $id;
        $data   = ['department_name' => trim((string) ($_POST['department_name'] ?? '')), 'is_active' => $_POST['is_active'] ?? null];
        $errors = Validator::required($data, ['department_name']);
        if ($errors !== []) {
            Session::flash('errors', $errors);
            Session::flash('old', $data);
            $this->redirect('/settings/departments');
        }
        $before = $this->settings->findDepartment($recId);
        $this->settings->updateDepartment($recId, $data);
        Audit::log('settings', 'UPDATE', $recId, $before, $data);
        Session::flash('success', 'Department updated.');
        $this->redirect('/settings/departments');
    }

    public function destroyDepartment(string $id): void
    {
        $this->verifyCsrf('/settings/departments');
        $recId  = (int) $id;
        $before = $this->settings->findDepartment($recId);
        if (!$before) {
            Session::flash('error', 'Department not found.');
            $this->redirect('/settings/departments');
        }
        $this->settings->deleteDepartment($recId);
        Audit::log('settings', 'DELETE', $recId, $before, null);
        Session::flash('success', 'Department deleted.');
        $this->redirect('/settings/departments');
    }

    // ── Designations ───────────────────────────────────────────────────────────

    public function designations(): void
    {
        $this->view('settings/designations', [
            'title'   => 'Positions / Designations',
            'csrf'    => CSRF::token(),
            'rows'    => $this->settings->allDesignations(),
            'success' => Session::pullFlash('success'),
            'error'   => Session::pullFlash('error'),
            'errors'  => Session::pullFlash('errors', []),
            'old'     => Session::pullFlash('old', []),
        ]);
    }

    public function storeDesignation(): void
    {
        $this->verifyCsrf('/settings/designations');
        $data   = ['designation_name' => trim((string) ($_POST['designation_name'] ?? '')), 'description' => trim((string) ($_POST['description'] ?? ''))];
        $errors = Validator::required($data, ['designation_name']);
        if ($errors !== []) {
            Session::flash('errors', $errors);
            Session::flash('old', $data);
            $this->redirect('/settings/designations');
        }
        $id = $this->settings->createDesignation($data);
        Audit::log('settings', 'CREATE', $id, null, $data);
        Session::flash('success', 'Position/Designation added.');
        $this->redirect('/settings/designations');
    }

    public function updateDesignation(string $id): void
    {
        $this->verifyCsrf('/settings/designations');
        $recId  = (int) $id;
        $data   = ['designation_name' => trim((string) ($_POST['designation_name'] ?? '')), 'description' => trim((string) ($_POST['description'] ?? ''))];
        $errors = Validator::required($data, ['designation_name']);
        if ($errors !== []) {
            Session::flash('errors', $errors);
            Session::flash('old', $data);
            $this->redirect('/settings/designations');
        }
        $before = $this->settings->findDesignation($recId);
        $this->settings->updateDesignation($recId, $data);
        Audit::log('settings', 'UPDATE', $recId, $before, $data);
        Session::flash('success', 'Position updated.');
        $this->redirect('/settings/designations');
    }

    public function destroyDesignation(string $id): void
    {
        $this->verifyCsrf('/settings/designations');
        $recId  = (int) $id;
        $before = $this->settings->findDesignation($recId);
        if (!$before) {
            Session::flash('error', 'Designation not found.');
            $this->redirect('/settings/designations');
        }
        $this->settings->deleteDesignation($recId);
        Audit::log('settings', 'DELETE', $recId, $before, null);
        Session::flash('success', 'Designation deleted.');
        $this->redirect('/settings/designations');
    }

    // ── Leave Types ────────────────────────────────────────────────────────────

    public function leaveTypes(): void
    {
        $this->view('settings/leave-types', [
            'title'   => 'Leave Types',
            'csrf'    => CSRF::token(),
            'rows'    => $this->settings->allLeaveTypes(),
            'success' => Session::pullFlash('success'),
            'error'   => Session::pullFlash('error'),
            'errors'  => Session::pullFlash('errors', []),
            'old'     => Session::pullFlash('old', []),
        ]);
    }

    public function storeLeaveType(): void
    {
        $this->verifyCsrf('/settings/leave-types');
        $data = [
            'type_name'    => trim((string) ($_POST['type_name'] ?? '')),
            'description'  => trim((string) ($_POST['description'] ?? '')),
            'default_days' => trim((string) ($_POST['default_days'] ?? '0')),
            'is_paid'      => $_POST['is_paid'] ?? null,
            'is_active'    => $_POST['is_active'] ?? null,
        ];
        $errors = Validator::required($data, ['type_name']);
        if ($errors !== []) {
            Session::flash('errors', $errors);
            Session::flash('old', $data);
            $this->redirect('/settings/leave-types');
        }
        $id = $this->settings->createLeaveType($data);
        Audit::log('settings', 'CREATE', $id, null, $data);
        Session::flash('success', 'Leave type added.');
        $this->redirect('/settings/leave-types');
    }

    public function updateLeaveType(string $id): void
    {
        $this->verifyCsrf('/settings/leave-types');
        $recId = (int) $id;
        $data  = [
            'type_name'    => trim((string) ($_POST['type_name'] ?? '')),
            'description'  => trim((string) ($_POST['description'] ?? '')),
            'default_days' => trim((string) ($_POST['default_days'] ?? '0')),
            'is_paid'      => $_POST['is_paid'] ?? null,
            'is_active'    => $_POST['is_active'] ?? null,
        ];
        $errors = Validator::required($data, ['type_name']);
        if ($errors !== []) {
            Session::flash('errors', $errors);
            Session::flash('old', $data);
            $this->redirect('/settings/leave-types');
        }
        $before = $this->settings->findLeaveType($recId);
        $this->settings->updateLeaveType($recId, $data);
        Audit::log('settings', 'UPDATE', $recId, $before, $data);
        Session::flash('success', 'Leave type updated.');
        $this->redirect('/settings/leave-types');
    }

    public function destroyLeaveType(string $id): void
    {
        $this->verifyCsrf('/settings/leave-types');
        $recId  = (int) $id;
        $before = $this->settings->findLeaveType($recId);
        if (!$before) {
            Session::flash('error', 'Leave type not found.');
            $this->redirect('/settings/leave-types');
        }
        $this->settings->deleteLeaveType($recId);
        Audit::log('settings', 'DELETE', $recId, $before, null);
        Session::flash('success', 'Leave type deleted.');
        $this->redirect('/settings/leave-types');
    }

    // ── Salary Grades ──────────────────────────────────────────────────────────

    public function salaryGrades(): void
    {
        $this->view('settings/salary-grades', [
            'title'   => 'Salary Grades',
            'csrf'    => CSRF::token(),
            'rows'    => $this->settings->allSalaryGrades(),
            'success' => Session::pullFlash('success'),
            'error'   => Session::pullFlash('error'),
            'errors'  => Session::pullFlash('errors', []),
            'old'     => Session::pullFlash('old', []),
        ]);
    }

    public function storeSalaryGrade(): void
    {
        $this->verifyCsrf('/settings/salary-grades');
        $data   = [
            'grade_name' => trim((string) ($_POST['grade_name'] ?? '')),
            'min_salary' => trim((string) ($_POST['min_salary'] ?? '0')),
            'max_salary' => trim((string) ($_POST['max_salary'] ?? '0')),
        ];
        $errors = Validator::required($data, ['grade_name']);
        if ($errors !== []) {
            Session::flash('errors', $errors);
            Session::flash('old', $data);
            $this->redirect('/settings/salary-grades');
        }
        $id = $this->settings->createSalaryGrade($data);
        Audit::log('settings', 'CREATE', $id, null, $data);
        Session::flash('success', 'Salary grade added.');
        $this->redirect('/settings/salary-grades');
    }

    public function updateSalaryGrade(string $id): void
    {
        $this->verifyCsrf('/settings/salary-grades');
        $recId  = (int) $id;
        $data   = [
            'grade_name' => trim((string) ($_POST['grade_name'] ?? '')),
            'min_salary' => trim((string) ($_POST['min_salary'] ?? '0')),
            'max_salary' => trim((string) ($_POST['max_salary'] ?? '0')),
        ];
        $errors = Validator::required($data, ['grade_name']);
        if ($errors !== []) {
            Session::flash('errors', $errors);
            Session::flash('old', $data);
            $this->redirect('/settings/salary-grades');
        }
        $before = $this->settings->findSalaryGrade($recId);
        $this->settings->updateSalaryGrade($recId, $data);
        Audit::log('settings', 'UPDATE', $recId, $before, $data);
        Session::flash('success', 'Salary grade updated.');
        $this->redirect('/settings/salary-grades');
    }

    public function destroySalaryGrade(string $id): void
    {
        $this->verifyCsrf('/settings/salary-grades');
        $recId  = (int) $id;
        $before = $this->settings->findSalaryGrade($recId);
        if (!$before) {
            Session::flash('error', 'Salary grade not found.');
            $this->redirect('/settings/salary-grades');
        }
        $this->settings->deleteSalaryGrade($recId);
        Audit::log('settings', 'DELETE', $recId, $before, null);
        Session::flash('success', 'Salary grade deleted.');
        $this->redirect('/settings/salary-grades');
    }

    // ── Private helpers ────────────────────────────────────────────────────────

    private function verifyCsrf(string $redirectBack): void
    {
        $token = $_POST['_csrf'] ?? null;
        if (!CSRF::verify(is_string($token) ? $token : null)) {
            Session::flash('error', 'Your session token is invalid. Please try again.');
            $this->redirect($redirectBack);
        }
    }
}

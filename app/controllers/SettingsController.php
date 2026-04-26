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
}

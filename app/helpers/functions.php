<?php

declare(strict_types=1);

if (!function_exists('env')) {
    function env(string $key, mixed $default = null): mixed
    {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? null;

        if ($value === null) {
            return $default;
        }

        $normalized = strtolower((string) $value);

        return match ($normalized) {
            'true', '(true)' => true,
            'false', '(false)' => false,
            'null', '(null)' => null,
            'empty', '(empty)' => '',
            default => $value,
        };
    }
}

if (!function_exists('config')) {
    function config(string $file): array
    {
        $path = __DIR__ . '/../config/' . $file . '.php';

        if (!file_exists($path)) {
            throw new RuntimeException('Config file not found: ' . $file);
        }

        $config = require $path;

        if (!is_array($config)) {
            throw new RuntimeException('Config file must return array: ' . $file);
        }

        return $config;
    }
}

if (!function_exists('subscription_mode')) {
    function subscription_mode(): string
    {
        static $mode = null;

        if (is_string($mode)) {
            return $mode;
        }

        $app = config('app');
        $mode = strtolower((string) ($app['subscription_mode'] ?? 'testing'));

        if (!in_array($mode, ['testing', 'production'], true)) {
            $mode = 'testing';
        }

        return $mode;
    }
}

if (!function_exists('subscription_payment_required')) {
    function subscription_payment_required(): bool
    {
        $app = config('app');

        if (array_key_exists('subscription_required', $app)) {
            return (bool) $app['subscription_required'];
        }

        return subscription_mode() === 'production';
    }
}

if (!function_exists('subscription_feature_locks_enabled')) {
    function subscription_feature_locks_enabled(): bool
    {
        $app = config('app');

        if (!array_key_exists('subscription_feature_locks', $app)) {
            return true;
        }

        return (bool) $app['subscription_feature_locks'];
    }
}

if (!function_exists('is_subscription_testing_mode')) {
    function is_subscription_testing_mode(): bool
    {
        return subscription_mode() === 'testing' || !subscription_payment_required();
    }
}

if (!function_exists('super_admin_only_mode_enabled')) {
    function super_admin_only_mode_enabled(): bool
    {
        $app = config('app');

        if (!array_key_exists('super_admin_only_mode', $app)) {
            return false;
        }

        return (bool) $app['super_admin_only_mode'];
    }
}

if (!function_exists('subscription_lock_modal_enabled')) {
    function subscription_lock_modal_enabled(): bool
    {
        $app = config('app');

        if (!array_key_exists('subscription_lock_modal_enabled', $app)) {
            return true;
        }

        return (bool) $app['subscription_lock_modal_enabled'];
    }
}

if (!function_exists('normal_plan_name')) {
    function normal_plan_name(): string
    {
        $app = config('app');
        $name = trim((string) ($app['normal_plan_name'] ?? 'Normal'));

        return $name !== '' ? $name : 'Normal';
    }
}

if (!function_exists('normal_plan_source_name')) {
    function normal_plan_source_name(): string
    {
        $app = config('app');
        $name = trim((string) ($app['normal_plan_source_name'] ?? 'Starter'));

        return $name !== '' ? $name : 'Starter';
    }
}

if (!function_exists('display_plan_name_for_access')) {
    function display_plan_name_for_access(?string $planName): string
    {
        $resolved = trim((string) $planName);

        if ($resolved === '') {
            return '';
        }

        if (strcasecmp($resolved, normal_plan_source_name()) !== 0) {
            return $resolved;
        }

        return normal_plan_name() . ' (' . $resolved . ')';
    }
}

if (!function_exists('e')) {
    function e(?string $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('app_base_path')) {
    function app_base_path(): string
    {
        static $basePath = null;

        if (is_string($basePath)) {
            return $basePath;
        }

        $configuredUrl = (string) (config('app')['url'] ?? '');
        $parsedPath = (string) (parse_url($configuredUrl, PHP_URL_PATH) ?? '');
        $parsedPath = trim($parsedPath);

        if ($parsedPath === '' || $parsedPath === '/') {
            $basePath = '';
            return $basePath;
        }

        $basePath = '/' . trim($parsedPath, '/');

        return $basePath;
    }
}

if (!function_exists('asset_url')) {
    function asset_url(string $path): string
    {
        $normalizedPath = '/' . ltrim($path, '/');

        return app_base_path() . $normalizedPath;
    }
}

if (!function_exists('auth_user')) {
    function auth_user(): ?array
    {
        $user = \App\Core\Auth::user();

        return is_array($user) ? $user : null;
    }
}

if (!function_exists('can')) {
    function can(string $permissionKey): bool
    {
        if ($permissionKey === '') {
            return false;
        }

        return \App\Core\Auth::can($permissionKey);
    }
}

if (!function_exists('is_role')) {
    function is_role(string $roleName, ?array $user = null): bool
    {
        if ($roleName === '') {
            return false;
        }

        $resolvedUser = is_array($user) ? $user : auth_user();
        if ($resolvedUser === null) {
            return false;
        }

        return (string) ($resolvedUser['role_name'] ?? '') === $roleName;
    }
}

if (!function_exists('is_super_admin_user')) {
    function is_super_admin_user(?array $user = null): bool
    {
        return is_role('Super Admin', $user);
    }
}

if (!function_exists('is_employee_role')) {
    function is_employee_role(?array $user = null): bool
    {
        return is_role('Employee', $user);
    }
}

if (!function_exists('permission_for_path')) {
    function permission_for_path(string $path): ?string
    {
        $normalized = rtrim($path, '/');
        if ($normalized === '') {
            $normalized = '/';
        }

        return match ($normalized) {
            '/dashboard' => 'dashboard.view',
            '/employees' => 'employees.view',
            '/attendance' => 'attendance.view',
            '/leave' => 'leave.view',
            '/payroll' => 'payroll.view',
            '/settings' => 'settings.manage',
            default => null,
        };
    }
}

if (!function_exists('nav_feature_lock_context')) {
    /**
     * @return array{is_lockable: bool, is_locked: bool, feature_key: ?string, feature_label: ?string, plan_name: string, message: string}
     */
    function nav_feature_lock_context(string $path, ?array $user = null): array
    {
        $context = [
            'is_lockable' => false,
            'is_locked' => false,
            'feature_key' => null,
            'feature_label' => null,
            'plan_name' => '',
            'message' => '',
        ];

        if (!subscription_feature_locks_enabled()) {
            return $context;
        }

        $normalizedPath = rtrim($path, '/');
        if ($normalizedPath === '') {
            $normalizedPath = '/';
        }

        if ($normalizedPath === '/billing' || str_starts_with($normalizedPath, '/billing/')) {
            return $context;
        }

        $resolvedUser = is_array($user) ? $user : auth_user();
        if (!is_array($resolvedUser)) {
            return $context;
        }

        $subscription = new \App\Models\Subscription();
        $featureKey = $subscription->featureKeyForPath($normalizedPath);

        if (!is_string($featureKey) || $featureKey === '') {
            return $context;
        }

        $context['is_lockable'] = true;
        $context['feature_key'] = $featureKey;
        $context['feature_label'] = $subscription->featureLabel($featureKey);

        $isTestingMode = is_subscription_testing_mode();
        $companyId = null;

        if (!$isTestingMode) {
            $userId = (int) ($resolvedUser['id'] ?? 0);

            if ($userId > 0) {
                $companyId = $subscription->resolveCompanyIdForUser($userId);
            }
        }

        $fallbackPlanId = (int) \App\Core\Session::get('billing.pending_plan_id', 0);

        if ($isTestingMode && $fallbackPlanId <= 0) {
            $defaultPlan = $subscription->defaultTestingPlan();

            if (is_array($defaultPlan) && isset($defaultPlan['id'])) {
                $fallbackPlanId = (int) $defaultPlan['id'];
            }
        }

        $decision = $subscription->accessDecisionForPath(
            $normalizedPath,
            $isTestingMode ? null : $companyId,
            $fallbackPlanId > 0 ? $fallbackPlanId : null
        );

        if ((bool) ($decision['allowed'] ?? false)) {
            $context['plan_name'] = display_plan_name_for_access((string) ($decision['plan_name'] ?? ''));
            return $context;
        }

        $featureLabel = (string) ($context['feature_label'] ?? 'This module');
        $planName = display_plan_name_for_access((string) ($decision['plan_name'] ?? ''));
        $context['is_locked'] = true;
        $context['plan_name'] = $planName;

        if ($isTestingMode) {
            $context['message'] = $planName !== ''
                ? $featureLabel . ' is locked in your selected plan (' . $planName . '). Upgrade to unlock this module.'
                : $featureLabel . ' is locked in your selected plan. Upgrade to unlock this module.';

            return $context;
        }

        $context['message'] = $planName !== ''
            ? $featureLabel . ' is not included in your current plan (' . $planName . '). Upgrade to unlock this module.'
            : $featureLabel . ' is not included in your current plan. Upgrade to unlock this module.';

        return $context;
    }
}

if (!function_exists('can_access_path')) {
    function can_access_path(string $path): bool
    {
        $normalized = rtrim($path, '/');
        if ($normalized === '') {
            $normalized = '/';
        }

        if ($normalized === '/login') {
            return !\App\Core\Auth::check();
        }

        if (!\App\Core\Auth::check()) {
            return false;
        }

        if (super_admin_only_mode_enabled() && !is_super_admin_user()) {
            return false;
        }

        if ($normalized === '/billing' || str_starts_with($normalized, '/billing/')) {
            return true;
        }

        $permission = permission_for_path($normalized);
        if ($permission === null) {
            return false;
        }

        return can($permission);
    }
}

if (!function_exists('post_auth_entry_path')) {
    function post_auth_entry_path(?array $user = null): string
    {
        $resolvedUser = is_array($user) ? $user : auth_user();

        if ($resolvedUser === null) {
            return '/login';
        }

        if (super_admin_only_mode_enabled() && !is_super_admin_user($resolvedUser)) {
            return '/login';
        }

        if (is_subscription_testing_mode()) {
            return role_landing_path($resolvedUser);
        }

        return '/billing';
    }
}

if (!function_exists('role_landing_path')) {
    function role_landing_path(?array $user = null): string
    {
        $resolvedUser = is_array($user) ? $user : auth_user();
        if ($resolvedUser === null) {
            return '/login';
        }

        if (super_admin_only_mode_enabled() && !is_super_admin_user($resolvedUser)) {
            return '/login';
        }

        if (super_admin_only_mode_enabled()) {
            return can_access_path('/dashboard') ? '/dashboard' : '/billing';
        }

        $preferredPath = match ((string) ($resolvedUser['role_name'] ?? '')) {
            'Super Admin' => '/settings',
            'HR Admin' => '/employees',
            'Manager' => '/leave',
            'Employee' => '/attendance',
            default => '/',
        };

        if (can_access_path($preferredPath)) {
            return $preferredPath;
        }

        $fallbackPaths = ['/dashboard', '/employees', '/attendance', '/leave', '/payroll', '/settings', '/billing'];
        foreach ($fallbackPaths as $path) {
            if (can_access_path($path)) {
                return $path;
            }
        }

        return '/billing';
    }
}

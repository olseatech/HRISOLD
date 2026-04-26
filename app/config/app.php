<?php

declare(strict_types=1);

$subscriptionMode = strtolower((string) env('SUBSCRIPTION_MODE', 'testing'));

if (!in_array($subscriptionMode, ['testing', 'production'], true)) {
    $subscriptionMode = 'testing';
}

$subscriptionRequired = (bool) env('SUBSCRIPTION_REQUIRED', $subscriptionMode === 'production');
$subscriptionFeatureLocks = (bool) env('SUBSCRIPTION_ENFORCE_FEATURE_LOCKS', true);

$toBool = static function (mixed $value, bool $default): bool {
    if (is_bool($value)) {
        return $value;
    }

    if ($value === null) {
        return $default;
    }

    $parsed = filter_var((string) $value, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);

    return $parsed ?? $default;
};

$superAdminOnlyMode = $toBool(env('SUPER_ADMIN_ONLY_MODE', true), true);
$subscriptionLockModalEnabled = $toBool(env('SUBSCRIPTION_LOCK_MODAL_ENABLED', true), true);
$normalPlanName = trim((string) env('NORMAL_PLAN_NAME', 'Normal'));
$normalPlanSourceName = trim((string) env('NORMAL_PLAN_SOURCE_NAME', 'Starter'));

if ($normalPlanName === '') {
    $normalPlanName = 'Normal';
}

if ($normalPlanSourceName === '') {
    $normalPlanSourceName = 'Starter';
}

return [
    'name' => env('APP_NAME', 'HRIS v1'),
    'env' => env('APP_ENV', 'production'),
    'debug' => (bool) env('APP_DEBUG', false),
    'url' => env('APP_URL', 'http://localhost'),
    'timezone' => env('APP_TIMEZONE', 'UTC'),
    'subscription_mode' => $subscriptionMode,
    'subscription_required' => $subscriptionRequired,
    'subscription_feature_locks' => $subscriptionFeatureLocks,
    'super_admin_only_mode' => $superAdminOnlyMode,
    'subscription_lock_modal_enabled' => $subscriptionLockModalEnabled,
    'normal_plan_name' => $normalPlanName,
    'normal_plan_source_name' => $normalPlanSourceName,
];

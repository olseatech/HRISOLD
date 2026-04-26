<?php

declare(strict_types=1);

use App\Core\Auth;

$app = config('app');
$authUser = Auth::user();
$todayLabel = date('M d, Y');
$currentPath = (string) (parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH) ?: '/');
$breadcrumbMap = [
    '/' => 'Dashboard',
    '/employees' => 'Employees',
    '/attendance' => 'Attendance',
    '/leave' => 'Leave',
    '/payroll' => 'Payroll',
    '/settings' => 'Settings',
];
$breadcrumb = 'Dashboard';
foreach ($breadcrumbMap as $path => $label) {
    if ($path === '/' ? $currentPath === '/' : str_starts_with($currentPath, $path)) {
        $breadcrumb = $label;
    }
}
?>
<header class="topbar">
    <button class="sidebar-toggle topbar-menu-btn" id="sidebarToggle" aria-label="Toggle navigation" aria-controls="sidebar">
        <svg width="16" height="16" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M3 5h14M3 10h14M3 15h14"/></svg>
    </button>
    <div class="topbar-meta">
        <div class="topbar-breadcrumb">
            <span><?= e((string) ($app['name'] ?? 'HRIS')) ?></span>
            <span class="sep" aria-hidden="true">/</span>
            <span><?= e($breadcrumb) ?></span>
        </div>
        <h1 class="topbar-title"><?= e($title ?? $breadcrumb) ?></h1>
    </div>
    <div class="topbar-user">
        <span class="topbar-date" title="System date"><?= e($todayLabel) ?></span>
        <div class="topbar-account">
            <span class="topbar-account-name"><?= e((string) ($authUser['username'] ?? 'User')) ?></span>
            <span class="topbar-account-role"><?= e((string) ($authUser['role_name'] ?? 'No role')) ?></span>
        </div>
    </div>
</header>

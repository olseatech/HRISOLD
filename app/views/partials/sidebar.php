<?php
declare(strict_types=1);

use App\Core\Auth;

$currentPath = (string) (parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH) ?: '/');
$authUser = Auth::user();

$iconDashboard  = '<svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="2.5" y="2.5" width="6" height="7"/><rect x="11.5" y="2.5" width="6" height="4"/><rect x="2.5" y="12.5" width="6" height="5"/><rect x="11.5" y="9.5" width="6" height="8"/></svg>';
$iconEmployees  = '<svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="10" cy="6.5" r="3"/><path d="M3.5 17c0-3.3 2.9-6 6.5-6s6.5 2.7 6.5 6"/></svg>';
$iconAttendance = '<svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="10" cy="10" r="7"/><path d="M10 6v4l2.5 2"/></svg>';
$iconLeave      = '<svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4.5" width="14" height="13" rx="1"/><path d="M3 8h14M7 2.5v3M13 2.5v3"/></svg>';
$iconPayroll    = '<svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="2.5" y="5" width="15" height="10" rx="1"/><circle cx="10" cy="10" r="2.2"/><path d="M5.5 8.5v3M14.5 8.5v3"/></svg>';
$iconSettings   = '<svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="10" cy="10" r="2.4"/><path d="M10 2.5v2M10 15.5v2M2.5 10h2M15.5 10h2M4.7 4.7l1.4 1.4M13.9 13.9l1.4 1.4M4.7 15.3l1.4-1.4M13.9 6.1l1.4-1.4"/></svg>';
$iconBilling    = '<svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="16" height="12" rx="1"/><path d="M2 8h16M6 2v3M14 2v3"/></svg>';
$iconPds        = '<svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2H5a1 1 0 0 0-1 1v14a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1V6z"/><polyline points="12 2 12 6 16 6"/><line x1="7" y1="9" x2="13" y2="9"/><line x1="7" y1="12" x2="13" y2="12"/><line x1="7" y1="15" x2="10" y2="15"/></svg>';
$iconServiceRec = '<svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="2" width="14" height="16" rx="1"/><path d="M7 6h6M7 9h6M7 12h4"/><circle cx="14.5" cy="14.5" r="3"/><path d="m13.5 14.5 1 1 1.5-1.5"/></svg>';
$iconClearance  = '<svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M10 2L3 5v5c0 4.4 3 8.1 7 9 4-0.9 7-4.6 7-9V5l-7-3z"/><path d="M7 10l2 2 4-4"/></svg>';
$iconAppointment = '<svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="14" height="13" rx="1"/><path d="M3 8h14M7 2v3M13 2v3M7 12h2M7 15h2M11 12h2"/></svg>';
$iconLogout     = '<svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M8 3.5H4.5a1 1 0 0 0-1 1v11a1 1 0 0 0 1 1H8"/><path d="M12 6.5 15.5 10 12 13.5M7 10h8.5"/></svg>';
$iconLock       = '<svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="8" width="12" height="9" rx="1"/><path d="M7 8V5a3 3 0 0 1 6 0v3"/></svg>';

$navItems = [
    ['path' => '/',           'label' => 'Dashboard',  'permission' => 'dashboard.view',  'icon' => $iconDashboard],
    ['path' => '/employees',  'label' => 'Employees',  'permission' => 'employees.view',  'icon' => $iconEmployees],
    ['path' => '/attendance', 'label' => 'Attendance', 'permission' => 'attendance.view', 'icon' => $iconAttendance],
    ['path' => '/leave',      'label' => 'Leave',      'permission' => 'leave.view',      'icon' => $iconLeave],
    ['path' => '/payroll',    'label' => 'Payroll',    'permission' => 'payroll.view',    'icon' => $iconPayroll],
    ['path' => '/pds',            'label' => 'PDS',            'permission' => 'pds.view',             'icon' => $iconPds],
    ['path' => '/service-records','label' => 'Service Record', 'permission' => 'service_records.view', 'icon' => $iconServiceRec],
    ['path' => '/clearances',     'label' => 'Clearance',      'permission' => 'clearances.view',       'icon' => $iconClearance],
    ['path' => '/appointments',   'label' => 'Appointments',   'permission' => 'appointments.view',     'icon' => $iconAppointment],
    ['path' => '/settings',       'label' => 'Settings',       'permission' => 'settings.manage',      'icon' => $iconSettings],
    ['path' => '/billing',    'label' => 'Billing',    'permission' => 'billing.view',    'icon' => $iconBilling],
];
?>
<aside class="sidebar" id="sidebar" aria-label="Primary navigation">

    <!-- ── Brand header ──────────────────────────────────── -->
    <div class="sidebar-header">
        <div class="sidebar-seal" aria-hidden="true">
            <svg viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
                <circle cx="24" cy="24" r="22" fill="#ffffff" stroke="#b68409" stroke-width="1.5"/>
                <circle cx="24" cy="24" r="17" fill="none" stroke="#b68409" stroke-width="1"/>
                <path d="M24 10 L27 19 L36 19 L29 25 L32 34 L24 28 L16 34 L19 25 L12 19 L21 19 Z" fill="#0b3d91" stroke="#0b3d91" stroke-width="0.5"/>
                <text x="24" y="43" font-family="Inter, sans-serif" font-size="3" font-weight="700" fill="#0b3d91" text-anchor="middle" letter-spacing="0.5">REPUBLIC</text>
            </svg>
        </div>
        <div class="sidebar-brand">
            <span class="brand-kicker">Official Portal</span>
            <span class="brand-name">Barangay HRIS</span>
            <span class="brand-sub">Human Resources System</span>
        </div>
    </div>

    <!-- ── Navigation modules (grows to fill space) ──────── -->
    <div class="sidebar-group">
        <p class="sidebar-label">Modules</p>
        <nav class="sidebar-nav" aria-label="Main navigation">
            <?php foreach ($navItems as $item): ?>
                <?php if (!can((string) ($item['permission'] ?? ''))): ?>
                    <?php continue; ?>
                <?php endif; ?>
                <?php
                $isRoot   = $item['path'] === '/';
                $isActive = $isRoot ? $currentPath === '/' : str_starts_with($currentPath, $item['path']);

                $lockContext = nav_feature_lock_context($item['path'], $authUser);
                $isLocked    = (bool) ($lockContext['is_locked'] ?? false);
                $lockMessage = (string) ($lockContext['message'] ?? '');
                $featureLabel = (string) ($lockContext['feature_label'] ?? $item['label']);
                $planName    = (string) ($lockContext['plan_name'] ?? '');
                ?>
                <?php if ($isLocked): ?>
                    <a
                        class="nav-link nav-link-locked<?= $isActive ? ' is-active' : '' ?>"
                        href="<?= e($item['path']) ?>"
                        <?= $isActive ? 'aria-current="page"' : '' ?>
                        data-lock-trigger
                        data-lock-feature-label="<?= e($featureLabel) ?>"
                        data-lock-message="<?= e($lockMessage) ?>"
                        data-lock-plan-name="<?= e($planName) ?>"
                        aria-label="<?= e($item['label']) ?> — locked"
                    >
                        <span class="nav-link-icon" aria-hidden="true"><?= $item['icon'] ?></span>
                        <span><?= e($item['label']) ?></span>
                        <span class="nav-link-lock" aria-hidden="true"><?= $iconLock ?></span>
                    </a>
                <?php else: ?>
                    <a
                        class="nav-link<?= $isActive ? ' is-active' : '' ?>"
                        href="<?= e($item['path']) ?>"
                        <?= $isActive ? 'aria-current="page"' : '' ?>
                    >
                        <span class="nav-link-icon" aria-hidden="true"><?= $item['icon'] ?></span>
                        <span><?= e($item['label']) ?></span>
                    </a>
                <?php endif; ?>
            <?php endforeach; ?>
        </nav>
    </div>

    <!-- ── Account block — pinned to bottom ──────────────── -->
    <div class="sidebar-footer">
        <?php if ($authUser): ?>
            <div class="sidebar-user">
                <span class="sidebar-user-name"><?= e((string) ($authUser['username'] ?? 'User')) ?></span>
                <span class="sidebar-user-role"><?= e((string) ($authUser['role_name'] ?? 'No role')) ?></span>
            </div>
        <?php endif; ?>
        <a class="nav-link logout" href="/logout">
            <span class="nav-link-icon" aria-hidden="true"><?= $iconLogout ?></span>
            <span>Sign out</span>
        </a>
    </div>

</aside>


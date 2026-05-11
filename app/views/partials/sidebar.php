<?php
declare(strict_types=1);

use App\Core\Auth;

$currentPath = (string) (parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH) ?: '/');
$authUser    = Auth::user();

/* ── SVG icon library ──────────────────────────────────────── */
$ic = static function (string $path, string $extra = ''): string {
    return '<svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.75"
            stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" ' . $extra . '>' . $path . '</svg>';
};

$icons = [
    'dashboard'    => $ic('<rect x="2.5" y="2.5" width="6" height="7"/><rect x="11.5" y="2.5" width="6" height="4"/><rect x="2.5" y="12.5" width="6" height="5"/><rect x="11.5" y="9.5" width="6" height="8"/>'),
    'employees'    => $ic('<circle cx="10" cy="6.5" r="3"/><path d="M3.5 17c0-3.3 2.9-6 6.5-6s6.5 2.7 6.5 6"/>'),
    'attendance'   => $ic('<circle cx="10" cy="10" r="7"/><path d="M10 6v4l2.5 2"/>'),
    'leave'        => $ic('<rect x="3" y="4.5" width="14" height="13" rx="1"/><path d="M3 8h14M7 2.5v3M13 2.5v3"/>'),
    'payroll'      => $ic('<rect x="2.5" y="5" width="15" height="10" rx="1"/><circle cx="10" cy="10" r="2.2"/><path d="M5.5 8.5v3M14.5 8.5v3"/>'),
    'my_leave'     => $ic('<circle cx="10" cy="6.5" r="3"/><path d="M3.5 17c0-3.3 2.9-6 6.5-6s6.5 2.7 6.5 6"/><path d="M13 12.5l1 1 2-2"/>'),
    'holidays'     => $ic('<rect x="3" y="4.5" width="14" height="13" rx="1"/><path d="M3 8h14M7 2.5v3M13 2.5v3"/><circle cx="10" cy="13" r="1.5"/>'),
    'pds'          => $ic('<path d="M12 2H5a1 1 0 0 0-1 1v14a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1V6z"/><polyline points="12 2 12 6 16 6"/><line x1="7" y1="9" x2="13" y2="9"/><line x1="7" y1="12" x2="13" y2="12"/><line x1="7" y1="15" x2="10" y2="15"/>'),
    'service_rec'  => $ic('<rect x="3" y="2" width="14" height="16" rx="1"/><path d="M7 6h6M7 9h6M7 12h4"/><circle cx="14.5" cy="14.5" r="3"/><path d="m13.5 14.5 1 1 1.5-1.5"/>'),
    'clearance'    => $ic('<path d="M10 2L3 5v5c0 4.4 3 8.1 7 9 4-0.9 7-4.6 7-9V5l-7-3z"/><path d="M7 10l2 2 4-4"/>'),
    'appointment'  => $ic('<rect x="3" y="4" width="14" height="13" rx="1"/><path d="M3 8h14M7 2v3M13 2v3M7 12h2M7 15h2M11 12h2"/>'),
    'documents'    => $ic('<path d="M11 2H5a1 1 0 0 0-1 1v14a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1V6z"/><polyline points="11 2 11 6 15 6"/><line x1="7" y1="9" x2="13" y2="9"/><line x1="7" y1="12" x2="13" y2="12"/><line x1="7" y1="15" x2="10" y2="15"/>'),
    'my_svc'       => $ic('<circle cx="8" cy="6" r="3"/><path d="M2 17c0-3.3 2.7-6 6-6"/><path d="M12 12h6M12 15h6M12 18h4"/>'),
    'reports'      => $ic('<rect x="3" y="2" width="14" height="16" rx="1"/><path d="M7 6h6M7 9h4"/><path d="M7 13h2v4H7zM11 11h2v6h-2zM15 9h-1v8h1"/>'),
    'settings'     => $ic('<circle cx="10" cy="10" r="2.4"/><path d="M10 2.5v2M10 15.5v2M2.5 10h2M15.5 10h2M4.7 4.7l1.4 1.4M13.9 13.9l1.4 1.4M4.7 15.3l1.4-1.4M13.9 6.1l1.4-1.4"/>'),
    'billing'      => $ic('<rect x="2" y="4" width="16" height="12" rx="1"/><path d="M2 8h16M6 2v3M14 2v3"/>'),
    'logout'       => $ic('<path d="M8 3.5H4.5a1 1 0 0 0-1 1v11a1 1 0 0 0 1 1H8"/><path d="M12 6.5 15.5 10 12 13.5M7 10h8.5"/>'),
    'lock'         => $ic('<rect x="4" y="8" width="12" height="9" rx="1"/><path d="M7 8V5a3 3 0 0 1 6 0v3"/>'),
    'key'          => $ic('<circle cx="7.5" cy="10" r="4"/><path d="M10.5 10h7M15 10v2.5"/>'),
    'chevron_down' => $ic('<polyline points="5 8 10 13 15 8"/>'),
];

/* ── Nav group definitions ─────────────────────────────────── */
$navGroups = [
    [
        'id'    => 'core',
        'label' => 'Main',
        'items' => [
            ['path' => '/',          'label' => 'Dashboard',  'permission' => 'dashboard.view',  'icon' => $icons['dashboard']],
        ],
    ],
    [
        'id'    => 'workforce',
        'label' => 'Workforce',
        'items' => [
            ['path' => '/employees',       'label' => 'Employees',        'permission' => 'employees.view',       'icon' => $icons['employees']],
            ['path' => '/pds',             'label' => 'PDS',              'permission' => 'pds.view',             'icon' => $icons['pds']],
            ['path' => '/service-records', 'label' => 'Service Records',  'permission' => 'service_records.view', 'icon' => $icons['service_rec']],
            ['path' => '/my-service-record','label'=> 'My Service Record','permission' => 'service_records.view', 'icon' => $icons['my_svc']],
            ['path' => '/appointments',    'label' => 'Appointments',     'permission' => 'appointments.view',    'icon' => $icons['appointment']],
            ['path' => '/documents',       'label' => '201 Documents',    'permission' => 'documents.view',       'icon' => $icons['documents']],
        ],
    ],
    [
        'id'    => 'time',
        'label' => 'Time & Leave',
        'items' => [
            ['path' => '/attendance', 'label' => 'Attendance', 'permission' => 'attendance.view', 'icon' => $icons['attendance']],
            ['path' => '/leave',      'label' => 'Leave',      'permission' => 'leave.view',      'icon' => $icons['leave']],
            ['path' => '/my-leave',   'label' => 'My Leave',   'permission' => 'leave.my_leave',  'icon' => $icons['my_leave']],
            ['path' => '/holidays',   'label' => 'Holidays',   'permission' => 'holidays.manage', 'icon' => $icons['holidays']],
        ],
    ],
    [
        'id'    => 'payroll',
        'label' => 'Payroll',
        'items' => [
            ['path' => '/payroll', 'label' => 'Payroll', 'permission' => 'payroll.view', 'icon' => $icons['payroll']],
        ],
    ],
    [
        'id'    => 'compliance',
        'label' => 'Compliance',
        'items' => [
            ['path' => '/clearances', 'label' => 'Clearance', 'permission' => 'clearances.view', 'icon' => $icons['clearance']],
            ['path' => '/reports',    'label' => 'Reports',   'permission' => 'reports.view',    'icon' => $icons['reports']],
        ],
    ],
    [
        'id'    => 'admin',
        'label' => 'Administration',
        'items' => [
            ['path' => '/settings', 'label' => 'Settings', 'permission' => 'settings.manage', 'icon' => $icons['settings']],
            ['path' => '/billing',  'label' => 'Billing',  'permission' => 'billing.view',    'icon' => $icons['billing']],
        ],
    ],
];

/* ── Helper: is any item in this group active? ─────────────── */
$groupHasActive = static function (array $group) use ($currentPath): bool {
    foreach ($group['items'] as $item) {
        $isRoot = $item['path'] === '/';
        if ($isRoot ? $currentPath === '/' : str_starts_with($currentPath, $item['path'])) {
            return true;
        }
    }
    return false;
};
?>
<aside class="sidebar" id="sidebar" aria-label="Primary navigation">

    <!-- ── Brand header ──────────────────────────────────────── -->
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

    <!-- ── Grouped nav ───────────────────────────────────────── -->
    <nav class="sidebar-nav-root" aria-label="Main navigation">
    <?php foreach ($navGroups as $group):
        /* Filter to only items this user can see */
        $visibleItems = array_filter($group['items'], static fn($i) => can((string)($i['permission'] ?? '')));
        if (empty($visibleItems)) continue;

        $hasActive  = $groupHasActive($group);
        $groupId    = 'navgroup-' . $group['id'];
    ?>
        <div class="nav-group <?= $hasActive ? 'is-open' : '' ?>" data-group="<?= e($group['id']) ?>">
            <button
                class="nav-group-toggle"
                aria-controls="<?= e($groupId) ?>"
                aria-expanded="<?= $hasActive ? 'true' : 'false' ?>"
                type="button"
            >
                <span class="nav-group-label"><?= e($group['label']) ?></span>
                <span class="nav-group-chevron" aria-hidden="true"><?= $icons['chevron_down'] ?></span>
            </button>

            <ul class="nav-group-items" id="<?= e($groupId) ?>" role="list">
            <?php foreach ($visibleItems as $item):
                $isRoot   = $item['path'] === '/';
                $isActive = $isRoot ? $currentPath === '/' : str_starts_with($currentPath, $item['path']);

                $lockContext  = nav_feature_lock_context($item['path'], $authUser);
                $isLocked     = (bool) ($lockContext['is_locked'] ?? false);
                $lockMessage  = (string) ($lockContext['message'] ?? '');
                $featureLabel = (string) ($lockContext['feature_label'] ?? $item['label']);
                $planName     = (string) ($lockContext['plan_name'] ?? '');
            ?>
                <li role="listitem">
                <?php if ($isLocked): ?>
                    <a
                        class="nav-link nav-link-locked<?= $isActive ? ' is-active' : '' ?>"
                        href="<?= e($item['path']) ?>"
                        <?= $isActive ? 'aria-current="page"' : '' ?>
                        data-lock-trigger
                        data-lock-feature-label="<?= e($featureLabel) ?>"
                        data-lock-message="<?= e($lockMessage) ?>"
                        data-lock-plan-name="<?= e($planName) ?>"
                        aria-label="<?= e($item['label']) ?> — feature locked"
                    >
                        <span class="nav-link-icon" aria-hidden="true"><?= $item['icon'] ?></span>
                        <span><?= e($item['label']) ?></span>
                        <span class="nav-link-lock" aria-hidden="true"><?= $icons['lock'] ?></span>
                    </a>
                <?php else: ?>
                    <a
                        class="nav-link<?= $isActive ? ' is-active' : '' ?>"
                        href="<?= e($item['path']) ?>"
                        <?= $isActive ? 'aria-current="page"' : '' ?>
                        aria-label="<?= e($item['label']) ?>"
                    >
                        <span class="nav-link-icon" aria-hidden="true"><?= $item['icon'] ?></span>
                        <span><?= e($item['label']) ?></span>
                    </a>
                <?php endif; ?>
                </li>
            <?php endforeach; ?>
            </ul>
        </div>
    <?php endforeach; ?>
    </nav>

    <!-- ── Account block — pinned to bottom ──────────────────── -->
    <div class="sidebar-footer">
        <?php if ($authUser): ?>
            <div class="sidebar-user">
                <span class="sidebar-user-name"><?= e((string) ($authUser['username'] ?? 'User')) ?></span>
                <span class="sidebar-user-role"><?= e((string) ($authUser['role_name'] ?? 'No role')) ?></span>
            </div>
        <?php endif; ?>
        <a class="nav-link<?= str_starts_with($currentPath, '/change-password') ? ' is-active' : '' ?>"
           href="/change-password"
           aria-label="Change password">
            <span class="nav-link-icon" aria-hidden="true"><?= $icons['key'] ?></span>
            <span>Change Password</span>
        </a>
        <a class="nav-link logout" href="/logout" aria-label="Sign out">
            <span class="nav-link-icon" aria-hidden="true"><?= $icons['logout'] ?></span>
            <span>Sign out</span>
        </a>
    </div>

</aside>

<script>
(function () {
    'use strict';
    var STORE_KEY = 'hris_nav_open_groups';

    /* ── Restore saved open state from localStorage ── */
    function getSaved() {
        try { return JSON.parse(localStorage.getItem(STORE_KEY) || '{}'); } catch (_) { return {}; }
    }
    function save(map) {
        try { localStorage.setItem(STORE_KEY, JSON.stringify(map)); } catch (_) {}
    }

    document.querySelectorAll('.nav-group').forEach(function (group) {
        var id      = group.dataset.group;
        var btn     = group.querySelector('.nav-group-toggle');
        var items   = group.querySelector('.nav-group-items');
        var saved   = getSaved();

        /* Active groups are always open; others restore from storage */
        var isOpen  = group.classList.contains('is-open') || saved[id] === true;
        if (isOpen) {
            group.classList.add('is-open');
            btn.setAttribute('aria-expanded', 'true');
        }

        btn.addEventListener('click', function () {
            var open  = group.classList.toggle('is-open');
            btn.setAttribute('aria-expanded', open ? 'true' : 'false');
            var map   = getSaved();
            map[id]   = open;
            save(map);
        });
    });
})();
</script>

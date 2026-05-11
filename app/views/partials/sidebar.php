<?php
declare(strict_types=1);

use App\Core\Auth;

$currentPath = (string) (parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH) ?: '/');
$authUser    = Auth::user();

/* ─────────────────────────────────────────────────────────────
   Icon factory — identical style to old nav-link icons:
   currentColor stroke, 16 × 16, stroke-width 1.75
   ───────────────────────────────────────────────────────────── */
$ico = static function (string $d): string {
    return '<svg viewBox="0 0 20 20" width="16" height="16" fill="none"
        stroke="currentColor" stroke-width="1.75"
        stroke-linecap="round" stroke-linejoin="round"
        aria-hidden="true">' . $d . '</svg>';
};

/* Section-header icons (same paths/style as old flat sidebar) */
$icons = [
    // standalone
    'dashboard'  => $ico('<rect x="2.5" y="2.5" width="6" height="7"/><rect x="11.5" y="2.5" width="6" height="4"/><rect x="2.5" y="12.5" width="6" height="5"/><rect x="11.5" y="9.5" width="6" height="8"/>'),
    // section headers — representative icon for each group
    'workforce'  => $ico('<circle cx="10" cy="6.5" r="3"/><path d="M3.5 17c0-3.3 2.9-6 6.5-6s6.5 2.7 6.5 6"/>'),
    'time'       => $ico('<rect x="3" y="4.5" width="14" height="13" rx="1"/><path d="M3 8h14M7 2.5v3M13 2.5v3"/>'),
    'payroll'    => $ico('<rect x="2.5" y="5" width="15" height="10" rx="1"/><circle cx="10" cy="10" r="2.2"/><path d="M5.5 8.5v3M14.5 8.5v3"/>'),
    'compliance' => $ico('<path d="M10 2L3 5v5c0 4.4 3 8.1 7 9 4-.9 7-4.6 7-9V5l-7-3z"/><path d="M7 10l2 2 4-4"/>'),
    'admin'      => $ico('<circle cx="10" cy="10" r="2.4"/><path d="M10 2.5v2M10 15.5v2M2.5 10h2M15.5 10h2M4.7 4.7l1.4 1.4M13.9 13.9l1.4 1.4M4.7 15.3l1.4-1.4M13.9 6.1l1.4-1.4"/>'),
    // footer
    'key'        => $ico('<circle cx="7.5" cy="10" r="4"/><path d="M10.5 10h7M15 10v2.5"/>'),
    'logout'     => $ico('<path d="M8 3.5H4.5a1 1 0 0 0-1 1v11a1 1 0 0 0 1 1H8"/><path d="M12 6.5 15.5 10 12 13.5M7 10h8.5"/>'),
    // chevron (smaller, used inline)
    'chevron'    => '<svg viewBox="0 0 16 16" width="12" height="12" fill="none"
        stroke="currentColor" stroke-width="2.2" stroke-linecap="round"
        stroke-linejoin="round" aria-hidden="true"><polyline points="4 6 8 10 12 6"/></svg>',
    // lock badge
    'lock'       => '<svg viewBox="0 0 16 16" width="12" height="12" fill="none"
        stroke="currentColor" stroke-width="2" stroke-linecap="round"
        stroke-linejoin="round" aria-hidden="true">
        <rect x="2.5" y="7" width="11" height="8" rx="1"/>
        <path d="M5 7V4.5a3 3 0 0 1 6 0V7"/></svg>',
    // sub-item icons (same style, used inside sections)
    'employees'  => $ico('<circle cx="10" cy="6.5" r="3"/><path d="M3.5 17c0-3.3 2.9-6 6.5-6s6.5 2.7 6.5 6"/>'),
    'pds'        => $ico('<path d="M12 2H5a1 1 0 0 0-1 1v14a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1V6z"/><polyline points="12 2 12 6 16 6"/><line x1="7" y1="9" x2="13" y2="9"/><line x1="7" y1="12" x2="13" y2="12"/><line x1="7" y1="15" x2="10" y2="15"/>'),
    'svc_rec'    => $ico('<rect x="3" y="2" width="14" height="16" rx="1"/><path d="M7 6h6M7 9h6M7 12h4"/><circle cx="14.5" cy="14.5" r="3"/><path d="m13.5 14.5 1 1 1.5-1.5"/>'),
    'my_svc'     => $ico('<circle cx="8" cy="6" r="3"/><path d="M2 17c0-3.3 2.7-6 6-6"/><path d="M12 12h6M12 15h6M12 18h4"/>'),
    'appt'       => $ico('<rect x="3" y="4" width="14" height="13" rx="1"/><path d="M3 8h14M7 2v3M13 2v3M7 12h2M7 15h2M11 12h2"/>'),
    'docs'       => $ico('<path d="M11 2H5a1 1 0 0 0-1 1v14a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1V6z"/><polyline points="11 2 11 6 15 6"/><line x1="7" y1="9" x2="13" y2="9"/><line x1="7" y1="12" x2="13" y2="12"/><line x1="7" y1="15" x2="10" y2="15"/>'),
    'attend'     => $ico('<circle cx="10" cy="10" r="7"/><path d="M10 6v4l2.5 2"/>'),
    'leave'      => $ico('<rect x="3" y="4.5" width="14" height="13" rx="1"/><path d="M3 8h14M7 2.5v3M13 2.5v3"/>'),
    'my_leave'   => $ico('<circle cx="10" cy="6.5" r="3"/><path d="M3.5 17c0-3.3 2.9-6 6.5-6s6.5 2.7 6.5 6"/><path d="M13 12.5l1 1 2-2"/>'),
    'holidays'   => $ico('<rect x="3" y="4.5" width="14" height="13" rx="1"/><path d="M3 8h14M7 2.5v3M13 2.5v3"/><circle cx="10" cy="13" r="1.5"/>'),
    'payroll'    => $ico('<rect x="2.5" y="5" width="15" height="10" rx="1"/><circle cx="10" cy="10" r="2.2"/>'),
    'clearance'  => $ico('<path d="M10 2L3 5v5c0 4.4 3 8.1 7 9 4-.9 7-4.6 7-9V5l-7-3z"/><path d="M7 10l2 2 4-4"/>'),
    'reports'    => $ico('<rect x="3" y="2" width="14" height="16" rx="1"/><path d="M7 6h6M7 9h4"/><path d="M7 13h2v4H7zM11 11h2v6h-2zM15 9h-1v8h1"/>'),
    'settings'   => $ico('<circle cx="10" cy="10" r="2.4"/><path d="M10 2.5v2M10 15.5v2M2.5 10h2M15.5 10h2M4.7 4.7l1.4 1.4M13.9 13.9l1.4 1.4M4.7 15.3l1.4-1.4M13.9 6.1l1.4-1.4"/>'),
    'billing'    => $ico('<rect x="2" y="4" width="16" height="12" rx="1"/><path d="M2 8h16"/>'),
];

/* ─────────────────────────────────────────────────────────────
   Nav structure
   ───────────────────────────────────────────────────────────── */
$standalone = [
    ['path' => '/', 'label' => 'Dashboard', 'permission' => 'dashboard.view', 'icon' => $icons['dashboard']],
];

$sections = [
    [
        'id'    => 'workforce',
        'label' => 'Workforce',
        'icon'  => $icons['workforce'],
        'items' => [
            ['path' => '/employees',         'label' => 'Employees',         'permission' => 'employees.view',       'icon' => $icons['employees']],
            ['path' => '/pds',               'label' => 'PDS',               'permission' => 'pds.view',             'icon' => $icons['pds']],
            ['path' => '/service-records',   'label' => 'Service Records',   'permission' => 'service_records.view', 'icon' => $icons['svc_rec']],
            ['path' => '/my-service-record', 'label' => 'My Service Record', 'permission' => 'service_records.view', 'icon' => $icons['my_svc']],
            ['path' => '/appointments',      'label' => 'Appointments',      'permission' => 'appointments.view',    'icon' => $icons['appt']],
            ['path' => '/documents',         'label' => '201 Documents',     'permission' => 'documents.view',       'icon' => $icons['docs']],
        ],
    ],
    [
        'id'    => 'time',
        'label' => 'Time & Leave',
        'icon'  => $icons['time'],
        'items' => [
            ['path' => '/attendance', 'label' => 'Attendance', 'permission' => 'attendance.view', 'icon' => $icons['attend']],
            ['path' => '/leave',      'label' => 'Leave',      'permission' => 'leave.view',      'icon' => $icons['leave']],
            ['path' => '/my-leave',   'label' => 'My Leave',   'permission' => 'leave.my_leave',  'icon' => $icons['my_leave']],
            ['path' => '/holidays',   'label' => 'Holidays',   'permission' => 'holidays.manage', 'icon' => $icons['holidays']],
        ],
    ],
    [
        'id'    => 'payroll',
        'label' => 'Payroll',
        'icon'  => $icons['payroll'],
        'items' => [
            ['path' => '/payroll', 'label' => 'Payroll', 'permission' => 'payroll.view', 'icon' => $icons['payroll']],
        ],
    ],
    [
        'id'    => 'compliance',
        'label' => 'Compliance',
        'icon'  => $icons['compliance'],
        'items' => [
            ['path' => '/clearances', 'label' => 'Clearance', 'permission' => 'clearances.view', 'icon' => $icons['clearance']],
            ['path' => '/reports',    'label' => 'Reports',   'permission' => 'reports.view',    'icon' => $icons['reports']],
        ],
    ],
    [
        'id'    => 'admin',
        'label' => 'Administration',
        'icon'  => $icons['admin'],
        'items' => [
            ['path' => '/settings', 'label' => 'Settings', 'permission' => 'settings.manage', 'icon' => $icons['settings']],
            ['path' => '/billing',  'label' => 'Billing',  'permission' => 'billing.view',    'icon' => $icons['billing']],
        ],
    ],
];

/* ─────────────────────────────────────────────────────────────
   Helpers
   ───────────────────────────────────────────────────────────── */
$isActive = static function (string $path) use ($currentPath): bool {
    return $path === '/' ? $currentPath === '/' : str_starts_with($currentPath, $path);
};

$sectionHasActive = static function (array $section) use ($isActive): bool {
    foreach ($section['items'] as $item) {
        if ($isActive($item['path'])) return true;
    }
    return false;
};
?>
<aside class="sidebar" id="sidebar" aria-label="Primary navigation">

    <!-- Brand header -->
    <div class="sidebar-header">
        <div class="sidebar-seal" aria-hidden="true">
            <svg viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
                <circle cx="24" cy="24" r="22" fill="#ffffff" stroke="#b68409" stroke-width="1.5"/>
                <circle cx="24" cy="24" r="17" fill="none" stroke="#b68409" stroke-width="1"/>
                <path d="M24 10 L27 19 L36 19 L29 25 L32 34 L24 28 L16 34 L19 25 L12 19 L21 19 Z"
                      fill="#0b3d91" stroke="#0b3d91" stroke-width="0.5"/>
                <text x="24" y="43" font-family="Inter,sans-serif" font-size="3" font-weight="700"
                      fill="#0b3d91" text-anchor="middle" letter-spacing="0.5">REPUBLIC</text>
            </svg>
        </div>
        <div class="sidebar-brand">
            <span class="brand-kicker">Official Portal</span>
            <span class="brand-name">Barangay HRIS</span>
            <span class="brand-sub">Human Resources System</span>
        </div>
    </div>

    <!-- Scrollable nav -->
    <nav class="snav" aria-label="Main navigation">

        <!-- Standalone items (no accordion) -->
        <?php foreach ($standalone as $item):
            if (!can((string)($item['permission'] ?? ''))) continue;
            $active = $isActive($item['path']);
            $lc     = nav_feature_lock_context($item['path'], $authUser);
            $locked = (bool)($lc['is_locked'] ?? false);
        ?>
        <?php if ($locked): ?>
            <a class="snav-standalone snav-locked<?= $active ? ' is-active' : '' ?>"
               href="<?= e($item['path']) ?>"
               data-lock-trigger
               data-lock-feature-label="<?= e($lc['feature_label'] ?? $item['label']) ?>"
               data-lock-message="<?= e($lc['message'] ?? '') ?>"
               data-lock-plan-name="<?= e($lc['plan_name'] ?? '') ?>"
               aria-label="<?= e($item['label']) ?> — feature locked"
               <?= $active ? 'aria-current="page"' : '' ?>>
                <span class="snav-icon"><?= $item['icon'] ?></span>
                <span><?= e($item['label']) ?></span>
                <span class="snav-lock" aria-hidden="true"><?= $icons['lock'] ?></span>
            </a>
        <?php else: ?>
            <a class="snav-standalone<?= $active ? ' is-active' : '' ?>"
               href="<?= e($item['path']) ?>"
               aria-label="<?= e($item['label']) ?>"
               <?= $active ? 'aria-current="page"' : '' ?>>
                <span class="snav-icon"><?= $item['icon'] ?></span>
                <span><?= e($item['label']) ?></span>
            </a>
        <?php endif; ?>
        <?php endforeach; ?>

        <div class="snav-divider" role="separator"></div>

        <!-- Collapsible sections -->
        <?php foreach ($sections as $section):
            $visItems  = array_filter($section['items'],
                static fn($i) => can((string)($i['permission'] ?? '')));
            if (empty($visItems)) continue;

            $hasActive = $sectionHasActive($section);
            $sectId    = 'snav-sec-' . $section['id'];
        ?>
        <div class="snav-section<?= $hasActive ? ' is-open' : '' ?>"
             data-section="<?= e($section['id']) ?>">

            <button class="snav-toggle<?= $hasActive ? ' has-active' : '' ?>"
                    type="button"
                    aria-controls="<?= e($sectId) ?>"
                    aria-expanded="<?= $hasActive ? 'true' : 'false' ?>">
                <span class="snav-icon" aria-hidden="true"><?= $section['icon'] ?></span>
                <span class="snav-toggle-label"><?= e($section['label']) ?></span>
                <span class="snav-chevron<?= $hasActive ? ' is-open' : '' ?>"
                      aria-hidden="true"><?= $icons['chevron'] ?></span>
            </button>

            <ul class="snav-items" id="<?= e($sectId) ?>" role="list">
            <?php foreach ($visItems as $item):
                $active = $isActive($item['path']);
                $lc     = nav_feature_lock_context($item['path'], $authUser);
                $locked = (bool)($lc['is_locked'] ?? false);
            ?>
                <li role="listitem">
                <?php if ($locked): ?>
                    <a class="snav-link snav-locked<?= $active ? ' is-active' : '' ?>"
                       href="<?= e($item['path']) ?>"
                       data-lock-trigger
                       data-lock-feature-label="<?= e($lc['feature_label'] ?? $item['label']) ?>"
                       data-lock-message="<?= e($lc['message'] ?? '') ?>"
                       data-lock-plan-name="<?= e($lc['plan_name'] ?? '') ?>"
                       aria-label="<?= e($item['label']) ?> — feature locked"
                       <?= $active ? 'aria-current="page"' : '' ?>>
                        <span class="snav-icon" aria-hidden="true"><?= $item['icon'] ?></span>
                        <span><?= e($item['label']) ?></span>
                        <span class="snav-lock" aria-hidden="true"><?= $icons['lock'] ?></span>
                    </a>
                <?php else: ?>
                    <a class="snav-link<?= $active ? ' is-active' : '' ?>"
                       href="<?= e($item['path']) ?>"
                       aria-label="<?= e($item['label']) ?>"
                       <?= $active ? 'aria-current="page"' : '' ?>>
                        <span class="snav-icon" aria-hidden="true"><?= $item['icon'] ?></span>
                        <span><?= e($item['label']) ?></span>
                    </a>
                <?php endif; ?>
                </li>
            <?php endforeach; ?>
            </ul>

        </div>
        <?php endforeach; ?>

    </nav>

    <!-- Account footer -->
    <div class="sidebar-footer">
        <?php if ($authUser): ?>
        <div class="sidebar-user">
            <span class="sidebar-user-name"><?= e((string)($authUser['username'] ?? 'User')) ?></span>
            <span class="sidebar-user-role"><?= e((string)($authUser['role_name'] ?? 'No role')) ?></span>
        </div>
        <?php endif; ?>
        <a class="snav-footer-link<?= str_starts_with($currentPath, '/change-password') ? ' is-active' : '' ?>"
           href="/change-password" aria-label="Change your password">
            <span class="snav-icon" aria-hidden="true"><?= $icons['key'] ?></span>
            <span>Change Password</span>
        </a>
        <a class="snav-footer-link snav-footer-logout" href="/logout" aria-label="Sign out">
            <span class="snav-icon" aria-hidden="true"><?= $icons['logout'] ?></span>
            <span>Sign out</span>
        </a>
    </div>

</aside>

<script>
(function () {
    'use strict';
    var KEY = 'hris_snav_open'; // plain string: open section id, or ''

    function load()          { try { return localStorage.getItem(KEY) || ''; } catch (_) { return ''; } }
    function persist(openId) { try { localStorage.setItem(KEY, openId); }     catch (_) {} }

    function openSec(sec, btn, chev) {
        sec.classList.add('is-open');
        if (chev) chev.classList.add('is-open');
        if (btn)  btn.setAttribute('aria-expanded', 'true');
    }

    function closeSec(sec, btn, chev) {
        sec.classList.remove('is-open');
        if (chev) chev.classList.remove('is-open');
        if (btn)  btn.setAttribute('aria-expanded', 'false');
    }

    var all = Array.from(document.querySelectorAll('.snav-section'));
    var hasPhpActive = all.some(function (s) { return s.classList.contains('is-open'); });
    var saved = load();

    all.forEach(function (sec) {
        var id   = sec.dataset.section;
        var btn  = sec.querySelector('.snav-toggle');
        var chev = sec.querySelector('.snav-chevron');

        // Sync aria + chevron for PHP-set active section
        if (sec.classList.contains('is-open')) {
            if (chev) chev.classList.add('is-open');
            if (btn)  btn.setAttribute('aria-expanded', 'true');
        } else if (!hasPhpActive && saved === id) {
            // No active page section — restore last saved
            openSec(sec, btn, chev);
        }

        // Accordion click: close all, then open clicked (or collapse if same)
        btn.addEventListener('click', function () {
            var opening = !sec.classList.contains('is-open');
            all.forEach(function (other) {
                closeSec(other,
                    other.querySelector('.snav-toggle'),
                    other.querySelector('.snav-chevron'));
            });
            if (opening) { openSec(sec, btn, chev); persist(id); }
            else         { persist(''); }
        });
    });
}());
</script>

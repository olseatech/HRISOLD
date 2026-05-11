<?php
declare(strict_types=1);

use App\Core\Auth;

$currentPath = (string) (parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH) ?: '/');
$authUser    = Auth::user();

/* ─────────────────────────────────────────────────────────────
   Inline SVG helpers
   ───────────────────────────────────────────────────────────── */
// Coloured section-header icon
$sectionIcon = static function (string $paths, string $color, string $vb = '0 0 20 20'): string {
    return '<svg viewBox="' . $vb . '" width="18" height="18" fill="none"
            stroke="' . $color . '" stroke-width="1.75"
            stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $paths . '</svg>';
};

// Small monochrome sub-item bullet icon
$subIcon = static function (string $paths, string $vb = '0 0 20 20'): string {
    return '<svg viewBox="' . $vb . '" width="14" height="14" fill="none"
            stroke="currentColor" stroke-width="1.75"
            stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $paths . '</svg>';
};

// Chevron (shared)
$chevronSvg = '<svg viewBox="0 0 16 16" width="14" height="14" fill="none"
    stroke="currentColor" stroke-width="2.2" stroke-linecap="round"
    stroke-linejoin="round" aria-hidden="true"><polyline points="4 6 8 10 12 6"/></svg>';

// Lock badge for subscription-locked items
$lockSvg = '<svg viewBox="0 0 16 16" width="12" height="12" fill="none"
    stroke="currentColor" stroke-width="2" stroke-linecap="round"
    stroke-linejoin="round" aria-hidden="true">
    <rect x="2.5" y="7" width="11" height="8" rx="1"/>
    <path d="M5 7V4.5a3 3 0 0 1 6 0V7"/>
</svg>';

/* ─────────────────────────────────────────────────────────────
   Section-header icons  (coloured, unique per section)
   ───────────────────────────────────────────────────────────── */
$secIcons = [
    // amber – dashboard (standalone)
    'dashboard'  => $sectionIcon('<rect x="2" y="2" width="7" height="8"/><rect x="11" y="2" width="7" height="5"/><rect x="2" y="12" width="7" height="6"/><rect x="11" y="9" width="7" height="9"/>', '#d97706'),
    // blue – workforce / employees
    'workforce'  => $sectionIcon('<circle cx="10" cy="6.5" r="3"/><path d="M3.5 17c0-3.3 2.9-6 6.5-6s6.5 2.7 6.5 6"/>', '#0b3d91'),
    // teal – time & leave
    'time'       => $sectionIcon('<rect x="3" y="4" width="14" height="13" rx="1"/><path d="M3 8h14M7 2v3M13 2v3"/>', '#0f766e'),
    // purple – payroll
    'payroll'    => $sectionIcon('<rect x="2" y="5" width="16" height="11" rx="1"/><circle cx="10" cy="10.5" r="2.2"/><path d="M5 8.5v4M15 8.5v4"/>', '#7c3aed'),
    // red – compliance
    'compliance' => $sectionIcon('<path d="M10 2L3 5v5c0 4.4 3 8.1 7 9 4-.9 7-4.6 7-9V5l-7-3z"/><path d="M7 10l2 2 4-4"/>', '#dc2626'),
    // slate – administration
    'admin'      => $sectionIcon('<circle cx="10" cy="10" r="2.4"/><path d="M10 2.5v2M10 15.5v2M2.5 10h2M15.5 10h2M4.7 4.7l1.4 1.4M13.9 13.9l1.4 1.4M4.7 15.3l1.4-1.4M13.9 6.1l1.4-1.4"/>', '#64748b'),
    // gold – key / account actions
    'key'        => $sectionIcon('<circle cx="7.5" cy="10" r="4"/><path d="M10.5 10h7M15 10v2.5"/>', '#b68409'),
    // red – logout
    'logout'     => $sectionIcon('<path d="M8 3.5H4.5a1 1 0 0 0-1 1v11a1 1 0 0 0 1 1H8"/><path d="M12 6.5 15.5 10 12 13.5M7 10h8.5"/>', '#b42318'),
];

/* ─────────────────────────────────────────────────────────────
   Sub-item icons  (monochrome, 14 × 14)
   ───────────────────────────────────────────────────────────── */
$sub = [
    'employees'  => $subIcon('<circle cx="10" cy="6.5" r="3"/><path d="M3.5 17c0-3.3 2.9-6 6.5-6s6.5 2.7 6.5 6"/>'),
    'pds'        => $subIcon('<path d="M12 2H5a1 1 0 0 0-1 1v14a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1V6z"/><polyline points="12 2 12 6 16 6"/><line x1="7" y1="9" x2="13" y2="9"/><line x1="7" y1="12" x2="13" y2="12"/><line x1="7" y1="15" x2="10" y2="15"/>'),
    'svc_rec'    => $subIcon('<rect x="3" y="2" width="14" height="16" rx="1"/><path d="M7 6h6M7 9h6M7 12h4"/><circle cx="14.5" cy="14.5" r="3"/><path d="m13.5 14.5 1 1 1.5-1.5"/>'),
    'my_svc'     => $subIcon('<circle cx="8" cy="6" r="3"/><path d="M2 17c0-3.3 2.7-6 6-6"/><path d="M12 12h6M12 15h6M12 18h4"/>'),
    'appt'       => $subIcon('<rect x="3" y="4" width="14" height="13" rx="1"/><path d="M3 8h14M7 2v3M13 2v3M7 12h2M7 15h2M11 12h2"/>'),
    'docs'       => $subIcon('<path d="M11 2H5a1 1 0 0 0-1 1v14a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1V6z"/><polyline points="11 2 11 6 15 6"/>'),
    'attend'     => $subIcon('<circle cx="10" cy="10" r="7"/><path d="M10 6v4l2.5 2"/>'),
    'leave'      => $subIcon('<rect x="3" y="4.5" width="14" height="13" rx="1"/><path d="M3 8h14M7 2.5v3M13 2.5v3"/>'),
    'my_leave'   => $subIcon('<circle cx="10" cy="6.5" r="3"/><path d="M3.5 17c0-3.3 2.9-6 6.5-6s6.5 2.7 6.5 6"/><path d="M13 12.5l1 1 2-2"/>'),
    'holidays'   => $subIcon('<rect x="3" y="4.5" width="14" height="13" rx="1"/><path d="M3 8h14M7 2.5v3M13 2.5v3"/><circle cx="10" cy="13" r="1.5"/>'),
    'payroll'    => $subIcon('<rect x="2" y="5" width="16" height="11" rx="1"/><circle cx="10" cy="10.5" r="2.2"/>'),
    'clearance'  => $subIcon('<path d="M10 2L3 5v5c0 4.4 3 8.1 7 9 4-.9 7-4.6 7-9V5l-7-3z"/><path d="M7 10l2 2 4-4"/>'),
    'reports'    => $subIcon('<rect x="3" y="2" width="14" height="16" rx="1"/><path d="M7 6h6M7 9h4"/><path d="M7 13h2v4H7zM11 11h2v6h-2zM15 9h-1v8h1"/>'),
    'settings'   => $subIcon('<circle cx="10" cy="10" r="2.4"/><path d="M10 2.5v2M10 15.5v2M2.5 10h2M15.5 10h2"/>'),
    'billing'    => $subIcon('<rect x="2" y="4" width="16" height="12" rx="1"/><path d="M2 8h16"/>'),
];

/* ─────────────────────────────────────────────────────────────
   Navigation structure
   ───────────────────────────────────────────────────────────── */
$standalone = [
    ['path' => '/', 'label' => 'Dashboard', 'permission' => 'dashboard.view', 'icon' => $secIcons['dashboard']],
];

$sections = [
    [
        'id'    => 'workforce',
        'label' => 'Workforce',
        'icon'  => $secIcons['workforce'],
        'items' => [
            ['path' => '/employees',        'label' => 'Employees',          'permission' => 'employees.view',       'icon' => $sub['employees']],
            ['path' => '/pds',              'label' => 'PDS',                'permission' => 'pds.view',             'icon' => $sub['pds']],
            ['path' => '/service-records',  'label' => 'Service Records',    'permission' => 'service_records.view', 'icon' => $sub['svc_rec']],
            ['path' => '/my-service-record','label' => 'My Service Record',  'permission' => 'service_records.view', 'icon' => $sub['my_svc']],
            ['path' => '/appointments',     'label' => 'Appointments',       'permission' => 'appointments.view',    'icon' => $sub['appt']],
            ['path' => '/documents',        'label' => '201 Documents',      'permission' => 'documents.view',       'icon' => $sub['docs']],
        ],
    ],
    [
        'id'    => 'time',
        'label' => 'Time & Leave',
        'icon'  => $secIcons['time'],
        'items' => [
            ['path' => '/attendance', 'label' => 'Attendance', 'permission' => 'attendance.view', 'icon' => $sub['attend']],
            ['path' => '/leave',      'label' => 'Leave',      'permission' => 'leave.view',      'icon' => $sub['leave']],
            ['path' => '/my-leave',   'label' => 'My Leave',   'permission' => 'leave.my_leave',  'icon' => $sub['my_leave']],
            ['path' => '/holidays',   'label' => 'Holidays',   'permission' => 'holidays.manage', 'icon' => $sub['holidays']],
        ],
    ],
    [
        'id'    => 'payroll',
        'label' => 'Payroll',
        'icon'  => $secIcons['payroll'],
        'items' => [
            ['path' => '/payroll', 'label' => 'Payroll', 'permission' => 'payroll.view', 'icon' => $sub['payroll']],
        ],
    ],
    [
        'id'    => 'compliance',
        'label' => 'Compliance',
        'icon'  => $secIcons['compliance'],
        'items' => [
            ['path' => '/clearances', 'label' => 'Clearance', 'permission' => 'clearances.view', 'icon' => $sub['clearance']],
            ['path' => '/reports',    'label' => 'Reports',   'permission' => 'reports.view',    'icon' => $sub['reports']],
        ],
    ],
    [
        'id'    => 'admin',
        'label' => 'Administration',
        'icon'  => $secIcons['admin'],
        'items' => [
            ['path' => '/settings', 'label' => 'Settings', 'permission' => 'settings.manage', 'icon' => $sub['settings']],
            ['path' => '/billing',  'label' => 'Billing',  'permission' => 'billing.view',    'icon' => $sub['billing']],
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

    <!-- ── Brand header ─────────────────────────────────────── -->
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

    <!-- ── Scrollable nav body ───────────────────────────────── -->
    <nav class="snav" aria-label="Main navigation">

        <!-- Standalone items (no section) -->
        <?php foreach ($standalone as $item):
            if (!can((string)($item['permission'] ?? ''))) continue;
            $active = $isActive($item['path']);
            $lc     = nav_feature_lock_context($item['path'], $authUser);
            $locked = (bool)($lc['is_locked'] ?? false);
        ?>
        <?php if ($locked): ?>
            <a class="snav-standalone snav-link-locked<?= $active ? ' is-active' : '' ?>"
               href="<?= e($item['path']) ?>"
               data-lock-trigger
               data-lock-feature-label="<?= e($lc['feature_label'] ?? $item['label']) ?>"
               data-lock-message="<?= e($lc['message'] ?? '') ?>"
               data-lock-plan-name="<?= e($lc['plan_name'] ?? '') ?>"
               aria-label="<?= e($item['label']) ?> — feature locked"
               <?= $active ? 'aria-current="page"' : '' ?>>
                <span class="snav-item-icon"><?= $item['icon'] ?></span>
                <span class="snav-item-label"><?= e($item['label']) ?></span>
                <span class="snav-item-lock" aria-hidden="true"><?= $lockSvg ?></span>
            </a>
        <?php else: ?>
            <a class="snav-standalone<?= $active ? ' is-active' : '' ?>"
               href="<?= e($item['path']) ?>"
               aria-label="<?= e($item['label']) ?>"
               <?= $active ? 'aria-current="page"' : '' ?>>
                <span class="snav-item-icon"><?= $item['icon'] ?></span>
                <span class="snav-item-label"><?= e($item['label']) ?></span>
            </a>
        <?php endif; ?>
        <?php endforeach; ?>

        <?php if (!empty($standalone)): ?>
        <div class="snav-divider" role="separator"></div>
        <?php endif; ?>

        <!-- Collapsible sections -->
        <?php foreach ($sections as $section):
            $visItems = array_filter($section['items'],
                static fn($i) => can((string)($i['permission'] ?? '')));
            if (empty($visItems)) continue;

            $hasActive = $sectionHasActive($section);
            $sectId    = 'snav-' . $section['id'];
        ?>
        <div class="snav-section <?= $hasActive ? 'is-open' : '' ?>"
             data-section="<?= e($section['id']) ?>">

            <button class="snav-toggle"
                    type="button"
                    aria-controls="<?= e($sectId) ?>"
                    aria-expanded="<?= $hasActive ? 'true' : 'false' ?>">
                <span class="snav-toggle-icon"><?= $section['icon'] ?></span>
                <span class="snav-toggle-label"><?= e($section['label']) ?></span>
                <span class="snav-toggle-chevron <?= $hasActive ? 'is-open' : '' ?>"
                      aria-hidden="true"><?= $chevronSvg ?></span>
            </button>

            <ul class="snav-items" id="<?= e($sectId) ?>" role="list">
            <?php foreach ($visItems as $item):
                $active = $isActive($item['path']);
                $lc     = nav_feature_lock_context($item['path'], $authUser);
                $locked = (bool)($lc['is_locked'] ?? false);
            ?>
                <li role="listitem">
                <?php if ($locked): ?>
                    <a class="snav-link snav-link-locked<?= $active ? ' is-active' : '' ?>"
                       href="<?= e($item['path']) ?>"
                       data-lock-trigger
                       data-lock-feature-label="<?= e($lc['feature_label'] ?? $item['label']) ?>"
                       data-lock-message="<?= e($lc['message'] ?? '') ?>"
                       data-lock-plan-name="<?= e($lc['plan_name'] ?? '') ?>"
                       aria-label="<?= e($item['label']) ?> — feature locked"
                       <?= $active ? 'aria-current="page"' : '' ?>>
                        <span class="snav-link-icon"><?= $item['icon'] ?></span>
                        <span><?= e($item['label']) ?></span>
                        <span class="snav-item-lock" aria-hidden="true"><?= $lockSvg ?></span>
                    </a>
                <?php else: ?>
                    <a class="snav-link<?= $active ? ' is-active' : '' ?>"
                       href="<?= e($item['path']) ?>"
                       aria-label="<?= e($item['label']) ?>"
                       <?= $active ? 'aria-current="page"' : '' ?>>
                        <span class="snav-link-icon"><?= $item['icon'] ?></span>
                        <span><?= e($item['label']) ?></span>
                    </a>
                <?php endif; ?>
                </li>
            <?php endforeach; ?>
            </ul>
        </div>
        <?php endforeach; ?>

    </nav>

    <!-- ── Account footer ───────────────────────────────────── -->
    <div class="sidebar-footer">
        <?php if ($authUser): ?>
        <div class="sidebar-user">
            <span class="sidebar-user-name"><?= e((string)($authUser['username'] ?? 'User')) ?></span>
            <span class="sidebar-user-role"><?= e((string)($authUser['role_name'] ?? 'No role')) ?></span>
        </div>
        <?php endif; ?>

        <a class="snav-footer-link<?= str_starts_with($currentPath, '/change-password') ? ' is-active' : '' ?>"
           href="/change-password"
           aria-label="Change your password">
            <span class="snav-item-icon"><?= $secIcons['key'] ?></span>
            <span>Change Password</span>
        </a>

        <a class="snav-footer-link snav-footer-logout"
           href="/logout"
           aria-label="Sign out">
            <span class="snav-item-icon"><?= $secIcons['logout'] ?></span>
            <span>Sign out</span>
        </a>
    </div>

</aside>

<script>
(function () {
    'use strict';
    var KEY = 'hris_snav_open'; // plain string: open section-id, or ''

    function load()          { try { return localStorage.getItem(KEY) || ''; } catch (_) { return ''; } }
    function persist(openId) { try { localStorage.setItem(KEY, openId); }     catch (_) {} }

    function openSection(sec, btn, chevron) {
        sec.classList.add('is-open');
        if (chevron) chevron.classList.add('is-open');
        if (btn)     btn.setAttribute('aria-expanded', 'true');
    }

    function closeSection(sec, btn, chevron) {
        sec.classList.remove('is-open');
        if (chevron) chevron.classList.remove('is-open');
        if (btn)     btn.setAttribute('aria-expanded', 'false');
    }

    var allSections = Array.from(document.querySelectorAll('.snav-section'));

    // Phase 1 — initial render
    // PHP marks the active section .is-open; if none is active (e.g. Dashboard),
    // restore the last-saved section from localStorage.
    var hasPhpActive = allSections.some(function (s) { return s.classList.contains('is-open'); });
    var saved = load();

    allSections.forEach(function (sec) {
        var id      = sec.dataset.section;
        var btn     = sec.querySelector('.snav-toggle');
        var chevron = sec.querySelector('.snav-toggle-chevron');

        if (sec.classList.contains('is-open')) {
            // Sync chevron + aria for the PHP-marked active section
            if (chevron) chevron.classList.add('is-open');
            if (btn)     btn.setAttribute('aria-expanded', 'true');
        } else if (!hasPhpActive && saved === id) {
            // No active section on this page — restore the saved one
            openSection(sec, btn, chevron);
        }

        // Phase 2 — accordion click handler
        btn.addEventListener('click', function () {
            var isNowOpen = !sec.classList.contains('is-open');

            // Close every section first
            allSections.forEach(function (other) {
                closeSection(other,
                    other.querySelector('.snav-toggle'),
                    other.querySelector('.snav-toggle-chevron'));
            });

            // Open the clicked section (unless it was already open — acts as a toggle)
            if (isNowOpen) {
                openSection(sec, btn, chevron);
                persist(id);
            } else {
                persist(''); // all sections collapsed
            }
        });
    });
}());
</script>

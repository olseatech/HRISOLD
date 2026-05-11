<?php
$company = is_array($company ?? null) ? $company : [];
$system = is_array($system ?? null) ? $system : [];
$roleRows = is_array($roles ?? null) ? $roles : [];
$superAdminOnlyMode = super_admin_only_mode_enabled();

if ($superAdminOnlyMode) {
    $roleRows = array_values(array_filter($roleRows, static function (array $role): bool {
        return (string) ($role['role_name'] ?? '') === 'Super Admin';
    }));
}

$activeRoles = 0;
$inactiveRoles = 0;
foreach ($roleRows as $role) {
    if ((int) ($role['is_active'] ?? 0) === 1) {
        $activeRoles++;
    } else {
        $inactiveRoles++;
    }
}
?>
<section class="page">
    <header class="page-banner">
        <div class="page-banner-copy">
            <p class="page-banner-kicker">Settings</p>
            <h2 class="page-banner-title">Configure company, system, and role controls</h2>
            <p class="page-banner-sub">Maintain organization details, global preferences, and role activation from a single admin control panel.</p>
            <div class="page-banner-meta">
                <span class="badge badge-blue">Company profile</span>
                <span class="badge badge-teal">System preferences</span>
                <span class="badge">Role lifecycle</span>
            </div>
        </div>
        <div class="page-banner-actions">
            <a class="btn" href="/settings">Refresh</a>
        </div>
    </header>
    <?php require __DIR__ . '/../partials/alerts.php'; ?>
    <section class="stat-grid" aria-label="Settings summary">
        <article class="stat">
            <span class="stat-label">Total roles</span>
            <span class="stat-value"><?= e((string) count($roleRows)) ?></span>
            <span class="stat-note">Configured in system</span>
        </article>
        <article class="stat stat-teal">
            <span class="stat-label">Active roles</span>
            <span class="stat-value"><?= e((string) $activeRoles) ?></span>
            <span class="stat-note">Currently enabled</span>
        </article>
        <article class="stat stat-red">
            <span class="stat-label">Inactive roles</span>
            <span class="stat-value"><?= e((string) $inactiveRoles) ?></span>
            <span class="stat-note">Disabled</span>
        </article>
    </section>
    <section class="card">
        <div class="card-head">
            <h3>Company Profile</h3>
            <p>Update organization identity and contact details used across the system.</p>
        </div>
        <form class="form-grid" method="post" action="/settings/company" novalidate>
            <input type="hidden" name="_csrf" value="<?= e((string) ($csrf ?? '')) ?>">
            <label>
                <span>Company Name</span>
                <input type="text" name="company_name" value="<?= e((string) ($company['company_name'] ?? '')) ?>" required>
            </label>
            <label>
                <span>Email</span>
                <input type="email" name="email" value="<?= e((string) ($company['email'] ?? '')) ?>">
            </label>
            <label>
                <span>Phone</span>
                <input type="text" name="phone" value="<?= e((string) ($company['phone'] ?? '')) ?>">
            </label>
            <label>
                <span>Website</span>
                <input type="text" name="website" value="<?= e((string) ($company['website'] ?? '')) ?>">
            </label>
            <label>
                <span>Logo Path</span>
                <input type="text" name="logo_path" value="<?= e((string) ($company['logo_path'] ?? '')) ?>">
            </label>
            <label class="full-width">
                <span>Address</span>
                <textarea name="address" rows="3"><?= e((string) ($company['address'] ?? '')) ?></textarea>
            </label>
            <div class="full-width form-actions">
                <p class="form-hint">Company name is required. Email format is validated on submit.</p>
                <button class="btn btn-primary" type="submit">Save company</button>
            </div>
        </form>
    </section>
    <section class="card">
        <div class="card-head">
            <h3>System Preferences</h3>
            <p>Configure global timezone, date format, and default currency settings.</p>
        </div>
        <form class="form-grid" method="post" action="/settings/system" novalidate>
            <input type="hidden" name="_csrf" value="<?= e((string) ($csrf ?? '')) ?>">
            <label>
                <span>Timezone</span>
                <input type="text" name="timezone" value="<?= e((string) ($system['timezone'] ?? 'Asia/Manila')) ?>" required>
            </label>
            <label>
                <span>Date Format</span>
                <input type="text" name="date_format" value="<?= e((string) ($system['date_format'] ?? 'Y-m-d')) ?>" required>
            </label>
            <label>
                <span>Default Currency</span>
                <input type="text" name="default_currency" value="<?= e((string) ($system['default_currency'] ?? 'PHP')) ?>" required>
            </label>
            <div class="full-width form-actions">
                <p class="form-hint">Applies globally to attendance, leave, and payroll workflows.</p>
                <button class="btn btn-primary" type="submit">Save system</button>
            </div>
        </form>
    </section>
    <!-- Lookup tables quick access -->
    <section class="card" style="margin-bottom:var(--space-5);">
        <div class="card-head">
            <h3>Reference / Lookup Tables</h3>
            <p>Manage dropdown options used throughout the system — departments, positions, leave types, and salary grades.</p>
        </div>
        <div style="display:grid; grid-template-columns:repeat(auto-fill,minmax(220px,1fr)); gap:var(--space-4); padding:var(--space-5);">
            <a href="/settings/departments" class="card card-shine" style="padding:var(--space-4); text-decoration:none; display:flex; align-items:center; gap:12px; border:1px solid var(--border);">
                <div style="width:36px;height:36px;border-radius:10px;background:var(--blue-50);color:var(--blue-600);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
                </div>
                <div><strong style="font-size:13px;display:block;">Departments</strong><span style="font-size:12px;color:var(--text-muted);">Divisions &amp; Units</span></div>
            </a>
            <a href="/settings/designations" class="card card-shine" style="padding:var(--space-4); text-decoration:none; display:flex; align-items:center; gap:12px; border:1px solid var(--border);">
                <div style="width:36px;height:36px;border-radius:10px;background:var(--teal-50);color:var(--teal-600);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                </div>
                <div><strong style="font-size:13px;display:block;">Positions</strong><span style="font-size:12px;color:var(--text-muted);">Designations</span></div>
            </a>
            <a href="/settings/leave-types" class="card card-shine" style="padding:var(--space-4); text-decoration:none; display:flex; align-items:center; gap:12px; border:1px solid var(--border);">
                <div style="width:36px;height:36px;border-radius:10px;background:var(--amber-50);color:var(--amber-600);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M3 10h18M8 2v3M16 2v3"/></svg>
                </div>
                <div><strong style="font-size:13px;display:block;">Leave Types</strong><span style="font-size:12px;color:var(--text-muted);">Vacation, Sick, etc.</span></div>
            </a>
            <a href="/settings/salary-grades" class="card card-shine" style="padding:var(--space-4); text-decoration:none; display:flex; align-items:center; gap:12px; border:1px solid var(--border);">
                <div style="width:36px;height:36px;border-radius:10px;background:var(--green-50,#f0fdf4);color:var(--green-600,#16a34a);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 1 0 0 7h5a3.5 3.5 0 1 1 0 7H6"/></svg>
                </div>
                <div><strong style="font-size:13px;display:block;">Salary Grades</strong><span style="font-size:12px;color:var(--text-muted);">Pay grade ranges</span></div>
            </a>
        </div>
    </section>

    <section class="card">
        <div class="card-head">
            <h3>Roles</h3>
            <p>
                <?= $superAdminOnlyMode
                    ? 'Super Admin-only mode is enabled. Other actor roles are hidden and cannot be reactivated from this panel.'
                    : 'Review permission counts and toggle role availability.' ?>
            </p>
        </div>
        <div class="data-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Role</th>
                        <th>Description</th>
                        <th>Permissions</th>
                        <th>Status</th>
                        <th style="text-align:right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($roleRows === []): ?>
                        <tr>
                            <td colspan="5"><p class="empty-state">No roles found.</p></td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($roleRows as $role): ?>
                            <tr>
                                <td>
                                    <div class="person">
                                        <strong><?= e((string) ($role['role_name'] ?? '-')) ?></strong>
                                        <span>ID: <?= e((string) ($role['id'] ?? '-')) ?></span>
                                    </div>
                                </td>
                                <td><?= e((string) ($role['description'] ?? '-')) ?></td>
                                <td><?= e((string) ((int) ($role['permission_count'] ?? 0))) ?></td>
                                <td>
                                    <span class="set-badge <?= ((int) ($role['is_active'] ?? 0) === 1) ? 'set-badge-active' : 'set-badge-inactive' ?>">
                                        <?= ((int) ($role['is_active'] ?? 0) === 1) ? 'Active' : 'Inactive' ?>
                                    </span>
                                </td>
                                <td style="text-align:right;">
                                    <?php if ($superAdminOnlyMode): ?>
                                        <span class="set-badge set-badge-inactive">Locked by mode</span>
                                    <?php else: ?>
                                        <form method="post" action="/settings/roles/<?= (int) ($role['id'] ?? 0) ?>/toggle" style="display:inline;">
                                            <input type="hidden" name="_csrf" value="<?= e((string) ($csrf ?? '')) ?>">
                                            <button class="set-toggle-btn" type="submit">Toggle</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</section>

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

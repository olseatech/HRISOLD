<?php
$rows         = is_array($rows ?? null) ? $rows : [];
$empList      = is_array($employees ?? null) ? $employees : [];
$currentQ     = (string) ($query ?? '');
$currentEmpId = (string) ($empId ?? '');
$currentPage  = (int) ($page ?? 1);
$pageCount    = (int) ($totalPages ?? 1);
$totalCount   = (int) ($total ?? 0);
$totalCurrent = (int) ($totalCurrent ?? 0);
$totalEmp     = (int) ($totalEmp ?? 0);
$canCreate = can('appointments.create');
$canUpdate = can('appointments.update');
$canDelete = can('appointments.delete');
?>

<section class="page">
    <header class="page-banner">
        <div class="page-banner-copy">
            <p class="page-banner-kicker">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                Appointments
            </p>
            <h2 class="page-banner-title">Appointment Records</h2>
            <p class="page-banner-sub">Formal appointment documents — original, promotional, transfer, and other appointment types.</p>
            <div class="page-banner-meta">
                <span class="badge badge-blue">RBAC Active</span>
                <span class="badge">Audit Logged</span>
            </div>
        </div>
        <div class="page-banner-actions">
            <a class="btn btn-secondary" href="/appointments">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 1 1-9-9c2.52 0 4.93 1 6.74 2.74L21 8"/><path d="M21 3v5h-5"/></svg>
                Refresh
            </a>
            <?php if ($canCreate): ?>
                <a class="btn btn-primary" href="/appointments/create">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Add Appointment
                </a>
            <?php endif; ?>
        </div>
    </header>

    <?php require __DIR__ . '/../partials/alerts.php'; ?>

    <section class="stat-grid" aria-label="Appointments Summary">
        <article class="stat card-shine">
            <div class="stat-icon is-blue">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            </div>
            <div class="stat-data">
                <span class="stat-label">Total Appointments</span>
                <span class="stat-value"><?= e((string) $totalCount) ?></span>
                <span class="stat-note">Matching filters</span>
            </div>
        </article>
        <article class="stat stat-teal card-shine">
            <div class="stat-icon is-teal">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </div>
            <div class="stat-data">
                <span class="stat-label">Current Appointments</span>
                <span class="stat-value"><?= e((string) $totalCurrent) ?></span>
                <span class="stat-note">Active positions</span>
            </div>
        </article>
        <article class="stat stat-gold card-shine">
            <div class="stat-icon" style="background:var(--amber-50); color:var(--amber-600);">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
            <div class="stat-data">
                <span class="stat-label">Employees Covered</span>
                <span class="stat-value"><?= e((string) $totalEmp) ?></span>
                <span class="stat-note">With at least 1 appointment</span>
            </div>
        </article>
    </section>

    <section class="card overflow-hidden">
        <div class="card-header">
            <div class="card-header-copy">
                <h3>Appointment List</h3>
                <p>Filter by employee name, code, or position.</p>
            </div>
        </div>

        <div class="toolbar glass-toolbar">
            <form method="get" action="/appointments" class="filter-bar" role="search" style="display:flex; width:100%; gap:var(--space-3); align-items:flex-end; flex-wrap:wrap;">
                <label class="filter-field">
                    <span>Search</span>
                    <input type="text" name="q" value="<?= e($currentQ) ?>" placeholder="Name, code, or position">
                </label>
                <label class="filter-field">
                    <span>Employee</span>
                    <select name="employee_id">
                        <option value="">All employees</option>
                        <?php foreach ($empList as $emp): ?>
                            <option value="<?= (int) ($emp['id'] ?? 0) ?>" <?= $currentEmpId === (string) ($emp['id'] ?? '') ? 'selected' : '' ?>>
                                <?= e((string) ($emp['employee_code'] ?? '')) ?> &mdash; <?= e((string) ($emp['last_name'] ?? '')) ?>, <?= e((string) ($emp['first_name'] ?? '')) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <button class="btn btn-primary" type="submit">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                    Apply
                </button>
                <?php if ($currentQ !== '' || $currentEmpId !== ''): ?>
                    <a class="btn btn-secondary" href="/appointments">Clear</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="table-wrap no-border no-radius shadow-none">
            <table class="table data-table interactive-rows">
                <thead>
                    <tr>
                        <th scope="col">Employee</th>
                        <th scope="col">Position Title</th>
                        <th scope="col">Type</th>
                        <th scope="col">Employment Status</th>
                        <th scope="col">Effectivity Date</th>
                        <th scope="col">Status</th>
                        <th scope="col" style="text-align:right;">Operations</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($rows === []): ?>
                        <tr>
                            <td colspan="7">
                                <div class="table-empty">
                                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" style="opacity:.25; margin-bottom:12px;"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                    <p>No appointments found<?= $currentQ !== '' ? ' matching "' . e($currentQ) . '"' : '' ?>.</p>
                                    <?php if ($canCreate): ?>
                                        <a class="btn btn-primary" href="/appointments/create">Add the first appointment</a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($rows as $row): ?>
                            <?php
                            $isCurrent   = (bool) ($row['is_current'] ?? false);
                            $empName     = trim(($row['emp_last'] ?? '') . ', ' . ($row['emp_first'] ?? ''));
                            $statusClass = $isCurrent ? 'badge-success' : 'badge-neutral';
                            $statusLabel = $isCurrent ? 'Current' : 'Past';
                            ?>
                            <tr>
                                <td>
                                    <div class="person">
                                        <strong><?= e($empName !== ', ' ? $empName : '-') ?></strong>
                                        <span><?= e((string) ($row['employee_code'] ?? '-')) ?></span>
                                    </div>
                                </td>
                                <td>
                                    <strong><?= e((string) ($row['position_title'] ?? '-')) ?></strong>
                                    <?php if (($row['office_unit'] ?? '') !== ''): ?>
                                        <br><small style="color:var(--text-muted);"><?= e((string) $row['office_unit']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge badge-info" style="font-size:10px;"><?= e((string) ($row['appointment_type'] ?? '-')) ?></span>
                                </td>
                                <td>
                                    <?php if (($row['employment_status'] ?? '') !== ''): ?>
                                        <?= e((string) $row['employment_status']) ?>
                                    <?php else: ?>
                                        <span style="color:var(--text-muted);">—</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= e(format_date((string) ($row['effectivity_date'] ?? ''))) ?></td>
                                <td><span class="badge <?= e($statusClass) ?>"><?= e($statusLabel) ?></span></td>
                                <td style="text-align:right;">
                                    <div class="row-actions" style="justify-content:flex-end;">
                                        <a href="/appointments/<?= (int) ($row['id'] ?? 0) ?>" class="btn-icon" title="View">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                        </a>
                                        <?php if ($canUpdate): ?>
                                            <a href="/appointments/<?= (int) ($row['id'] ?? 0) ?>/edit" class="btn-icon" title="Edit">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                            </a>
                                        <?php endif; ?>
                                        <?php if ($canDelete): ?>
                                            <form method="post" action="/appointments/<?= (int) ($row['id'] ?? 0) ?>/delete" onsubmit="return confirm('Delete this appointment?');" style="display:inline;">
                                                <input type="hidden" name="_csrf" value="<?= e(\App\Core\CSRF::token()) ?>">
                                                <button type="submit" class="btn-icon text-red" title="Delete">
                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($pageCount > 1): ?>
            <div class="card-footer">
                <?php $base = '/appointments?q=' . urlencode($currentQ) . '&employee_id=' . urlencode($currentEmpId) . '&page='; ?>
                <nav class="pagination" aria-label="Appointments pagination">
                    <?php for ($i = 1; $i <= $pageCount; $i++): ?>
                        <a class="page-link <?= $currentPage === $i ? 'is-active' : '' ?>" href="<?= e($base . $i) ?>"><?= $i ?></a>
                    <?php endfor; ?>
                </nav>
            </div>
        <?php endif; ?>
    </section>
</section>

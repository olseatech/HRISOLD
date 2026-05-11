<?php
$rows          = is_array($rows ?? null) ? $rows : [];
$empList       = is_array($employees ?? null) ? $employees : [];
$statusOptions = is_array($statuses ?? null) ? $statuses : [];
$currentQ      = (string) ($query ?? '');
$currentEmpId  = (string) ($empId ?? '');
$currentStatus = (string) ($currentStatus ?? '');
$currentPage   = (int) ($page ?? 1);
$pageCount     = (int) ($totalPages ?? 1);
$totalCount    = (int) ($total ?? 0);
$totalPending  = (int) ($totalPending ?? 0);
$totalApproved = (int) ($totalApproved ?? 0);
$totalRejected = (int) ($totalRejected ?? 0);
$canCreate = can('clearances.create');
$canUpdate = can('clearances.update');
$canDelete = can('clearances.delete');
$canApprove = can('clearances.approve');

$statusBadge = static function (string $s): string {
    return match($s) {
        'Approved' => 'badge-success',
        'Rejected' => 'badge-danger',
        default    => 'badge-warning',
    };
};
?>

<section class="page">
    <header class="page-banner">
        <div class="page-banner-copy">
            <p class="page-banner-kicker">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/></svg>
                Clearances
            </p>
            <h2 class="page-banner-title">Employee Clearance Registry</h2>
            <p class="page-banner-sub">Manage clearance requests for resignation, retirement, transfer, and other separations.</p>
            <div class="page-banner-meta">
                <span class="badge badge-blue">RBAC Active</span>
                <span class="badge">Audit Logged</span>
            </div>
        </div>
        <div class="page-banner-actions">
            <a class="btn btn-secondary" href="/clearances">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 1 1-9-9c2.52 0 4.93 1 6.74 2.74L21 8"/><path d="M21 3v5h-5"/></svg>
                Refresh
            </a>
            <?php if ($canCreate): ?>
                <a class="btn btn-primary" href="/clearances/create">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    New Clearance
                </a>
            <?php endif; ?>
        </div>
    </header>

    <?php require __DIR__ . '/../partials/alerts.php'; ?>

    <section class="stat-grid" aria-label="Clearance Summary">
        <article class="stat card-shine">
            <div class="stat-icon is-blue">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            </div>
            <div class="stat-data">
                <span class="stat-label">Total Requests</span>
                <span class="stat-value"><?= e((string) $totalCount) ?></span>
                <span class="stat-note">Matching filters</span>
            </div>
        </article>
        <article class="stat stat-gold card-shine">
            <div class="stat-icon" style="background:var(--amber-50); color:var(--amber-600);">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            </div>
            <div class="stat-data">
                <span class="stat-label">Pending</span>
                <span class="stat-value"><?= e((string) $totalPending) ?></span>
                <span class="stat-note">Awaiting action</span>
            </div>
        </article>
        <article class="stat stat-teal card-shine">
            <div class="stat-icon is-teal">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </div>
            <div class="stat-data">
                <span class="stat-label">Approved</span>
                <span class="stat-value"><?= e((string) $totalApproved) ?></span>
                <span class="stat-note">Cleared</span>
            </div>
        </article>
    </section>

    <section class="card overflow-hidden">
        <div class="card-header">
            <div class="card-header-copy">
                <h3>Clearance Requests</h3>
                <p>Filter by employee name, type, or status. Actions available per row based on permissions.</p>
            </div>
        </div>

        <div class="toolbar glass-toolbar">
            <form method="get" action="/clearances" class="filter-bar" role="search" style="display:flex; width:100%; gap:var(--space-3); align-items:flex-end; flex-wrap:wrap;">
                <label class="filter-field">
                    <span>Search</span>
                    <input type="text" name="q" value="<?= e($currentQ) ?>" placeholder="Employee name or code">
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
                <label class="filter-field">
                    <span>Status</span>
                    <select name="status">
                        <option value="">All statuses</option>
                        <?php foreach ($statusOptions as $s): ?>
                            <option value="<?= e($s) ?>" <?= $currentStatus === $s ? 'selected' : '' ?>><?= e($s) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <button class="btn btn-primary" type="submit">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                    Apply
                </button>
                <?php if ($currentQ !== '' || $currentEmpId !== '' || $currentStatus !== ''): ?>
                    <a class="btn btn-secondary" href="/clearances">Clear</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="table-wrap no-border no-radius shadow-none">
            <table class="table data-table interactive-rows">
                <thead>
                    <tr>
                        <th scope="col">Employee</th>
                        <th scope="col">Type</th>
                        <th scope="col">Request Date</th>
                        <th scope="col">Status</th>
                        <th scope="col">Processed</th>
                        <th scope="col" style="text-align:right;">Operations</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($rows === []): ?>
                        <tr>
                            <td colspan="6">
                                <div class="table-empty">
                                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" style="opacity:.25; margin-bottom:12px;"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                                    <p>No clearance requests found<?= $currentQ !== '' ? ' matching "' . e($currentQ) . '"' : '' ?>.</p>
                                    <?php if ($canCreate): ?>
                                        <a class="btn btn-primary" href="/clearances/create">Create the first clearance</a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($rows as $row): ?>
                            <?php
                            $empName  = trim(($row['emp_last'] ?? '') . ', ' . ($row['emp_first'] ?? ''));
                            $rowStatus = (string) ($row['status'] ?? 'Pending');
                            ?>
                            <tr>
                                <td>
                                    <div class="person">
                                        <strong><?= e($empName !== ', ' ? $empName : '-') ?></strong>
                                        <span><?= e((string) ($row['employee_code'] ?? '-')) ?></span>
                                    </div>
                                </td>
                                <td><?= e((string) ($row['clearance_type'] ?? '-')) ?></td>
                                <td><?= e(format_date((string) ($row['request_date'] ?? ''))) ?></td>
                                <td><span class="badge <?= e($statusBadge($rowStatus)) ?>"><?= e($rowStatus) ?></span></td>
                                <td>
                                    <?php if (($row['processed_at'] ?? '') !== ''): ?>
                                        <small style="color:var(--text-muted);"><?= e(format_date((string) $row['processed_at'])) ?></small>
                                    <?php else: ?>
                                        <span style="color:var(--text-muted);">—</span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align:right;">
                                    <div class="row-actions" style="justify-content:flex-end;">
                                        <a href="/clearances/<?= (int) ($row['id'] ?? 0) ?>" class="btn-icon" title="View">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                        </a>
                                        <?php if ($canUpdate && $rowStatus === 'Pending'): ?>
                                            <a href="/clearances/<?= (int) ($row['id'] ?? 0) ?>/edit" class="btn-icon" title="Edit">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                            </a>
                                        <?php endif; ?>
                                        <?php if ($canDelete): ?>
                                            <form method="post" action="/clearances/<?= (int) ($row['id'] ?? 0) ?>/delete" onsubmit="return confirm('Delete this clearance request?');" style="display:inline;">
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
                <?php $base = '/clearances?q=' . urlencode($currentQ) . '&employee_id=' . urlencode($currentEmpId) . '&status=' . urlencode($currentStatus) . '&page='; ?>
                <nav class="pagination" aria-label="Clearance pagination">
                    <?php for ($i = 1; $i <= $pageCount; $i++): ?>
                        <a class="page-link <?= $currentPage === $i ? 'is-active' : '' ?>" href="<?= e($base . $i) ?>"><?= $i ?></a>
                    <?php endfor; ?>
                </nav>
            </div>
        <?php endif; ?>
    </section>
</section>

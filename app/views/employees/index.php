<?php
$employeeRows = is_array($employees ?? null) ? $employees : [];
$currentQuery = (string) ($query ?? '');
$currentStatus = (string) ($status ?? '');
$statusList = is_array($statusOptions ?? null) ? $statusOptions : [];
$currentPage = (int) ($page ?? 1);
$pageCount = (int) ($totalPages ?? 1);
$totalCount = (int) ($total ?? 0);
$canCreateEmployee = can('employees.create');
$canUpdateEmployee = can('employees.update');
$canDeleteEmployee = can('employees.delete');

$statusClassMap = [
    'active'     => 'badge-success',
    'probation'  => 'badge-info',
    'on_leave'   => 'badge-warning',
    'resigned'   => 'badge-neutral',
    'terminated' => 'badge-danger',
];
?>

<section class="page">
    <header class="page-banner">
        <div class="page-banner-copy">
            <p class="page-banner-kicker">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                Employee Directory
            </p>
            <h2 class="page-banner-title">Workforce records in one focused view</h2>
            <p class="page-banner-sub">Search across department levels, designation codes, or employment status with high-audit traceability.</p>
            <div class="page-banner-meta">
                <span class="badge badge-blue">Live Roster</span>
                <span class="badge badge-teal">RBAC Active</span>
                <span class="badge">Audit Friendly</span>
            </div>
        </div>
        <div class="page-banner-actions">
            <a class="btn btn-secondary" href="/employees">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 1 1-9-9c2.52 0 4.93 1 6.74 2.74L21 8"/><path d="M21 3v5h-5"/></svg>
                Refresh
            </a>
            <?php if ($canCreateEmployee): ?>
                <a class="btn btn-primary" href="/employees/create">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Enroll Employee
                </a>
            <?php endif; ?>
        </div>
    </header>

    <?php require __DIR__ . '/../partials/alerts.php'; ?>

    <section class="stat-grid" aria-label="Directory Summary">
        <article class="stat card-shine">
            <div class="stat-icon is-blue">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3h18v18H3z"/><path d="M3 9h18"/><path d="M9 3v18"/></svg>
            </div>
            <div class="stat-data">
                <span class="stat-label">Total Records</span>
                <span class="stat-value"><?= e((string) $totalCount) ?></span>
                <span class="stat-note">Matching Active Filters</span>
            </div>
        </article>
        
        <article class="stat stat-teal card-shine">
            <div class="stat-icon is-teal">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><line x1="10" y1="9" x2="8" y2="9"/></svg>
            </div>
            <div class="stat-data">
                <span class="stat-label">On This Page</span>
                <span class="stat-value"><?= e((string) count($employeeRows)) ?></span>
                <span class="stat-note">Page <?= e((string) $currentPage) ?> of <?= e((string) max(1, $pageCount)) ?></span>
            </div>
        </article>

        <article class="stat stat-gold card-shine">
            <div class="stat-icon is-blue" style="background: var(--amber-50); color: var(--amber-600);">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
            </div>
            <div class="stat-data">
                <span class="stat-label">Filtered Status</span>
                <span class="stat-value"><?= $currentStatus !== '' ? e($currentStatus) : 'All Active' ?></span>
                <span class="stat-note">Current scope selection</span>
            </div>
        </article>
    </section>

    <section class="card overflow-hidden">
        <div class="card-header">
            <div class="card-header-copy">
                <h3>Employee List</h3>
                <p>Filter by code, name, or status. Actions available per row based on permissions.</p>
            </div>
        </div>

        <div class="toolbar glass-toolbar">
            <form method="get" action="/employees" class="filter-bar" role="search" style="display:flex; width:100%; gap:var(--space-3); align-items:flex-end;">
                <label class="filter-field">
                    <span>Search</span>
                    <input type="text" name="q" value="<?= e($currentQuery) ?>" placeholder="Code, name, email, department">
                </label>
                
                <label class="filter-field">
                    <span>Status</span>
                    <select name="status">
                        <option value="">All statuses</option>
                        <?php foreach ($statusList as $item): ?>
                            <option value="<?= e((string) $item) ?>" <?= $currentStatus === $item ? 'selected' : '' ?>><?= e((string) $item) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <button class="btn btn-primary" type="submit">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                    Apply filters
                </button>
            </form>
        </div>

        <div class="table-wrap no-border no-radius shadow-none">
            <table class="table data-table interactive-rows">
                <thead>
                    <tr>
                        <th scope="col">Employee Identity</th>
                        <th scope="col">Department</th>
                        <th scope="col">Designation</th>
                        <th scope="col">Status</th>
                        <th scope="col">Contact</th>
                        <th scope="col" style="text-align:right;">Operations</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($employeeRows === []): ?>
                        <tr>
                            <td colspan="6">
                                <div class="table-empty">
                                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" style="opacity:0.25; margin-bottom:12px;"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                                    <p>No employees found matching the current filters.</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($employeeRows as $employee): ?>
                            <?php
                            $statusValue = (string) ($employee['employment_status'] ?? 'Unknown');
                            $statusKey = strtolower(str_replace([' ', '-'], '_', $statusValue));
                            $statusClass = $statusClassMap[$statusKey] ?? 'badge-neutral';
                            $name = trim((string) (($employee['first_name'] ?? '') . ' ' . ($employee['last_name'] ?? '')));
                            ?>
                            <tr>
                                <td>
                                    <div class="person">
                                        <strong><?= e($name !== '' ? $name : 'Unknown employee') ?></strong>
                                        <span><?= e((string) ($employee['employee_code'] ?? '-')) ?></span>
                                    </div>
                                </td>
                                <td><?= e((string) ($employee['department_name'] ?? '-')) ?></td>
                                <td>
                                    <span class="badge badge-info" style="font-weight:600; font-size:10px; background:var(--blue-50); border:none; text-transform:uppercase;">
                                        <?= e((string) ($employee['designation_name'] ?? '-')) ?>
                                    </span>
                                </td>
                                <td><span class="badge <?= e($statusClass) ?>"><?= e($statusValue) ?></span></td>
                                <td><code class="text-mono"><?= e((string) ($employee['email'] ?? '-')) ?></code></td>
                                <td style="text-align:right;">
                                    <div class="row-actions" style="justify-content:flex-end;">
                                        <a href="/employees/<?= (int) ($employee['id'] ?? 0) ?>" class="btn-icon" title="View Profile">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                        </a>
                                        <?php if ($canUpdateEmployee): ?>
                                            <a href="/employees/<?= (int) ($employee['id'] ?? 0) ?>/edit" class="btn-icon" title="Edit Records">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                            </a>
                                        <?php endif; ?>
                                        <?php if ($canDeleteEmployee): ?>
                                            <form method="post" action="/employees/<?= (int) ($employee['id'] ?? 0) ?>/delete" onsubmit="return confirm('Delete this employee record?');" style="display:inline;">
                                                <input type="hidden" name="_csrf" value="<?= e(App\Core\CSRF::token()) ?>">
                                                <button type="submit" class="btn-icon text-red" title="Delete Record">
                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
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
                <?php $base = '/employees?q=' . urlencode($currentQuery) . '&status=' . urlencode($currentStatus) . '&page='; ?>
                <nav class="pagination" aria-label="Employee pagination">
                    <?php for ($i = 1; $i <= $pageCount; $i++): ?>
                        <a class="page-link <?= $currentPage === $i ? 'is-active' : '' ?>" href="<?= e($base . $i) ?>"><?= $i ?></a>
                    <?php endfor; ?>
                </nav>
            </div>
        <?php endif; ?>
    </section>
</section>

<?php
$leaveRequests  = is_array($requests ?? null) ? $requests : [];
$employeeOptions = is_array($employees ?? null) ? $employees : [];
$statusList     = is_array($statusOptions ?? null) ? $statusOptions : [];
$canRequestLeave = can('leave.request');
$canApproveLeave = can('leave.approve');

$currentQuery  = (string) ($query ?? '');
$currentStatus = (string) ($status ?? '');
$currentPage   = (int) ($page ?? 1);
$pageCount     = (int) ($totalPages ?? 1);
$totalCount    = (int) ($total ?? 0);

$pendingCount  = 0;
$approvedCount = 0;
$rejectedCount = 0;
foreach ($leaveRequests as $entry) {
    $s = (string) ($entry['status'] ?? '');
    if ($s === 'Pending')  $pendingCount++;
    elseif ($s === 'Approved') $approvedCount++;
    elseif ($s === 'Rejected') $rejectedCount++;
}

$statusBadgeClass = static function (string $s): string {
    return match(strtolower($s)) {
        'draft'     => 'badge',
        'pending'   => 'badge badge-warning',
        'approved'  => 'badge badge-success',
        'rejected'  => 'badge badge-danger',
        'cancelled' => 'badge badge-neutral',
        default     => 'badge',
    };
};
?>
<section class="page">
    <header class="page-banner">
        <div class="page-banner-copy">
            <p class="page-banner-kicker">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                Leave Management
            </p>
            <h2 class="page-banner-title">Leave Requests</h2>
            <p class="page-banner-sub">Review, approve, and reject employee leave requests.</p>
            <div class="page-banner-meta">
                <span class="badge badge-blue">Approval Queue</span>
                <span class="badge">Audit Tracked</span>
            </div>
        </div>
        <div class="page-banner-actions">
            <a class="btn btn-secondary" href="/leave">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 1 1-9-9c2.52 0 4.93 1 6.74 2.74L21 8"/><path d="M21 3v5h-5"/></svg>
                Refresh
            </a>
            <?php if ($canRequestLeave): ?>
                <a class="btn btn-primary" href="/leave/create">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    New Request
                </a>
            <?php endif; ?>
        </div>
    </header>

    <?php require __DIR__ . '/../partials/alerts.php'; ?>

    <section class="stat-grid" aria-label="Leave summary">
        <article class="stat card-shine">
            <div class="stat-icon is-blue">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
            </div>
            <div class="stat-data">
                <span class="stat-label">Total</span>
                <span class="stat-value"><?= e((string) $totalCount) ?></span>
                <span class="stat-note">Matching filters</span>
            </div>
        </article>
        <article class="stat stat-gold card-shine">
            <div class="stat-icon" style="background:var(--amber-50); color:var(--amber-600);">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
            </div>
            <div class="stat-data">
                <span class="stat-label">Pending</span>
                <span class="stat-value"><?= e((string) $pendingCount) ?></span>
                <span class="stat-note">Awaiting review</span>
            </div>
        </article>
        <article class="stat stat-teal card-shine">
            <div class="stat-icon is-teal">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m9 11 3 3L22 4"/></svg>
            </div>
            <div class="stat-data">
                <span class="stat-label">Processed</span>
                <span class="stat-value"><?= e((string) ($approvedCount + $rejectedCount)) ?></span>
                <span class="stat-note">Approved or rejected</span>
            </div>
        </article>
    </section>

    <section class="card overflow-hidden">
        <div class="card-header">
            <div class="card-header-copy">
                <h3>Request Queue</h3>
                <p><?= $canApproveLeave ? 'Filter by employee or status, then approve or reject pending items.' : 'Filter by status and review request progress.' ?></p>
            </div>
        </div>

        <div class="toolbar glass-toolbar">
            <form method="get" action="/leave" class="filter-bar" role="search" style="display:flex; width:100%; gap:var(--space-3); align-items:flex-end; flex-wrap:wrap;">
                <label class="filter-field">
                    <span>Search</span>
                    <input type="text" name="q" value="<?= e($currentQuery) ?>" placeholder="Employee name or leave type">
                </label>
                <label class="filter-field">
                    <span>Status</span>
                    <select name="status">
                        <option value="">All statuses</option>
                        <?php foreach ($statusList as $item): ?>
                            <option value="<?= e((string) $item) ?>" <?= ($currentStatus === $item) ? 'selected' : '' ?>><?= e((string) $item) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <button class="btn btn-primary" type="submit">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                    Apply
                </button>
                <?php if ($currentQuery !== '' || $currentStatus !== ''): ?>
                    <a class="btn btn-secondary" href="/leave">Clear</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="table-wrap no-border no-radius shadow-none">
            <table class="data-table interactive-rows">
                <thead>
                    <tr>
                        <th scope="col">Employee</th>
                        <th scope="col">Type</th>
                        <th scope="col">Dates</th>
                        <th scope="col">Days</th>
                        <th scope="col">Status</th>
                        <th scope="col" style="text-align:right;">Operations</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($leaveRequests === []): ?>
                        <tr>
                            <td colspan="6">
                                <div class="table-empty">
                                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" style="opacity:.25; margin-bottom:12px;"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                                    <p>No leave requests found<?= $currentStatus !== '' ? ' with status "' . e($currentStatus) . '"' : '' ?>.</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($leaveRequests as $request): ?>
                            <?php $statusValue = (string) ($request['status'] ?? 'Unknown'); ?>
                            <tr>
                                <td>
                                    <div class="person">
                                        <strong><?= e((string) (($request['first_name'] ?? '') . ' ' . ($request['last_name'] ?? ''))) ?></strong>
                                        <span><?= e((string) ($request['employee_code'] ?? '-')) ?></span>
                                    </div>
                                </td>
                                <td><?= e((string) ($request['type_name'] ?? '-')) ?></td>
                                <td style="font-family:monospace; font-size:12px; white-space:nowrap;">
                                    <?= e((string) ($request['start_date'] ?? '-')) ?> &rarr; <?= e((string) ($request['end_date'] ?? '-')) ?>
                                </td>
                                <td><?= e((string) ($request['total_days'] ?? '-')) ?></td>
                                <td><span class="<?= e($statusBadgeClass($statusValue)) ?>"><?= e($statusValue) ?></span></td>
                                <td style="text-align:right;">
                                    <div class="row-actions" style="justify-content:flex-end;">
                                        <a href="/leave/<?= (int) ($request['id'] ?? 0) ?>" class="btn-icon" title="View">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                        </a>
                                        <?php if ($statusValue === 'Pending' && $canApproveLeave): ?>
                                            <form method="post" action="/leave/<?= (int) ($request['id'] ?? 0) ?>/approve" style="display:inline;">
                                                <input type="hidden" name="_csrf" value="<?= e(\App\Core\CSRF::token()) ?>">
                                                <input type="hidden" name="review_remarks" value="Approved">
                                                <button class="btn-icon text-success" title="Approve" type="submit">
                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                                                </button>
                                            </form>
                                            <form method="post" action="/leave/<?= (int) ($request['id'] ?? 0) ?>/reject" style="display:inline;">
                                                <input type="hidden" name="_csrf" value="<?= e(\App\Core\CSRF::token()) ?>">
                                                <input type="hidden" name="review_remarks" value="Rejected">
                                                <button class="btn-icon text-red" title="Reject" type="submit">
                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
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
                <?php $base = '/leave?q=' . urlencode($currentQuery) . '&status=' . urlencode($currentStatus) . '&page='; ?>
                <nav class="pagination" aria-label="Leave pagination">
                    <?php for ($i = 1; $i <= $pageCount; $i++): ?>
                        <a class="page-link <?= $currentPage === $i ? 'is-active' : '' ?>" href="<?= e($base . $i) ?>"><?= $i ?></a>
                    <?php endfor; ?>
                </nav>
            </div>
        <?php endif; ?>
    </section>
</section>

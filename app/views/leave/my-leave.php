<?php
$leaveRequests = is_array($requests ?? null) ? $requests : [];
$statusList    = is_array($statusOptions ?? null) ? $statusOptions : [];
$currentStatus = (string) ($status ?? '');
$currentPage   = (int) ($page ?? 1);
$pageCount     = (int) ($totalPages ?? 1);
$totalCount    = (int) ($total ?? 0);
$noEmployee    = (bool) ($noEmployee ?? false);
$canRequest    = can('leave.request');

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
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
                Leave Management
            </p>
            <h2 class="page-banner-title">My Leave</h2>
            <p class="page-banner-sub">View and manage your personal leave requests.</p>
            <div class="page-banner-meta">
                <span class="badge badge-blue">Self-Service</span>
            </div>
        </div>
        <div class="page-banner-actions">
            <a class="btn btn-secondary" href="/my-leave">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 1 1-9-9c2.52 0 4.93 1 6.74 2.74L21 8"/><path d="M21 3v5h-5"/></svg>
                Refresh
            </a>
            <?php if ($canRequest && !$noEmployee): ?>
                <a class="btn btn-primary" href="/leave/create">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    New Request
                </a>
            <?php endif; ?>
        </div>
    </header>

    <?php require __DIR__ . '/../partials/alerts.php'; ?>

    <?php if ($noEmployee): ?>
        <div style="margin-top:var(--space-5); padding:var(--space-5); background:var(--amber-50,#fffbeb); border:1px solid var(--amber-200,#fde68a); border-radius:var(--radius-lg);">
            <p style="font-weight:600; color:var(--amber-700);">Account not linked to an employee profile.</p>
            <p style="font-size:13px; color:var(--text-muted); margin-top:4px;">Contact an administrator to link your login account to an employee record before you can file a leave request.</p>
        </div>
    <?php else: ?>

        <section class="stat-grid" aria-label="My leave summary">
            <article class="stat card-shine">
                <div class="stat-icon is-blue">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                </div>
                <div class="stat-data">
                    <span class="stat-label">Total Requests</span>
                    <span class="stat-value"><?= e((string) $totalCount) ?></span>
                    <span class="stat-note">All time</span>
                </div>
            </article>
        </section>

        <section class="card overflow-hidden">
            <div class="card-header">
                <div class="card-header-copy">
                    <h3>My Requests</h3>
                    <p>Filter by status. Click a row to view details.</p>
                </div>
            </div>

            <div class="toolbar glass-toolbar">
                <form method="get" action="/my-leave" class="filter-bar" role="search" style="display:flex; width:100%; gap:var(--space-3); align-items:flex-end; flex-wrap:wrap;">
                    <label class="filter-field">
                        <span>Status</span>
                        <select name="status">
                            <option value="">All statuses</option>
                            <?php foreach ($statusList as $item): ?>
                                <option value="<?= e((string) $item) ?>" <?= ($currentStatus === $item) ? 'selected' : '' ?>><?= e((string) $item) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <button class="btn btn-primary" type="submit">Apply</button>
                    <?php if ($currentStatus !== ''): ?>
                        <a class="btn btn-secondary" href="/my-leave">Clear</a>
                    <?php endif; ?>
                </form>
            </div>

            <div class="table-wrap no-border no-radius shadow-none">
                <table class="data-table interactive-rows">
                    <thead>
                        <tr>
                            <th scope="col">Type</th>
                            <th scope="col">Dates</th>
                            <th scope="col">Days</th>
                            <th scope="col">Status</th>
                            <th scope="col" style="text-align:right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($leaveRequests === []): ?>
                            <tr>
                                <td colspan="5">
                                    <div class="table-empty">
                                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" style="opacity:.25; margin-bottom:12px;"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                                        <p>No leave requests found<?= $currentStatus !== '' ? ' with status "' . e($currentStatus) . '"' : '' ?>.</p>
                                        <?php if ($canRequest): ?>
                                            <a class="btn btn-primary" href="/leave/create">File your first request</a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($leaveRequests as $request): ?>
                                <?php $statusValue = (string) ($request['status'] ?? 'Unknown'); ?>
                                <tr>
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
                                            <?php if ($statusValue === 'Draft'): ?>
                                                <a href="/leave/<?= (int) ($request['id'] ?? 0) ?>/edit" class="btn-icon" title="Edit">
                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                                </a>
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
                    <?php $base = '/my-leave?status=' . urlencode($currentStatus) . '&page='; ?>
                    <nav class="pagination" aria-label="My leave pagination">
                        <?php for ($i = 1; $i <= $pageCount; $i++): ?>
                            <a class="page-link <?= $currentPage === $i ? 'is-active' : '' ?>" href="<?= e($base . $i) ?>"><?= $i ?></a>
                        <?php endfor; ?>
                    </nav>
                </div>
            <?php endif; ?>
        </section>

    <?php endif; ?>
</section>

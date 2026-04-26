<?php
$leaveRequests = is_array($requests ?? null) ? $requests : [];
$employeeOptions = is_array($employees ?? null) ? $employees : [];
$typeOptions = is_array($leaveTypes ?? null) ? $leaveTypes : [];
$statusList = is_array($statusOptions ?? null) ? $statusOptions : [];
$oldInput = is_array($old ?? null) ? $old : [];
$currentEmployeeRecord = is_array($currentEmployee ?? null) ? $currentEmployee : [];
$isSelfServiceUser = (bool) ($isSelfService ?? false);
$canRequestLeave = can('leave.request');
$canApproveLeave = can('leave.approve');

$currentQuery = (string) ($query ?? '');
$currentStatus = (string) ($status ?? '');
$currentPage = (int) ($page ?? 1);
$pageCount = (int) ($totalPages ?? 1);
$totalCount = (int) ($total ?? 0);
$selectedEmployeeId = $isSelfServiceUser
    ? (int) ($currentEmployeeRecord['id'] ?? 0)
    : (int) ($oldInput['employee_id'] ?? 0);
$pendingCount = 0;
$approvedCount = 0;
$rejectedCount = 0;
foreach ($leaveRequests as $entry) {
    $entryStatus = (string) ($entry['status'] ?? '');
    if ($entryStatus === 'Pending') {
        $pendingCount++;
    } elseif ($entryStatus === 'Approved') {
        $approvedCount++;
    } elseif ($entryStatus === 'Rejected') {
        $rejectedCount++;
    }
}
$statusClassMap = [
    'pending' => 'leave-badge-pending',
    'approved' => 'leave-badge-approved',
    'rejected' => 'leave-badge-rejected',
    'cancelled' => 'leave-badge-cancelled',
];
?>
<section class="page">
    <header class="page-banner">
        <div class="page-banner-copy">
            <p class="page-banner-kicker">Leave Management</p>
            <h2 class="page-banner-title">Manage requests and approvals in one workflow</h2>
            <p class="page-banner-sub">Submit leave requests, review status at a glance, and process approvals without context switching.</p>
            <div class="page-banner-meta">
                <span class="badge badge-blue">Request intake</span>
                <span class="badge badge-teal">Approval queue</span>
                <span class="badge">Audit tracked</span>
            </div>
        </div>
        <div class="page-banner-actions">
            <a class="btn" href="/leave">Refresh</a>
        </div>
    </header>
    <?php require __DIR__ . '/../partials/alerts.php'; ?>
    <section class="stat-grid" aria-label="Leave summary">
        <article class="stat">
            <span class="stat-label">Total requests</span>
            <span class="stat-value"><?= e((string) $totalCount) ?></span>
            <span class="stat-note">Matching filters</span>
        </article>
        <article class="stat stat-gold">
            <span class="stat-label">Pending</span>
            <span class="stat-value"><?= e((string) $pendingCount) ?></span>
            <span class="stat-note">Awaiting review</span>
        </article>
        <article class="stat stat-teal">
            <span class="stat-label">Processed</span>
            <span class="stat-value"><?= e((string) ($approvedCount + $rejectedCount)) ?></span>
            <span class="stat-note">Approved or rejected</span>
        </article>
    </section>
    <?php if ($canRequestLeave): ?>
        <section class="card">
            <div class="card-head">
                <h3>Submit Leave Request</h3>
                <p>Create a leave request by selecting employee, type, and date range.</p>
            </div>
            <form class="form-grid" method="post" action="/leave/request" novalidate>
                <input type="hidden" name="_csrf" value="<?= e((string) ($csrf ?? '')) ?>">
                <?php if ($isSelfServiceUser): ?>
                    <?php if ($selectedEmployeeId > 0): ?>
                        <input type="hidden" name="employee_id" value="<?= $selectedEmployeeId ?>">
                        <div class="full-width">
                            <label>Employee</label>
                            <p class="form-hint" style="margin-top:4px;">
                                <strong style="color:var(--color-text-primary);"><?= e((string) (($currentEmployeeRecord['employee_code'] ?? '-') . ' — ' . ($currentEmployeeRecord['first_name'] ?? '') . ' ' . ($currentEmployeeRecord['last_name'] ?? ''))) ?></strong>
                            </p>
                        </div>
                    <?php else: ?>
                        <div class="full-width">
                            <label>Employee</label>
                            <p class="form-hint" style="margin-top:4px;">Your account is not linked to an employee profile.</p>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <label>
                        <span>Employee</span>
                        <select name="employee_id" required>
                            <option value="">Select</option>
                            <?php foreach ($employeeOptions as $employee): ?>
                                <option value="<?= (int) $employee['id'] ?>" <?= ((int) ($oldInput['employee_id'] ?? 0) === (int) $employee['id']) ? 'selected' : '' ?>><?= e((string) ($employee['employee_code'] . ' — ' . $employee['first_name'] . ' ' . $employee['last_name'])) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                <?php endif; ?>
                <label>
                    <span>Leave Type</span>
                    <select name="leave_type_id" required>
                        <option value="">Select</option>
                        <?php foreach ($typeOptions as $leaveType): ?>
                            <option value="<?= (int) $leaveType['id'] ?>" <?= ((int) ($oldInput['leave_type_id'] ?? 0) === (int) $leaveType['id']) ? 'selected' : '' ?>><?= e((string) $leaveType['type_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>
                    <span>Start Date</span>
                    <input type="date" name="start_date" value="<?= e((string) ($oldInput['start_date'] ?? '')) ?>" required>
                </label>
                <label>
                    <span>End Date</span>
                    <input type="date" name="end_date" value="<?= e((string) ($oldInput['end_date'] ?? '')) ?>" required>
                </label>
                <label>
                    <span>Total Days</span>
                    <input type="number" step="0.5" name="total_days" value="<?= e((string) ($oldInput['total_days'] ?? '')) ?>" required>
                </label>
                <label class="full-width">
                    <span>Reason</span>
                    <textarea name="reason" rows="2"><?= e((string) ($oldInput['reason'] ?? '')) ?></textarea>
                </label>
                <?php if (!$isSelfServiceUser || $selectedEmployeeId > 0): ?>
                    <div class="full-width form-actions">
                        <p class="form-hint">Overlapping pending/approved requests are blocked automatically.</p>
                        <button class="btn btn-primary" type="submit">Submit request</button>
                    </div>
                <?php endif; ?>
            </form>
        </section>
    <?php endif; ?>
    <section class="card">
        <div class="card-head">
            <h3>Request Queue</h3>
            <p><?= $canApproveLeave ? 'Filter by employee or status, then approve or reject pending items.' : 'Filter by status and review request progress.' ?></p>
        </div>
        <form class="filter-bar" method="get" action="/leave" role="search">
            <label class="filter-field">
                <span>Search</span>
                <input type="text" name="q" value="<?= e($currentQuery) ?>" placeholder="Employee code, name, or leave type">
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
            <button class="btn btn-primary" type="submit">Apply filters</button>
        </form>
        <div class="data-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Type</th>
                        <th>Dates</th>
                        <th>Days</th>
                        <th>Status</th>
                        <th>Reason</th>
                        <th style="text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($leaveRequests === []): ?>
                        <tr>
                            <td colspan="7"><p class="empty-state">No leave requests found for the current filters.</p></td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($leaveRequests as $request): ?>
                            <?php
                            $statusValue = (string) ($request['status'] ?? 'Unknown');
                            $statusKey = strtolower(str_replace([' ', '-'], '_', $statusValue));
                            $statusClass = $statusClassMap[$statusKey] ?? 'leave-badge-default';
                            ?>
                            <tr>
                                <td>
                                    <div class="person">
                                        <strong><?= e((string) (($request['first_name'] ?? '') . ' ' . ($request['last_name'] ?? ''))) ?></strong>
                                        <span><?= e((string) ($request['employee_code'] ?? '-')) ?></span>
                                    </div>
                                </td>
                                <td><?= e((string) ($request['type_name'] ?? '-')) ?></td>
                                <td style="font-family:var(--font-mono); font-size:0.82rem;"><?= e((string) ($request['start_date'] ?? '-')) ?> &rarr; <?= e((string) ($request['end_date'] ?? '-')) ?></td>
                                <td><?= e((string) ($request['total_days'] ?? '-')) ?></td>
                                <td><span class="leave-badge <?= e($statusClass) ?>"><?= e($statusValue) ?></span></td>
                                <td><?= e((string) ($request['reason'] ?? '-')) ?></td>
                                <td style="text-align:right;">
                                    <div class="leave-actions" style="justify-content:flex-end;">
                                        <?php if ($statusValue === 'Pending'): ?>
                                            <?php if ($canApproveLeave): ?>
                                                <form method="post" action="/leave/<?= (int) ($request['id'] ?? 0) ?>/approve">
                                                    <input type="hidden" name="_csrf" value="<?= e((string) ($csrf ?? '')) ?>">
                                                    <input type="hidden" name="review_remarks" value="Approved">
                                                    <button class="leave-action-btn approve" type="submit">Approve</button>
                                                </form>
                                                <form method="post" action="/leave/<?= (int) ($request['id'] ?? 0) ?>/reject">
                                                    <input type="hidden" name="_csrf" value="<?= e((string) ($csrf ?? '')) ?>">
                                                    <input type="hidden" name="review_remarks" value="Rejected">
                                                    <button class="leave-action-btn reject" type="submit">Reject</button>
                                                </form>
                                            <?php else: ?>
                                                <span style="color:var(--color-text-tertiary);">—</span>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span style="color:var(--color-text-tertiary);">—</span>
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
            <?php $base = '/leave?q=' . urlencode($currentQuery) . '&status=' . urlencode($currentStatus) . '&page='; ?>
            <nav class="pagination" aria-label="Leave pagination" style="margin-top:12px;">
                <?php for ($i = 1; $i <= $pageCount; $i++): ?>
                    <a class="page-link <?= $currentPage === $i ? 'is-active' : '' ?>" href="<?= e($base . $i) ?>"><?= $i ?></a>
                <?php endfor; ?>
            </nav>
        <?php endif; ?>
    </section>
</section>

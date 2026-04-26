<?php
/**
 * @var array $records
 * @var array $employees
 * @var array $statusOptions
 * @var array $old
 * @var string $csrf
 * @var string $date
 * @var string $query
 * @var string $status
 * @var int $page
 * @var int $totalPages
 * @var int $total
 */

$attendanceRecords = is_array($records ?? null) ? $records : [];
$employeeOptions = is_array($employees ?? null) ? $employees : [];
$statusList = is_array($statusOptions ?? null) ? $statusOptions : [];
$oldInput = is_array($old ?? null) ? $old : [];

$currentDate = (string) ($date ?? date('Y-m-d'));
$currentQuery = (string) ($query ?? '');
$currentStatus = (string) ($status ?? '');
$currentPage = (int) ($page ?? 1);
$pageCount = (int) ($totalPages ?? 1);
$totalCount = (int) ($total ?? 0);
$canManageAttendance = can('attendance.manage');

// Calculate page-level analytics
$presentCount = 0;
$lateCount = 0;
$absentCount = 0;

foreach ($attendanceRecords as $entry) {
    $entryStatus = (string) ($entry['status'] ?? '');
    if ($entryStatus === 'Present') {
        $presentCount++;
    } elseif ($entryStatus === 'Late') {
        $lateCount++;
    } elseif ($entryStatus === 'Absent') {
        $absentCount++;
    }
}

$statusClassMap = [
    'present'   => 'badge-success',
    'late'      => 'badge-warning',
    'absent'    => 'badge-danger',
    'half_day'  => 'badge-teal',
    'holiday'   => 'badge-info',
    'rest_day'  => 'badge-neutral',
];
?>

<section class="page">
    <header class="page-banner">
        <div class="page-banner-copy">
            <p class="page-banner-kicker">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                Attendance Module
            </p>
            <h2 class="page-banner-title">Track daily attendance with operational clarity</h2>
            <p class="page-banner-sub">Record employee attendance and review daily trends in one focused workspace.</p>
            <div class="page-banner-meta">
                <span class="badge badge-blue">Daily record</span>
                <span class="badge badge-teal">Status aware</span>
                <span class="badge">Timesheet ready</span>
            </div>
        </div>
        <div class="page-banner-actions">
            <a class="btn btn-secondary" href="/attendance">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 1 1-9-9c2.52 0 4.93 1 6.74 2.74L21 8"/><path d="M21 3v5h-5"/></svg>
                Refresh
            </a>
        </div>
    </header>

    <?php require __DIR__ . '/../partials/alerts.php'; ?>

    <section class="stat-grid" aria-label="Attendance summary">
        <article class="stat card-shine">
            <div class="stat-icon is-blue">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3h18v18H3z"/><path d="M3 9h18"/><path d="M9 3v18"/></svg>
            </div>
            <div class="stat-data">
                <span class="stat-label">Total records</span>
                <span class="stat-value"><?= e((string) $totalCount) ?></span>
                <span class="stat-note">For current filters</span>
            </div>
        </article>
        <article class="stat stat-teal card-shine">
            <div class="stat-icon is-teal">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </div>
            <div class="stat-data">
                <span class="stat-label">Present (page)</span>
                <span class="stat-value"><?= e((string) $presentCount) ?></span>
                <span class="stat-note">On screen</span>
            </div>
        </article>
        <article class="stat stat-red card-shine">
            <div class="stat-icon is-red">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            </div>
            <div class="stat-data">
                <span class="stat-label">Late + Absent</span>
                <span class="stat-value text-red"><?= e((string) ($lateCount + $absentCount)) ?></span>
                <span class="stat-note">On screen</span>
            </div>
        </article>
    </section>

    <?php if ($canManageAttendance): ?>
        <section class="card glass-card">
            <div class="card-header">
                <div class="card-header-copy">
                    <h3>Record Attendance</h3>
                    <p>Submit clock details and status for an employee on a specific date.</p>
                </div>
            </div>
            <div class="card-body">
                <form class="form-grid" method="post" action="/attendance" novalidate>
                    <input type="hidden" name="_csrf" value="<?= e((string) ($csrf ?? '')) ?>">
                    
                    <label>
                        <span>Employee</span>
                        <select name="employee_id" required>
                            <option value="">Select</option>
                            <?php foreach ($employeeOptions as $employee): ?>
                                <option value="<?= (int) $employee['id'] ?>" <?= ((int) ($oldInput['employee_id'] ?? 0) === (int) $employee['id']) ? 'selected' : '' ?>>
                                    <?= e((string) ($employee['employee_code'] . ' — ' . $employee['first_name'] . ' ' . $employee['last_name'])) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>

                    <label>
                        <span>Date</span>
                        <input type="date" name="date" value="<?= e((string) ($oldInput['date'] ?? $currentDate)) ?>" required>
                    </label>

                    <label>
                        <span>Status</span>
                        <select name="status" required>
                            <?php foreach ($statusList as $item): ?>
                                <option value="<?= e((string) $item) ?>" <?= (($oldInput['status'] ?? 'Present') === $item) ? 'selected' : '' ?>>
                                    <?= e((string) $item) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>

                    <label>
                        <span>Clock In</span>
                        <input type="datetime-local" name="clock_in" value="<?= e(str_replace(' ', 'T', (string) ($oldInput['clock_in'] ?? ''))) ?>">
                    </label>

                    <label>
                        <span>Clock Out</span>
                        <input type="datetime-local" name="clock_out" value="<?= e(str_replace(' ', 'T', (string) ($oldInput['clock_out'] ?? ''))) ?>">
                    </label>

                    <label>
                        <span>Hours Worked</span>
                        <input type="number" step="0.01" name="hours_worked" value="<?= e((string) ($oldInput['hours_worked'] ?? '')) ?>">
                    </label>

                    <label>
                        <span>Overtime Hours</span>
                        <input type="number" step="0.01" name="overtime_hrs" value="<?= e((string) ($oldInput['overtime_hrs'] ?? '0')) ?>">
                    </label>

                    <label class="full-width">
                        <span>Remarks</span>
                        <textarea name="remarks" rows="2" placeholder="Any notable observations..."><?= e((string) ($oldInput['remarks'] ?? '')) ?></textarea>
                    </label>

                    <div class="full-width form-actions">
                        <p class="form-hint">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="margin-right:4px;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                            Leave hours blank to auto-calculate from clock in/out.
                        </p>
                        <button class="btn btn-primary" type="submit">
                            Save attendance record
                        </button>
                    </div>
                </form>
            </div>
        </section>
    <?php endif; ?>

    <section class="card overflow-hidden">
        <div class="card-header">
            <div class="card-header-copy">
                <h3>Attendance Records</h3>
                <p>Filter by employee, date, and status to review daily attendance snapshots.</p>
            </div>
        </div>

        <div class="toolbar glass-toolbar">
            <form method="get" action="/attendance" class="filter-bar" role="search" style="display:flex; width:100%; gap:var(--space-3); align-items:flex-end;">
                <label class="filter-field">
                    <span>Search</span>
                    <input type="text" name="q" value="<?= e($currentQuery) ?>" placeholder="Search employee code or name...">
                </label>
                
                <label class="filter-field">
                    <span>Date</span>
                    <input type="date" name="date" value="<?= e($currentDate) ?>">
                </label>

                <label class="filter-field">
                    <span>Status</span>
                    <select name="status">
                        <option value="">All statuses</option>
                        <?php foreach ($statusList as $item): ?>
                            <option value="<?= e((string) $item) ?>" <?= ($currentStatus === $item) ? 'selected' : '' ?>>
                                <?= e((string) $item) ?>
                            </option>
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
                        <th>Employee</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Clock In</th>
                        <th>Clock Out</th>
                        <th>Hours</th>
                        <th>OT</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($attendanceRecords === []): ?>
                        <tr>
                            <td colspan="7">
                                <div class="table-empty">
                                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" style="opacity:0.25; margin-bottom:12px;"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/><line x1="8" y1="13" x2="16" y2="13"/><line x1="8" y1="17" x2="16" y2="17"/><line x1="10" y1="9" x2="8" y2="9"/></svg>
                                    <p>No attendance records found for the selected filters.</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($attendanceRecords as $record): ?>
                            <?php
                            $statusValue = (string) ($record['status'] ?? 'Unknown');
                            $statusKey = strtolower(str_replace([' ', '-'], '_', $statusValue));
                            $statusClass = $statusClassMap[$statusKey] ?? 'badge-neutral';
                            ?>
                            <tr>
                                <td>
                                    <div class="person">
                                        <strong><?= e((string) (($record['first_name'] ?? '') . ' ' . ($record['last_name'] ?? ''))) ?></strong>
                                        <span><?= e((string) ($record['employee_code'] ?? '-')) ?></span>
                                    </div>
                                </td>
                                <td><code class="text-mono"><?= e((string) ($record['date'] ?? '-')) ?></code></td>
                                <td><span class="badge <?= e($statusClass) ?>"><?= e($statusValue) ?></span></td>
                                <td><?= e((string) ($record['clock_in'] ?? '-')) ?></td>
                                <td><?= e((string) ($record['clock_out'] ?? '-')) ?></td>
                                <td class="font-bold"><?= e((string) ($record['hours_worked'] ?? '-')) ?></td>
                                <td><span class="text-blue font-bold"><?= e((string) ($record['overtime_hrs'] ?? '0')) ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($pageCount > 1): ?>
            <div class="card-footer">
                <?php $base = '/attendance?date=' . urlencode($currentDate) . '&q=' . urlencode($currentQuery) . '&status=' . urlencode($currentStatus) . '&page='; ?>
                <nav class="pagination" aria-label="Attendance pagination">
                    <?php for ($i = 1; $i <= $pageCount; $i++): ?>
                        <a class="page-link <?= $currentPage === $i ? 'is-active' : '' ?>" href="<?= e($base . $i) ?>"><?= $i ?></a>
                    <?php endfor; ?>
                </nav>
            </div>
        <?php endif; ?>
    </section>
</section>

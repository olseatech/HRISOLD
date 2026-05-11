<?php
$record    = is_array($record ?? null) ? $record : [];
$canUpdate = can('appointments.update');
$canDelete = can('appointments.delete');

$recId     = (int) ($record['id'] ?? 0);
$empName   = trim(($record['emp_last'] ?? '') . ', ' . ($record['emp_first'] ?? ''));
$empCode   = (string) ($record['employee_code'] ?? '');
$isCurrent = (bool) ($record['is_current'] ?? false);

$val = static function (string $key, string $fallback = '—') use ($record): string {
    $v = trim((string) ($record[$key] ?? ''));
    return $v !== '' ? htmlspecialchars($v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') : htmlspecialchars($fallback, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
};
?>

<section class="page">
    <header class="page-banner">
        <div class="page-banner-copy">
            <p class="page-banner-kicker">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                Appointments
            </p>
            <h2 class="page-banner-title"><?= $val('position_title') ?></h2>
            <p class="page-banner-sub">
                <?= e($empName !== ', ' ? $empName : 'Unknown') ?>
                <?php if ($empCode !== ''): ?>&nbsp;<code style="font-weight:600; color:var(--blue-700);"><?= e($empCode) ?></code><?php endif; ?>
            </p>
            <div class="page-banner-meta">
                <span class="badge <?= $isCurrent ? 'badge-success' : 'badge-neutral' ?>"><?= $isCurrent ? 'Current' : 'Past' ?></span>
                <?php if (($record['appointment_type'] ?? '') !== ''): ?>
                    <span class="badge badge-info"><?= e((string) $record['appointment_type']) ?></span>
                <?php endif; ?>
            </div>
        </div>
        <div class="page-banner-actions">
            <a class="btn btn-secondary" href="/appointments">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                Back to list
            </a>
            <?php if ($canUpdate): ?>
                <a class="btn btn-primary" href="/appointments/<?= $recId ?>/edit">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    Edit
                </a>
            <?php endif; ?>
        </div>
    </header>

    <?php require __DIR__ . '/../partials/alerts.php'; ?>

    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(380px, 1fr)); gap:var(--space-5);">

        <!-- Position & Office -->
        <article class="card card-shine">
            <div class="card-header" style="border-bottom:1px solid var(--slate-100);">
                <div style="display:flex; align-items:center; gap:12px;">
                    <div style="width:36px; height:36px; border-radius:10px; background:var(--blue-50); color:var(--blue-600); display:flex; align-items:center; justify-content:center;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                    </div>
                    <div>
                        <h3 style="font-size:1rem; font-weight:800; color:var(--slate-900);">Position &amp; Office</h3>
                        <p style="font-size:.75rem; color:var(--slate-500);">Position details and organizational unit.</p>
                    </div>
                </div>
            </div>
            <div class="card-body" style="padding:var(--space-5);">
                <dl class="detail-list">
                    <div class="detail-row">
                        <dt>Employee</dt>
                        <dd>
                            <?= e($empName !== ', ' ? $empName : '—') ?>
                            <?php if ($empCode !== ''): ?><br><small style="color:var(--text-muted);"><?= e($empCode) ?></small><?php endif; ?>
                        </dd>
                    </div>
                    <div class="detail-row">
                        <dt>Position Title</dt>
                        <dd><?= $val('position_title') ?></dd>
                    </div>
                    <div class="detail-row">
                        <dt>Appointment Type</dt>
                        <dd><?= $val('appointment_type') ?></dd>
                    </div>
                    <div class="detail-row">
                        <dt>Plantilla Item No.</dt>
                        <dd><?= $val('item_number') ?></dd>
                    </div>
                    <div class="detail-row">
                        <dt>Office / Unit</dt>
                        <dd><?= $val('office_unit') ?></dd>
                    </div>
                    <div class="detail-row">
                        <dt>Division</dt>
                        <dd><?= $val('division') ?></dd>
                    </div>
                    <div class="detail-row">
                        <dt>Employment Status</dt>
                        <dd><?= $val('employment_status') ?></dd>
                    </div>
                </dl>
            </div>
        </article>

        <!-- Compensation & Dates -->
        <article class="card card-shine">
            <div class="card-header" style="border-bottom:1px solid var(--slate-100);">
                <div style="display:flex; align-items:center; gap:12px;">
                    <div style="width:36px; height:36px; border-radius:10px; background:var(--teal-50,#f0fdfa); color:var(--teal-600,#0d9488); display:flex; align-items:center; justify-content:center;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="2.5" y="5" width="15" height="10" rx="1"/><circle cx="10" cy="10" r="2.2"/><path d="M5.5 8.5v3M14.5 8.5v3"/></svg>
                    </div>
                    <div>
                        <h3 style="font-size:1rem; font-weight:800; color:var(--slate-900);">Compensation &amp; Dates</h3>
                        <p style="font-size:.75rem; color:var(--slate-500);">Salary and key appointment dates.</p>
                    </div>
                </div>
            </div>
            <div class="card-body" style="padding:var(--space-5);">
                <dl class="detail-list">
                    <div class="detail-row">
                        <dt>Salary Grade</dt>
                        <dd>
                            <?php if (($record['salary_grade'] ?? '') !== ''): ?>
                                SG <?= e((string) $record['salary_grade']) ?>
                                <?php if (($record['salary_step'] ?? '') !== ''): ?>/ Step <?= e((string) $record['salary_step']) ?><?php endif; ?>
                            <?php else: ?>—<?php endif; ?>
                        </dd>
                    </div>
                    <div class="detail-row">
                        <dt>Monthly Salary</dt>
                        <dd>
                            <?php if (($record['monthly_salary'] ?? '') !== '' && $record['monthly_salary'] !== null): ?>
                                ₱ <?= number_format((float) $record['monthly_salary'], 2) ?>
                            <?php else: ?>—<?php endif; ?>
                        </dd>
                    </div>
                    <div class="detail-row">
                        <dt>Effectivity Date</dt>
                        <dd><?= e(format_date((string) ($record['effectivity_date'] ?? ''))) ?></dd>
                    </div>
                    <div class="detail-row">
                        <dt>Oath Date</dt>
                        <dd><?= ($record['oath_date'] ?? '') !== '' ? e(format_date((string) $record['oath_date'])) : '—' ?></dd>
                    </div>
                    <div class="detail-row">
                        <dt>Report Date</dt>
                        <dd><?= ($record['report_date'] ?? '') !== '' ? e(format_date((string) $record['report_date'])) : '—' ?></dd>
                    </div>
                    <div class="detail-row">
                        <dt>Status</dt>
                        <dd><span class="badge <?= $isCurrent ? 'badge-success' : 'badge-neutral' ?>"><?= $isCurrent ? 'Current Appointment' : 'Past Appointment' ?></span></dd>
                    </div>
                    <?php if (($record['remarks'] ?? '') !== ''): ?>
                        <div class="detail-row">
                            <dt>Remarks</dt>
                            <dd><?= e((string) $record['remarks']) ?></dd>
                        </div>
                    <?php endif; ?>
                </dl>
            </div>
        </article>

    </div>

    <?php if ($canDelete): ?>
        <div style="margin-top:var(--space-6); padding:var(--space-4); background:var(--danger-bg,#fff5f5); border:1px solid var(--danger-border,#fecdd3); border-radius:var(--radius-lg); display:flex; align-items:center; justify-content:space-between; gap:var(--space-4);">
            <div>
                <p style="font-weight:600; color:var(--danger,#dc2626); margin-bottom:2px;">Delete this appointment</p>
                <p style="font-size:13px; color:var(--text-muted);">This action is permanent and cannot be undone. An audit log entry will be created.</p>
            </div>
            <form method="post" action="/appointments/<?= $recId ?>/delete" onsubmit="return confirm('Permanently delete this appointment?');">
                <input type="hidden" name="_csrf" value="<?= e(\App\Core\CSRF::token()) ?>">
                <button type="submit" class="btn btn-danger" style="background:var(--danger,#dc2626); color:#fff; border-color:var(--danger,#dc2626);">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                    Delete Appointment
                </button>
            </form>
        </div>
    <?php endif; ?>

</section>

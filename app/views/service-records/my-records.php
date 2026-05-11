<?php
$rows       = is_array($rows ?? null) ? $rows : [];
$current    = is_array($current ?? null) ? $current : null;
$noEmployee = (bool) ($noEmployee ?? false);
?>
<section class="page">
    <header class="page-banner">
        <div class="page-banner-copy">
            <p class="page-banner-kicker">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="2" width="14" height="16" rx="1"/><path d="M7 6h6M7 9h6M7 12h4"/><circle cx="14.5" cy="14.5" r="3"/><path d="m13.5 14.5 1 1 1.5-1.5"/></svg>
                Self-Service
            </p>
            <h2 class="page-banner-title">My Service Record</h2>
            <p class="page-banner-sub">View your employment history and current position on file.</p>
            <div class="page-banner-meta">
                <span class="badge badge-blue">Read-only</span>
            </div>
        </div>
        <div class="page-banner-actions">
            <a class="btn btn-secondary" href="/my-service-record">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 1 1-9-9c2.52 0 4.93 1 6.74 2.74L21 8"/><path d="M21 3v5h-5"/></svg>
                Refresh
            </a>
        </div>
    </header>

    <?php require __DIR__ . '/../partials/alerts.php'; ?>

    <?php if ($noEmployee): ?>
        <div style="margin-top:var(--space-5); padding:var(--space-5); background:var(--amber-50,#fffbeb); border:1px solid var(--amber-200,#fde68a); border-radius:var(--radius-lg);">
            <p style="font-weight:600; color:var(--amber-700);">Account not linked to an employee profile.</p>
            <p style="font-size:13px; color:var(--text-muted); margin-top:4px;">Contact an administrator to link your login account to an employee record.</p>
        </div>
    <?php else: ?>

        <?php if ($current): ?>
        <section class="card" style="margin-bottom:var(--space-5);">
            <div class="card-header">
                <div class="card-header-copy">
                    <h3>Current Position</h3>
                    <p>Your active appointment on record.</p>
                </div>
            </div>
            <div class="card-body" style="padding:var(--space-5);">
                <div style="display:grid; grid-template-columns:repeat(auto-fill,minmax(200px,1fr)); gap:var(--space-4);">
                    <div>
                        <p style="font-size:11px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:4px;">Position Title</p>
                        <p style="font-weight:700;font-size:15px;"><?= e((string)($current['position_title']??'-')) ?></p>
                    </div>
                    <div>
                        <p style="font-size:11px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:4px;">Office / Unit</p>
                        <p style="font-weight:600;"><?= e((string)($current['office_unit']??'-')) ?></p>
                    </div>
                    <div>
                        <p style="font-size:11px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:4px;">Salary Grade</p>
                        <p><?= e((string)($current['salary_grade']??'-')) ?><?= $current['salary_step']??'' ? ' / Step '.e((string)$current['salary_step']) : '' ?></p>
                    </div>
                    <div>
                        <p style="font-size:11px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:4px;">Appointment Status</p>
                        <p><span class="badge badge-success"><?= e((string)($current['appointment_status']??'-')) ?></span></p>
                    </div>
                    <div>
                        <p style="font-size:11px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:4px;">Effective From</p>
                        <p style="font-family:monospace;"><?= e((string)($current['date_from']??'-')) ?></p>
                    </div>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <section class="card overflow-hidden">
            <div class="card-header">
                <div class="card-header-copy">
                    <h3>Employment History</h3>
                    <p>All service record entries on file.</p>
                </div>
            </div>
            <div class="table-wrap no-border no-radius shadow-none">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Position</th>
                            <th>Office / Unit</th>
                            <th>Status</th>
                            <th>From</th>
                            <th>To</th>
                            <th>Monthly Salary</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($rows === []): ?>
                            <tr>
                                <td colspan="6">
                                    <div class="table-empty">
                                        <p>No service records found for your account.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($rows as $row): ?>
                                <tr>
                                    <td>
                                        <strong><?= e((string)($row['position_title']??'-')) ?></strong>
                                        <?php if ((int)($row['is_current']??0)===1): ?>
                                            <span class="badge badge-success" style="margin-left:4px;font-size:10px;">Current</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= e((string)($row['office_unit']??'-')) ?></td>
                                    <td><span class="badge"><?= e((string)($row['appointment_status']??'-')) ?></span></td>
                                    <td style="font-family:monospace;font-size:12px;"><?= e((string)($row['date_from']??'-')) ?></td>
                                    <td style="font-family:monospace;font-size:12px;"><?= e((string)($row['date_to']??'Present')) ?></td>
                                    <td style="font-family:monospace;"><?= $row['monthly_salary'] ? '₱'.e(number_format((float)$row['monthly_salary'],2)) : '-' ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

    <?php endif; ?>
</section>

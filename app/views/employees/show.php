<?php
$employee = is_array($employee ?? null) ? $employee : [];
$name = trim((string) (($employee['first_name'] ?? '') . ' ' . ($employee['last_name'] ?? '')));
$status = (string) ($employee['employment_status'] ?? 'Unknown');
$statusKey = strtolower(str_replace([' ', '-'], '_', $status));
$statusClassMap = [
    'active'     => 'badge-success',
    'probation'  => 'badge-info',
    'on_leave'   => 'badge-warning',
    'resigned'   => 'badge-neutral',
    'terminated' => 'badge-danger',
];
$statusClass = $statusClassMap[$statusKey] ?? 'badge-neutral';
$canUpdateEmployee = can('employees.update');
?>

<section class="page">
    <header class="page-banner">
        <div class="page-banner-copy">
            <p class="page-banner-kicker">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                Employee Dossier
            </p>
            <h2 class="page-banner-title"><?= e($name !== '' ? $name : 'Unknown employee') ?></h2>
            <p class="page-banner-sub">System Code: <code class="text-mono" style="font-weight:700; color:var(--blue-700);"><?= e((string) ($employee['employee_code'] ?? '-')) ?></code></p>
            <div class="page-banner-meta">
                <span class="badge <?= e($statusClass) ?>"><?= e($status) ?></span>
                <span class="badge">Record Verified</span>
            </div>
        </div>
        <div class="page-banner-actions">
            <a class="btn btn-secondary" href="/employees">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                Back to directory
            </a>
            <?php if ($canUpdateEmployee): ?>
                <a class="btn btn-primary" href="/employees/<?= (int) ($employee['id'] ?? 0) ?>/edit">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    Edit Profile
                </a>
            <?php endif; ?>
        </div>
    </header>

    <?php require __DIR__ . '/../partials/alerts.php'; ?>

    <!-- Quick links to related modules -->
    <section style="display:grid; grid-template-columns:repeat(auto-fill,minmax(160px,1fr)); gap:var(--space-3); margin-bottom:var(--space-5);">
        <?php $empId = (int) ($employee['id'] ?? 0); ?>
        <?php if (can('pds.view')): ?>
        <a href="/pds?employee_id=<?= $empId ?>" class="card card-shine" style="padding:var(--space-3) var(--space-4); text-decoration:none; display:flex; align-items:center; gap:10px; border:1px solid var(--border);">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2H5a1 1 0 0 0-1 1v14a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1V6z"/><polyline points="12 2 12 6 16 6"/><line x1="7" y1="9" x2="13" y2="9"/><line x1="7" y1="12" x2="13" y2="12"/></svg>
            <span style="font-size:13px; font-weight:600;">PDS</span>
        </a>
        <?php endif; ?>
        <?php if (can('service_records.view')): ?>
        <a href="/service-records?employee_id=<?= $empId ?>" class="card card-shine" style="padding:var(--space-3) var(--space-4); text-decoration:none; display:flex; align-items:center; gap:10px; border:1px solid var(--border);">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="2" width="14" height="16" rx="1"/><path d="M7 6h6M7 9h6M7 12h4"/><circle cx="14.5" cy="14.5" r="3"/></svg>
            <span style="font-size:13px; font-weight:600;">Service Record</span>
        </a>
        <?php endif; ?>
        <?php if (can('clearances.view')): ?>
        <a href="/clearances?employee_id=<?= $empId ?>" class="card card-shine" style="padding:var(--space-3) var(--space-4); text-decoration:none; display:flex; align-items:center; gap:10px; border:1px solid var(--border);">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M10 2L3 5v5c0 4.4 3 8.1 7 9 4-0.9 7-4.6 7-9V5l-7-3z"/><path d="M7 10l2 2 4-4"/></svg>
            <span style="font-size:13px; font-weight:600;">Clearance</span>
        </a>
        <?php endif; ?>
        <?php if (can('appointments.view')): ?>
        <a href="/appointments?employee_id=<?= $empId ?>" class="card card-shine" style="padding:var(--space-3) var(--space-4); text-decoration:none; display:flex; align-items:center; gap:10px; border:1px solid var(--border);">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="14" height="13" rx="1"/><path d="M3 8h14M7 2v3M13 2v3"/></svg>
            <span style="font-size:13px; font-weight:600;">Appointments</span>
        </a>
        <?php endif; ?>
        <?php if (can('documents.view')): ?>
        <a href="/documents?employee_id=<?= $empId ?>" class="card card-shine" style="padding:var(--space-3) var(--space-4); text-decoration:none; display:flex; align-items:center; gap:10px; border:1px solid var(--border);">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M11 2H5a1 1 0 0 0-1 1v14a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1V6z"/><polyline points="11 2 11 6 15 6"/><line x1="7" y1="9" x2="13" y2="9"/><line x1="7" y1="12" x2="13" y2="12"/></svg>
            <span style="font-size:13px; font-weight:600;">201 Documents</span>
        </a>
        <?php endif; ?>
        <?php if (can('leave.view')): ?>
        <a href="/leave?employee_id=<?= $empId ?>" class="card card-shine" style="padding:var(--space-3) var(--space-4); text-decoration:none; display:flex; align-items:center; gap:10px; border:1px solid var(--border);">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M3 10h18M8 2v3M16 2v3"/></svg>
            <span style="font-size:13px; font-weight:600;">Leave</span>
        </a>
        <?php endif; ?>
        <?php if (can('reports.view')): ?>
        <a href="/reports?employee_id=<?= $empId ?>" class="card card-shine" style="padding:var(--space-3) var(--space-4); text-decoration:none; display:flex; align-items:center; gap:10px; border:1px solid var(--border);">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
            <span style="font-size:13px; font-weight:600;">Reports</span>
        </a>
        <?php endif; ?>
    </section>

    <section class="emp-profile-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: var(--space-6);">
        
        <article class="card card-shine">
            <div class="card-header" style="border-bottom: 1px solid var(--slate-100);">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div style="width: 36px; height: 36px; border-radius: 10px; background: var(--blue-50); color: var(--blue-600); display: flex; align-items: center; justify-content: center;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    </div>
                    <div>
                        <h3 style="font-size: 1rem; font-weight: 800; color: var(--slate-900);">Identity Dossier</h3>
                        <p style="font-size: 0.75rem; color: var(--slate-500);">Verified personal identification records.</p>
                    </div>
                </div>
            </div>
            <dl class="detail-list" style="margin: 0; display: grid; gap: 0;">
                <div style="display:flex; justify-content:space-between; padding: 12px 20px; border-bottom: 1px solid var(--slate-50);">
                    <dt style="font-size: var(--text-xs); font-weight: 700; color: var(--slate-400); text-transform: uppercase; letter-spacing:0.05em;">First Name</dt>
                    <dd style="font-size: var(--text-sm); font-weight: 700; color: var(--slate-800); margin: 0;"><?= e((string) ($employee['first_name'] ?? '-')) ?></dd>
                </div>
                <div style="display:flex; justify-content:space-between; padding: 12px 20px; border-bottom: 1px solid var(--slate-50);">
                    <dt style="font-size: var(--text-xs); font-weight: 700; color: var(--slate-400); text-transform: uppercase;">Middle Name</dt>
                    <dd style="font-size: var(--text-sm); font-weight: 700; color: var(--slate-800); margin: 0;"><?= e((string) ($employee['middle_name'] ?? '-')) ?></dd>
                </div>
                <div style="display:flex; justify-content:space-between; padding: 12px 20px; border-bottom: 1px solid var(--slate-50);">
                    <dt style="font-size: var(--text-xs); font-weight: 700; color: var(--slate-400); text-transform: uppercase;">Last Name</dt>
                    <dd style="font-size: var(--text-sm); font-weight: 700; color: var(--slate-800); margin: 0;"><?= e((string) ($employee['last_name'] ?? '-')) ?></dd>
                </div>
                <div style="display:flex; justify-content:space-between; padding: 12px 20px; border-bottom: 1px solid var(--slate-50);">
                    <dt style="font-size: var(--text-xs); font-weight: 700; color: var(--slate-400); text-transform: uppercase;">Gender Orientation</dt>
                    <dd style="font-size: var(--text-sm); font-weight: 700; color: var(--slate-800); margin: 0;"><?= e((string) ($employee['gender'] ?? '-')) ?></dd>
                </div>
                <div style="display:flex; justify-content:space-between; padding: 12px 20px;">
                    <dt style="font-size: var(--text-xs); font-weight: 700; color: var(--slate-400); text-transform: uppercase;">Date of Birth</dt>
                    <dd style="font-size: var(--text-sm); font-weight: 700; color: var(--slate-800); margin: 0;"><?= e((string) ($employee['date_of_birth'] ?? '-')) ?></dd>
                </div>
            </dl>
        </article>

        <article class="card card-shine">
            <div class="card-header" style="border-bottom: 1px solid var(--slate-100);">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div style="width: 36px; height: 36px; border-radius: 10px; background: var(--teal-50); color: var(--teal-600); display: flex; align-items: center; justify-content: center;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    </div>
                    <div>
                        <h3 style="font-size: 1rem; font-weight: 800; color: var(--slate-900);">Employment Profile</h3>
                        <p style="font-size: 0.75rem; color: var(--slate-500);">Job placement and organizational metrics.</p>
                    </div>
                </div>
            </div>
            <dl class="detail-list" style="margin: 0; display: grid; gap: 0;">
                <div style="display:flex; justify-content:space-between; padding: 12px 20px; border-bottom: 1px solid var(--slate-50);">
                    <dt style="font-size: var(--text-xs); font-weight: 700; color: var(--slate-400); text-transform: uppercase;">Active Status</dt>
                    <dd style="margin:0;"><span class="badge <?= e($statusClass) ?>" style="font-size: 10px;"><?= e($status) ?></span></dd>
                </div>
                <div style="display:flex; justify-content:space-between; padding: 12px 20px; border-bottom: 1px solid var(--slate-50);">
                    <dt style="font-size: var(--text-xs); font-weight: 700; color: var(--slate-400); text-transform: uppercase;">Contract Type</dt>
                    <dd style="font-size: var(--text-sm); font-weight: 700; color: var(--slate-800); margin: 0;"><?= e((string) ($employee['employment_type'] ?? '-')) ?></dd>
                </div>
                <div style="display:flex; justify-content:space-between; padding: 12px 20px; border-bottom: 1px solid var(--slate-50);">
                    <dt style="font-size: var(--text-xs); font-weight: 700; color: var(--slate-400); text-transform: uppercase;">Date Hired</dt>
                    <dd style="font-size: var(--text-sm); font-weight: 700; color: var(--slate-800); margin: 0;"><?= e((string) ($employee['date_hired'] ?? '-')) ?></dd>
                </div>
                <div style="display:flex; justify-content:space-between; padding: 12px 20px; border-bottom: 1px solid var(--slate-50);">
                    <dt style="font-size: var(--text-xs); font-weight: 700; color: var(--slate-400); text-transform: uppercase;">Department</dt>
                    <dd style="font-size: var(--text-sm); font-weight: 700; color: var(--blue-700); margin: 0;"><?= e((string) ($employee['department_name'] ?? '-')) ?></dd>
                </div>
                <div style="display:flex; justify-content:space-between; padding: 12px 20px;">
                    <dt style="font-size: var(--text-xs); font-weight: 700; color: var(--slate-400); text-transform: uppercase;">Supervisor</dt>
                    <dd style="font-size: var(--text-sm); font-weight: 700; color: var(--slate-800); margin: 0;">#<?= e((string) ($employee['supervisor_id'] ?? 'NONE')) ?></dd>
                </div>
            </dl>
        </article>

        <article class="card card-shine">
            <div class="card-header" style="border-bottom: 1px solid var(--slate-100);">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div style="width: 36px; height: 36px; border-radius: 10px; background: var(--amber-50); color: var(--amber-600); display: flex; align-items: center; justify-content: center;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                    </div>
                    <div>
                        <h3 style="font-size: 1rem; font-weight: 800; color: var(--slate-900);">Communication Hub</h3>
                        <p style="font-size: 0.75rem; color: var(--slate-500);">Direct channels and contact references.</p>
                    </div>
                </div>
            </div>
            <dl class="detail-list" style="margin: 0; display: grid; gap: 0;">
                <div style="display:flex; justify-content:space-between; padding: 12px 20px; border-bottom: 1px solid var(--slate-50);">
                    <dt style="font-size: var(--text-xs); font-weight: 700; color: var(--slate-400); text-transform: uppercase;">Official Email</dt>
                    <dd style="font-size: var(--text-sm); font-weight: 700; color: var(--blue-700); margin: 0; border-bottom: 1px solid currentColor;">
                        <?= e((string) ($employee['email'] ?? '-')) ?>
                    </dd>
                </div>
                <div style="display:flex; justify-content:space-between; padding: 12px 20px; border-bottom: 1px solid var(--slate-50);">
                    <dt style="font-size: var(--text-xs); font-weight: 700; color: var(--slate-400); text-transform: uppercase;">Mobile Contact</dt>
                    <dd style="font-size: var(--text-sm); font-weight: 700; color: var(--slate-800); margin: 0;"><?= e((string) ($employee['phone'] ?? '-')) ?></dd>
                </div>
                <div style="display:flex; justify-content:space-between; padding: 12px 20px; border-bottom: 1px solid var(--slate-50);">
                    <dt style="font-size: var(--text-xs); font-weight: 700; color: var(--slate-400); text-transform: uppercase;">Nationality</dt>
                    <dd style="font-size: var(--text-sm); font-weight: 700; color: var(--slate-800); margin: 0;"><?= e((string) ($employee['nationality'] ?? '-')) ?></dd>
                </div>
                <div style="display:flex; justify-content:space-between; padding: 12px 20px;">
                    <dt style="font-size: var(--text-xs); font-weight: 700; color: var(--slate-400); text-transform: uppercase;">Marital Status</dt>
                    <dd style="font-size: var(--text-sm); font-weight: 700; color: var(--slate-800); margin: 0;"><?= e((string) ($employee['marital_status'] ?? '-')) ?></dd>
                </div>
            </dl>
        </article>

        <article class="card card-shine" style="grid-column: span 1;">
            <div class="card-header" style="border-bottom: 1px solid var(--slate-100);">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div style="width: 36px; height: 36px; border-radius: 10px; background: var(--slate-50); color: var(--slate-600); display: flex; align-items: center; justify-content: center;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                    </div>
                    <div>
                        <h3 style="font-size: 1rem; font-weight: 800; color: var(--slate-900);">Location Details</h3>
                        <p style="font-size: 0.75rem; color: var(--slate-500);">Primary residential markers.</p>
                    </div>
                </div>
            </div>
            <div style="padding: 20px;">
                <p style="font-size: var(--text-sm); line-height: 1.6; color: var(--slate-600); margin: 0;">
                    <?= nl2br(e((string) ($employee['address'] ?? 'No address recorded on file.'))) ?>
                </p>
            </div>
        </article>

    </section>
</section>

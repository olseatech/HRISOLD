<?php
$employees = is_array($employees ?? null) ? $employees : [];
$empId     = (int) ($empId ?? 0);

$reportTypes = [
    ['key' => 'pds',            'label' => 'PDS / CS Form 212',     'icon' => '📄', 'desc' => 'Personal Data Sheet — all sections',        'route' => '/reports/pds/'],
    ['key' => 'service-record', 'label' => 'Service Record',         'icon' => '📋', 'desc' => 'Full employment history',                    'route' => '/reports/service-record/'],
    ['key' => 'certification',  'label' => 'Employee Certification', 'icon' => '📜', 'desc' => 'Certifies current position and employment',  'route' => '/reports/certification/'],
];
?>
<section class="page">
    <header class="page-banner">
        <div class="page-banner-copy">
            <p class="page-banner-kicker">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                Reports
            </p>
            <h2 class="page-banner-title">Print Reports</h2>
            <p class="page-banner-sub">Select a report type and an employee to generate a print-ready document.</p>
        </div>
        <div class="page-banner-actions">
            <a class="btn btn-secondary" href="/reports">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 1 1-9-9c2.52 0 4.93 1 6.74 2.74L21 8"/><path d="M21 3v5h-5"/></svg>
                Refresh
            </a>
        </div>
    </header>

    <?php require __DIR__ . '/../partials/alerts.php'; ?>

    <!-- Employee picker -->
    <section class="card" style="margin-bottom:var(--space-5);">
        <div class="card-header">
            <div class="card-header-copy">
                <h3>Select Employee</h3>
                <p>Choose an employee to generate reports for.</p>
            </div>
        </div>
        <div class="card-body" style="padding:var(--space-5);">
            <form method="get" action="/reports" style="display:flex; gap:var(--space-4); align-items:flex-end; flex-wrap:wrap;">
                <label class="field" style="min-width:280px;">
                    <span>Employee</span>
                    <select name="employee_id" onchange="this.form.submit()">
                        <option value="">— Select employee —</option>
                        <?php foreach ($employees as $emp): ?>
                            <option value="<?= (int)($emp['id']??0) ?>" <?= $empId === (int)($emp['id']??0) ? 'selected' : '' ?>>
                                <?= e((string)($emp['full_name']??'')) ?> (<?= e((string)($emp['employee_code']??'')) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <button class="btn btn-primary" type="submit">Load Reports</button>
            </form>
        </div>
    </section>

    <?php if ($empId > 0): ?>
    <section style="display:grid; grid-template-columns:repeat(auto-fill,minmax(260px,1fr)); gap:var(--space-4);">
        <?php foreach ($reportTypes as $r): ?>
        <article class="card card-shine" style="padding:var(--space-5);">
            <div style="font-size:28px; margin-bottom:var(--space-3);"><?= $r['icon'] ?></div>
            <h3 style="font-size:15px; font-weight:700; margin-bottom:4px;"><?= e($r['label']) ?></h3>
            <p style="font-size:12px; color:var(--text-muted); margin-bottom:var(--space-4);"><?= e($r['desc']) ?></p>
            <a href="<?= e($r['route'] . $empId) ?>" target="_blank" class="btn btn-primary" style="width:100%; text-align:center; justify-content:center;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                Print / Open
            </a>
        </article>
        <?php endforeach; ?>

        <!-- Clearance report — requires clearance ID -->
        <article class="card card-shine" style="padding:var(--space-5);">
            <div style="font-size:28px; margin-bottom:var(--space-3);">🧾</div>
            <h3 style="font-size:15px; font-weight:700; margin-bottom:4px;">Clearance Form</h3>
            <p style="font-size:12px; color:var(--text-muted); margin-bottom:var(--space-4);">Print a specific clearance request. Requires Clearance ID.</p>
            <form method="get" action="" id="clearanceForm" onsubmit="openClearancePrint(event)" style="display:flex; gap:8px;">
                <input type="number" id="clearanceId" placeholder="Clearance ID" min="1" style="flex:1; padding:8px; border:1px solid var(--border); border-radius:var(--radius); font-size:13px;">
                <button type="submit" class="btn btn-primary">Print</button>
            </form>
        </article>
    </section>
    <?php else: ?>
    <div style="text-align:center; padding:var(--space-8); color:var(--text-muted);">
        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" style="opacity:.3; margin-bottom:12px;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
        <p>Select an employee above to see available reports.</p>
    </div>
    <?php endif; ?>
</section>
<script>
function openClearancePrint(e) {
    e.preventDefault();
    const id = document.getElementById('clearanceId').value;
    if (!id) { alert('Please enter a Clearance ID.'); return; }
    window.open('/reports/clearance/' + id, '_blank');
}
</script>

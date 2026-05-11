<?php
$record   = is_array($record ?? null) ? $record : [];
$old      = is_array($old ?? null) ? $old : [];
$errors   = is_array($errors ?? null) ? $errors : [];
$empList  = is_array($employees ?? null) ? $employees : [];
$types    = is_array($types ?? null) ? $types : [];
$statuses = is_array($statuses ?? null) ? $statuses : [];

$v = static function (string $key) use ($old, $record): string {
    return (string) ($old !== [] ? ($old[$key] ?? '') : ($record[$key] ?? ''));
};
$err = static function (string $key) use ($errors): string {
    return isset($errors[$key])
        ? '<span class="field-error">' . htmlspecialchars((string) $errors[$key], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</span>'
        : '';
};

$recId   = (int) ($record['id'] ?? 0);
$empName = trim(($record['emp_last'] ?? '') . ', ' . ($record['emp_first'] ?? ''));
$empCode = (string) ($record['employee_code'] ?? '');
?>

<section class="page">
    <header class="page-banner">
        <div class="page-banner-copy">
            <p class="page-banner-kicker">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                Appointments
            </p>
            <h2 class="page-banner-title">Edit Appointment</h2>
            <p class="page-banner-sub">
                <?= e($empName !== ', ' ? $empName : 'Unknown') ?>
                <?php if ($empCode !== ''): ?><code style="font-weight:600; color:var(--blue-700);"><?= e($empCode) ?></code><?php endif; ?>
            </p>
        </div>
        <div class="page-banner-actions">
            <a class="btn btn-secondary" href="/appointments/<?= $recId ?>">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                Back to Appointment
            </a>
        </div>
    </header>

    <?php require __DIR__ . '/../partials/alerts.php'; ?>

    <form method="post" action="/appointments/<?= $recId ?>/update" novalidate>
        <input type="hidden" name="_csrf" value="<?= e((string) ($csrf ?? '')) ?>">

        <div class="card" style="margin-bottom:var(--space-5);">
            <div class="card-header">
                <div class="card-header-copy">
                    <h3>Appointment Details</h3>
                    <p>Required fields are marked with <em class="text-red">*</em>. Employee cannot be changed.</p>
                </div>
            </div>
            <div class="card-body" style="padding:var(--space-5);">

                <!-- Employee (locked) + Appointment Type -->
                <div class="form-grid" style="display:grid; grid-template-columns:1fr 1fr; gap:var(--space-4); margin-bottom:var(--space-4);">
                    <div class="field">
                        <span>Employee</span>
                        <input type="text" value="<?= e($empCode !== '' ? $empCode . ' — ' . $empName : $empName) ?>" disabled style="background:var(--bg-muted); color:var(--text-muted);">
                        <input type="hidden" name="employee_id" value="<?= (int) ($record['employee_id'] ?? 0) ?>">
                    </div>
                    <label class="field">
                        <span>Appointment Type <em class="text-red">*</em></span>
                        <select name="appointment_type" required>
                            <option value="">— Select type —</option>
                            <?php foreach ($types as $t): ?>
                                <option value="<?= e($t) ?>" <?= $v('appointment_type') === $t ? 'selected' : '' ?>><?= e($t) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?= $err('appointment_type') ?>
                    </label>
                </div>

                <!-- Position Title + Item Number -->
                <div class="form-grid" style="display:grid; grid-template-columns:2fr 1fr; gap:var(--space-4); margin-bottom:var(--space-4);">
                    <label class="field">
                        <span>Position Title <em class="text-red">*</em></span>
                        <input type="text" name="position_title" value="<?= e($v('position_title')) ?>" maxlength="150" required>
                        <?= $err('position_title') ?>
                    </label>
                    <label class="field">
                        <span>Plantilla Item Number</span>
                        <input type="text" name="item_number" value="<?= e($v('item_number')) ?>" maxlength="50">
                    </label>
                </div>

                <!-- Office/Unit + Division + Employment Status -->
                <div class="form-grid" style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:var(--space-4); margin-bottom:var(--space-4);">
                    <label class="field">
                        <span>Office / Unit</span>
                        <input type="text" name="office_unit" value="<?= e($v('office_unit')) ?>" maxlength="150">
                    </label>
                    <label class="field">
                        <span>Division</span>
                        <input type="text" name="division" value="<?= e($v('division')) ?>" maxlength="150">
                    </label>
                    <label class="field">
                        <span>Employment Status</span>
                        <select name="employment_status">
                            <option value="">— None —</option>
                            <?php foreach ($statuses as $s): ?>
                                <option value="<?= e($s) ?>" <?= $v('employment_status') === $s ? 'selected' : '' ?>><?= e($s) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?= $err('employment_status') ?>
                    </label>
                </div>

                <!-- Salary Grade + Step + Monthly Salary -->
                <div class="form-grid" style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:var(--space-4); margin-bottom:var(--space-4);">
                    <label class="field">
                        <span>Salary Grade</span>
                        <input type="text" name="salary_grade" value="<?= e($v('salary_grade')) ?>" maxlength="10" placeholder="e.g. 15">
                    </label>
                    <label class="field">
                        <span>Step</span>
                        <input type="text" name="salary_step" value="<?= e($v('salary_step')) ?>" maxlength="5" placeholder="e.g. 4">
                    </label>
                    <label class="field">
                        <span>Monthly Salary</span>
                        <input type="number" name="monthly_salary" value="<?= e($v('monthly_salary')) ?>" step="0.01" min="0" placeholder="0.00">
                    </label>
                </div>

                <!-- Effectivity + Oath + Report dates + Is Current -->
                <div class="form-grid" style="display:grid; grid-template-columns:1fr 1fr 1fr 1fr; gap:var(--space-4); margin-bottom:var(--space-4);">
                    <label class="field">
                        <span>Effectivity Date <em class="text-red">*</em></span>
                        <input type="date" name="effectivity_date" value="<?= e($v('effectivity_date')) ?>" required>
                        <?= $err('effectivity_date') ?>
                    </label>
                    <label class="field">
                        <span>Oath Date</span>
                        <input type="date" name="oath_date" value="<?= e($v('oath_date')) ?>">
                        <?= $err('oath_date') ?>
                    </label>
                    <label class="field">
                        <span>Report Date</span>
                        <input type="date" name="report_date" value="<?= e($v('report_date')) ?>">
                        <?= $err('report_date') ?>
                    </label>
                    <div class="field" style="justify-content:flex-end; padding-top:var(--space-4);">
                        <label style="display:flex; align-items:center; gap:8px; cursor:pointer; font-weight:500;">
                            <?php $isCurrent = $old !== [] ? $v('is_current') === '1' : (bool) ($record['is_current'] ?? false); ?>
                            <input type="checkbox" name="is_current" value="1" <?= $isCurrent ? 'checked' : '' ?>>
                            Current Appointment
                        </label>
                    </div>
                </div>

                <!-- Remarks -->
                <label class="field">
                    <span>Remarks</span>
                    <textarea name="remarks" rows="3" style="resize:vertical;"><?= e($v('remarks')) ?></textarea>
                </label>

            </div>
        </div>

        <div style="display:flex; justify-content:flex-end; gap:var(--space-3); padding-bottom:var(--space-6);">
            <a class="btn btn-secondary" href="/appointments/<?= $recId ?>">Cancel</a>
            <button class="btn btn-primary" type="submit">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v14a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                Update Appointment
            </button>
        </div>

    </form>
</section>

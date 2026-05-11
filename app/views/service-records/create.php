<?php
$old      = is_array($old ?? null) ? $old : [];
$errors   = is_array($errors ?? null) ? $errors : [];
$empList  = is_array($employees ?? null) ? $employees : [];
$statuses = is_array($statuses ?? null) ? $statuses : [];
$natures  = is_array($natures ?? null) ? $natures : [];
$sepTypes = is_array($sepTypes ?? null) ? $sepTypes : [];
$preEmpId = (string) ($preEmpId ?? '');

$v = static function (string $key) use ($old): string {
    return (string) ($old[$key] ?? '');
};
$err = static function (string $key) use ($errors): string {
    return isset($errors[$key])
        ? '<span class="field-error">' . htmlspecialchars((string) $errors[$key], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</span>'
        : '';
};
?>

<section class="page">
    <header class="page-banner">
        <div class="page-banner-copy">
            <p class="page-banner-kicker">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M8 7h8M8 11h8M8 15h5"/><circle cx="18" cy="18" r="4"/><path d="m16.5 18 1 1 2-2"/></svg>
                Service Records
            </p>
            <h2 class="page-banner-title">Add Service Record</h2>
            <p class="page-banner-sub">Record employment history — position, salary grade, appointment type, and separation details.</p>
        </div>
        <div class="page-banner-actions">
            <a class="btn btn-secondary" href="/service-records">Cancel</a>
        </div>
    </header>

    <?php require __DIR__ . '/../partials/alerts.php'; ?>

    <form method="post" action="/service-records" novalidate>
        <input type="hidden" name="_csrf" value="<?= e((string) ($csrf ?? '')) ?>">

        <div class="card" style="margin-bottom:var(--space-5);">
            <div class="card-header">
                <div class="card-header-copy">
                    <h3>Employment Details</h3>
                    <p>Required fields are marked with <em class="text-red">*</em>.</p>
                </div>
            </div>
            <div class="card-body" style="padding:var(--space-5);">

                <!-- Row 1: Employee + Position Title -->
                <div class="form-grid" style="display:grid; grid-template-columns:1fr 1fr; gap:var(--space-4); margin-bottom:var(--space-4);">
                    <label class="field">
                        <span>Employee <em class="text-red">*</em></span>
                        <select name="employee_id" required>
                            <option value="">— Select employee —</option>
                            <?php foreach ($empList as $emp): ?>
                                <?php
                                $eid = (string) ($emp['id'] ?? '');
                                $sel = ($v('employee_id') !== '' ? $v('employee_id') : $preEmpId) === $eid ? 'selected' : '';
                                ?>
                                <option value="<?= (int) ($emp['id'] ?? 0) ?>" <?= $sel ?>>
                                    <?= e((string) ($emp['employee_code'] ?? '')) ?> &mdash;
                                    <?= e((string) ($emp['last_name'] ?? '')) ?>, <?= e((string) ($emp['first_name'] ?? '')) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?= $err('employee_id') ?>
                    </label>
                    <label class="field">
                        <span>Position Title <em class="text-red">*</em></span>
                        <input type="text" name="position_title" value="<?= e($v('position_title')) ?>" maxlength="150" required>
                        <?= $err('position_title') ?>
                    </label>
                </div>

                <!-- Row 2: Item Number + Office/Unit + Division -->
                <div class="form-grid" style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:var(--space-4); margin-bottom:var(--space-4);">
                    <label class="field">
                        <span>Plantilla Item Number</span>
                        <input type="text" name="item_number" value="<?= e($v('item_number')) ?>" maxlength="50">
                    </label>
                    <label class="field">
                        <span>Office / Unit</span>
                        <input type="text" name="office_unit" value="<?= e($v('office_unit')) ?>" maxlength="150">
                    </label>
                    <label class="field">
                        <span>Division</span>
                        <input type="text" name="division" value="<?= e($v('division')) ?>" maxlength="150">
                    </label>
                </div>

                <!-- Row 3: Salary Grade + Step + Monthly Salary -->
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

                <!-- Row 4: Appointment Status + Appointment Nature -->
                <div class="form-grid" style="display:grid; grid-template-columns:1fr 1fr; gap:var(--space-4); margin-bottom:var(--space-4);">
                    <label class="field">
                        <span>Appointment Status</span>
                        <select name="appointment_status">
                            <option value="">— None —</option>
                            <?php foreach ($statuses as $s): ?>
                                <option value="<?= e($s) ?>" <?= $v('appointment_status') === $s ? 'selected' : '' ?>><?= e($s) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?= $err('appointment_status') ?>
                    </label>
                    <label class="field">
                        <span>Appointment Nature</span>
                        <select name="appointment_nature">
                            <option value="">— None —</option>
                            <?php foreach ($natures as $n): ?>
                                <option value="<?= e($n) ?>" <?= $v('appointment_nature') === $n ? 'selected' : '' ?>><?= e($n) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?= $err('appointment_nature') ?>
                    </label>
                </div>

                <!-- Row 5: Date From + Date To + Is Current -->
                <div class="form-grid" style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:var(--space-4); margin-bottom:var(--space-4);">
                    <label class="field">
                        <span>Date From <em class="text-red">*</em></span>
                        <input type="date" name="date_from" value="<?= e($v('date_from')) ?>" required>
                        <?= $err('date_from') ?>
                    </label>
                    <label class="field">
                        <span>Date To <small style="color:var(--text-muted);">(leave blank if current)</small></span>
                        <input type="date" name="date_to" value="<?= e($v('date_to')) ?>">
                        <?= $err('date_to') ?>
                    </label>
                    <div class="field" style="justify-content:flex-end; padding-top:var(--space-4);">
                        <label style="display:flex; align-items:center; gap:8px; cursor:pointer; font-weight:500;">
                            <input type="checkbox" name="is_current" value="1" <?= $v('is_current') === '1' ? 'checked' : '' ?>>
                            Current Appointment
                        </label>
                    </div>
                </div>

            </div>
        </div>

        <div class="card" style="margin-bottom:var(--space-5);">
            <div class="card-header">
                <div class="card-header-copy">
                    <h3>Separation Details</h3>
                    <p>Fill in only if the employee has separated from this position.</p>
                </div>
            </div>
            <div class="card-body" style="padding:var(--space-5);">

                <!-- Row: Separation Type + Separation Date -->
                <div class="form-grid" style="display:grid; grid-template-columns:1fr 1fr; gap:var(--space-4); margin-bottom:var(--space-4);">
                    <label class="field">
                        <span>Separation Type</span>
                        <select name="separation_type">
                            <option value="">— None —</option>
                            <?php foreach ($sepTypes as $st): ?>
                                <option value="<?= e($st) ?>" <?= $v('separation_type') === $st ? 'selected' : '' ?>><?= e($st) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?= $err('separation_type') ?>
                    </label>
                    <label class="field">
                        <span>Separation Date</span>
                        <input type="date" name="separation_date" value="<?= e($v('separation_date')) ?>">
                        <?= $err('separation_date') ?>
                    </label>
                </div>

                <!-- Remarks -->
                <label class="field">
                    <span>Remarks</span>
                    <textarea name="remarks" rows="3" style="resize:vertical;"><?= e($v('remarks')) ?></textarea>
                </label>

            </div>
        </div>

        <div style="display:flex; justify-content:flex-end; gap:var(--space-3); padding-bottom:var(--space-6);">
            <a class="btn btn-secondary" href="/service-records">Cancel</a>
            <button class="btn btn-primary" type="submit">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v14a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                Save Record
            </button>
        </div>

    </form>
</section>

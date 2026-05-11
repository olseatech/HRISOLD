<?php
$old     = is_array($old ?? null) ? $old : [];
$errors  = is_array($errors ?? null) ? $errors : [];
$empList = is_array($employees ?? null) ? $employees : [];
$types   = is_array($types ?? null) ? $types : [];
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
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/></svg>
                Clearances
            </p>
            <h2 class="page-banner-title">New Clearance Request</h2>
            <p class="page-banner-sub">Create a clearance request for an employee. Default sign-off offices will be added automatically.</p>
        </div>
        <div class="page-banner-actions">
            <a class="btn btn-secondary" href="/clearances">Cancel</a>
        </div>
    </header>

    <?php require __DIR__ . '/../partials/alerts.php'; ?>

    <form method="post" action="/clearances" novalidate>
        <input type="hidden" name="_csrf" value="<?= e((string) ($csrf ?? '')) ?>">

        <div class="card" style="margin-bottom:var(--space-5);">
            <div class="card-header">
                <div class="card-header-copy">
                    <h3>Clearance Details</h3>
                    <p>Required fields are marked with <em class="text-red">*</em>.</p>
                </div>
            </div>
            <div class="card-body" style="padding:var(--space-5);">

                <!-- Employee + Type -->
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
                        <span>Clearance Type <em class="text-red">*</em></span>
                        <select name="clearance_type" required>
                            <option value="">— Select type —</option>
                            <?php foreach ($types as $t): ?>
                                <option value="<?= e($t) ?>" <?= $v('clearance_type') === $t ? 'selected' : '' ?>><?= e($t) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?= $err('clearance_type') ?>
                    </label>
                </div>

                <!-- Request Date -->
                <div style="margin-bottom:var(--space-4); max-width:320px;">
                    <label class="field">
                        <span>Request Date <em class="text-red">*</em></span>
                        <input type="date" name="request_date" value="<?= e($v('request_date') !== '' ? $v('request_date') : date('Y-m-d')) ?>" required>
                        <?= $err('request_date') ?>
                    </label>
                </div>

                <!-- Purpose -->
                <label class="field" style="margin-bottom:var(--space-4);">
                    <span>Purpose / Reason</span>
                    <textarea name="purpose" rows="3" style="resize:vertical;" placeholder="Brief description of why the clearance is being requested..."><?= e($v('purpose')) ?></textarea>
                </label>

                <!-- Remarks -->
                <label class="field">
                    <span>Remarks</span>
                    <textarea name="remarks" rows="2" style="resize:vertical;"><?= e($v('remarks')) ?></textarea>
                </label>

            </div>
        </div>

        <div class="card" style="margin-bottom:var(--space-5); background:var(--bg-muted);">
            <div class="card-body" style="padding:var(--space-4);">
                <p style="font-size:13px; color:var(--text-muted); display:flex; align-items:center; gap:8px;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    The following offices will be added automatically as sign-off items: Finance / Accounting, Property / Supply, Information Technology, Human Resources, Immediate Supervisor, Administration.
                </p>
            </div>
        </div>

        <div style="display:flex; justify-content:flex-end; gap:var(--space-3); padding-bottom:var(--space-6);">
            <a class="btn btn-secondary" href="/clearances">Cancel</a>
            <button class="btn btn-primary" type="submit">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v14a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                Create Clearance
            </button>
        </div>

    </form>
</section>

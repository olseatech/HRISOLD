<?php
$record  = is_array($record ?? null) ? $record : [];
$old     = is_array($old ?? null) ? $old : [];
$errors  = is_array($errors ?? null) ? $errors : [];
$empList = is_array($employees ?? null) ? $employees : [];
$types   = is_array($types ?? null) ? $types : [];

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
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/></svg>
                Clearances
            </p>
            <h2 class="page-banner-title">Edit Clearance Request</h2>
            <p class="page-banner-sub">
                <?= e($empName !== ', ' ? $empName : 'Unknown') ?>
                <?php if ($empCode !== ''): ?><code style="font-weight:600; color:var(--blue-700);"><?= e($empCode) ?></code><?php endif; ?>
            </p>
        </div>
        <div class="page-banner-actions">
            <a class="btn btn-secondary" href="/clearances/<?= $recId ?>">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                Back to Clearance
            </a>
        </div>
    </header>

    <?php require __DIR__ . '/../partials/alerts.php'; ?>

    <form method="post" action="/clearances/<?= $recId ?>/update" novalidate>
        <input type="hidden" name="_csrf" value="<?= e((string) ($csrf ?? '')) ?>">

        <div class="card" style="margin-bottom:var(--space-5);">
            <div class="card-header">
                <div class="card-header-copy">
                    <h3>Clearance Details</h3>
                    <p>Required fields are marked with <em class="text-red">*</em>. Employee cannot be changed.</p>
                </div>
            </div>
            <div class="card-body" style="padding:var(--space-5);">

                <!-- Employee (locked) + Type -->
                <div class="form-grid" style="display:grid; grid-template-columns:1fr 1fr; gap:var(--space-4); margin-bottom:var(--space-4);">
                    <div class="field">
                        <span>Employee</span>
                        <input type="text" value="<?= e($empCode !== '' ? $empCode . ' — ' . $empName : $empName) ?>" disabled style="background:var(--bg-muted); color:var(--text-muted);">
                        <input type="hidden" name="employee_id" value="<?= (int) ($record['employee_id'] ?? 0) ?>">
                    </div>
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
                        <input type="date" name="request_date" value="<?= e($v('request_date')) ?>" required>
                        <?= $err('request_date') ?>
                    </label>
                </div>

                <!-- Purpose -->
                <label class="field" style="margin-bottom:var(--space-4);">
                    <span>Purpose / Reason</span>
                    <textarea name="purpose" rows="3" style="resize:vertical;"><?= e($v('purpose')) ?></textarea>
                </label>

                <!-- Remarks -->
                <label class="field">
                    <span>Remarks</span>
                    <textarea name="remarks" rows="2" style="resize:vertical;"><?= e($v('remarks')) ?></textarea>
                </label>

            </div>
        </div>

        <div style="display:flex; justify-content:flex-end; gap:var(--space-3); padding-bottom:var(--space-6);">
            <a class="btn btn-secondary" href="/clearances/<?= $recId ?>">Cancel</a>
            <button class="btn btn-primary" type="submit">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v14a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                Update Clearance
            </button>
        </div>

    </form>
</section>

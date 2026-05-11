<?php
$record     = is_array($record ?? null) ? $record : [];
$old        = is_array($old ?? null) ? $old : [];
$errors     = is_array($errors ?? null) ? $errors : [];
$typeOptions= is_array($leaveTypes ?? null) ? $leaveTypes : [];
$leaveId    = (int) ($record['id'] ?? 0);

$v = static function (string $key, string $fallback = '') use ($old, $record): string {
    if ($old !== []) {
        return (string) ($old[$key] ?? $fallback);
    }
    return (string) ($record[$key] ?? $fallback);
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
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                Leave Management
            </p>
            <h2 class="page-banner-title">Edit Draft Request #<?= $leaveId ?></h2>
            <p class="page-banner-sub">Update the details of this draft. Only draft requests can be edited.</p>
        </div>
        <div class="page-banner-actions">
            <a class="btn btn-secondary" href="/leave/<?= $leaveId ?>">Cancel</a>
        </div>
    </header>

    <?php require __DIR__ . '/../partials/alerts.php'; ?>

    <form method="post" action="/leave/<?= $leaveId ?>/update" enctype="multipart/form-data" novalidate>
        <input type="hidden" name="_csrf" value="<?= e((string) ($csrf ?? '')) ?>">

        <div class="card" style="margin-bottom:var(--space-5);">
            <div class="card-header">
                <div class="card-header-copy">
                    <h3>Request Details</h3>
                    <p>Required fields are marked with <em class="text-red">*</em>.</p>
                </div>
            </div>
            <div class="card-body" style="padding:var(--space-5);">

                <!-- Employee (locked) -->
                <div class="field" style="margin-bottom:var(--space-4);">
                    <span class="field-label">Employee</span>
                    <p style="margin-top:4px; font-weight:600; color:var(--slate-900);">
                        <?= e((string) ($record['employee_code'] ?? '')) ?> &mdash;
                        <?= e((string) ($record['first_name'] ?? '')) ?> <?= e((string) ($record['last_name'] ?? '')) ?>
                    </p>
                </div>

                <!-- Leave type + days -->
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:var(--space-4); margin-bottom:var(--space-4);">
                    <label class="field">
                        <span>Leave Type <em class="text-red">*</em></span>
                        <select name="leave_type_id" required>
                            <option value="">— Select type —</option>
                            <?php foreach ($typeOptions as $lt): ?>
                                <option value="<?= (int) ($lt['id'] ?? 0) ?>"
                                    <?= $v('leave_type_id') === (string) ($lt['id'] ?? '') ? 'selected' : '' ?>>
                                    <?= e((string) ($lt['type_name'] ?? '')) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?= $err('leave_type_id') ?>
                    </label>
                    <label class="field">
                        <span>Number of Days <em class="text-red">*</em></span>
                        <input type="number" name="total_days" id="totalDaysInput"
                               value="<?= e($v('total_days')) ?>" step="0.5" min="0.5" required>
                        <?= $err('total_days') ?>
                    </label>
                </div>

                <!-- Date range -->
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:var(--space-4); margin-bottom:var(--space-4);">
                    <label class="field">
                        <span>Start Date <em class="text-red">*</em></span>
                        <input type="date" name="start_date" id="startDate" value="<?= e($v('start_date')) ?>" required>
                        <?= $err('start_date') ?>
                    </label>
                    <label class="field">
                        <span>End Date <em class="text-red">*</em></span>
                        <input type="date" name="end_date" id="endDate" value="<?= e($v('end_date')) ?>" required>
                        <?= $err('end_date') ?>
                    </label>
                </div>

                <!-- Reason -->
                <label class="field" style="margin-bottom:var(--space-4);">
                    <span>Reason <small style="color:var(--text-muted);">(optional)</small></span>
                    <textarea name="reason" rows="3" style="resize:vertical;"><?= e($v('reason')) ?></textarea>
                </label>

                <!-- Add more attachments -->
                <div class="field">
                    <span>Add More Documents <small style="color:var(--text-muted);">(optional)</small></span>
                    <div style="border:2px dashed var(--border); border-radius:var(--radius-lg); padding:var(--space-4); text-align:center; background:var(--bg-muted);">
                        <input type="file" name="attachments[]" multiple accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                               style="display:block; margin:0 auto; max-width:300px;"
                               onchange="updateFileList(this)">
                        <p id="fileListPreview" style="font-size:12px; color:var(--primary); margin-top:8px; font-weight:500;"></p>
                        <p style="font-size:11px; color:var(--text-muted); margin-top:4px;">PDF, JPG, PNG, DOC, DOCX &mdash; max 10 MB each</p>
                    </div>
                </div>

            </div>
        </div>

        <div style="display:flex; justify-content:flex-end; gap:var(--space-3); padding-bottom:var(--space-6);">
            <a class="btn btn-secondary" href="/leave/<?= $leaveId ?>">Cancel</a>
            <button class="btn btn-primary" type="submit">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                Save Changes
            </button>
        </div>
    </form>
</section>

<script>
function updateFileList(input) {
    const preview = document.getElementById('fileListPreview');
    if (input.files.length === 0) { preview.textContent = ''; return; }
    const names = Array.from(input.files).map(f => f.name).join(', ');
    preview.textContent = input.files.length + ' file(s): ' + names;
}

(function () {
    const startEl = document.getElementById('startDate');
    const endEl   = document.getElementById('endDate');
    const daysEl  = document.getElementById('totalDaysInput');

    function countWeekdays(from, to) {
        let count = 0;
        const d = new Date(from);
        const end = new Date(to);
        while (d <= end) {
            const dow = d.getDay();
            if (dow !== 0 && dow !== 6) count++;
            d.setDate(d.getDate() + 1);
        }
        return count;
    }

    function recalc() {
        if (!startEl.value || !endEl.value) return;
        if (startEl.value > endEl.value) return;
        const days = countWeekdays(startEl.value, endEl.value);
        if (days > 0) daysEl.value = days;
    }

    startEl.addEventListener('change', recalc);
    endEl.addEventListener('change', recalc);
})();
</script>

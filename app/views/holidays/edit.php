<?php
$record = is_array($record ?? null) ? $record : [];
$old    = is_array($old ?? null) ? $old : [];
$errors = is_array($errors ?? null) ? $errors : [];
$types  = is_array($types ?? null) ? $types : [];
$recId  = (int) ($record['id'] ?? 0);

$v = static function (string $key, string $fallback = '') use ($old, $record): string {
    if ($old !== []) {
        return (string) ($old[$key] ?? $fallback);
    }
    return (string) ($record[$key] ?? $fallback);
};

$isRecurring = $old !== []
    ? ($v('is_recurring') === '1')
    : (int) ($record['is_recurring'] ?? 0) === 1;

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
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M3 10h18M16 2v4M8 2v4"/></svg>
                Holidays
            </p>
            <h2 class="page-banner-title">Edit Holiday</h2>
            <p class="page-banner-sub">Update this holiday's details.</p>
        </div>
        <div class="page-banner-actions">
            <a class="btn btn-secondary" href="/holidays">Back to list</a>
        </div>
    </header>

    <?php require __DIR__ . '/../partials/alerts.php'; ?>

    <form method="post" action="/holidays/<?= $recId ?>/update" novalidate>
        <input type="hidden" name="_csrf" value="<?= e((string) ($csrf ?? '')) ?>">

        <div class="card" style="margin-bottom:var(--space-5);">
            <div class="card-header">
                <div class="card-header-copy">
                    <h3>Holiday Details</h3>
                    <p>Required fields are marked with <em class="text-red">*</em>.</p>
                </div>
            </div>
            <div class="card-body" style="padding:var(--space-5);">

                <!-- Name + Date -->
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:var(--space-4); margin-bottom:var(--space-4);">
                    <label class="field">
                        <span>Holiday Name <em class="text-red">*</em></span>
                        <input type="text" name="name" value="<?= e($v('name')) ?>"
                               maxlength="150" required>
                        <?= $err('name') ?>
                    </label>
                    <label class="field">
                        <span>Date <em class="text-red">*</em></span>
                        <input type="date" name="holiday_date" value="<?= e($v('holiday_date')) ?>" required>
                        <?= $err('holiday_date') ?>
                    </label>
                </div>

                <!-- Type + Recurring -->
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:var(--space-4); margin-bottom:var(--space-4);">
                    <label class="field">
                        <span>Holiday Type <em class="text-red">*</em></span>
                        <select name="holiday_type" required>
                            <option value="">— Select type —</option>
                            <?php foreach ($types as $t): ?>
                                <option value="<?= e($t) ?>" <?= $v('holiday_type') === $t ? 'selected' : '' ?>><?= e($t) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?= $err('holiday_type') ?>
                    </label>
                    <div class="field" style="padding-top:28px;">
                        <label style="display:flex; align-items:center; gap:10px; cursor:pointer;">
                            <input type="checkbox" name="is_recurring" value="1"
                                   <?= $isRecurring ? 'checked' : '' ?>
                                   style="width:16px; height:16px;">
                            <span>Recurring annually <small style="color:var(--text-muted);">(same month/day every year)</small></span>
                        </label>
                    </div>
                </div>

                <!-- Remarks -->
                <label class="field">
                    <span>Remarks <small style="color:var(--text-muted);">(optional)</small></span>
                    <input type="text" name="remarks" value="<?= e($v('remarks')) ?>" maxlength="255">
                </label>

            </div>
        </div>

        <div style="display:flex; justify-content:flex-end; gap:var(--space-3); padding-bottom:var(--space-6);">
            <a class="btn btn-secondary" href="/holidays">Cancel</a>
            <button class="btn btn-primary" type="submit">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                Save Changes
            </button>
        </div>
    </form>
</section>

<?php
$old        = is_array($old ?? null) ? $old : [];
$errors     = is_array($errors ?? null) ? $errors : [];
$empList    = is_array($employees ?? null) ? $employees : [];
$categories = is_array($categories ?? null) ? $categories : [];
$preEmpId   = (string) ($preEmpId ?? '');

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
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                201 Documents
            </p>
            <h2 class="page-banner-title">Upload Document</h2>
            <p class="page-banner-sub">Attach a file to an employee's 201 file. Allowed types: PDF, JPG, PNG, DOC, DOCX &mdash; max 10 MB.</p>
        </div>
        <div class="page-banner-actions">
            <a class="btn btn-secondary" href="/documents">Cancel</a>
        </div>
    </header>

    <?php require __DIR__ . '/../partials/alerts.php'; ?>

    <form method="post" action="/documents" enctype="multipart/form-data" novalidate>
        <input type="hidden" name="_csrf" value="<?= e((string) ($csrf ?? '')) ?>">

        <div class="card" style="margin-bottom:var(--space-5);">
            <div class="card-header">
                <div class="card-header-copy">
                    <h3>Document Details</h3>
                    <p>Required fields are marked with <em class="text-red">*</em>.</p>
                </div>
            </div>
            <div class="card-body" style="padding:var(--space-5);">

                <!-- Employee + Category -->
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
                        <span>Category <em class="text-red">*</em></span>
                        <select name="doc_category" required>
                            <option value="">— Select category —</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= e($cat) ?>" <?= $v('doc_category') === $cat ? 'selected' : '' ?>><?= e($cat) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?= $err('doc_category') ?>
                    </label>
                </div>

                <!-- Title -->
                <label class="field" style="margin-bottom:var(--space-4);">
                    <span>Document Title <em class="text-red">*</em></span>
                    <input type="text" name="title" value="<?= e($v('title')) ?>" maxlength="200" required placeholder="e.g. Appointment Order No. 2024-001">
                    <?= $err('title') ?>
                </label>

                <!-- Description -->
                <label class="field" style="margin-bottom:var(--space-4);">
                    <span>Description <small style="color:var(--text-muted);">(optional)</small></span>
                    <textarea name="description" rows="2" style="resize:vertical;" placeholder="Brief description of this document..."><?= e($v('description')) ?></textarea>
                </label>

                <!-- File Upload -->
                <div class="field">
                    <span>File <em class="text-red">*</em></span>
                    <div style="border:2px dashed var(--border); border-radius:var(--radius-lg); padding:var(--space-5); text-align:center; background:var(--bg-muted);">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="color:var(--text-muted); margin-bottom:8px;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                        <p style="font-size:13px; color:var(--text-muted); margin-bottom:var(--space-3);">Drag and drop or click to select</p>
                        <input type="file" name="document" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" required
                            style="display:block; margin:0 auto; max-width:280px;"
                            onchange="document.getElementById('fileName').textContent = this.files[0] ? this.files[0].name : ''">
                        <p id="fileName" style="font-size:12px; color:var(--primary); margin-top:8px; font-weight:500;"></p>
                        <p style="font-size:11px; color:var(--text-muted); margin-top:4px;">PDF, JPG, PNG, DOC, DOCX &mdash; max 10 MB</p>
                    </div>
                    <?= $err('document') ?>
                </div>

            </div>
        </div>

        <div style="display:flex; justify-content:flex-end; gap:var(--space-3); padding-bottom:var(--space-6);">
            <a class="btn btn-secondary" href="/documents">Cancel</a>
            <button class="btn btn-primary" type="submit">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                Upload Document
            </button>
        </div>

    </form>
</section>

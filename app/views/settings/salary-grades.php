<?php
$rows   = is_array($rows ?? null) ? $rows : [];
$errors = is_array($errors ?? null) ? $errors : [];
$old    = is_array($old ?? null) ? $old : [];
?>
<section class="page">
    <header class="page-banner">
        <div class="page-banner-copy">
            <p class="page-banner-kicker">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 1 0 0 7h5a3.5 3.5 0 1 1 0 7H6"/></svg>
                Settings
            </p>
            <h2 class="page-banner-title">Salary Grades</h2>
            <p class="page-banner-sub">Define salary grade ranges used in employee compensation and payroll.</p>
            <div class="page-banner-meta">
                <span class="badge badge-blue"><?= e((string) count($rows)) ?> grades</span>
            </div>
        </div>
        <div class="page-banner-actions">
            <a class="btn btn-secondary" href="/settings">← Back to Settings</a>
        </div>
    </header>

    <?php require __DIR__ . '/../partials/alerts.php'; ?>

    <div style="display:grid; grid-template-columns:1fr 1fr; gap:var(--space-5); align-items:start;">

        <!-- Add form -->
        <section class="card">
            <div class="card-header"><div class="card-header-copy"><h3>Add Salary Grade</h3></div></div>
            <div class="card-body" style="padding:var(--space-5);">
                <form method="post" action="/settings/salary-grades" novalidate>
                    <input type="hidden" name="_csrf" value="<?= e((string) ($csrf ?? '')) ?>">
                    <div style="margin-bottom:var(--space-4);">
                        <label class="field">
                            <span>Grade Name <em class="text-red">*</em></span>
                            <input type="text" name="grade_name" value="<?= e((string) ($old['grade_name'] ?? '')) ?>"
                                   maxlength="50" required placeholder="e.g. SG-1 or Grade 1">
                            <?php if (isset($errors['grade_name'])): ?>
                                <span class="field-error"><?= e((string) $errors['grade_name']) ?></span>
                            <?php endif; ?>
                        </label>
                    </div>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:var(--space-4); margin-bottom:var(--space-5);">
                        <label class="field">
                            <span>Minimum Salary</span>
                            <input type="number" name="min_salary" value="<?= e((string) ($old['min_salary'] ?? '0')) ?>" min="0" step="0.01">
                        </label>
                        <label class="field">
                            <span>Maximum Salary</span>
                            <input type="number" name="max_salary" value="<?= e((string) ($old['max_salary'] ?? '0')) ?>" min="0" step="0.01">
                        </label>
                    </div>
                    <button class="btn btn-primary" type="submit">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        Add Grade
                    </button>
                </form>
            </div>
        </section>

        <!-- List -->
        <section class="card overflow-hidden">
            <div class="card-header"><div class="card-header-copy"><h3>Salary Grade Table</h3></div></div>
            <div class="table-wrap no-border no-radius shadow-none">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Grade</th>
                            <th>Min (₱)</th>
                            <th>Max (₱)</th>
                            <th style="text-align:right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($rows === []): ?>
                            <tr><td colspan="4"><div class="table-empty"><p>No salary grades defined yet.</p></div></td></tr>
                        <?php else: ?>
                            <?php foreach ($rows as $row): ?>
                                <tr>
                                    <td><strong><?= e((string) ($row['grade_name'] ?? '-')) ?></strong></td>
                                    <td style="font-family:monospace;"><?= e(number_format((float)($row['min_salary']??0), 2)) ?></td>
                                    <td style="font-family:monospace;"><?= e(number_format((float)($row['max_salary']??0), 2)) ?></td>
                                    <td style="text-align:right;">
                                        <div class="row-actions" style="justify-content:flex-end;">
                                            <button class="btn-icon"
                                                    onclick="openEditSG(<?= (int)($row['id']??0) ?>, <?= htmlspecialchars(json_encode($row['grade_name']??''), ENT_QUOTES) ?>, <?= (float)($row['min_salary']??0) ?>, <?= (float)($row['max_salary']??0) ?>)"
                                                    title="Edit">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                            </button>
                                            <form method="post" action="/settings/salary-grades/<?= (int)($row['id']??0) ?>/delete"
                                                  onsubmit="return confirm('Delete this salary grade?');" style="display:inline;">
                                                <input type="hidden" name="_csrf" value="<?= e(\App\Core\CSRF::token()) ?>">
                                                <button type="submit" class="btn-icon text-red" title="Delete">
                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <!-- Edit modal -->
    <div id="editSGModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,.45); z-index:9999; align-items:center; justify-content:center;">
        <div class="card" style="width:400px; padding:var(--space-5);">
            <h3 style="margin-bottom:var(--space-4);">Edit Salary Grade</h3>
            <form method="post" id="editSGForm" novalidate>
                <input type="hidden" name="_csrf" value="<?= e((string) ($csrf ?? '')) ?>">
                <div style="margin-bottom:var(--space-4);">
                    <label class="field"><span>Grade Name <em class="text-red">*</em></span>
                        <input type="text" name="grade_name" id="editSGName" maxlength="50" required>
                    </label>
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:var(--space-4); margin-bottom:var(--space-5);">
                    <label class="field"><span>Min Salary</span>
                        <input type="number" name="min_salary" id="editSGMin" min="0" step="0.01">
                    </label>
                    <label class="field"><span>Max Salary</span>
                        <input type="number" name="max_salary" id="editSGMax" min="0" step="0.01">
                    </label>
                </div>
                <div style="display:flex; gap:var(--space-3); justify-content:flex-end;">
                    <button type="button" class="btn btn-secondary" onclick="closeSGModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</section>
<script>
function openEditSG(id, name, min, max) {
    document.getElementById('editSGName').value = name;
    document.getElementById('editSGMin').value  = min;
    document.getElementById('editSGMax').value  = max;
    document.getElementById('editSGForm').action = '/settings/salary-grades/' + id + '/update';
    document.getElementById('editSGModal').style.display = 'flex';
}
function closeSGModal() { document.getElementById('editSGModal').style.display = 'none'; }
document.getElementById('editSGModal').addEventListener('click', function(e){ if(e.target===this) closeSGModal(); });
</script>

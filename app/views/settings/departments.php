<?php
$rows   = is_array($rows ?? null) ? $rows : [];
$errors = is_array($errors ?? null) ? $errors : [];
$old    = is_array($old ?? null) ? $old : [];
?>
<section class="page">
    <header class="page-banner">
        <div class="page-banner-copy">
            <p class="page-banner-kicker">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
                Settings
            </p>
            <h2 class="page-banner-title">Departments / Divisions</h2>
            <p class="page-banner-sub">Manage organizational units used when creating employees.</p>
            <div class="page-banner-meta">
                <span class="badge badge-blue"><?= e((string) count($rows)) ?> total</span>
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
            <div class="card-header">
                <div class="card-header-copy">
                    <h3>Add Department</h3>
                    <p>Required fields are marked with <em class="text-red">*</em>.</p>
                </div>
            </div>
            <div class="card-body" style="padding:var(--space-5);">
                <form method="post" action="/settings/departments" novalidate>
                    <input type="hidden" name="_csrf" value="<?= e((string) ($csrf ?? '')) ?>">
                    <div style="margin-bottom:var(--space-4);">
                        <label class="field">
                            <span>Department Name <em class="text-red">*</em></span>
                            <input type="text" name="department_name" value="<?= e((string) ($old['department_name'] ?? '')) ?>"
                                   maxlength="100" required placeholder="e.g. Human Resources">
                            <?php if (isset($errors['department_name'])): ?>
                                <span class="field-error"><?= e((string) $errors['department_name']) ?></span>
                            <?php endif; ?>
                        </label>
                    </div>
                    <div style="margin-bottom:var(--space-5);">
                        <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                            <input type="checkbox" name="is_active" value="1"
                                   <?= ($old === [] || isset($old['is_active'])) ? 'checked' : '' ?>
                                   style="width:16px;height:16px;">
                            <span style="font-size:13px;">Active</span>
                        </label>
                    </div>
                    <button class="btn btn-primary" type="submit">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        Add Department
                    </button>
                </form>
            </div>
        </section>

        <!-- List -->
        <section class="card overflow-hidden">
            <div class="card-header">
                <div class="card-header-copy"><h3>Current Departments</h3></div>
            </div>
            <div class="table-wrap no-border no-radius shadow-none">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Status</th>
                            <th style="text-align:right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($rows === []): ?>
                            <tr><td colspan="3"><div class="table-empty"><p>No departments yet.</p></div></td></tr>
                        <?php else: ?>
                            <?php foreach ($rows as $row): ?>
                                <tr>
                                    <td><strong><?= e((string) ($row['department_name'] ?? '-')) ?></strong>
                                        <?php if ($row['branch_name'] ?? ''): ?>
                                            <br><small style="color:var(--text-muted);"><?= e((string) $row['branch_name']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ((int)($row['is_active'] ?? 0) === 1): ?>
                                            <span class="badge badge-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge badge-neutral">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="text-align:right;">
                                        <div class="row-actions" style="justify-content:flex-end;">
                                            <button class="btn-icon"
                                                    onclick="openEditDept(<?= (int)($row['id']??0) ?>, <?= htmlspecialchars(json_encode($row['department_name']??''), ENT_QUOTES) ?>, <?= (int)($row['is_active']??0) ?>)"
                                                    title="Edit">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                            </button>
                                            <form method="post" action="/settings/departments/<?= (int)($row['id']??0) ?>/delete"
                                                  onsubmit="return confirm('Delete this department?');" style="display:inline;">
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
    <div id="editDeptModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,.45); z-index:9999; align-items:center; justify-content:center;">
        <div class="card" style="width:380px; padding:var(--space-5);">
            <h3 style="margin-bottom:var(--space-4);">Edit Department</h3>
            <form method="post" id="editDeptForm" novalidate>
                <input type="hidden" name="_csrf" value="<?= e((string) ($csrf ?? '')) ?>">
                <div style="margin-bottom:var(--space-4);">
                    <label class="field">
                        <span>Department Name <em class="text-red">*</em></span>
                        <input type="text" name="department_name" id="editDeptName" maxlength="100" required>
                    </label>
                </div>
                <div style="margin-bottom:var(--space-5);">
                    <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                        <input type="checkbox" name="is_active" id="editDeptActive" value="1" style="width:16px;height:16px;">
                        <span style="font-size:13px;">Active</span>
                    </label>
                </div>
                <div style="display:flex; gap:var(--space-3); justify-content:flex-end;">
                    <button type="button" class="btn btn-secondary" onclick="closeDeptModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</section>
<script>
function openEditDept(id, name, isActive) {
    document.getElementById('editDeptName').value = name;
    document.getElementById('editDeptActive').checked = isActive === 1;
    document.getElementById('editDeptForm').action = '/settings/departments/' + id + '/update';
    document.getElementById('editDeptModal').style.display = 'flex';
}
function closeDeptModal() { document.getElementById('editDeptModal').style.display = 'none'; }
document.getElementById('editDeptModal').addEventListener('click', function(e){ if(e.target===this) closeDeptModal(); });
</script>

<?php
$rows   = is_array($rows ?? null) ? $rows : [];
$errors = is_array($errors ?? null) ? $errors : [];
$old    = is_array($old ?? null) ? $old : [];
?>
<section class="page">
    <header class="page-banner">
        <div class="page-banner-copy">
            <p class="page-banner-kicker">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M3 10h18M8 2v3M16 2v3"/></svg>
                Settings
            </p>
            <h2 class="page-banner-title">Leave Types</h2>
            <p class="page-banner-sub">Configure leave categories available for employee requests.</p>
            <div class="page-banner-meta">
                <span class="badge badge-blue"><?= e((string) count($rows)) ?> types</span>
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
            <div class="card-header"><div class="card-header-copy"><h3>Add Leave Type</h3></div></div>
            <div class="card-body" style="padding:var(--space-5);">
                <form method="post" action="/settings/leave-types" novalidate>
                    <input type="hidden" name="_csrf" value="<?= e((string) ($csrf ?? '')) ?>">
                    <div style="margin-bottom:var(--space-4);">
                        <label class="field">
                            <span>Leave Type Name <em class="text-red">*</em></span>
                            <input type="text" name="type_name" value="<?= e((string) ($old['type_name'] ?? '')) ?>"
                                   maxlength="100" required placeholder="e.g. Vacation Leave">
                            <?php if (isset($errors['type_name'])): ?>
                                <span class="field-error"><?= e((string) $errors['type_name']) ?></span>
                            <?php endif; ?>
                        </label>
                    </div>
                    <div style="margin-bottom:var(--space-4);">
                        <label class="field">
                            <span>Description <small style="color:var(--text-muted);">(optional)</small></span>
                            <input type="text" name="description" value="<?= e((string) ($old['description'] ?? '')) ?>" maxlength="255">
                        </label>
                    </div>
                    <div style="margin-bottom:var(--space-4);">
                        <label class="field">
                            <span>Default Days Allowed</span>
                            <input type="number" name="default_days" value="<?= e((string) ($old['default_days'] ?? '5')) ?>" min="0" max="365" style="width:120px;">
                        </label>
                    </div>
                    <div style="margin-bottom:var(--space-4); display:flex; gap:var(--space-5);">
                        <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                            <input type="checkbox" name="is_paid" value="1"
                                   <?= ($old === [] || isset($old['is_paid'])) ? 'checked' : '' ?>
                                   style="width:16px;height:16px;">
                            <span style="font-size:13px;">Paid leave</span>
                        </label>
                        <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                            <input type="checkbox" name="is_active" value="1"
                                   <?= ($old === [] || isset($old['is_active'])) ? 'checked' : '' ?>
                                   style="width:16px;height:16px;">
                            <span style="font-size:13px;">Active</span>
                        </label>
                    </div>
                    <button class="btn btn-primary" type="submit">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        Add Leave Type
                    </button>
                </form>
            </div>
        </section>

        <!-- List -->
        <section class="card overflow-hidden">
            <div class="card-header"><div class="card-header-copy"><h3>Configured Leave Types</h3></div></div>
            <div class="table-wrap no-border no-radius shadow-none">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Days</th>
                            <th>Paid</th>
                            <th>Status</th>
                            <th style="text-align:right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($rows === []): ?>
                            <tr><td colspan="5"><div class="table-empty"><p>No leave types configured yet.</p></div></td></tr>
                        <?php else: ?>
                            <?php foreach ($rows as $row): ?>
                                <tr>
                                    <td>
                                        <strong><?= e((string) ($row['type_name'] ?? '-')) ?></strong>
                                        <?php if ($row['description'] ?? ''): ?>
                                            <br><small style="color:var(--text-muted);"><?= e((string) $row['description']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= e((string) ($row['default_days'] ?? '0')) ?></td>
                                    <td><?= (int)($row['is_paid']??0)===1 ? '<span class="badge badge-success">Yes</span>' : '<span style="color:var(--text-muted);font-size:12px;">No</span>' ?></td>
                                    <td><?= (int)($row['is_active']??0)===1 ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-neutral">Inactive</span>' ?></td>
                                    <td style="text-align:right;">
                                        <div class="row-actions" style="justify-content:flex-end;">
                                            <button class="btn-icon"
                                                    onclick="openEditLT(<?= (int)($row['id']??0) ?>, <?= htmlspecialchars(json_encode($row['type_name']??''), ENT_QUOTES) ?>, <?= htmlspecialchars(json_encode($row['description']??''), ENT_QUOTES) ?>, <?= (int)($row['default_days']??0) ?>, <?= (int)($row['is_paid']??0) ?>, <?= (int)($row['is_active']??0) ?>)"
                                                    title="Edit">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                            </button>
                                            <form method="post" action="/settings/leave-types/<?= (int)($row['id']??0) ?>/delete"
                                                  onsubmit="return confirm('Delete this leave type?');" style="display:inline;">
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
    <div id="editLTModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,.45); z-index:9999; align-items:center; justify-content:center;">
        <div class="card" style="width:420px; padding:var(--space-5);">
            <h3 style="margin-bottom:var(--space-4);">Edit Leave Type</h3>
            <form method="post" id="editLTForm" novalidate>
                <input type="hidden" name="_csrf" value="<?= e((string) ($csrf ?? '')) ?>">
                <div style="margin-bottom:var(--space-4);">
                    <label class="field"><span>Name <em class="text-red">*</em></span>
                        <input type="text" name="type_name" id="editLTName" maxlength="100" required>
                    </label>
                </div>
                <div style="margin-bottom:var(--space-4);">
                    <label class="field"><span>Description</span>
                        <input type="text" name="description" id="editLTDesc" maxlength="255">
                    </label>
                </div>
                <div style="margin-bottom:var(--space-4);">
                    <label class="field"><span>Default Days</span>
                        <input type="number" name="default_days" id="editLTDays" min="0" max="365" style="width:120px;">
                    </label>
                </div>
                <div style="margin-bottom:var(--space-5); display:flex; gap:var(--space-5);">
                    <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                        <input type="checkbox" name="is_paid" id="editLTPaid" value="1" style="width:16px;height:16px;">
                        <span style="font-size:13px;">Paid</span>
                    </label>
                    <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                        <input type="checkbox" name="is_active" id="editLTActive" value="1" style="width:16px;height:16px;">
                        <span style="font-size:13px;">Active</span>
                    </label>
                </div>
                <div style="display:flex; gap:var(--space-3); justify-content:flex-end;">
                    <button type="button" class="btn btn-secondary" onclick="closeLTModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</section>
<script>
function openEditLT(id, name, desc, days, isPaid, isActive) {
    document.getElementById('editLTName').value  = name;
    document.getElementById('editLTDesc').value  = desc;
    document.getElementById('editLTDays').value  = days;
    document.getElementById('editLTPaid').checked   = isPaid === 1;
    document.getElementById('editLTActive').checked = isActive === 1;
    document.getElementById('editLTForm').action = '/settings/leave-types/' + id + '/update';
    document.getElementById('editLTModal').style.display = 'flex';
}
function closeLTModal() { document.getElementById('editLTModal').style.display = 'none'; }
document.getElementById('editLTModal').addEventListener('click', function(e){ if(e.target===this) closeLTModal(); });
</script>

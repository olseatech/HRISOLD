<?php
$record     = is_array($record ?? null) ? $record : [];
$items      = is_array($items ?? null) ? $items : [];
$canUpdate  = can('clearances.update');
$canDelete  = can('clearances.delete');
$canApprove = can('clearances.approve');

$recId     = (int) ($record['id'] ?? 0);
$empName   = trim(($record['emp_last'] ?? '') . ', ' . ($record['emp_first'] ?? ''));
$empCode   = (string) ($record['employee_code'] ?? '');
$recStatus = (string) ($record['status'] ?? 'Pending');

$statusBadgeClass = match($recStatus) {
    'Approved' => 'badge-success',
    'Rejected' => 'badge-danger',
    default    => 'badge-warning',
};

$itemStatusBadge = static function (string $s): string {
    return match($s) {
        'Cleared'         => 'badge-success',
        'Not Applicable'  => 'badge-neutral',
        default           => 'badge-warning',
    };
};

$val = static function (string $key, string $fallback = '—') use ($record): string {
    $v = trim((string) ($record[$key] ?? ''));
    return $v !== '' ? htmlspecialchars($v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') : htmlspecialchars($fallback, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
};
?>

<section class="page">
    <header class="page-banner">
        <div class="page-banner-copy">
            <p class="page-banner-kicker">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/></svg>
                Clearances
            </p>
            <h2 class="page-banner-title"><?= $val('clearance_type') ?> Clearance</h2>
            <p class="page-banner-sub">
                <?= e($empName !== ', ' ? $empName : 'Unknown') ?>
                <?php if ($empCode !== ''): ?>&nbsp;<code style="font-weight:600; color:var(--blue-700);"><?= e($empCode) ?></code><?php endif; ?>
            </p>
            <div class="page-banner-meta">
                <span class="badge <?= e($statusBadgeClass) ?>"><?= e($recStatus) ?></span>
                <span class="badge"><?= e(format_date((string) ($record['request_date'] ?? ''))) ?></span>
            </div>
        </div>
        <div class="page-banner-actions">
            <a class="btn btn-secondary" href="/clearances">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                Back to list
            </a>
            <?php if ($canUpdate && $recStatus === 'Pending'): ?>
                <a class="btn btn-secondary" href="/clearances/<?= $recId ?>/edit">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    Edit
                </a>
            <?php endif; ?>
        </div>
    </header>

    <?php require __DIR__ . '/../partials/alerts.php'; ?>

    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(380px, 1fr)); gap:var(--space-5); margin-bottom:var(--space-5);">

        <!-- Clearance Info -->
        <article class="card card-shine">
            <div class="card-header" style="border-bottom:1px solid var(--slate-100);">
                <div style="display:flex; align-items:center; gap:12px;">
                    <div style="width:36px; height:36px; border-radius:10px; background:var(--blue-50); color:var(--blue-600); display:flex; align-items:center; justify-content:center;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    </div>
                    <div>
                        <h3 style="font-size:1rem; font-weight:800; color:var(--slate-900);">Clearance Information</h3>
                        <p style="font-size:.75rem; color:var(--slate-500);">Request details and current status.</p>
                    </div>
                </div>
            </div>
            <div class="card-body" style="padding:var(--space-5);">
                <dl class="detail-list">
                    <div class="detail-row">
                        <dt>Employee</dt>
                        <dd>
                            <?= e($empName !== ', ' ? $empName : '—') ?>
                            <?php if ($empCode !== ''): ?><br><small style="color:var(--text-muted);"><?= e($empCode) ?></small><?php endif; ?>
                        </dd>
                    </div>
                    <div class="detail-row">
                        <dt>Clearance Type</dt>
                        <dd><?= $val('clearance_type') ?></dd>
                    </div>
                    <div class="detail-row">
                        <dt>Request Date</dt>
                        <dd><?= e(format_date((string) ($record['request_date'] ?? ''))) ?></dd>
                    </div>
                    <div class="detail-row">
                        <dt>Status</dt>
                        <dd><span class="badge <?= e($statusBadgeClass) ?>"><?= e($recStatus) ?></span></dd>
                    </div>
                    <?php if (($record['processed_at'] ?? '') !== ''): ?>
                        <div class="detail-row">
                            <dt>Processed At</dt>
                            <dd><?= e(format_date((string) $record['processed_at'])) ?></dd>
                        </div>
                    <?php endif; ?>
                    <?php if (($record['purpose'] ?? '') !== ''): ?>
                        <div class="detail-row">
                            <dt>Purpose</dt>
                            <dd><?= e((string) $record['purpose']) ?></dd>
                        </div>
                    <?php endif; ?>
                    <?php if (($record['remarks'] ?? '') !== ''): ?>
                        <div class="detail-row">
                            <dt>Remarks</dt>
                            <dd><?= e((string) $record['remarks']) ?></dd>
                        </div>
                    <?php endif; ?>
                </dl>
            </div>
        </article>

        <!-- Approve / Reject Panel -->
        <?php if ($canApprove && $recStatus === 'Pending'): ?>
        <article class="card card-shine">
            <div class="card-header" style="border-bottom:1px solid var(--slate-100);">
                <div style="display:flex; align-items:center; gap:12px;">
                    <div style="width:36px; height:36px; border-radius:10px; background:var(--teal-50,#f0fdfa); color:var(--teal-600,#0d9488); display:flex; align-items:center; justify-content:center;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    </div>
                    <div>
                        <h3 style="font-size:1rem; font-weight:800; color:var(--slate-900);">Process Request</h3>
                        <p style="font-size:.75rem; color:var(--slate-500);">Approve or reject this clearance.</p>
                    </div>
                </div>
            </div>
            <div class="card-body" style="padding:var(--space-5);">
                <form method="post" action="/clearances/<?= $recId ?>/approve">
                    <input type="hidden" name="_csrf" value="<?= e(\App\Core\CSRF::token()) ?>">
                    <button type="submit" class="btn btn-primary" style="width:100%; margin-bottom:var(--space-3);" onclick="return confirm('Approve this clearance request?');">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                        Approve Clearance
                    </button>
                </form>
                <form method="post" action="/clearances/<?= $recId ?>/reject" id="rejectForm">
                    <input type="hidden" name="_csrf" value="<?= e(\App\Core\CSRF::token()) ?>">
                    <label class="field" style="margin-bottom:var(--space-3);">
                        <span>Rejection Reason <small style="color:var(--text-muted);">(optional)</small></span>
                        <textarea name="remarks" rows="2" style="resize:vertical;" placeholder="State reason for rejection..."></textarea>
                    </label>
                    <button type="submit" class="btn btn-danger" style="width:100%; background:var(--danger,#dc2626); color:#fff; border-color:var(--danger,#dc2626);" onclick="return confirm('Reject this clearance request?');">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                        Reject Clearance
                    </button>
                </form>
            </div>
        </article>
        <?php endif; ?>

    </div>

    <!-- Clearance Items / Sign-off Sheet -->
    <section class="card overflow-hidden" style="margin-bottom:var(--space-5);">
        <div class="card-header">
            <div class="card-header-copy">
                <h3>Sign-off Sheet</h3>
                <p>Status per office/department. Update each item as the employee secures clearance.</p>
            </div>
        </div>
        <div class="table-wrap no-border no-radius shadow-none">
            <table class="table data-table">
                <thead>
                    <tr>
                        <th scope="col">Office / Department</th>
                        <th scope="col">Responsible Person</th>
                        <th scope="col">Status</th>
                        <th scope="col">Cleared At</th>
                        <th scope="col">Remarks</th>
                        <?php if ($canUpdate): ?>
                            <th scope="col" style="text-align:right;">Action</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($items === []): ?>
                        <tr>
                            <td colspan="<?= $canUpdate ? 6 : 5 ?>">
                                <div class="table-empty">
                                    <p>No sign-off items found.</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($items as $item): ?>
                            <?php $itemSt = (string) ($item['status'] ?? 'Pending'); ?>
                            <tr>
                                <td><strong><?= e((string) ($item['office_name'] ?? '-')) ?></strong></td>
                                <td><?= e((string) ($item['responsible_person'] ?? '—')) ?></td>
                                <td><span class="badge <?= e($itemStatusBadge($itemSt)) ?>"><?= e($itemSt) ?></span></td>
                                <td>
                                    <?php if (($item['cleared_at'] ?? '') !== ''): ?>
                                        <small><?= e(format_date((string) $item['cleared_at'])) ?></small>
                                    <?php else: ?>
                                        <span style="color:var(--text-muted);">—</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= e((string) ($item['remarks'] ?? '—')) ?></td>
                                <?php if ($canUpdate): ?>
                                    <td style="text-align:right;">
                                        <button type="button" class="btn btn-sm btn-secondary"
                                            onclick="openItemModal(<?= (int) ($item['id'] ?? 0) ?>, '<?= e(addslashes((string) ($item['office_name'] ?? ''))) ?>', '<?= e($itemSt) ?>', '<?= e(addslashes((string) ($item['remarks'] ?? ''))) ?>')">
                                            Update
                                        </button>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <?php if ($canUpdate): ?>
    <!-- Item Update Modal -->
    <div id="itemModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,.45); z-index:1000; align-items:center; justify-content:center;">
        <div style="background:#fff; border-radius:var(--radius-xl,12px); padding:var(--space-5); width:100%; max-width:440px; box-shadow:0 20px 60px rgba(0,0,0,.2);">
            <h3 style="margin-bottom:var(--space-4); font-size:1rem;">Update Sign-off: <span id="modalOfficeName"></span></h3>
            <form method="post" id="itemUpdateForm">
                <input type="hidden" name="_csrf" value="<?= e(\App\Core\CSRF::token()) ?>">
                <input type="hidden" name="item_id" id="modalItemId">
                <label class="field" style="margin-bottom:var(--space-3);">
                    <span>Status</span>
                    <select name="item_status" id="modalItemStatus">
                        <option value="Pending">Pending</option>
                        <option value="Cleared">Cleared</option>
                        <option value="Not Applicable">Not Applicable</option>
                    </select>
                </label>
                <label class="field" style="margin-bottom:var(--space-4);">
                    <span>Remarks</span>
                    <input type="text" name="item_remarks" id="modalItemRemarks" maxlength="255">
                </label>
                <div style="display:flex; gap:var(--space-3); justify-content:flex-end;">
                    <button type="button" class="btn btn-secondary" onclick="closeItemModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
    <script>
    function openItemModal(id, office, status, remarks) {
        document.getElementById('modalItemId').value = id;
        document.getElementById('modalOfficeName').textContent = office;
        document.getElementById('modalItemStatus').value = status;
        document.getElementById('modalItemRemarks').value = remarks;
        document.getElementById('itemUpdateForm').action = '/clearances/<?= $recId ?>/item-update';
        document.getElementById('itemModal').style.display = 'flex';
    }
    function closeItemModal() {
        document.getElementById('itemModal').style.display = 'none';
    }
    document.getElementById('itemModal').addEventListener('click', function(e) {
        if (e.target === this) closeItemModal();
    });
    </script>
    <?php endif; ?>

    <?php if ($canDelete): ?>
        <div style="margin-top:var(--space-4); padding:var(--space-4); background:var(--danger-bg,#fff5f5); border:1px solid var(--danger-border,#fecdd3); border-radius:var(--radius-lg); display:flex; align-items:center; justify-content:space-between; gap:var(--space-4);">
            <div>
                <p style="font-weight:600; color:var(--danger,#dc2626); margin-bottom:2px;">Delete this clearance request</p>
                <p style="font-size:13px; color:var(--text-muted);">This is permanent. All sign-off items will also be deleted.</p>
            </div>
            <form method="post" action="/clearances/<?= $recId ?>/delete" onsubmit="return confirm('Permanently delete this clearance request?');">
                <input type="hidden" name="_csrf" value="<?= e(\App\Core\CSRF::token()) ?>">
                <button type="submit" class="btn btn-danger" style="background:var(--danger,#dc2626); color:#fff; border-color:var(--danger,#dc2626);">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                    Delete Request
                </button>
            </form>
        </div>
    <?php endif; ?>

</section>

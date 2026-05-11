<?php
$record      = is_array($record ?? null) ? $record : [];
$attachments = is_array($attachments ?? null) ? $attachments : [];

$leaveId     = (int) ($record['id'] ?? 0);
$statusValue = (string) ($record['status'] ?? '');
$empName     = trim(($record['last_name'] ?? '') . ', ' . ($record['first_name'] ?? ''));
$empCode     = (string) ($record['employee_code'] ?? '');

$canApprove  = can('leave.approve');
$canRequest  = can('leave.request');
$currentUser = \App\Core\Auth::user();
$myEmpId     = isset($currentUser['employee_id']) ? (int) $currentUser['employee_id'] : 0;
$isOwner     = $myEmpId > 0 && $myEmpId === (int) ($record['employee_id'] ?? 0);

$statusBadgeStyle = match($statusValue) {
    'Draft'     => 'background:var(--slate-100); color:var(--slate-600);',
    'Pending'   => 'background:var(--amber-50); color:var(--amber-700);',
    'Approved'  => 'background:var(--green-50); color:var(--green-700);',
    'Rejected'  => 'background:var(--red-50,#fff5f5); color:var(--danger,#dc2626);',
    'Cancelled' => 'background:var(--slate-100); color:var(--slate-500);',
    default     => '',
};

$val = static function (string $key, string $fallback = '—') use ($record): string {
    $v = trim((string) ($record[$key] ?? ''));
    return $v !== '' ? htmlspecialchars($v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') : htmlspecialchars($fallback, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
};

$formatSize = static function (int $bytes): string {
    if ($bytes >= 1048576) return round($bytes / 1048576, 1) . ' MB';
    if ($bytes >= 1024)    return round($bytes / 1024, 1) . ' KB';
    return $bytes . ' B';
};
?>
<section class="page">
    <header class="page-banner">
        <div class="page-banner-copy">
            <p class="page-banner-kicker">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                Leave Management
            </p>
            <h2 class="page-banner-title">Leave Request #<?= $leaveId ?></h2>
            <p class="page-banner-sub">
                <?= e($empName !== ', ' ? $empName : 'Unknown') ?>
                <?php if ($empCode !== ''): ?>&nbsp;<code style="font-weight:600; color:var(--blue-700);"><?= e($empCode) ?></code><?php endif; ?>
            </p>
            <div class="page-banner-meta">
                <span class="badge" style="<?= e($statusBadgeStyle) ?>"><?= e($statusValue) ?></span>
                <?php if (($record['submitted_at'] ?? '') !== '' && $record['submitted_at'] !== null): ?>
                    <span class="badge">Submitted <?= e(format_date((string) $record['submitted_at'])) ?></span>
                <?php endif; ?>
            </div>
        </div>
        <div class="page-banner-actions">
            <?php if ($isOwner): ?>
                <a class="btn btn-secondary" href="/my-leave">My Leave</a>
            <?php else: ?>
                <a class="btn btn-secondary" href="/leave">Back to list</a>
            <?php endif; ?>
            <?php if ($isOwner && $canRequest && $statusValue === 'Draft'): ?>
                <a class="btn btn-secondary" href="/leave/<?= $leaveId ?>/edit">Edit Draft</a>
            <?php endif; ?>
        </div>
    </header>

    <?php require __DIR__ . '/../partials/alerts.php'; ?>

    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(360px, 1fr)); gap:var(--space-5);">

        <!-- Leave Details -->
        <article class="card card-shine">
            <div class="card-header" style="border-bottom:1px solid var(--slate-100);">
                <div style="display:flex; align-items:center; gap:12px;">
                    <div style="width:36px; height:36px; border-radius:10px; background:var(--blue-50); color:var(--blue-600); display:flex; align-items:center; justify-content:center;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                    </div>
                    <div>
                        <h3 style="font-size:1rem; font-weight:800; color:var(--slate-900);">Leave Details</h3>
                        <p style="font-size:.75rem; color:var(--slate-500);">Request information and dates.</p>
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
                        <dt>Leave Type</dt>
                        <dd><?= $val('type_name') ?></dd>
                    </div>
                    <div class="detail-row">
                        <dt>Start Date</dt>
                        <dd><?= $val('start_date') ?></dd>
                    </div>
                    <div class="detail-row">
                        <dt>End Date</dt>
                        <dd><?= $val('end_date') ?></dd>
                    </div>
                    <div class="detail-row">
                        <dt>Days Applied</dt>
                        <dd><?= $val('total_days') ?></dd>
                    </div>
                    <div class="detail-row">
                        <dt>Status</dt>
                        <dd><span class="badge" style="<?= e($statusBadgeStyle) ?>"><?= e($statusValue) ?></span></dd>
                    </div>
                    <?php if (($record['reason'] ?? '') !== ''): ?>
                        <div class="detail-row">
                            <dt>Reason</dt>
                            <dd><?= e((string) $record['reason']) ?></dd>
                        </div>
                    <?php endif; ?>
                    <?php if (($record['review_remarks'] ?? '') !== ''): ?>
                        <div class="detail-row">
                            <dt>Review Remarks</dt>
                            <dd><?= e((string) $record['review_remarks']) ?></dd>
                        </div>
                    <?php endif; ?>
                    <div class="detail-row">
                        <dt>Requested</dt>
                        <dd><?= e(format_date((string) ($record['created_at'] ?? ''))) ?></dd>
                    </div>
                </dl>
            </div>
        </article>

        <!-- Attachments -->
        <article class="card card-shine">
            <div class="card-header" style="border-bottom:1px solid var(--slate-100);">
                <div style="display:flex; align-items:center; gap:12px;">
                    <div style="width:36px; height:36px; border-radius:10px; background:var(--teal-50,#f0fdfa); color:var(--teal-600,#0d9488); display:flex; align-items:center; justify-content:center;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m21.44 11.05-9.19 9.19a6 6 0 0 1-8.49-8.49l8.57-8.57A4 4 0 1 1 18 8.84l-8.59 8.57a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg>
                    </div>
                    <div>
                        <h3 style="font-size:1rem; font-weight:800; color:var(--slate-900);">Supporting Documents</h3>
                        <p style="font-size:.75rem; color:var(--slate-500);"><?= count($attachments) ?> attachment(s)</p>
                    </div>
                </div>
            </div>
            <div class="card-body" style="padding:var(--space-5);">
                <?php if ($attachments === []): ?>
                    <p style="color:var(--text-muted); font-size:13px; text-align:center; padding:var(--space-4) 0;">No attachments uploaded.</p>
                <?php else: ?>
                    <ul style="list-style:none; padding:0; margin:0; display:flex; flex-direction:column; gap:var(--space-3);">
                        <?php foreach ($attachments as $att): ?>
                            <?php
                            $attSize = (int) ($att['file_size'] ?? 0);
                            $attExt  = strtolower(pathinfo((string) ($att['original_filename'] ?? ''), PATHINFO_EXTENSION));
                            ?>
                            <li style="display:flex; align-items:center; gap:12px; padding:var(--space-3); background:var(--bg-muted); border-radius:var(--radius);">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0; color:var(--blue-600);"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                                <div style="flex:1; min-width:0;">
                                    <p style="font-size:13px; font-weight:600; color:var(--slate-900); overflow:hidden; text-overflow:ellipsis; white-space:nowrap;"><?= e((string) ($att['original_filename'] ?? '')) ?></p>
                                    <p style="font-size:11px; color:var(--text-muted);"><?= e(strtoupper($attExt)) ?><?= $attSize > 0 ? ' &middot; ' . e($formatSize($attSize)) : '' ?></p>
                                </div>
                                <a href="/leave/<?= $leaveId ?>/attachment/<?= (int) ($att['id'] ?? 0) ?>/download"
                                   class="btn-icon" title="Download">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </article>

    </div>

    <!-- Employee actions: Submit (Draft) or Cancel (Pending) -->
    <?php if ($isOwner && $canRequest && $statusValue === 'Draft'): ?>
        <div style="margin-top:var(--space-6); padding:var(--space-4); background:var(--blue-50); border:1px solid var(--blue-200,#bfdbfe); border-radius:var(--radius-lg); display:flex; align-items:center; justify-content:space-between; gap:var(--space-4);">
            <div>
                <p style="font-weight:600; color:var(--blue-700); margin-bottom:2px;">Ready to submit?</p>
                <p style="font-size:13px; color:var(--text-muted);">Once submitted, the request will go to your approver.</p>
            </div>
            <form method="post" action="/leave/<?= $leaveId ?>/submit">
                <input type="hidden" name="_csrf" value="<?= e(\App\Core\CSRF::token()) ?>">
                <button type="submit" class="btn btn-primary">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/></svg>
                    Submit for Approval
                </button>
            </form>
        </div>
    <?php endif; ?>

    <?php if ($isOwner && $canRequest && $statusValue === 'Pending'): ?>
        <div style="margin-top:var(--space-6); padding:var(--space-4); background:var(--amber-50,#fffbeb); border:1px solid var(--amber-200,#fde68a); border-radius:var(--radius-lg); display:flex; align-items:center; justify-content:space-between; gap:var(--space-4);">
            <div>
                <p style="font-weight:600; color:var(--amber-700); margin-bottom:2px;">Need to withdraw?</p>
                <p style="font-size:13px; color:var(--text-muted);">You can cancel a pending request before it is processed.</p>
            </div>
            <form method="post" action="/leave/<?= $leaveId ?>/cancel" onsubmit="return confirm('Cancel this leave request?');">
                <input type="hidden" name="_csrf" value="<?= e(\App\Core\CSRF::token()) ?>">
                <button type="submit" class="btn" style="background:var(--amber-600,#d97706); color:#fff; border-color:var(--amber-600,#d97706);">
                    Cancel Request
                </button>
            </form>
        </div>
    <?php endif; ?>

    <!-- Admin approve/reject panel -->
    <?php if ($canApprove && $statusValue === 'Pending'): ?>
        <?php
        $currentUserEmpId = isset($currentUser['employee_id']) ? (int) $currentUser['employee_id'] : null;
        $reqEmpId         = (int) ($record['employee_id'] ?? 0);
        $isSelfRequest    = $currentUserEmpId !== null && $currentUserEmpId === $reqEmpId;
        ?>
        <?php if (!$isSelfRequest): ?>
            <div style="margin-top:var(--space-6); padding:var(--space-5); background:var(--bg-muted); border:1px solid var(--border); border-radius:var(--radius-lg);">
                <h4 style="font-size:.95rem; font-weight:700; margin-bottom:var(--space-4); color:var(--slate-900);">Process Request</h4>
                <div style="display:flex; gap:var(--space-4); flex-wrap:wrap;">
                    <form method="post" action="/leave/<?= $leaveId ?>/approve" style="flex:1; min-width:200px;">
                        <input type="hidden" name="_csrf" value="<?= e(\App\Core\CSRF::token()) ?>">
                        <input type="hidden" name="from_show" value="1">
                        <label class="field" style="margin-bottom:var(--space-3);">
                            <span>Approval Remarks</span>
                            <input type="text" name="review_remarks" value="Approved" maxlength="500">
                        </label>
                        <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                            Approve
                        </button>
                    </form>
                    <form method="post" action="/leave/<?= $leaveId ?>/reject" style="flex:1; min-width:200px;">
                        <input type="hidden" name="_csrf" value="<?= e(\App\Core\CSRF::token()) ?>">
                        <input type="hidden" name="from_show" value="1">
                        <label class="field" style="margin-bottom:var(--space-3);">
                            <span>Rejection Reason</span>
                            <input type="text" name="review_remarks" placeholder="Reason for rejection..." maxlength="500">
                        </label>
                        <button type="submit" class="btn btn-danger" style="width:100%; justify-content:center; background:var(--danger,#dc2626); color:#fff; border-color:var(--danger,#dc2626);">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
                            Reject
                        </button>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>

</section>

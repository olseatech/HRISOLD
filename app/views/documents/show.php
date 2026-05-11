<?php
$record    = is_array($record ?? null) ? $record : [];
$canDelete = can('documents.delete');

$docId    = (int) ($record['id'] ?? 0);
$empName  = trim(($record['emp_last'] ?? '') . ', ' . ($record['emp_first'] ?? ''));
$empCode  = (string) ($record['employee_code'] ?? '');
$cat      = (string) ($record['doc_category'] ?? 'Others');
$ext      = strtolower(pathinfo((string) ($record['original_filename'] ?? ''), PATHINFO_EXTENSION));
$fileSize = (int) ($record['file_size'] ?? 0);

$categoryBadgeClass = match($cat) {
    'PDS'            => 'badge-blue',
    'Appointment'    => 'badge-info',
    'Service Record' => 'badge-teal',
    'Clearance'      => 'badge-warning',
    'Certificate'    => 'badge-success',
    'ID'             => 'badge-neutral',
    default          => '',
};

$formatSize = static function (int $bytes): string {
    if ($bytes >= 1048576) return round($bytes / 1048576, 1) . ' MB';
    if ($bytes >= 1024)    return round($bytes / 1024, 1) . ' KB';
    return $bytes . ' B';
};

$isImage = in_array($ext, ['jpg', 'jpeg', 'png'], true);
$isPdf   = $ext === 'pdf';

$val = static function (string $key, string $fallback = '—') use ($record): string {
    $v = trim((string) ($record[$key] ?? ''));
    return $v !== '' ? htmlspecialchars($v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') : htmlspecialchars($fallback, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
};
?>

<section class="page">
    <header class="page-banner">
        <div class="page-banner-copy">
            <p class="page-banner-kicker">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                201 Documents
            </p>
            <h2 class="page-banner-title"><?= $val('title') ?></h2>
            <p class="page-banner-sub">
                <?= e($empName !== ', ' ? $empName : 'Unknown') ?>
                <?php if ($empCode !== ''): ?>&nbsp;<code style="font-weight:600; color:var(--blue-700);"><?= e($empCode) ?></code><?php endif; ?>
            </p>
            <div class="page-banner-meta">
                <span class="badge <?= e($categoryBadgeClass) ?>"><?= e($cat) ?></span>
                <span class="badge"><?= e(strtoupper($ext)) ?></span>
                <?php if ($fileSize > 0): ?>
                    <span class="badge"><?= e($formatSize($fileSize)) ?></span>
                <?php endif; ?>
            </div>
        </div>
        <div class="page-banner-actions">
            <a class="btn btn-secondary" href="/documents">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                Back to list
            </a>
            <a class="btn btn-primary" href="/documents/<?= $docId ?>/download">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Download
            </a>
        </div>
    </header>

    <?php require __DIR__ . '/../partials/alerts.php'; ?>

    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(380px, 1fr)); gap:var(--space-5);">

        <!-- Document Info -->
        <article class="card card-shine">
            <div class="card-header" style="border-bottom:1px solid var(--slate-100);">
                <div style="display:flex; align-items:center; gap:12px;">
                    <div style="width:36px; height:36px; border-radius:10px; background:var(--blue-50); color:var(--blue-600); display:flex; align-items:center; justify-content:center;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                    </div>
                    <div>
                        <h3 style="font-size:1rem; font-weight:800; color:var(--slate-900);">Document Information</h3>
                        <p style="font-size:.75rem; color:var(--slate-500);">Metadata and file details.</p>
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
                        <dt>Title</dt>
                        <dd><?= $val('title') ?></dd>
                    </div>
                    <div class="detail-row">
                        <dt>Category</dt>
                        <dd><span class="badge <?= e($categoryBadgeClass) ?>"><?= e($cat) ?></span></dd>
                    </div>
                    <?php if (($record['description'] ?? '') !== ''): ?>
                        <div class="detail-row">
                            <dt>Description</dt>
                            <dd><?= e((string) $record['description']) ?></dd>
                        </div>
                    <?php endif; ?>
                    <div class="detail-row">
                        <dt>Original Filename</dt>
                        <dd style="font-family:monospace; font-size:12px; word-break:break-all;"><?= $val('original_filename') ?></dd>
                    </div>
                    <div class="detail-row">
                        <dt>File Type</dt>
                        <dd><?= e(strtoupper($ext)) ?> &mdash; <?= $val('mime_type') ?></dd>
                    </div>
                    <div class="detail-row">
                        <dt>File Size</dt>
                        <dd><?= $fileSize > 0 ? e($formatSize($fileSize)) : '—' ?></dd>
                    </div>
                    <div class="detail-row">
                        <dt>Uploaded</dt>
                        <dd><?= e(format_date((string) ($record['created_at'] ?? ''))) ?></dd>
                    </div>
                </dl>
            </div>
        </article>

        <!-- Preview / Download Card -->
        <article class="card card-shine">
            <div class="card-header" style="border-bottom:1px solid var(--slate-100);">
                <div style="display:flex; align-items:center; gap:12px;">
                    <div style="width:36px; height:36px; border-radius:10px; background:var(--teal-50,#f0fdfa); color:var(--teal-600,#0d9488); display:flex; align-items:center; justify-content:center;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    </div>
                    <div>
                        <h3 style="font-size:1rem; font-weight:800; color:var(--slate-900);">Download</h3>
                        <p style="font-size:.75rem; color:var(--slate-500);">Files are streamed securely through the server.</p>
                    </div>
                </div>
            </div>
            <div class="card-body" style="padding:var(--space-5); display:flex; flex-direction:column; align-items:center; justify-content:center; gap:var(--space-4); min-height:160px;">

                <?php if ($isPdf): ?>
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="color:var(--danger,#dc2626); opacity:.7;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                    <p style="font-size:13px; color:var(--text-muted);">PDF Document</p>
                <?php elseif ($isImage): ?>
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="color:var(--primary); opacity:.7;"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                    <p style="font-size:13px; color:var(--text-muted);">Image File</p>
                <?php else: ?>
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="color:var(--blue-600); opacity:.7;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                    <p style="font-size:13px; color:var(--text-muted);">Document File</p>
                <?php endif; ?>

                <a class="btn btn-primary" href="/documents/<?= $docId ?>/download" style="width:100%; text-align:center; justify-content:center;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    Download File
                </a>
                <p style="font-size:11px; color:var(--text-muted); text-align:center;">
                    <?= e((string) ($record['original_filename'] ?? '')) ?>
                    <?php if ($fileSize > 0): ?>&nbsp;(<?= e($formatSize($fileSize)) ?>)<?php endif; ?>
                </p>
            </div>
        </article>

    </div>

    <?php if ($canDelete): ?>
        <div style="margin-top:var(--space-6); padding:var(--space-4); background:var(--danger-bg,#fff5f5); border:1px solid var(--danger-border,#fecdd3); border-radius:var(--radius-lg); display:flex; align-items:center; justify-content:space-between; gap:var(--space-4);">
            <div>
                <p style="font-weight:600; color:var(--danger,#dc2626); margin-bottom:2px;">Delete this document</p>
                <p style="font-size:13px; color:var(--text-muted);">This permanently removes the file and its record. It cannot be undone.</p>
            </div>
            <form method="post" action="/documents/<?= $docId ?>/delete" onsubmit="return confirm('Permanently delete this document and its file?');">
                <input type="hidden" name="_csrf" value="<?= e(\App\Core\CSRF::token()) ?>">
                <button type="submit" class="btn btn-danger" style="background:var(--danger,#dc2626); color:#fff; border-color:var(--danger,#dc2626);">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                    Delete Document
                </button>
            </form>
        </div>
    <?php endif; ?>

</section>

<?php
$rows            = is_array($rows ?? null) ? $rows : [];
$empList         = is_array($employees ?? null) ? $employees : [];
$categoryOptions = is_array($categories ?? null) ? $categories : [];
$currentQ        = (string) ($query ?? '');
$currentEmpId    = (string) ($empId ?? '');
$currentCategory = (string) ($currentCategory ?? '');
$currentPage     = (int) ($page ?? 1);
$pageCount       = (int) ($totalPages ?? 1);
$totalCount      = (int) ($total ?? 0);
$totalDocs       = (int) ($totalDocs ?? 0);
$totalEmp        = (int) ($totalEmp ?? 0);
$canManage = can('documents.manage');
$canDelete = can('documents.delete');

$categoryBadgeClass = static function (string $cat): string {
    return match($cat) {
        'PDS'            => 'badge-blue',
        'Appointment'    => 'badge-info',
        'Service Record' => 'badge-teal',
        'Clearance'      => 'badge-warning',
        'Certificate'    => 'badge-success',
        'ID'             => 'badge-neutral',
        default          => '',
    };
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
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                201 Documents
            </p>
            <h2 class="page-banner-title">Employee 201 File Registry</h2>
            <p class="page-banner-sub">Secure document repository — PDS, appointments, service records, clearances, certificates, and IDs.</p>
            <div class="page-banner-meta">
                <span class="badge badge-blue">Secured Upload</span>
                <span class="badge">Audit Logged</span>
            </div>
        </div>
        <div class="page-banner-actions">
            <a class="btn btn-secondary" href="/documents">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 1 1-9-9c2.52 0 4.93 1 6.74 2.74L21 8"/><path d="M21 3v5h-5"/></svg>
                Refresh
            </a>
            <?php if ($canManage): ?>
                <a class="btn btn-primary" href="/documents/create">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Upload Document
                </a>
            <?php endif; ?>
        </div>
    </header>

    <?php require __DIR__ . '/../partials/alerts.php'; ?>

    <section class="stat-grid" aria-label="Documents Summary">
        <article class="stat card-shine">
            <div class="stat-icon is-blue">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            </div>
            <div class="stat-data">
                <span class="stat-label">Total Documents</span>
                <span class="stat-value"><?= e((string) $totalDocs) ?></span>
                <span class="stat-note">All documents on file</span>
            </div>
        </article>
        <article class="stat stat-teal card-shine">
            <div class="stat-icon is-teal">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
            </div>
            <div class="stat-data">
                <span class="stat-label">Matching Filters</span>
                <span class="stat-value"><?= e((string) $totalCount) ?></span>
                <span class="stat-note">Current results</span>
            </div>
        </article>
        <article class="stat stat-gold card-shine">
            <div class="stat-icon" style="background:var(--amber-50); color:var(--amber-600);">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
            <div class="stat-data">
                <span class="stat-label">Employees on File</span>
                <span class="stat-value"><?= e((string) $totalEmp) ?></span>
                <span class="stat-note">With at least 1 document</span>
            </div>
        </article>
    </section>

    <section class="card overflow-hidden">
        <div class="card-header">
            <div class="card-header-copy">
                <h3>Document List</h3>
                <p>Filter by employee, category, or keyword. Download and delete per row.</p>
            </div>
        </div>

        <div class="toolbar glass-toolbar">
            <form method="get" action="/documents" class="filter-bar" role="search" style="display:flex; width:100%; gap:var(--space-3); align-items:flex-end; flex-wrap:wrap;">
                <label class="filter-field">
                    <span>Search</span>
                    <input type="text" name="q" value="<?= e($currentQ) ?>" placeholder="Employee name or document title">
                </label>
                <label class="filter-field">
                    <span>Employee</span>
                    <select name="employee_id">
                        <option value="">All employees</option>
                        <?php foreach ($empList as $emp): ?>
                            <option value="<?= (int) ($emp['id'] ?? 0) ?>" <?= $currentEmpId === (string) ($emp['id'] ?? '') ? 'selected' : '' ?>>
                                <?= e((string) ($emp['employee_code'] ?? '')) ?> &mdash; <?= e((string) ($emp['last_name'] ?? '')) ?>, <?= e((string) ($emp['first_name'] ?? '')) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label class="filter-field">
                    <span>Category</span>
                    <select name="category">
                        <option value="">All categories</option>
                        <?php foreach ($categoryOptions as $cat): ?>
                            <option value="<?= e($cat) ?>" <?= $currentCategory === $cat ? 'selected' : '' ?>><?= e($cat) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <button class="btn btn-primary" type="submit">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                    Apply
                </button>
                <?php if ($currentQ !== '' || $currentEmpId !== '' || $currentCategory !== ''): ?>
                    <a class="btn btn-secondary" href="/documents">Clear</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="table-wrap no-border no-radius shadow-none">
            <table class="table data-table interactive-rows">
                <thead>
                    <tr>
                        <th scope="col">Employee</th>
                        <th scope="col">Document Title</th>
                        <th scope="col">Category</th>
                        <th scope="col">File</th>
                        <th scope="col">Uploaded</th>
                        <th scope="col" style="text-align:right;">Operations</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($rows === []): ?>
                        <tr>
                            <td colspan="6">
                                <div class="table-empty">
                                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" style="opacity:.25; margin-bottom:12px;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                                    <p>No documents found<?= $currentQ !== '' ? ' matching "' . e($currentQ) . '"' : '' ?>.</p>
                                    <?php if ($canManage): ?>
                                        <a class="btn btn-primary" href="/documents/create">Upload the first document</a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($rows as $row): ?>
                            <?php
                            $empName  = trim(($row['emp_last'] ?? '') . ', ' . ($row['emp_first'] ?? ''));
                            $cat      = (string) ($row['doc_category'] ?? 'Others');
                            $fileSize = (int) ($row['file_size'] ?? 0);
                            $ext      = strtolower(pathinfo((string) ($row['original_filename'] ?? ''), PATHINFO_EXTENSION));
                            ?>
                            <tr>
                                <td>
                                    <div class="person">
                                        <strong><?= e($empName !== ', ' ? $empName : '-') ?></strong>
                                        <span><?= e((string) ($row['employee_code'] ?? '-')) ?></span>
                                    </div>
                                </td>
                                <td>
                                    <strong><?= e((string) ($row['title'] ?? '-')) ?></strong>
                                    <?php if (($row['description'] ?? '') !== ''): ?>
                                        <br><small style="color:var(--text-muted);"><?= e((string) $row['description']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><span class="badge <?= e($categoryBadgeClass($cat)) ?>"><?= e($cat) ?></span></td>
                                <td>
                                    <span style="font-size:12px; color:var(--text-muted);">
                                        <?= e(strtoupper($ext)) ?>
                                        <?php if ($fileSize > 0): ?>&nbsp;&middot;&nbsp;<?= e($formatSize($fileSize)) ?><?php endif; ?>
                                    </span>
                                </td>
                                <td>
                                    <small style="color:var(--text-muted);"><?= e(format_date((string) ($row['created_at'] ?? ''))) ?></small>
                                </td>
                                <td style="text-align:right;">
                                    <div class="row-actions" style="justify-content:flex-end;">
                                        <a href="/documents/<?= (int) ($row['id'] ?? 0) ?>" class="btn-icon" title="View">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                        </a>
                                        <a href="/documents/<?= (int) ($row['id'] ?? 0) ?>/download" class="btn-icon" title="Download">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                        </a>
                                        <?php if ($canDelete): ?>
                                            <form method="post" action="/documents/<?= (int) ($row['id'] ?? 0) ?>/delete" onsubmit="return confirm('Delete this document? This cannot be undone.');" style="display:inline;">
                                                <input type="hidden" name="_csrf" value="<?= e(\App\Core\CSRF::token()) ?>">
                                                <button type="submit" class="btn-icon text-red" title="Delete">
                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($pageCount > 1): ?>
            <div class="card-footer">
                <?php $base = '/documents?q=' . urlencode($currentQ) . '&employee_id=' . urlencode($currentEmpId) . '&category=' . urlencode($currentCategory) . '&page='; ?>
                <nav class="pagination" aria-label="Documents pagination">
                    <?php for ($i = 1; $i <= $pageCount; $i++): ?>
                        <a class="page-link <?= $currentPage === $i ? 'is-active' : '' ?>" href="<?= e($base . $i) ?>"><?= $i ?></a>
                    <?php endfor; ?>
                </nav>
            </div>
        <?php endif; ?>
    </section>
</section>

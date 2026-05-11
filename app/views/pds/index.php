<?php
$records    = is_array($records ?? null) ? $records : [];
$currentQ   = (string) ($query ?? '');
$currentPage = (int) ($page ?? 1);
$pageCount  = (int) ($totalPages ?? 1);
$totalCount = (int) ($total ?? 0);
$canCreate  = can('pds.create');
$canUpdate  = can('pds.update');
$canDelete  = can('pds.delete');
?>

<section class="page">
    <header class="page-banner">
        <div class="page-banner-copy">
            <p class="page-banner-kicker">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                Personal Data Sheet
            </p>
            <h2 class="page-banner-title">CS Form 212 &mdash; Employee PDS Registry</h2>
            <p class="page-banner-sub">Manage civil service personal data sheets for all employees. Search by name or employee code.</p>
            <div class="page-banner-meta">
                <span class="badge badge-blue">CSC Form 212</span>
                <span class="badge badge-teal">RBAC Active</span>
                <span class="badge">Audit Logged</span>
            </div>
        </div>
        <div class="page-banner-actions">
            <a class="btn btn-secondary" href="/pds">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 1 1-9-9c2.52 0 4.93 1 6.74 2.74L21 8"/><path d="M21 3v5h-5"/></svg>
                Refresh
            </a>
            <?php if ($canCreate): ?>
                <a class="btn btn-primary" href="/pds/create">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    New PDS
                </a>
            <?php endif; ?>
        </div>
    </header>

    <?php require __DIR__ . '/../partials/alerts.php'; ?>

    <section class="stat-grid" aria-label="PDS Summary">
        <article class="stat card-shine">
            <div class="stat-icon is-blue">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            </div>
            <div class="stat-data">
                <span class="stat-label">Total PDS Records</span>
                <span class="stat-value"><?= e((string) $totalCount) ?></span>
                <span class="stat-note">Matching current filters</span>
            </div>
        </article>
        <article class="stat stat-teal card-shine">
            <div class="stat-icon is-teal">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
            </div>
            <div class="stat-data">
                <span class="stat-label">On This Page</span>
                <span class="stat-value"><?= e((string) count($records)) ?></span>
                <span class="stat-note">Page <?= e((string) $currentPage) ?> of <?= e((string) max(1, $pageCount)) ?></span>
            </div>
        </article>
        <article class="stat stat-gold card-shine">
            <div class="stat-icon is-blue" style="background:var(--amber-50); color:var(--amber-600);">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
            </div>
            <div class="stat-data">
                <span class="stat-label">Search Filter</span>
                <span class="stat-value"><?= $currentQ !== '' ? e($currentQ) : 'None' ?></span>
                <span class="stat-note">Current keyword</span>
            </div>
        </article>
    </section>

    <section class="card overflow-hidden">
        <div class="card-header">
            <div class="card-header-copy">
                <h3>PDS Records</h3>
                <p>Search by employee name or code. Click View to see all PDS sections.</p>
            </div>
        </div>

        <div class="toolbar glass-toolbar">
            <form method="get" action="/pds" class="filter-bar" role="search" style="display:flex; width:100%; gap:var(--space-3); align-items:flex-end;">
                <label class="filter-field">
                    <span>Search</span>
                    <input type="text" name="q" value="<?= e($currentQ) ?>" placeholder="Employee name or code">
                </label>
                <button class="btn btn-primary" type="submit">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                    Search
                </button>
                <?php if ($currentQ !== ''): ?>
                    <a class="btn btn-secondary" href="/pds">Clear</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="table-wrap no-border no-radius shadow-none">
            <table class="table data-table interactive-rows">
                <thead>
                    <tr>
                        <th scope="col">Employee</th>
                        <th scope="col">PDS Name on Record</th>
                        <th scope="col">Status</th>
                        <th scope="col">Last Updated</th>
                        <th scope="col" style="text-align:right;">Operations</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($records === []): ?>
                        <tr>
                            <td colspan="5">
                                <div class="table-empty">
                                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" style="opacity:0.25; margin-bottom:12px;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                                    <p>No PDS records found<?= $currentQ !== '' ? ' matching "' . e($currentQ) . '"' : '' ?>.</p>
                                    <?php if ($canCreate): ?>
                                        <a class="btn btn-primary" href="/pds/create">Create the first PDS</a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($records as $row): ?>
                            <?php
                            $statusClass = ($row['status'] ?? '') === 'Complete' ? 'badge-success' : 'badge-warning';
                            $empName = trim((string) (($row['emp_last'] ?? '') . ', ' . ($row['emp_first'] ?? '')));
                            $pdsName = trim((string) (($row['surname'] ?? '') . ', ' . ($row['first_name'] ?? '')));
                            ?>
                            <tr>
                                <td>
                                    <div class="person">
                                        <strong><?= e($empName !== ', ' ? $empName : '-') ?></strong>
                                        <span><?= e((string) ($row['employee_code'] ?? '-')) ?></span>
                                    </div>
                                </td>
                                <td><?= e($pdsName !== ', ' ? $pdsName : '(not filled)') ?></td>
                                <td><span class="badge <?= e($statusClass) ?>"><?= e((string) ($row['status'] ?? 'Draft')) ?></span></td>
                                <td><?= e(format_date((string) ($row['updated_at'] ?? ''))) ?></td>
                                <td style="text-align:right;">
                                    <div class="row-actions" style="justify-content:flex-end;">
                                        <a href="/pds/<?= (int) ($row['id'] ?? 0) ?>" class="btn-icon" title="View PDS">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                        </a>
                                        <?php if ($canUpdate): ?>
                                            <a href="/pds/<?= (int) ($row['id'] ?? 0) ?>/edit" class="btn-icon" title="Edit PDS">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                            </a>
                                        <?php endif; ?>
                                        <?php if ($canDelete): ?>
                                            <form method="post" action="/pds/<?= (int) ($row['id'] ?? 0) ?>/delete" onsubmit="return confirm('Permanently delete this PDS record?');" style="display:inline;">
                                                <input type="hidden" name="_csrf" value="<?= e(\App\Core\CSRF::token()) ?>">
                                                <button type="submit" class="btn-icon text-red" title="Delete PDS">
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
                <?php $base = '/pds?q=' . urlencode($currentQ) . '&page='; ?>
                <nav class="pagination" aria-label="PDS pagination">
                    <?php for ($i = 1; $i <= $pageCount; $i++): ?>
                        <a class="page-link <?= $currentPage === $i ? 'is-active' : '' ?>" href="<?= e($base . $i) ?>"><?= $i ?></a>
                    <?php endfor; ?>
                </nav>
            </div>
        <?php endif; ?>
    </section>
</section>

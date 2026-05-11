<?php
$rows           = is_array($rows ?? null) ? $rows : [];
$currentYear    = (int) ($year ?? (int) date('Y'));
$countYear      = (int) ($countYear ?? 0);
$countRecurring = (int) ($countRecurring ?? 0);

$typeBadgeClass = static function (string $t): string {
    return match($t) {
        'Regular'              => 'badge badge-danger',
        'Special Non-Working'  => 'badge badge-warning',
        'Special Working'      => 'badge badge-info',
        default                => 'badge',
    };
};
?>
<section class="page">
    <header class="page-banner">
        <div class="page-banner-copy">
            <p class="page-banner-kicker">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M3 10h18M8 2v3M16 2v3"/><circle cx="15" cy="16" r="2"/></svg>
                Leave Management
            </p>
            <h2 class="page-banner-title">Public Holidays</h2>
            <p class="page-banner-sub">Manage holidays used to calculate working days in leave requests.</p>
            <div class="page-banner-meta">
                <span class="badge badge-blue"><?= e((string) $currentYear) ?></span>
                <span class="badge"><?= e((string) $countRecurring) ?> recurring</span>
            </div>
        </div>
        <div class="page-banner-actions">
            <a class="btn btn-secondary" href="/holidays?year=<?= $currentYear ?>">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 1 1-9-9c2.52 0 4.93 1 6.74 2.74L21 8"/><path d="M21 3v5h-5"/></svg>
                Refresh
            </a>
            <a class="btn btn-primary" href="/holidays/create">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Add Holiday
            </a>
        </div>
    </header>

    <?php require __DIR__ . '/../partials/alerts.php'; ?>

    <section class="stat-grid" aria-label="Holiday summary">
        <article class="stat card-shine">
            <div class="stat-icon is-blue">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
            </div>
            <div class="stat-data">
                <span class="stat-label">Holidays in <?= e((string) $currentYear) ?></span>
                <span class="stat-value"><?= e((string) $countYear) ?></span>
                <span class="stat-note">Exact-date entries</span>
            </div>
        </article>
        <article class="stat stat-gold card-shine">
            <div class="stat-icon" style="background:var(--amber-50); color:var(--amber-600);">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
            <div class="stat-data">
                <span class="stat-label">Recurring</span>
                <span class="stat-value"><?= e((string) $countRecurring) ?></span>
                <span class="stat-note">Applied every year</span>
            </div>
        </article>
    </section>

    <section class="card overflow-hidden">
        <div class="card-header">
            <div class="card-header-copy">
                <h3>Holiday List</h3>
                <p>Filter by year. Recurring holidays apply to all years.</p>
            </div>
        </div>

        <div class="toolbar glass-toolbar">
            <form method="get" action="/holidays" class="filter-bar" role="search" style="display:flex; width:100%; gap:var(--space-3); align-items:flex-end; flex-wrap:wrap;">
                <label class="filter-field">
                    <span>Year</span>
                    <select name="year" onchange="this.form.submit()">
                        <?php for ($y = (int) date('Y') + 1; $y >= 2020; $y--): ?>
                            <option value="<?= $y ?>" <?= $currentYear === $y ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                </label>
                <button class="btn btn-primary" type="submit">Apply</button>
            </form>
        </div>

        <div class="table-wrap no-border no-radius shadow-none">
            <table class="data-table interactive-rows">
                <thead>
                    <tr>
                        <th scope="col">Date</th>
                        <th scope="col">Name</th>
                        <th scope="col">Type</th>
                        <th scope="col">Recurring</th>
                        <th scope="col">Remarks</th>
                        <th scope="col" style="text-align:right;">Operations</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($rows === []): ?>
                        <tr>
                            <td colspan="6">
                                <div class="table-empty">
                                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" style="opacity:.25; margin-bottom:12px;"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M3 10h18M16 2v4M8 2v4"/></svg>
                                    <p>No holidays found for <?= e((string) $currentYear) ?>.</p>
                                    <a class="btn btn-primary" href="/holidays/create">Add the first holiday</a>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($rows as $row): ?>
                            <tr>
                                <td style="font-family:monospace; font-size:12px; white-space:nowrap; font-weight:600;">
                                    <?= e((string) ($row['holiday_date'] ?? '-')) ?>
                                </td>
                                <td><strong><?= e((string) ($row['name'] ?? '-')) ?></strong></td>
                                <td><span class="<?= e($typeBadgeClass((string) ($row['holiday_type'] ?? ''))) ?>"><?= e((string) ($row['holiday_type'] ?? '-')) ?></span></td>
                                <td>
                                    <?php if ((int) ($row['is_recurring'] ?? 0) === 1): ?>
                                        <span class="badge badge-success">Yes</span>
                                    <?php else: ?>
                                        <span style="color:var(--text-muted); font-size:12px;">No</span>
                                    <?php endif; ?>
                                </td>
                                <td style="color:var(--text-muted); font-size:12px;"><?= e((string) ($row['remarks'] ?? '')) ?></td>
                                <td style="text-align:right;">
                                    <div class="row-actions" style="justify-content:flex-end;">
                                        <a href="/holidays/<?= (int) ($row['id'] ?? 0) ?>/edit" class="btn-icon" title="Edit">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                        </a>
                                        <form method="post" action="/holidays/<?= (int) ($row['id'] ?? 0) ?>/delete"
                                              onsubmit="return confirm('Delete this holiday? This cannot be undone.');"
                                              style="display:inline;">
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
</section>

<?php
$gradeRows = is_array($grades ?? null) ? $grades : [];
$employeeOptions = is_array($employees ?? null) ? $employees : [];
$salaryRows = is_array($salaries ?? null) ? $salaries : [];
$oldInput = is_array($old ?? null) ? $old : [];

$currentQuery = (string) ($query ?? '');
$currentPage = (int) ($page ?? 1);
$pageCount = (int) ($totalPages ?? 1);
$totalCount = (int) ($total ?? 0);

$currentSalaryCount = 0;
foreach ($salaryRows as $salary) {
    if ((int) ($salary['is_current'] ?? 0) === 1) {
        $currentSalaryCount++;
    }
}
?>
<section class="page">
    <header class="page-banner">
        <div class="page-banner-copy">
            <p class="page-banner-kicker">Payroll</p>
            <h2 class="page-banner-title">Compensation setup and salary records</h2>
            <p class="page-banner-sub">Manage salary grades, assign employee salaries, and review effective pay history from one streamlined page.</p>
            <div class="page-banner-meta">
                <span class="badge badge-blue">Salary grades</span>
                <span class="badge badge-teal">Current flag tracking</span>
                <span class="badge">History-aware</span>
            </div>
        </div>
        <div class="page-banner-actions">
            <a class="btn" href="/payroll">Refresh</a>
        </div>
    </header>
    <?php require __DIR__ . '/../partials/alerts.php'; ?>
    <section class="stat-grid" aria-label="Payroll summary">
        <article class="stat">
            <span class="stat-label">Total records</span>
            <span class="stat-value"><?= e((string) $totalCount) ?></span>
            <span class="stat-note">Matching search</span>
        </article>
        <article class="stat stat-teal">
            <span class="stat-label">Current on page</span>
            <span class="stat-value"><?= e((string) $currentSalaryCount) ?></span>
            <span class="stat-note">Marked current</span>
        </article>
        <article class="stat stat-gold">
            <span class="stat-label">Salary grades</span>
            <span class="stat-value"><?= e((string) count($gradeRows)) ?></span>
            <span class="stat-note">Configured</span>
        </article>
    </section>
    <section class="pay-grid">
        <article class="card">
            <div class="card-head">
                <h3>Salary Grades</h3>
                <p>Create or update salary ranges by grade name.</p>
            </div>
            <form class="form-grid" method="post" action="/payroll/grades" novalidate>
                <input type="hidden" name="_csrf" value="<?= e((string) ($csrf ?? '')) ?>">
                <label>
                    <span>Grade Name</span>
                    <input type="text" name="grade_name" value="<?= e((string) ($oldInput['grade_name'] ?? '')) ?>" placeholder="e.g. SG-1" required>
                </label>
                <label>
                    <span>Min Salary</span>
                    <input type="number" step="0.01" name="min_salary" value="<?= e((string) ($oldInput['min_salary'] ?? '')) ?>" required>
                </label>
                <label>
                    <span>Max Salary</span>
                    <input type="number" step="0.01" name="max_salary" value="<?= e((string) ($oldInput['max_salary'] ?? '')) ?>" required>
                </label>
                <div class="full-width form-actions">
                    <p class="form-hint">Ranges are used to validate basic salary assignments.</p>
                    <button class="btn btn-primary" type="submit">Save grade</button>
                </div>
            </form>
            <div class="data-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Grade</th>
                            <th>Min Salary</th>
                            <th>Max Salary</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($gradeRows === []): ?>
                            <tr>
                                <td colspan="3"><p class="empty-state">No salary grades found.</p></td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($gradeRows as $grade): ?>
                                <tr>
                                    <td><strong><?= e((string) ($grade['grade_name'] ?? '-')) ?></strong></td>
                                    <td style="font-family:var(--font-mono);"><?= e((string) ($grade['min_salary'] ?? '-')) ?></td>
                                    <td style="font-family:var(--font-mono);"><?= e((string) ($grade['max_salary'] ?? '-')) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </article>
        <article class="card">
            <div class="card-head">
                <h3>Assign Employee Salary</h3>
                <p>Create a salary record and optionally mark it as current.</p>
            </div>
            <form class="form-grid" method="post" action="/payroll/salaries" novalidate>
                <input type="hidden" name="_csrf" value="<?= e((string) ($csrf ?? '')) ?>">
                <label>
                    <span>Employee</span>
                    <select name="employee_id" required>
                        <option value="">Select</option>
                        <?php foreach ($employeeOptions as $employee): ?>
                            <option value="<?= (int) $employee['id'] ?>" <?= ((string) ($oldInput['employee_id'] ?? '') === (string) $employee['id']) ? 'selected' : '' ?>><?= e((string) ($employee['employee_code'] ?? '-')) ?> &mdash; <?= e((string) (($employee['first_name'] ?? '') . ' ' . ($employee['last_name'] ?? ''))) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>
                    <span>Salary Grade</span>
                    <select name="salary_grade_id">
                        <option value="">None</option>
                        <?php foreach ($gradeRows as $grade): ?>
                            <option value="<?= (int) $grade['id'] ?>" <?= ((string) ($oldInput['salary_grade_id'] ?? '') === (string) $grade['id']) ? 'selected' : '' ?>><?= e((string) $grade['grade_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>
                    <span>Basic Salary</span>
                    <input type="number" step="0.01" name="basic_salary" value="<?= e((string) ($oldInput['basic_salary'] ?? '')) ?>" required>
                </label>
                <label>
                    <span>Effective Date</span>
                    <input type="date" name="effective_date" value="<?= e((string) ($oldInput['effective_date'] ?? '')) ?>" required>
                </label>
                <label>
                    <span>Set as Current</span>
                    <select name="is_current">
                        <option value="1" <?= (($oldInput['is_current'] ?? '1') === '1') ? 'selected' : '' ?>>Yes</option>
                        <option value="0" <?= (($oldInput['is_current'] ?? '1') === '0') ? 'selected' : '' ?>>No</option>
                    </select>
                </label>
                <div class="full-width form-actions">
                    <p class="form-hint">If set as current, older records for the employee are automatically unset.</p>
                    <button class="btn btn-primary" type="submit">Save salary</button>
                </div>
            </form>
        </article>
    </section>
    <section class="card">
        <div class="card-head">
            <h3>Salary Records</h3>
            <p>Search by employee code, name, or grade to find salary records quickly.</p>
        </div>
        <form class="filter-bar" method="get" action="/payroll" role="search">
            <label class="filter-field">
                <span>Search</span>
                <input type="text" name="q" value="<?= e($currentQuery) ?>" placeholder="Search employee or grade">
            </label>
            <button class="btn btn-primary" type="submit">Search</button>
        </form>
        <div class="data-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Grade</th>
                        <th>Basic Salary</th>
                        <th>Effective Date</th>
                        <th>Current</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($salaryRows === []): ?>
                        <tr>
                            <td colspan="5"><p class="empty-state">No salary records found.</p></td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($salaryRows as $salary): ?>
                            <tr>
                                <td>
                                    <div class="person">
                                        <strong><?= e((string) (($salary['first_name'] ?? '') . ' ' . ($salary['last_name'] ?? ''))) ?></strong>
                                        <span><?= e((string) ($salary['employee_code'] ?? '-')) ?></span>
                                    </div>
                                </td>
                                <td><?= e((string) ($salary['grade_name'] ?? '-')) ?></td>
                                <td style="font-family:var(--font-mono);"><?= e((string) ($salary['basic_salary'] ?? '-')) ?></td>
                                <td style="font-family:var(--font-mono);"><?= e((string) ($salary['effective_date'] ?? '-')) ?></td>
                                <td>
                                    <span class="pay-badge <?= ((int) ($salary['is_current'] ?? 0) === 1) ? 'pay-badge-current' : 'pay-badge-historical' ?>">
                                        <?= ((int) ($salary['is_current'] ?? 0) === 1) ? 'Current' : 'Historical' ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if ($pageCount > 1): ?>
            <?php $base = '/payroll?q=' . urlencode($currentQuery) . '&page='; ?>
            <nav class="pagination" aria-label="Payroll pagination" style="margin-top:12px;">
                <?php for ($i = 1; $i <= $pageCount; $i++): ?>
                    <a class="page-link <?= $currentPage === $i ? 'is-active' : '' ?>" href="<?= e($base . $i) ?>"><?= $i ?></a>
                <?php endfor; ?>
            </nav>
        <?php endif; ?>
    </section>
</section>

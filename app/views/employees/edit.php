<?php
$model = $employee ?? [];
$input = is_array($old ?? null) && $old !== [] ? $old : $model;
$departmentOptions = is_array($departments ?? null) ? $departments : [];
$designationOptions = is_array($designations ?? null) ? $designations : [];
$supervisorOptions = is_array($supervisors ?? null) ? $supervisors : [];
?>

<section class="page">
    <header class="page-banner">
        <div class="page-banner-copy">
            <p class="page-banner-kicker">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                Profile Management
            </p>
            <h2 class="page-banner-title">Update employee profile details</h2>
            <p class="page-banner-sub">Adjust identity or employment records while keeping validation and audit trails intact.</p>
            <div class="page-banner-meta">
                <span class="badge badge-teal">ID: <?= e((string) ($model['employee_code'] ?? 'NEW')) ?></span>
                <span class="badge">Audit Active</span>
            </div>
        </div>
        <div class="page-banner-actions">
            <a class="btn btn-secondary" href="/employees/<?= (int) ($model['id'] ?? 0) ?>">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                View Profile
            </a>
        </div>
    </header>

    <?php require __DIR__ . '/../partials/alerts.php'; ?>

    <form class="card glass-card" method="post" action="/employees/<?= (int) ($model['id'] ?? 0) ?>/update" novalidate>
        <input type="hidden" name="_csrf" value="<?= e((string) ($csrf ?? '')) ?>">
        
        <section class="form-section card-shine">
            <div class="form-section-head">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div style="width: 32px; height: 32px; border-radius: 8px; background: var(--blue-50); color: var(--blue-600); display: flex; align-items: center; justify-content: center;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    </div>
                    <h3>Personal Details</h3>
                </div>
                <p>Core identity and contact information synchronized across all modules.</p>
            </div>
            
            <div class="form-grid">
                <label>
                    <span class="field-label">First Name <em class="text-red">*</em></span>
                    <input type="text" name="first_name" value="<?= e((string) ($input['first_name'] ?? '')) ?>" required>
                </label>
                <label>
                    <span class="field-label">Middle Name</span>
                    <input type="text" name="middle_name" value="<?= e((string) ($input['middle_name'] ?? '')) ?>">
                </label>
                <label>
                    <span class="field-label">Last Name <em class="text-red">*</em></span>
                    <input type="text" name="last_name" value="<?= e((string) ($input['last_name'] ?? '')) ?>" required>
                </label>
                <label>
                    <span class="field-label">Gender <em class="text-red">*</em></span>
                    <select name="gender" required>
                        <?php foreach (['Male', 'Female', 'Other'] as $gender): ?>
                            <option value="<?= e($gender) ?>" <?= (($input['gender'] ?? '') === $gender) ? 'selected' : '' ?>><?= e($gender) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>
                    <span class="field-label">Date of Birth <em class="text-red">*</em></span>
                    <input type="date" name="date_of_birth" value="<?= e((string) ($input['date_of_birth'] ?? '')) ?>" required>
                </label>
                <label>
                    <span class="field-label">Marital Status</span>
                    <select name="marital_status">
                        <?php foreach (['Single', 'Married', 'Divorced', 'Widowed'] as $item): ?>
                            <option value="<?= e($item) ?>" <?= (($input['marital_status'] ?? 'Single') === $item) ? 'selected' : '' ?>><?= e($item) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>
                    <span class="field-label">Nationality</span>
                    <input type="text" name="nationality" value="<?= e((string) ($input['nationality'] ?? '')) ?>">
                </label>
                <label>
                    <span class="field-label">Phone Number</span>
                    <input type="tel" name="phone" value="<?= e((string) ($input['phone'] ?? '')) ?>">
                </label>
                <label>
                    <span class="field-label">Email Address</span>
                    <input type="email" name="email" value="<?= e((string) ($input['email'] ?? '')) ?>">
                </label>
                <label class="full-width">
                    <span class="field-label">Residential Address</span>
                    <textarea name="address" rows="3"><?= e((string) ($input['address'] ?? '')) ?></textarea>
                </label>
            </div>
        </section>

        <section class="form-section card-shine">
            <div class="form-section-head">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div style="width: 32px; height: 32px; border-radius: 8px; background: var(--teal-50); color: var(--teal-600); display: flex; align-items: center; justify-content: center;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    </div>
                    <h3>Employment Assignment</h3>
                </div>
                <p>Placement records and status details used for payroll and reporting.</p>
            </div>
            
            <div class="form-grid">
                <label>
                    <span class="field-label">Department <em class="text-red">*</em></span>
                    <select name="department_id" required>
                        <?php foreach ($departmentOptions as $department): ?>
                            <option value="<?= (int) $department['id'] ?>" <?= ((int) ($input['department_id'] ?? 0) === (int) $department['id']) ? 'selected' : '' ?>>
                                <?= e((string) $department['department_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>
                    <span class="field-label">Designation <em class="text-red">*</em></span>
                    <select name="designation_id" required>
                        <?php foreach ($designationOptions as $designation): ?>
                            <option value="<?= (int) $designation['id'] ?>" <?= ((int) ($input['designation_id'] ?? 0) === (int) $designation['id']) ? 'selected' : '' ?>>
                                <?= e((string) $designation['designation_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>
                    <span class="field-label">Employment Type</span>
                    <select name="employment_type">
                        <?php foreach (['Full-Time', 'Part-Time', 'Contract', 'Intern'] as $item): ?>
                            <option value="<?= e($item) ?>" <?= (($input['employment_type'] ?? 'Full-Time') === $item) ? 'selected' : '' ?>><?= e($item) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>
                    <span class="field-label">Contract Status</span>
                    <select name="employment_status">
                        <?php foreach (['Active', 'Probation', 'On Leave', 'Resigned', 'Terminated'] as $item): ?>
                            <option value="<?= e($item) ?>" <?= (($input['employment_status'] ?? 'Active') === $item) ? 'selected' : '' ?>><?= e($item) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>
                    <span class="field-label">Date Hired <em class="text-red">*</em></span>
                    <input type="date" name="date_hired" value="<?= e((string) ($input['date_hired'] ?? '')) ?>" required>
                </label>
                <label>
                    <span class="field-label">Regularization Date</span>
                    <input type="date" name="date_regularized" value="<?= e((string) ($input['date_regularized'] ?? '')) ?>">
                </label>
                <label>
                    <span class="field-label">Separation Date</span>
                    <input type="date" name="date_separated" value="<?= e((string) ($input['date_separated'] ?? '')) ?>">
                </label>
                <label class="full-width">
                    <span class="field-label">Supervisor / Reporting Manager</span>
                    <select name="supervisor_id">
                        <option value="">No direct supervisor</option>
                        <?php foreach ($supervisorOptions as $supervisor): ?>
                            <option value="<?= (int) $supervisor['id'] ?>" <?= ((int) ($input['supervisor_id'] ?? 0) === (int) $supervisor['id']) ? 'selected' : '' ?>>
                                <?= e((string) ($supervisor['employee_code'] . ' — ' . $supervisor['first_name'] . ' ' . $supervisor['last_name'])) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>
            </div>
        </section>

        <div class="form-actions">
            <p class="form-hint">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="margin-right:4px;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                Changes are recorded in the system audit logs for administrative traceability.
            </p>
            <div class="inline-actions">
                <a class="btn btn-ghost" href="/employees/<?= (int) ($model['id'] ?? 0) ?>">Cancel changes</a>
                <button class="btn btn-primary" type="submit">
                    Save profile updates
                </button>
            </div>
        </div>
    </form>
</section>

<?php
$oldInput = is_array($old ?? null) ? $old : [];
$departmentOptions = is_array($departments ?? null) ? $departments : [];
$designationOptions = is_array($designations ?? null) ? $designations : [];
$supervisorOptions = is_array($supervisors ?? null) ? $supervisors : [];
?>

<section class="page">
    <header class="page-banner">
        <div class="page-banner-copy">
            <p class="page-banner-kicker">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
                Enrollment Module
            </p>
            <h2 class="page-banner-title">Register a new employee profile</h2>
            <p class="page-banner-sub">Capture identity, contact, and employment assignment details in a guided, structured flow.</p>
            <div class="page-banner-meta">
                <span class="badge badge-teal">Guided Entry</span>
                <span class="badge">Validation Active</span>
            </div>
        </div>
        <div class="page-banner-actions">
            <a class="btn btn-secondary" href="/employees">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                Back to list
            </a>
        </div>
    </header>

    <?php require __DIR__ . '/../partials/alerts.php'; ?>

    <form class="card glass-card" method="post" action="/employees" novalidate>
        <input type="hidden" name="_csrf" value="<?= e((string) ($csrf ?? '')) ?>">
        
        <section class="form-section card-shine">
            <div class="form-section-head">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div style="width: 32px; height: 32px; border-radius: 8px; background: var(--blue-50); color: var(--blue-600); display: flex; align-items: center; justify-content: center;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    </div>
                    <h3>Personal Details</h3>
                </div>
                <p>Primary information used to synchronize attendance, payroll, and leave workflows.</p>
            </div>
            
            <div class="form-grid">
                <label>
                    <span class="field-label">First Name <em class="text-red">*</em></span>
                    <input type="text" name="first_name" value="<?= e((string) ($oldInput['first_name'] ?? '')) ?>" placeholder="e.g. Juan" required>
                </label>
                <label>
                    <span class="field-label">Middle Name</span>
                    <input type="text" name="middle_name" value="<?= e((string) ($oldInput['middle_name'] ?? '')) ?>" placeholder="e.g. Mercado">
                </label>
                <label>
                    <span class="field-label">Last Name <em class="text-red">*</em></span>
                    <input type="text" name="last_name" value="<?= e((string) ($oldInput['last_name'] ?? '')) ?>" placeholder="e.g. Dela Cruz" required>
                </label>
                <label>
                    <span class="field-label">Gender <em class="text-red">*</em></span>
                    <select name="gender" required>
                        <option value="">Select</option>
                        <?php foreach (['Male', 'Female', 'Other'] as $gender): ?>
                            <option value="<?= e($gender) ?>" <?= (($oldInput['gender'] ?? '') === $gender) ? 'selected' : '' ?>><?= e($gender) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>
                    <span class="field-label">Date of Birth <em class="text-red">*</em></span>
                    <input type="date" name="date_of_birth" value="<?= e((string) ($oldInput['date_of_birth'] ?? '')) ?>" required>
                </label>
                <label>
                    <span class="field-label">Marital Status</span>
                    <select name="marital_status">
                        <?php foreach (['Single', 'Married', 'Divorced', 'Widowed'] as $item): ?>
                            <option value="<?= e($item) ?>" <?= (($oldInput['marital_status'] ?? 'Single') === $item) ? 'selected' : '' ?>><?= e($item) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>
                    <span class="field-label">Nationality</span>
                    <input type="text" name="nationality" value="<?= e((string) ($oldInput['nationality'] ?? '')) ?>" placeholder="e.g. Filipino">
                </label>
                <label>
                    <span class="field-label">Phone Number</span>
                    <input type="tel" name="phone" value="<?= e((string) ($oldInput['phone'] ?? '')) ?>" placeholder="e.g. 0912 345 6789">
                </label>
                <label>
                    <span class="field-label">Email Address</span>
                    <input type="email" name="email" value="<?= e((string) ($oldInput['email'] ?? '')) ?>" placeholder="e.g. juan@example.com">
                </label>
                <label class="full-width">
                    <span class="field-label">Residential Address</span>
                    <textarea name="address" rows="3" placeholder="Enter full permanent residence..."><?= e((string) ($oldInput['address'] ?? '')) ?></textarea>
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
                <p>Departmental placement and status controls that define module accessibility.</p>
            </div>
            
            <div class="form-grid">
                <label>
                    <span class="field-label">Department <em class="text-red">*</em></span>
                    <select name="department_id" required>
                        <option value="">Choose department</option>
                        <?php foreach ($departmentOptions as $department): ?>
                            <option value="<?= (int) $department['id'] ?>" <?= ((string) ($oldInput['department_id'] ?? '') === (string) $department['id']) ? 'selected' : '' ?>>
                                <?= e((string) $department['department_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>
                    <span class="field-label">Designation <em class="text-red">*</em></span>
                    <select name="designation_id" required>
                        <option value="">Choose role</option>
                        <?php foreach ($designationOptions as $designation): ?>
                            <option value="<?= (int) $designation['id'] ?>" <?= ((string) ($oldInput['designation_id'] ?? '') === (string) $designation['id']) ? 'selected' : '' ?>>
                                <?= e((string) $designation['designation_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>
                    <span class="field-label">Employment Type</span>
                    <select name="employment_type">
                        <?php foreach (['Full-Time', 'Part-Time', 'Contract', 'Intern'] as $item): ?>
                            <option value="<?= e($item) ?>" <?= (($oldInput['employment_type'] ?? 'Full-Time') === $item) ? 'selected' : '' ?>><?= e($item) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>
                    <span class="field-label">Contract Status</span>
                    <select name="employment_status">
                        <?php foreach (['Active', 'Probation', 'On Leave', 'Resigned', 'Terminated'] as $item): ?>
                            <option value="<?= e($item) ?>" <?= (($oldInput['employment_status'] ?? 'Active') === $item) ? 'selected' : '' ?>><?= e($item) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>
                    <span class="field-label">Date Hired <em class="text-red">*</em></span>
                    <input type="date" name="date_hired" value="<?= e((string) ($oldInput['date_hired'] ?? '')) ?>" required>
                </label>
                <label>
                    <span class="field-label">Regularization Date</span>
                    <input type="date" name="date_regularized" value="<?= e((string) ($oldInput['date_regularized'] ?? '')) ?>">
                </label>
                <label>
                    <span class="field-label">Separation Date</span>
                    <input type="date" name="date_separated" value="<?= e((string) ($oldInput['date_separated'] ?? '')) ?>">
                </label>
                <label class="full-width">
                    <span class="field-label">Supervisor / Reporting Manager</span>
                    <select name="supervisor_id">
                        <option value="">No direct supervisor</option>
                        <?php foreach ($supervisorOptions as $supervisor): ?>
                            <option value="<?= (int) $supervisor['id'] ?>" <?= ((string) ($oldInput['supervisor_id'] ?? '') === (string) $supervisor['id']) ? 'selected' : '' ?>>
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
                Fields marked with an asterisk <em class="text-red">(*)</em> are required for system enrollment.
            </p>
            <div class="inline-actions">
                <a class="btn btn-ghost" href="/employees">Cancel action</a>
                <button class="btn btn-primary" type="submit">
                    Enroll new employee
                </button>
            </div>
        </div>
    </form>
</section>

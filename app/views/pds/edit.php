<?php
$pdsData  = is_array($pds ?? null) ? $pds : [];
$sections = is_array($sections ?? null) ? $sections : [];
$errors   = is_array($errors ?? null) ? $errors : [];
$oldInput = is_array($old ?? null) ? $old : [];
$empList  = is_array($employees ?? null) ? $employees : [];
$pdsId    = (int) ($pdsData['id'] ?? 0);

// Use $old (flash) if present (validation failed), otherwise use saved $pds data
$val = static function (string $key) use ($oldInput, $pdsData): string {
    if ($oldInput !== []) {
        return (string) ($oldInput[$key] ?? '');
    }
    return (string) ($pdsData[$key] ?? '');
};
$err = static function (string $key) use ($errors): string {
    return isset($errors[$key]) ? '<span class="field-error">' . htmlspecialchars((string) $errors[$key], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</span>' : '';
};

$childRows = static function (string $section, array $sections): array {
    return is_array($sections[$section] ?? null) ? $sections[$section] : [];
};
?>
<style>
.pds-tabs { display:flex; flex-wrap:wrap; gap:0; }
.pds-tabs input[type="radio"] { display:none; }
.pds-tab-label {
    padding:10px 16px; cursor:pointer; border-bottom:2px solid transparent;
    font-size:13px; font-weight:500; color:var(--text-muted);
    white-space:nowrap; user-select:none;
}
.pds-tab-label:hover { color:var(--text-base); }
.pds-tabs input[type="radio"]:checked + .pds-tab-label {
    color:var(--primary); border-bottom-color:var(--primary); font-weight:600;
}
.pds-panel { display:none; width:100%; padding:var(--space-5); }
#etab1:checked ~ #epanel1,
#etab2:checked ~ #epanel2,
#etab3:checked ~ #epanel3,
#etab4:checked ~ #epanel4,
#etab5:checked ~ #epanel5,
#etab6:checked ~ #epanel6,
#etab7:checked ~ #epanel7,
#etab8:checked ~ #epanel8,
#etab9:checked ~ #epanel9,
#etab10:checked ~ #epanel10 { display:block; }
.dynamic-table { width:100%; border-collapse:collapse; margin-top:var(--space-3); }
.dynamic-table th { font-size:11px; text-transform:uppercase; letter-spacing:.05em; color:var(--text-muted); padding:6px 8px; border-bottom:1px solid var(--border); text-align:left; }
.dynamic-table td { padding:4px 6px; vertical-align:top; }
.dynamic-table td input, .dynamic-table td select { width:100%; }
.btn-remove-row { background:none; border:none; cursor:pointer; color:var(--danger); padding:4px; line-height:1; }
.section-heading { font-size:13px; font-weight:600; text-transform:uppercase; letter-spacing:.06em; color:var(--text-muted); margin:var(--space-4) 0 var(--space-2); padding-bottom:6px; border-bottom:1px solid var(--border); }
</style>

<section class="page">
    <header class="page-banner">
        <div class="page-banner-copy">
            <h2 class="page-banner-title">Edit Personal Data Sheet</h2>
            <p class="page-banner-sub">
                Employee: <strong><?= e((string) ($pdsData['emp_last'] ?? '')) ?>, <?= e((string) ($pdsData['emp_first'] ?? '')) ?></strong>
                &mdash; <?= e((string) ($pdsData['employee_code'] ?? '')) ?>
            </p>
        </div>
        <div class="page-banner-actions">
            <a class="btn btn-secondary" href="/pds/<?= $pdsId ?>">Cancel</a>
        </div>
    </header>

    <?php require __DIR__ . '/../partials/alerts.php'; ?>

    <form method="post" action="/pds/<?= $pdsId ?>/update" novalidate>
        <input type="hidden" name="_csrf" value="<?= e((string) ($csrf ?? '')) ?>">
        <!-- employee_id cannot change on edit -->
        <input type="hidden" name="employee_id" value="<?= (int) ($pdsData['employee_id'] ?? 0) ?>">

        <div class="card">
            <div class="pds-tabs" style="border-bottom:1px solid var(--border); padding:0 var(--space-4);">
                <input type="radio" name="pds_tab" id="etab1" checked>
                <label class="pds-tab-label" for="etab1">1. Personal</label>
                <input type="radio" name="pds_tab" id="etab2">
                <label class="pds-tab-label" for="etab2">2. Family</label>
                <input type="radio" name="pds_tab" id="etab3">
                <label class="pds-tab-label" for="etab3">3. Education</label>
                <input type="radio" name="pds_tab" id="etab4">
                <label class="pds-tab-label" for="etab4">4. Civil Service</label>
                <input type="radio" name="pds_tab" id="etab5">
                <label class="pds-tab-label" for="etab5">5. Work Experience</label>
                <input type="radio" name="pds_tab" id="etab6">
                <label class="pds-tab-label" for="etab6">6. Voluntary Work</label>
                <input type="radio" name="pds_tab" id="etab7">
                <label class="pds-tab-label" for="etab7">7. L&amp;D</label>
                <input type="radio" name="pds_tab" id="etab8">
                <label class="pds-tab-label" for="etab8">8. Other Info</label>
                <input type="radio" name="pds_tab" id="etab9">
                <label class="pds-tab-label" for="etab9">9. References</label>
                <input type="radio" name="pds_tab" id="etab10">
                <label class="pds-tab-label" for="etab10">10. Questions</label>

                <!-- Panel 1: Personal -->
                <div class="pds-panel" id="epanel1">
                    <p class="section-heading">Form Status</p>
                    <div class="form-grid form-grid-3">
                        <label>
                            <span class="field-label">Status</span>
                            <select name="status">
                                <option value="Draft" <?= $val('status') !== 'Complete' ? 'selected' : '' ?>>Draft</option>
                                <option value="Complete" <?= $val('status') === 'Complete' ? 'selected' : '' ?>>Complete</option>
                            </select>
                        </label>
                    </div>

                    <p class="section-heading">Name</p>
                    <div class="form-grid form-grid-4">
                        <label>
                            <span class="field-label">Surname <em class="text-red">*</em></span>
                            <input type="text" name="surname" value="<?= e($val('surname')) ?>" required>
                            <?= $err('surname') ?>
                        </label>
                        <label>
                            <span class="field-label">First Name <em class="text-red">*</em></span>
                            <input type="text" name="first_name" value="<?= e($val('first_name')) ?>" required>
                            <?= $err('first_name') ?>
                        </label>
                        <label>
                            <span class="field-label">Middle Name</span>
                            <input type="text" name="middle_name" value="<?= e($val('middle_name')) ?>">
                        </label>
                        <label>
                            <span class="field-label">Extension</span>
                            <input type="text" name="name_extension" value="<?= e($val('name_extension')) ?>">
                        </label>
                    </div>

                    <p class="section-heading">Personal Details</p>
                    <div class="form-grid form-grid-3">
                        <label>
                            <span class="field-label">Date of Birth <em class="text-red">*</em></span>
                            <input type="date" name="birthdate" value="<?= e($val('birthdate')) ?>" required>
                            <?= $err('birthdate') ?>
                        </label>
                        <label>
                            <span class="field-label">Place of Birth</span>
                            <input type="text" name="birthplace" value="<?= e($val('birthplace')) ?>">
                        </label>
                        <label>
                            <span class="field-label">Sex <em class="text-red">*</em></span>
                            <select name="sex" required>
                                <option value="">Select</option>
                                <?php foreach (['Male','Female'] as $opt): ?>
                                    <option value="<?= e($opt) ?>" <?= $val('sex') === $opt ? 'selected' : '' ?>><?= e($opt) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <?= $err('sex') ?>
                        </label>
                        <label>
                            <span class="field-label">Civil Status <em class="text-red">*</em></span>
                            <select name="civil_status" required>
                                <option value="">Select</option>
                                <?php foreach (['Single','Married','Widowed','Separated'] as $opt): ?>
                                    <option value="<?= e($opt) ?>" <?= $val('civil_status') === $opt ? 'selected' : '' ?>><?= e($opt) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <?= $err('civil_status') ?>
                        </label>
                        <label><span class="field-label">Height (m)</span><input type="number" step="0.01" name="height" value="<?= e($val('height')) ?>"></label>
                        <label><span class="field-label">Weight (kg)</span><input type="number" step="0.01" name="weight" value="<?= e($val('weight')) ?>"></label>
                        <label>
                            <span class="field-label">Blood Type</span>
                            <select name="blood_type">
                                <option value="">Select</option>
                                <?php foreach (['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $bt): ?>
                                    <option value="<?= e($bt) ?>" <?= $val('blood_type') === $bt ? 'selected' : '' ?>><?= e($bt) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                    </div>

                    <p class="section-heading">Citizenship</p>
                    <div class="form-grid form-grid-3">
                        <label>
                            <span class="field-label">Dual Citizen?</span>
                            <select name="dual_citizenship">
                                <option value="0" <?= $val('dual_citizenship') !== '1' ? 'selected' : '' ?>>No</option>
                                <option value="1" <?= $val('dual_citizenship') === '1' ? 'selected' : '' ?>>Yes</option>
                            </select>
                        </label>
                        <label>
                            <span class="field-label">By</span>
                            <select name="citizenship_by">
                                <option value="">N/A</option>
                                <option value="birth" <?= $val('citizenship_by') === 'birth' ? 'selected' : '' ?>>Birth</option>
                                <option value="naturalization" <?= $val('citizenship_by') === 'naturalization' ? 'selected' : '' ?>>Naturalization</option>
                            </select>
                        </label>
                        <label><span class="field-label">Country</span><input type="text" name="citizenship_country" value="<?= e($val('citizenship_country')) ?>"></label>
                    </div>

                    <p class="section-heading">Residential Address</p>
                    <div class="form-grid form-grid-3">
                        <label><span class="field-label">House/Block/Lot No.</span><input type="text" name="res_house" value="<?= e($val('res_house')) ?>"></label>
                        <label><span class="field-label">Street</span><input type="text" name="res_street" value="<?= e($val('res_street')) ?>"></label>
                        <label><span class="field-label">Subdivision/Village</span><input type="text" name="res_subdivision" value="<?= e($val('res_subdivision')) ?>"></label>
                        <label><span class="field-label">Barangay</span><input type="text" name="res_barangay" value="<?= e($val('res_barangay')) ?>"></label>
                        <label><span class="field-label">City/Municipality</span><input type="text" name="res_city" value="<?= e($val('res_city')) ?>"></label>
                        <label><span class="field-label">Province</span><input type="text" name="res_province" value="<?= e($val('res_province')) ?>"></label>
                        <label><span class="field-label">ZIP</span><input type="text" name="res_zip" value="<?= e($val('res_zip')) ?>"></label>
                    </div>

                    <p class="section-heading">Permanent Address</p>
                    <div class="form-grid form-grid-3">
                        <label><span class="field-label">House/Block/Lot No.</span><input type="text" name="per_house" value="<?= e($val('per_house')) ?>"></label>
                        <label><span class="field-label">Street</span><input type="text" name="per_street" value="<?= e($val('per_street')) ?>"></label>
                        <label><span class="field-label">Subdivision/Village</span><input type="text" name="per_subdivision" value="<?= e($val('per_subdivision')) ?>"></label>
                        <label><span class="field-label">Barangay</span><input type="text" name="per_barangay" value="<?= e($val('per_barangay')) ?>"></label>
                        <label><span class="field-label">City/Municipality</span><input type="text" name="per_city" value="<?= e($val('per_city')) ?>"></label>
                        <label><span class="field-label">Province</span><input type="text" name="per_province" value="<?= e($val('per_province')) ?>"></label>
                        <label><span class="field-label">ZIP</span><input type="text" name="per_zip" value="<?= e($val('per_zip')) ?>"></label>
                    </div>

                    <p class="section-heading">Contact &amp; Government IDs</p>
                    <div class="form-grid form-grid-3">
                        <label><span class="field-label">Telephone</span><input type="text" name="telephone" value="<?= e($val('telephone')) ?>"></label>
                        <label><span class="field-label">Mobile</span><input type="text" name="mobile" value="<?= e($val('mobile')) ?>"></label>
                        <label><span class="field-label">Personal Email</span><input type="email" name="personal_email" value="<?= e($val('personal_email')) ?>"><?= $err('personal_email') ?></label>
                        <label><span class="field-label">GSIS ID</span><input type="text" name="gsis_id" value="<?= e($val('gsis_id')) ?>"></label>
                        <label><span class="field-label">Pag-IBIG ID</span><input type="text" name="pagibig_id" value="<?= e($val('pagibig_id')) ?>"></label>
                        <label><span class="field-label">PhilHealth</span><input type="text" name="philhealth_id" value="<?= e($val('philhealth_id')) ?>"></label>
                        <label><span class="field-label">SSS No.</span><input type="text" name="sss_no" value="<?= e($val('sss_no')) ?>"></label>
                        <label><span class="field-label">TIN No.</span><input type="text" name="tin_no" value="<?= e($val('tin_no')) ?>"></label>
                        <label><span class="field-label">Agency Employee No.</span><input type="text" name="agency_employee_no" value="<?= e($val('agency_employee_no')) ?>"></label>
                    </div>
                </div><!-- /epanel1 -->

                <!-- Panel 2: Family -->
                <div class="pds-panel" id="epanel2">
                    <p class="section-heading">Spouse Information</p>
                    <div class="form-grid form-grid-3">
                        <label><span class="field-label">Surname</span><input type="text" name="spouse_surname" value="<?= e($val('spouse_surname')) ?>"></label>
                        <label><span class="field-label">First Name</span><input type="text" name="spouse_firstname" value="<?= e($val('spouse_firstname')) ?>"></label>
                        <label><span class="field-label">Middle Name</span><input type="text" name="spouse_middlename" value="<?= e($val('spouse_middlename')) ?>"></label>
                        <label><span class="field-label">Extension</span><input type="text" name="spouse_extension" value="<?= e($val('spouse_extension')) ?>"></label>
                        <label><span class="field-label">Occupation</span><input type="text" name="spouse_occupation" value="<?= e($val('spouse_occupation')) ?>"></label>
                        <label><span class="field-label">Employer</span><input type="text" name="spouse_employer" value="<?= e($val('spouse_employer')) ?>"></label>
                        <label style="grid-column:span 2;"><span class="field-label">Business Address</span><input type="text" name="spouse_business_address" value="<?= e($val('spouse_business_address')) ?>"></label>
                        <label><span class="field-label">Telephone</span><input type="text" name="spouse_telephone" value="<?= e($val('spouse_telephone')) ?>"></label>
                    </div>

                    <p class="section-heading">Father's Name</p>
                    <div class="form-grid form-grid-4">
                        <label><span class="field-label">Surname</span><input type="text" name="father_surname" value="<?= e($val('father_surname')) ?>"></label>
                        <label><span class="field-label">First Name</span><input type="text" name="father_firstname" value="<?= e($val('father_firstname')) ?>"></label>
                        <label><span class="field-label">Middle Name</span><input type="text" name="father_middlename" value="<?= e($val('father_middlename')) ?>"></label>
                        <label><span class="field-label">Extension</span><input type="text" name="father_extension" value="<?= e($val('father_extension')) ?>"></label>
                    </div>

                    <p class="section-heading">Mother's Maiden Name</p>
                    <div class="form-grid form-grid-3">
                        <label><span class="field-label">Surname</span><input type="text" name="mother_surname" value="<?= e($val('mother_surname')) ?>"></label>
                        <label><span class="field-label">First Name</span><input type="text" name="mother_firstname" value="<?= e($val('mother_firstname')) ?>"></label>
                        <label><span class="field-label">Middle Name</span><input type="text" name="mother_middlename" value="<?= e($val('mother_middlename')) ?>"></label>
                    </div>

                    <p class="section-heading">Children</p>
                    <table class="dynamic-table">
                        <thead><tr><th>Full Name</th><th>Date of Birth</th><th style="width:40px;"></th></tr></thead>
                        <tbody id="echildren-body">
                            <?php $crows = $childRows('children', $sections); ?>
                            <?php if ($crows === []): ?>
                                <tr class="child-row">
                                    <td><input type="text" name="children[0][child_name]"></td>
                                    <td><input type="date" name="children[0][child_dob]"></td>
                                    <td><button type="button" class="btn-remove-row" onclick="removeRow(this)"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button></td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($crows as $ci => $crow): ?>
                                    <tr>
                                        <td><input type="text" name="children[<?= $ci ?>][child_name]" value="<?= e((string) ($crow['child_name'] ?? '')) ?>"></td>
                                        <td><input type="date" name="children[<?= $ci ?>][child_dob]" value="<?= e((string) ($crow['child_dob'] ?? '')) ?>"></td>
                                        <td><button type="button" class="btn-remove-row" onclick="removeRow(this)"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    <?php $eChildCount = max(1, count($childRows('children', $sections))); ?>
                    <button type="button" class="btn btn-secondary" style="margin-top:8px;" onclick="addERow('echildren-body','children',<?= $eChildCount ?>,['child_name','child_dob'])">+ Add Child</button>
                </div><!-- /epanel2 -->

                <!-- Panel 3: Education -->
                <div class="pds-panel" id="epanel3">
                    <p class="section-heading">Educational Background</p>
                    <table class="dynamic-table">
                        <thead><tr><th>Level</th><th>School Name</th><th>Degree/Course</th><th>From</th><th>To</th><th>Units</th><th>Year Grad.</th><th>Honors</th><th style="width:40px;"></th></tr></thead>
                        <tbody id="eedu-body">
                            <?php $eduRows = $childRows('education', $sections); ?>
                            <?php if ($eduRows === []): $eduRows = [[]]; endif; ?>
                            <?php foreach ($eduRows as $ei => $er): ?>
                                <tr>
                                    <td><select name="education[<?= $ei ?>][level]">
                                        <?php foreach (['Elementary','Secondary','Vocational','College','Graduate'] as $lvl): ?>
                                            <option value="<?= e($lvl) ?>" <?= ($er['level'] ?? '') === $lvl ? 'selected' : '' ?>><?= e($lvl) ?></option>
                                        <?php endforeach; ?>
                                    </select></td>
                                    <td><input type="text" name="education[<?= $ei ?>][school_name]" value="<?= e((string) ($er['school_name'] ?? '')) ?>"></td>
                                    <td><input type="text" name="education[<?= $ei ?>][degree_course]" value="<?= e((string) ($er['degree_course'] ?? '')) ?>"></td>
                                    <td><input type="text" name="education[<?= $ei ?>][period_from]" value="<?= e((string) ($er['period_from'] ?? '')) ?>"></td>
                                    <td><input type="text" name="education[<?= $ei ?>][period_to]" value="<?= e((string) ($er['period_to'] ?? '')) ?>"></td>
                                    <td><input type="text" name="education[<?= $ei ?>][units_earned]" value="<?= e((string) ($er['units_earned'] ?? '')) ?>"></td>
                                    <td><input type="text" name="education[<?= $ei ?>][year_graduated]" value="<?= e((string) ($er['year_graduated'] ?? '')) ?>"></td>
                                    <td><input type="text" name="education[<?= $ei ?>][scholarship_honors]" value="<?= e((string) ($er['scholarship_honors'] ?? '')) ?>"></td>
                                    <td><button type="button" class="btn-remove-row" onclick="removeRow(this)"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php $eEduCount = max(1, count($childRows('education', $sections))); ?>
                    <button type="button" class="btn btn-secondary" style="margin-top:8px;" onclick="addEduRow2(<?= $eEduCount ?>)">+ Add Education</button>
                </div><!-- /epanel3 -->

                <!-- Panel 4: Civil Service -->
                <div class="pds-panel" id="epanel4">
                    <p class="section-heading">Civil Service Eligibility</p>
                    <table class="dynamic-table">
                        <thead><tr><th>Career Service</th><th>Rating</th><th>Exam Date</th><th>Exam Place</th><th>License No.</th><th>Validity</th><th style="width:40px;"></th></tr></thead>
                        <tbody id="ecs-body">
                            <?php $csRows = $childRows('civil_service', $sections); ?>
                            <?php if ($csRows === []): $csRows = [[]]; endif; ?>
                            <?php foreach ($csRows as $ci => $cr): ?>
                                <tr>
                                    <td><input type="text" name="civil_service[<?= $ci ?>][career_service]" value="<?= e((string) ($cr['career_service'] ?? '')) ?>"></td>
                                    <td><input type="text" name="civil_service[<?= $ci ?>][rating]" value="<?= e((string) ($cr['rating'] ?? '')) ?>"></td>
                                    <td><input type="date" name="civil_service[<?= $ci ?>][exam_date]" value="<?= e((string) ($cr['exam_date'] ?? '')) ?>"></td>
                                    <td><input type="text" name="civil_service[<?= $ci ?>][exam_place]" value="<?= e((string) ($cr['exam_place'] ?? '')) ?>"></td>
                                    <td><input type="text" name="civil_service[<?= $ci ?>][license_number]" value="<?= e((string) ($cr['license_number'] ?? '')) ?>"></td>
                                    <td><input type="date" name="civil_service[<?= $ci ?>][license_validity]" value="<?= e((string) ($cr['license_validity'] ?? '')) ?>"></td>
                                    <td><button type="button" class="btn-remove-row" onclick="removeRow(this)"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php $eCsCount = max(1, count($childRows('civil_service', $sections))); ?>
                    <button type="button" class="btn btn-secondary" style="margin-top:8px;" onclick="addCsRow2(<?= $eCsCount ?>)">+ Add Eligibility</button>
                </div><!-- /epanel4 -->

                <!-- Panel 5: Work Experience -->
                <div class="pds-panel" id="epanel5">
                    <p class="section-heading">Work Experience</p>
                    <table class="dynamic-table">
                        <thead><tr><th>Date From</th><th>Date To</th><th>Position Title</th><th>Agency/Company</th><th>Monthly Salary</th><th>SG</th><th>Status</th><th>Govt?</th><th style="width:40px;"></th></tr></thead>
                        <tbody id="ewe-body">
                            <?php $weRows = $childRows('work_experience', $sections); ?>
                            <?php if ($weRows === []): $weRows = [[]]; endif; ?>
                            <?php foreach ($weRows as $wi => $wr): ?>
                                <tr>
                                    <td><input type="date" name="work_experience[<?= $wi ?>][date_from]" value="<?= e((string) ($wr['date_from'] ?? '')) ?>"></td>
                                    <td><input type="date" name="work_experience[<?= $wi ?>][date_to]" value="<?= e((string) ($wr['date_to'] ?? '')) ?>"></td>
                                    <td><input type="text" name="work_experience[<?= $wi ?>][position_title]" value="<?= e((string) ($wr['position_title'] ?? '')) ?>"></td>
                                    <td><input type="text" name="work_experience[<?= $wi ?>][department_agency]" value="<?= e((string) ($wr['department_agency'] ?? '')) ?>"></td>
                                    <td><input type="number" step="0.01" name="work_experience[<?= $wi ?>][monthly_salary]" value="<?= e((string) ($wr['monthly_salary'] ?? '')) ?>"></td>
                                    <td><input type="text" name="work_experience[<?= $wi ?>][salary_grade]" value="<?= e((string) ($wr['salary_grade'] ?? '')) ?>"></td>
                                    <td><input type="text" name="work_experience[<?= $wi ?>][appointment_status]" value="<?= e((string) ($wr['appointment_status'] ?? '')) ?>"></td>
                                    <td style="text-align:center;"><input type="checkbox" name="work_experience[<?= $wi ?>][is_govt_service]" value="1" <?= ($wr['is_govt_service'] ?? 0) ? 'checked' : '' ?>></td>
                                    <td><button type="button" class="btn-remove-row" onclick="removeRow(this)"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php $eWeCount = max(1, count($childRows('work_experience', $sections))); ?>
                    <button type="button" class="btn btn-secondary" style="margin-top:8px;" onclick="addWeRow2(<?= $eWeCount ?>)">+ Add Work Experience</button>
                </div><!-- /epanel5 -->

                <!-- Panel 6: Voluntary Work -->
                <div class="pds-panel" id="epanel6">
                    <p class="section-heading">Voluntary Work</p>
                    <table class="dynamic-table">
                        <thead><tr><th>Organization &amp; Address</th><th>From</th><th>To</th><th>Hours</th><th>Nature of Work</th><th style="width:40px;"></th></tr></thead>
                        <tbody id="evw-body">
                            <?php $vwRows = $childRows('voluntary_work', $sections); ?>
                            <?php if ($vwRows === []): $vwRows = [[]]; endif; ?>
                            <?php foreach ($vwRows as $vi => $vr): ?>
                                <tr>
                                    <td><input type="text" name="voluntary_work[<?= $vi ?>][organization]" value="<?= e((string) ($vr['organization'] ?? '')) ?>"></td>
                                    <td><input type="date" name="voluntary_work[<?= $vi ?>][date_from]" value="<?= e((string) ($vr['date_from'] ?? '')) ?>"></td>
                                    <td><input type="date" name="voluntary_work[<?= $vi ?>][date_to]" value="<?= e((string) ($vr['date_to'] ?? '')) ?>"></td>
                                    <td><input type="number" name="voluntary_work[<?= $vi ?>][hours_no]" value="<?= e((string) ($vr['hours_no'] ?? '')) ?>"></td>
                                    <td><input type="text" name="voluntary_work[<?= $vi ?>][nature_of_work]" value="<?= e((string) ($vr['nature_of_work'] ?? '')) ?>"></td>
                                    <td><button type="button" class="btn-remove-row" onclick="removeRow(this)"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php $eVwCount = max(1, count($childRows('voluntary_work', $sections))); ?>
                    <button type="button" class="btn btn-secondary" style="margin-top:8px;" onclick="addVwRow2(<?= $eVwCount ?>)">+ Add Voluntary Work</button>
                </div><!-- /epanel6 -->

                <!-- Panel 7: L&D -->
                <div class="pds-panel" id="epanel7">
                    <p class="section-heading">Learning and Development</p>
                    <table class="dynamic-table">
                        <thead><tr><th>Title</th><th>From</th><th>To</th><th>Hours</th><th>Type</th><th>Conducted By</th><th style="width:40px;"></th></tr></thead>
                        <tbody id="eld-body">
                            <?php $ldRows = $childRows('learning_development', $sections); ?>
                            <?php if ($ldRows === []): $ldRows = [[]]; endif; ?>
                            <?php foreach ($ldRows as $li => $lr): ?>
                                <tr>
                                    <td><input type="text" name="learning_development[<?= $li ?>][title]" value="<?= e((string) ($lr['title'] ?? '')) ?>"></td>
                                    <td><input type="date" name="learning_development[<?= $li ?>][date_from]" value="<?= e((string) ($lr['date_from'] ?? '')) ?>"></td>
                                    <td><input type="date" name="learning_development[<?= $li ?>][date_to]" value="<?= e((string) ($lr['date_to'] ?? '')) ?>"></td>
                                    <td><input type="number" name="learning_development[<?= $li ?>][hours_no]" value="<?= e((string) ($lr['hours_no'] ?? '')) ?>"></td>
                                    <td><select name="learning_development[<?= $li ?>][ld_type]">
                                        <option value="">Select</option>
                                        <?php foreach (['Managerial','Supervisory','Technical','Foundation'] as $t): ?>
                                            <option value="<?= e($t) ?>" <?= ($lr['ld_type'] ?? '') === $t ? 'selected' : '' ?>><?= e($t) ?></option>
                                        <?php endforeach; ?>
                                    </select></td>
                                    <td><input type="text" name="learning_development[<?= $li ?>][conducted_by]" value="<?= e((string) ($lr['conducted_by'] ?? '')) ?>"></td>
                                    <td><button type="button" class="btn-remove-row" onclick="removeRow(this)"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php $eLdCount = max(1, count($childRows('learning_development', $sections))); ?>
                    <button type="button" class="btn btn-secondary" style="margin-top:8px;" onclick="addLdRow2(<?= $eLdCount ?>)">+ Add L&amp;D</button>
                </div><!-- /epanel7 -->

                <!-- Panel 8: Other Info -->
                <div class="pds-panel" id="epanel8">
                    <?php
                    $oiRows = $childRows('other_info', $sections);
                    $skills = array_values(array_filter($oiRows, fn($r) => ($r['info_type'] ?? '') === 'skill'));
                    $recs   = array_values(array_filter($oiRows, fn($r) => ($r['info_type'] ?? '') === 'recognition'));
                    $mems   = array_values(array_filter($oiRows, fn($r) => ($r['info_type'] ?? '') === 'membership'));
                    if ($skills === []) $skills = [['value' => '']];
                    if ($recs === [])   $recs   = [['value' => '']];
                    if ($mems === [])   $mems   = [['value' => '']];
                    $skillBase = 0; $recBase = 100; $memBase = 200;
                    ?>
                    <p class="section-heading">Special Skills and Hobbies</p>
                    <div id="eskills-list">
                        <?php foreach ($skills as $si => $sr): ?>
                        <div class="other-row" style="display:flex; gap:8px; margin-bottom:6px;">
                            <input type="text" name="other_info[<?= $skillBase + $si ?>][value]" value="<?= e((string) ($sr['value'] ?? '')) ?>" style="flex:1;">
                            <input type="hidden" name="other_info[<?= $skillBase + $si ?>][info_type]" value="skill">
                            <button type="button" class="btn-remove-row" onclick="removeOtherRow(this)"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" class="btn btn-secondary" style="margin-top:4px;" onclick="addOtherRow('skill','eskills-list')">+ Add Skill</button>

                    <p class="section-heading">Non-Academic Distinctions / Recognition</p>
                    <div id="erec-list">
                        <?php foreach ($recs as $ri => $rr): ?>
                        <div class="other-row" style="display:flex; gap:8px; margin-bottom:6px;">
                            <input type="text" name="other_info[<?= $recBase + $ri ?>][value]" value="<?= e((string) ($rr['value'] ?? '')) ?>" style="flex:1;">
                            <input type="hidden" name="other_info[<?= $recBase + $ri ?>][info_type]" value="recognition">
                            <button type="button" class="btn-remove-row" onclick="removeOtherRow(this)"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" class="btn btn-secondary" style="margin-top:4px;" onclick="addOtherRow('recognition','erec-list')">+ Add Recognition</button>

                    <p class="section-heading">Membership in Association/Organization</p>
                    <div id="emem-list">
                        <?php foreach ($mems as $mi => $mr): ?>
                        <div class="other-row" style="display:flex; gap:8px; margin-bottom:6px;">
                            <input type="text" name="other_info[<?= $memBase + $mi ?>][value]" value="<?= e((string) ($mr['value'] ?? '')) ?>" style="flex:1;">
                            <input type="hidden" name="other_info[<?= $memBase + $mi ?>][info_type]" value="membership">
                            <button type="button" class="btn-remove-row" onclick="removeOtherRow(this)"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" class="btn btn-secondary" style="margin-top:4px;" onclick="addOtherRow('membership','emem-list')">+ Add Membership</button>
                </div><!-- /epanel8 -->

                <!-- Panel 9: References -->
                <div class="pds-panel" id="epanel9">
                    <p class="section-heading">Character References</p>
                    <table class="dynamic-table">
                        <thead><tr><th>Name</th><th>Address</th><th>Telephone</th><th style="width:40px;"></th></tr></thead>
                        <tbody id="eref-body">
                            <?php $refRows = $childRows('references', $sections); ?>
                            <?php if ($refRows === []): $refRows = [[],[],[]]; endif; ?>
                            <?php foreach ($refRows as $ri => $rr): ?>
                                <tr>
                                    <td><input type="text" name="references[<?= $ri ?>][ref_name]" value="<?= e((string) ($rr['ref_name'] ?? '')) ?>"></td>
                                    <td><input type="text" name="references[<?= $ri ?>][ref_address]" value="<?= e((string) ($rr['ref_address'] ?? '')) ?>"></td>
                                    <td><input type="text" name="references[<?= $ri ?>][ref_telephone]" value="<?= e((string) ($rr['ref_telephone'] ?? '')) ?>"></td>
                                    <td><button type="button" class="btn-remove-row" onclick="removeRow(this)"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php $eRefCount = max(3, count($childRows('references', $sections))); ?>
                    <button type="button" class="btn btn-secondary" style="margin-top:8px;" onclick="addRefRow2(<?= $eRefCount ?>)">+ Add Reference</button>
                </div><!-- /epanel9 -->

                <!-- Panel 10: Questions -->
                <div class="pds-panel" id="epanel10">
                    <p class="section-heading">Questions (Answer YES or NO)</p>
                    <?php
                    $questions = [
                        1 => 'Are you related within the third degree of consanguinity or of affinity to the appointing or recommending authority, or to the chief of bureau or office or to the person who has immediate supervision over you in the office, bureau or department where you will be appointed?',
                        2 => 'Have you ever been found guilty of any administrative offense?',
                        3 => 'Have you been criminally charged before any court?',
                        4 => 'Have you ever been convicted of any crime or violation of any law, decree, ordinance or regulation by any court or tribunal?',
                        5 => 'Have you ever been separated from the service in any of the following modes: resignation, retirement, dropped from the rolls, dismissal, termination, end of term, finished contract or phased out (abolition) in the public or private sector?',
                        6 => 'Have you ever been a candidate in a national or local election held within the last year (except Barangay election)?',
                        7 => 'Have you resigned from the government service during the three (3)-month period before the last election to promote/actively campaign for a national or local candidate?',
                        8 => 'Have you acquired the status of an immigrant or permanent resident of another country?',
                    ];
                    foreach ($questions as $n => $text):
                        $ansKey     = 'q' . $n . '_answer';
                        $detailsKey = 'q' . $n . '_details';
                        $savedAnswer = $val($ansKey);
                    ?>
                    <div style="margin-bottom:var(--space-4); padding:var(--space-3); background:var(--surface-2); border-radius:var(--radius); border:1px solid var(--border);">
                        <p style="font-size:13px; margin-bottom:8px;"><strong><?= $n ?>.</strong> <?= htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
                        <div style="display:flex; gap:var(--space-4); align-items:center; flex-wrap:wrap;">
                            <label style="display:flex; align-items:center; gap:6px; cursor:pointer;">
                                <input type="radio" name="<?= e($ansKey) ?>" value="Yes" <?= $savedAnswer === 'Yes' ? 'checked' : '' ?>> Yes
                            </label>
                            <label style="display:flex; align-items:center; gap:6px; cursor:pointer;">
                                <input type="radio" name="<?= e($ansKey) ?>" value="No" <?= $savedAnswer === 'No' ? 'checked' : '' ?>> No
                            </label>
                            <label style="flex:1; min-width:200px;">
                                <span class="field-label" style="font-size:11px;">If Yes, give details:</span>
                                <input type="text" name="<?= e($detailsKey) ?>" value="<?= e($val($detailsKey)) ?>">
                            </label>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div><!-- /epanel10 -->

            </div><!-- /.pds-tabs -->

            <div class="card-footer" style="display:flex; gap:var(--space-3); justify-content:flex-end;">
                <a class="btn btn-secondary" href="/pds/<?= $pdsId ?>">Cancel</a>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </div>
    </form>
</section>

<script>
function removeRow(btn) {
    var row = btn.closest('tr');
    if (row && row.parentElement && row.parentElement.rows.length > 1) { row.remove(); }
}
function removeOtherRow(btn) { var r = btn.closest('.other-row'); if (r) r.remove(); }

function addERow(tbodyId, prefix, startIdx, fields) {
    var tbody = document.getElementById(tbodyId);
    var idx = startIdx++;
    var tr = document.createElement('tr');
    tr.innerHTML = fields.map(function(f) {
        return '<td><input type="text" name="' + prefix + '[' + idx + '][' + f + ']"></td>';
    }).join('') + '<td><button type="button" class="btn-remove-row" onclick="removeRow(this)"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button></td>';
    tbody.appendChild(tr);
}

var eIdx = { edu:<?= $eEduCount ?>, cs:<?= $eCsCount ?>, we:<?= $eWeCount ?>, vw:<?= $eVwCount ?>, ld:<?= $eLdCount ?>, ref:<?= $eRefCount ?>, skill:<?= count($skills) ?>, rec:<?= 100 + count($recs) ?>, mem:<?= 200 + count($mems) ?> };

function addEduRow2(s) {
    var idx = eIdx.edu++; var tbody = document.getElementById('eedu-body');
    var levels = ['Elementary','Secondary','Vocational','College','Graduate'];
    var opts = levels.map(function(l) { return '<option value="' + l + '">' + l + '</option>'; }).join('');
    var tr = document.createElement('tr');
    tr.innerHTML = '<td><select name="education[' + idx + '][level]">' + opts + '</select></td>' +
        '<td><input type="text" name="education[' + idx + '][school_name]"></td>' +
        '<td><input type="text" name="education[' + idx + '][degree_course]"></td>' +
        '<td><input type="text" name="education[' + idx + '][period_from]"></td>' +
        '<td><input type="text" name="education[' + idx + '][period_to]"></td>' +
        '<td><input type="text" name="education[' + idx + '][units_earned]"></td>' +
        '<td><input type="text" name="education[' + idx + '][year_graduated]"></td>' +
        '<td><input type="text" name="education[' + idx + '][scholarship_honors]"></td>' +
        '<td><button type="button" class="btn-remove-row" onclick="removeRow(this)"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button></td>';
    tbody.appendChild(tr);
}
function addCsRow2(s) {
    var idx = eIdx.cs++; var tbody = document.getElementById('ecs-body'); var tr = document.createElement('tr');
    tr.innerHTML = '<td><input type="text" name="civil_service[' + idx + '][career_service]"></td>' +
        '<td><input type="text" name="civil_service[' + idx + '][rating]"></td>' +
        '<td><input type="date" name="civil_service[' + idx + '][exam_date]"></td>' +
        '<td><input type="text" name="civil_service[' + idx + '][exam_place]"></td>' +
        '<td><input type="text" name="civil_service[' + idx + '][license_number]"></td>' +
        '<td><input type="date" name="civil_service[' + idx + '][license_validity]"></td>' +
        '<td><button type="button" class="btn-remove-row" onclick="removeRow(this)"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button></td>';
    tbody.appendChild(tr);
}
function addWeRow2(s) {
    var idx = eIdx.we++; var tbody = document.getElementById('ewe-body'); var tr = document.createElement('tr');
    tr.innerHTML = '<td><input type="date" name="work_experience[' + idx + '][date_from]"></td>' +
        '<td><input type="date" name="work_experience[' + idx + '][date_to]"></td>' +
        '<td><input type="text" name="work_experience[' + idx + '][position_title]"></td>' +
        '<td><input type="text" name="work_experience[' + idx + '][department_agency]"></td>' +
        '<td><input type="number" step="0.01" name="work_experience[' + idx + '][monthly_salary]"></td>' +
        '<td><input type="text" name="work_experience[' + idx + '][salary_grade]"></td>' +
        '<td><input type="text" name="work_experience[' + idx + '][appointment_status]"></td>' +
        '<td style="text-align:center;"><input type="checkbox" name="work_experience[' + idx + '][is_govt_service]" value="1"></td>' +
        '<td><button type="button" class="btn-remove-row" onclick="removeRow(this)"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button></td>';
    tbody.appendChild(tr);
}
function addVwRow2(s) {
    var idx = eIdx.vw++; var tbody = document.getElementById('evw-body'); var tr = document.createElement('tr');
    tr.innerHTML = '<td><input type="text" name="voluntary_work[' + idx + '][organization]"></td>' +
        '<td><input type="date" name="voluntary_work[' + idx + '][date_from]"></td>' +
        '<td><input type="date" name="voluntary_work[' + idx + '][date_to]"></td>' +
        '<td><input type="number" name="voluntary_work[' + idx + '][hours_no]"></td>' +
        '<td><input type="text" name="voluntary_work[' + idx + '][nature_of_work]"></td>' +
        '<td><button type="button" class="btn-remove-row" onclick="removeRow(this)"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button></td>';
    tbody.appendChild(tr);
}
function addLdRow2(s) {
    var idx = eIdx.ld++; var tbody = document.getElementById('eld-body');
    var types = ['Managerial','Supervisory','Technical','Foundation'];
    var opts = '<option value="">Select</option>' + types.map(function(t) { return '<option value="' + t + '">' + t + '</option>'; }).join('');
    var tr = document.createElement('tr');
    tr.innerHTML = '<td><input type="text" name="learning_development[' + idx + '][title]"></td>' +
        '<td><input type="date" name="learning_development[' + idx + '][date_from]"></td>' +
        '<td><input type="date" name="learning_development[' + idx + '][date_to]"></td>' +
        '<td><input type="number" name="learning_development[' + idx + '][hours_no]"></td>' +
        '<td><select name="learning_development[' + idx + '][ld_type]">' + opts + '</select></td>' +
        '<td><input type="text" name="learning_development[' + idx + '][conducted_by]"></td>' +
        '<td><button type="button" class="btn-remove-row" onclick="removeRow(this)"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button></td>';
    tbody.appendChild(tr);
}
function addRefRow2(s) {
    var idx = eIdx.ref++; var tbody = document.getElementById('eref-body'); var tr = document.createElement('tr');
    tr.innerHTML = '<td><input type="text" name="references[' + idx + '][ref_name]"></td>' +
        '<td><input type="text" name="references[' + idx + '][ref_address]"></td>' +
        '<td><input type="text" name="references[' + idx + '][ref_telephone]"></td>' +
        '<td><button type="button" class="btn-remove-row" onclick="removeRow(this)"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button></td>';
    tbody.appendChild(tr);
}
function addOtherRow(type, listId) {
    var offsets = { skill: 0, recognition: 100, membership: 200 };
    var list = document.getElementById(listId);
    var idx = eIdx[type === 'skill' ? 'skill' : (type === 'recognition' ? 'rec' : 'mem')]++;
    idx += offsets[type] || 0;
    var div = document.createElement('div');
    div.className = 'other-row'; div.style = 'display:flex; gap:8px; margin-bottom:6px;';
    div.innerHTML = '<input type="text" name="other_info[' + idx + '][value]" style="flex:1;">' +
        '<input type="hidden" name="other_info[' + idx + '][info_type]" value="' + type + '">' +
        '<button type="button" class="btn-remove-row" onclick="removeOtherRow(this)"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>';
    list.appendChild(div);
}
</script>

<?php
$old       = is_array($old ?? null) ? $old : [];
$errors    = is_array($errors ?? null) ? $errors : [];
$empList   = is_array($employees ?? null) ? $employees : [];
$v = static function (string $key) use ($old): string {
    return (string) ($old[$key] ?? '');
};
$err = static function (string $key) use ($errors): string {
    return isset($errors[$key]) ? '<span class="field-error">' . htmlspecialchars((string) $errors[$key], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</span>' : '';
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
#tab1:checked ~ #panel1,
#tab2:checked ~ #panel2,
#tab3:checked ~ #panel3,
#tab4:checked ~ #panel4,
#tab5:checked ~ #panel5,
#tab6:checked ~ #panel6,
#tab7:checked ~ #panel7,
#tab8:checked ~ #panel8,
#tab9:checked ~ #panel9,
#tab10:checked ~ #panel10 { display:block; }
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
            <h2 class="page-banner-title">Create Personal Data Sheet</h2>
            <p class="page-banner-sub">Fill in all applicable sections. Required fields are marked with <em class="text-red">*</em>.</p>
        </div>
        <div class="page-banner-actions">
            <a class="btn btn-secondary" href="/pds">Cancel</a>
        </div>
    </header>

    <?php require __DIR__ . '/../partials/alerts.php'; ?>

    <form method="post" action="/pds" novalidate>
        <input type="hidden" name="_csrf" value="<?= e((string) ($csrf ?? '')) ?>">

        <div class="card">
            <!-- Tab navigation -->
            <div class="pds-tabs" style="border-bottom:1px solid var(--border); padding:0 var(--space-4);">
                <input type="radio" name="pds_tab" id="tab1" checked>
                <label class="pds-tab-label" for="tab1">1. Personal</label>
                <input type="radio" name="pds_tab" id="tab2">
                <label class="pds-tab-label" for="tab2">2. Family</label>
                <input type="radio" name="pds_tab" id="tab3">
                <label class="pds-tab-label" for="tab3">3. Education</label>
                <input type="radio" name="pds_tab" id="tab4">
                <label class="pds-tab-label" for="tab4">4. Civil Service</label>
                <input type="radio" name="pds_tab" id="tab5">
                <label class="pds-tab-label" for="tab5">5. Work Experience</label>
                <input type="radio" name="pds_tab" id="tab6">
                <label class="pds-tab-label" for="tab6">6. Voluntary Work</label>
                <input type="radio" name="pds_tab" id="tab7">
                <label class="pds-tab-label" for="tab7">7. L&amp;D</label>
                <input type="radio" name="pds_tab" id="tab8">
                <label class="pds-tab-label" for="tab8">8. Other Info</label>
                <input type="radio" name="pds_tab" id="tab9">
                <label class="pds-tab-label" for="tab9">9. References</label>
                <input type="radio" name="pds_tab" id="tab10">
                <label class="pds-tab-label" for="tab10">10. Questions</label>

                <!-- Tab panels -->
                <div class="pds-panel" id="panel1">
                    <p class="section-heading">Employee &amp; Basic Information</p>
                    <div class="form-grid form-grid-3">
                        <label>
                            <span class="field-label">Employee <em class="text-red">*</em></span>
                            <select name="employee_id" required>
                                <option value="">-- Select Employee --</option>
                                <?php foreach ($empList as $emp): ?>
                                    <option value="<?= (int) ($emp['id'] ?? 0) ?>" <?= $v('employee_id') === (string) ($emp['id'] ?? '') ? 'selected' : '' ?>>
                                        <?= e((string) ($emp['employee_code'] ?? '')) ?> &mdash; <?= e((string) ($emp['last_name'] ?? '')) ?>, <?= e((string) ($emp['first_name'] ?? '')) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?= $err('employee_id') ?>
                        </label>
                        <label>
                            <span class="field-label">Form Status</span>
                            <select name="status">
                                <option value="Draft" <?= $v('status') !== 'Complete' ? 'selected' : '' ?>>Draft</option>
                                <option value="Complete" <?= $v('status') === 'Complete' ? 'selected' : '' ?>>Complete</option>
                            </select>
                        </label>
                    </div>

                    <p class="section-heading">Name</p>
                    <div class="form-grid form-grid-4">
                        <label>
                            <span class="field-label">Surname <em class="text-red">*</em></span>
                            <input type="text" name="surname" value="<?= e($v('surname')) ?>" required>
                            <?= $err('surname') ?>
                        </label>
                        <label>
                            <span class="field-label">First Name <em class="text-red">*</em></span>
                            <input type="text" name="first_name" value="<?= e($v('first_name')) ?>" required>
                            <?= $err('first_name') ?>
                        </label>
                        <label>
                            <span class="field-label">Middle Name</span>
                            <input type="text" name="middle_name" value="<?= e($v('middle_name')) ?>">
                        </label>
                        <label>
                            <span class="field-label">Extension (Jr/III)</span>
                            <input type="text" name="name_extension" value="<?= e($v('name_extension')) ?>" placeholder="e.g. Jr.">
                        </label>
                    </div>

                    <p class="section-heading">Personal Details</p>
                    <div class="form-grid form-grid-3">
                        <label>
                            <span class="field-label">Date of Birth <em class="text-red">*</em></span>
                            <input type="date" name="birthdate" value="<?= e($v('birthdate')) ?>" required>
                            <?= $err('birthdate') ?>
                        </label>
                        <label>
                            <span class="field-label">Place of Birth</span>
                            <input type="text" name="birthplace" value="<?= e($v('birthplace')) ?>">
                        </label>
                        <label>
                            <span class="field-label">Sex <em class="text-red">*</em></span>
                            <select name="sex" required>
                                <option value="">Select</option>
                                <?php foreach (['Male','Female'] as $opt): ?>
                                    <option value="<?= e($opt) ?>" <?= $v('sex') === $opt ? 'selected' : '' ?>><?= e($opt) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <?= $err('sex') ?>
                        </label>
                        <label>
                            <span class="field-label">Civil Status <em class="text-red">*</em></span>
                            <select name="civil_status" required>
                                <option value="">Select</option>
                                <?php foreach (['Single','Married','Widowed','Separated'] as $opt): ?>
                                    <option value="<?= e($opt) ?>" <?= $v('civil_status') === $opt ? 'selected' : '' ?>><?= e($opt) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <?= $err('civil_status') ?>
                        </label>
                        <label>
                            <span class="field-label">Height (m)</span>
                            <input type="number" step="0.01" name="height" value="<?= e($v('height')) ?>" placeholder="e.g. 1.65">
                        </label>
                        <label>
                            <span class="field-label">Weight (kg)</span>
                            <input type="number" step="0.01" name="weight" value="<?= e($v('weight')) ?>" placeholder="e.g. 60">
                        </label>
                        <label>
                            <span class="field-label">Blood Type</span>
                            <select name="blood_type">
                                <option value="">Select</option>
                                <?php foreach (['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $bt): ?>
                                    <option value="<?= e($bt) ?>" <?= $v('blood_type') === $bt ? 'selected' : '' ?>><?= e($bt) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                    </div>

                    <p class="section-heading">Citizenship</p>
                    <div class="form-grid form-grid-3">
                        <label>
                            <span class="field-label">Dual Citizen?</span>
                            <select name="dual_citizenship">
                                <option value="0" <?= $v('dual_citizenship') !== '1' ? 'selected' : '' ?>>No</option>
                                <option value="1" <?= $v('dual_citizenship') === '1' ? 'selected' : '' ?>>Yes</option>
                            </select>
                        </label>
                        <label>
                            <span class="field-label">By</span>
                            <select name="citizenship_by">
                                <option value="">N/A</option>
                                <option value="birth" <?= $v('citizenship_by') === 'birth' ? 'selected' : '' ?>>Birth</option>
                                <option value="naturalization" <?= $v('citizenship_by') === 'naturalization' ? 'selected' : '' ?>>Naturalization</option>
                            </select>
                        </label>
                        <label>
                            <span class="field-label">Country</span>
                            <input type="text" name="citizenship_country" value="<?= e($v('citizenship_country')) ?>" placeholder="If dual citizen">
                        </label>
                    </div>

                    <p class="section-heading">Residential Address</p>
                    <div class="form-grid form-grid-3">
                        <label><span class="field-label">House/Block/Lot No.</span><input type="text" name="res_house" value="<?= e($v('res_house')) ?>"></label>
                        <label><span class="field-label">Street</span><input type="text" name="res_street" value="<?= e($v('res_street')) ?>"></label>
                        <label><span class="field-label">Subdivision/Village</span><input type="text" name="res_subdivision" value="<?= e($v('res_subdivision')) ?>"></label>
                        <label><span class="field-label">Barangay</span><input type="text" name="res_barangay" value="<?= e($v('res_barangay')) ?>"></label>
                        <label><span class="field-label">City/Municipality</span><input type="text" name="res_city" value="<?= e($v('res_city')) ?>"></label>
                        <label><span class="field-label">Province</span><input type="text" name="res_province" value="<?= e($v('res_province')) ?>"></label>
                        <label><span class="field-label">ZIP Code</span><input type="text" name="res_zip" value="<?= e($v('res_zip')) ?>"></label>
                    </div>

                    <p class="section-heading">Permanent Address</p>
                    <div class="form-grid form-grid-3">
                        <label><span class="field-label">House/Block/Lot No.</span><input type="text" name="per_house" value="<?= e($v('per_house')) ?>"></label>
                        <label><span class="field-label">Street</span><input type="text" name="per_street" value="<?= e($v('per_street')) ?>"></label>
                        <label><span class="field-label">Subdivision/Village</span><input type="text" name="per_subdivision" value="<?= e($v('per_subdivision')) ?>"></label>
                        <label><span class="field-label">Barangay</span><input type="text" name="per_barangay" value="<?= e($v('per_barangay')) ?>"></label>
                        <label><span class="field-label">City/Municipality</span><input type="text" name="per_city" value="<?= e($v('per_city')) ?>"></label>
                        <label><span class="field-label">Province</span><input type="text" name="per_province" value="<?= e($v('per_province')) ?>"></label>
                        <label><span class="field-label">ZIP Code</span><input type="text" name="per_zip" value="<?= e($v('per_zip')) ?>"></label>
                    </div>

                    <p class="section-heading">Contact &amp; Government IDs</p>
                    <div class="form-grid form-grid-3">
                        <label><span class="field-label">Telephone No.</span><input type="text" name="telephone" value="<?= e($v('telephone')) ?>"></label>
                        <label><span class="field-label">Mobile No.</span><input type="text" name="mobile" value="<?= e($v('mobile')) ?>"></label>
                        <label><span class="field-label">Personal Email</span><input type="email" name="personal_email" value="<?= e($v('personal_email')) ?>"><?= $err('personal_email') ?></label>
                        <label><span class="field-label">GSIS ID No.</span><input type="text" name="gsis_id" value="<?= e($v('gsis_id')) ?>"></label>
                        <label><span class="field-label">Pag-IBIG ID No.</span><input type="text" name="pagibig_id" value="<?= e($v('pagibig_id')) ?>"></label>
                        <label><span class="field-label">PhilHealth No.</span><input type="text" name="philhealth_id" value="<?= e($v('philhealth_id')) ?>"></label>
                        <label><span class="field-label">SSS No.</span><input type="text" name="sss_no" value="<?= e($v('sss_no')) ?>"></label>
                        <label><span class="field-label">TIN No.</span><input type="text" name="tin_no" value="<?= e($v('tin_no')) ?>"></label>
                        <label><span class="field-label">Agency Employee No.</span><input type="text" name="agency_employee_no" value="<?= e($v('agency_employee_no')) ?>"></label>
                    </div>
                </div><!-- /panel1 -->

                <div class="pds-panel" id="panel2">
                    <p class="section-heading">Spouse Information</p>
                    <div class="form-grid form-grid-3">
                        <label><span class="field-label">Surname</span><input type="text" name="spouse_surname" value="<?= e($v('spouse_surname')) ?>"></label>
                        <label><span class="field-label">First Name</span><input type="text" name="spouse_firstname" value="<?= e($v('spouse_firstname')) ?>"></label>
                        <label><span class="field-label">Middle Name</span><input type="text" name="spouse_middlename" value="<?= e($v('spouse_middlename')) ?>"></label>
                        <label><span class="field-label">Extension</span><input type="text" name="spouse_extension" value="<?= e($v('spouse_extension')) ?>" placeholder="Jr./III"></label>
                        <label><span class="field-label">Occupation</span><input type="text" name="spouse_occupation" value="<?= e($v('spouse_occupation')) ?>"></label>
                        <label><span class="field-label">Employer/Business Name</span><input type="text" name="spouse_employer" value="<?= e($v('spouse_employer')) ?>"></label>
                        <label style="grid-column:span 2;"><span class="field-label">Business Address</span><input type="text" name="spouse_business_address" value="<?= e($v('spouse_business_address')) ?>"></label>
                        <label><span class="field-label">Telephone No.</span><input type="text" name="spouse_telephone" value="<?= e($v('spouse_telephone')) ?>"></label>
                    </div>

                    <p class="section-heading">Father's Name</p>
                    <div class="form-grid form-grid-4">
                        <label><span class="field-label">Surname</span><input type="text" name="father_surname" value="<?= e($v('father_surname')) ?>"></label>
                        <label><span class="field-label">First Name</span><input type="text" name="father_firstname" value="<?= e($v('father_firstname')) ?>"></label>
                        <label><span class="field-label">Middle Name</span><input type="text" name="father_middlename" value="<?= e($v('father_middlename')) ?>"></label>
                        <label><span class="field-label">Extension</span><input type="text" name="father_extension" value="<?= e($v('father_extension')) ?>" placeholder="Jr./III"></label>
                    </div>

                    <p class="section-heading">Mother's Maiden Name</p>
                    <div class="form-grid form-grid-3">
                        <label><span class="field-label">Surname</span><input type="text" name="mother_surname" value="<?= e($v('mother_surname')) ?>"></label>
                        <label><span class="field-label">First Name</span><input type="text" name="mother_firstname" value="<?= e($v('mother_firstname')) ?>"></label>
                        <label><span class="field-label">Middle Name</span><input type="text" name="mother_middlename" value="<?= e($v('mother_middlename')) ?>"></label>
                    </div>

                    <p class="section-heading">Children</p>
                    <table class="dynamic-table" id="children-table">
                        <thead><tr><th>Full Name</th><th>Date of Birth</th><th style="width:40px;"></th></tr></thead>
                        <tbody id="children-body">
                            <tr class="child-row">
                                <td><input type="text" name="children[0][child_name]" placeholder="Full name"></td>
                                <td><input type="date" name="children[0][child_dob]"></td>
                                <td><button type="button" class="btn-remove-row" onclick="removeRow(this)" title="Remove">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                </button></td>
                            </tr>
                        </tbody>
                    </table>
                    <button type="button" class="btn btn-secondary" style="margin-top:8px;" onclick="addRow('children','children-body',['child_name','child_dob'])">+ Add Child</button>
                </div><!-- /panel2 -->

                <div class="pds-panel" id="panel3">
                    <p class="section-heading">Educational Background</p>
                    <table class="dynamic-table" id="education-table">
                        <thead><tr><th>Level</th><th>School Name</th><th>Degree/Course</th><th>From</th><th>To</th><th>Units Earned</th><th>Year Grad.</th><th>Honors</th><th style="width:40px;"></th></tr></thead>
                        <tbody id="education-body">
                            <tr class="edu-row">
                                <td>
                                    <select name="education[0][level]">
                                        <?php foreach (['Elementary','Secondary','Vocational','College','Graduate'] as $lvl): ?>
                                            <option value="<?= e($lvl) ?>"><?= e($lvl) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td><input type="text" name="education[0][school_name]" placeholder="School name"></td>
                                <td><input type="text" name="education[0][degree_course]" placeholder="Degree/Course"></td>
                                <td><input type="text" name="education[0][period_from]" placeholder="YYYY"></td>
                                <td><input type="text" name="education[0][period_to]" placeholder="YYYY"></td>
                                <td><input type="text" name="education[0][units_earned]" placeholder="Units"></td>
                                <td><input type="text" name="education[0][year_graduated]" placeholder="YYYY"></td>
                                <td><input type="text" name="education[0][scholarship_honors]" placeholder="Honors"></td>
                                <td><button type="button" class="btn-remove-row" onclick="removeRow(this)" title="Remove"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button></td>
                            </tr>
                        </tbody>
                    </table>
                    <button type="button" class="btn btn-secondary" style="margin-top:8px;" onclick="addEduRow()">+ Add Education</button>
                </div><!-- /panel3 -->

                <div class="pds-panel" id="panel4">
                    <p class="section-heading">Civil Service Eligibility</p>
                    <table class="dynamic-table" id="cs-table">
                        <thead><tr><th>Career Service / RA 1080</th><th>Rating</th><th>Exam Date</th><th>Exam Place</th><th>License No.</th><th>Validity</th><th style="width:40px;"></th></tr></thead>
                        <tbody id="cs-body">
                            <tr>
                                <td><input type="text" name="civil_service[0][career_service]" placeholder="e.g. Career Service Professional"></td>
                                <td><input type="text" name="civil_service[0][rating]" placeholder="e.g. 80.25"></td>
                                <td><input type="date" name="civil_service[0][exam_date]"></td>
                                <td><input type="text" name="civil_service[0][exam_place]" placeholder="City, Province"></td>
                                <td><input type="text" name="civil_service[0][license_number]"></td>
                                <td><input type="date" name="civil_service[0][license_validity]"></td>
                                <td><button type="button" class="btn-remove-row" onclick="removeRow(this)" title="Remove"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button></td>
                            </tr>
                        </tbody>
                    </table>
                    <button type="button" class="btn btn-secondary" style="margin-top:8px;" onclick="addCsRow()">+ Add Eligibility</button>
                </div><!-- /panel4 -->

                <div class="pds-panel" id="panel5">
                    <p class="section-heading">Work Experience</p>
                    <p style="font-size:12px; color:var(--text-muted);">Include all, whether government or private; part-time or full-time including military service.</p>
                    <table class="dynamic-table" id="we-table">
                        <thead><tr><th>Date From</th><th>Date To</th><th>Position Title</th><th>Dept./Agency/Office/Company</th><th>Monthly Salary</th><th>Salary Grade</th><th>Appointment Status</th><th>Govt?</th><th style="width:40px;"></th></tr></thead>
                        <tbody id="we-body">
                            <tr>
                                <td><input type="date" name="work_experience[0][date_from]"></td>
                                <td><input type="date" name="work_experience[0][date_to]" placeholder="Present"></td>
                                <td><input type="text" name="work_experience[0][position_title]"></td>
                                <td><input type="text" name="work_experience[0][department_agency]"></td>
                                <td><input type="number" step="0.01" name="work_experience[0][monthly_salary]"></td>
                                <td><input type="text" name="work_experience[0][salary_grade]" placeholder="e.g. 15-3"></td>
                                <td><input type="text" name="work_experience[0][appointment_status]" placeholder="Permanent/COS"></td>
                                <td style="text-align:center;"><input type="checkbox" name="work_experience[0][is_govt_service]" value="1"></td>
                                <td><button type="button" class="btn-remove-row" onclick="removeRow(this)" title="Remove"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button></td>
                            </tr>
                        </tbody>
                    </table>
                    <button type="button" class="btn btn-secondary" style="margin-top:8px;" onclick="addWeRow()">+ Add Work Experience</button>
                </div><!-- /panel5 -->

                <div class="pds-panel" id="panel6">
                    <p class="section-heading">Voluntary Work or Involvement in Civic / Non-Government / People / Voluntary Organization</p>
                    <table class="dynamic-table" id="vw-table">
                        <thead><tr><th>Organization &amp; Address</th><th>From</th><th>To</th><th>No. of Hours</th><th>Nature of Work</th><th style="width:40px;"></th></tr></thead>
                        <tbody id="vw-body">
                            <tr>
                                <td><input type="text" name="voluntary_work[0][organization]" placeholder="Organization name and address"></td>
                                <td><input type="date" name="voluntary_work[0][date_from]"></td>
                                <td><input type="date" name="voluntary_work[0][date_to]"></td>
                                <td><input type="number" name="voluntary_work[0][hours_no]"></td>
                                <td><input type="text" name="voluntary_work[0][nature_of_work]"></td>
                                <td><button type="button" class="btn-remove-row" onclick="removeRow(this)" title="Remove"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button></td>
                            </tr>
                        </tbody>
                    </table>
                    <button type="button" class="btn btn-secondary" style="margin-top:8px;" onclick="addVwRow()">+ Add Voluntary Work</button>
                </div><!-- /panel6 -->

                <div class="pds-panel" id="panel7">
                    <p class="section-heading">Learning and Development (L&amp;D) Interventions/Training Programs Attended</p>
                    <table class="dynamic-table" id="ld-table">
                        <thead><tr><th>Title of L&amp;D / Training</th><th>From</th><th>To</th><th>Hours</th><th>Type</th><th>Conducted by</th><th style="width:40px;"></th></tr></thead>
                        <tbody id="ld-body">
                            <tr>
                                <td><input type="text" name="learning_development[0][title]" placeholder="Training/program title"></td>
                                <td><input type="date" name="learning_development[0][date_from]"></td>
                                <td><input type="date" name="learning_development[0][date_to]"></td>
                                <td><input type="number" name="learning_development[0][hours_no]"></td>
                                <td>
                                    <select name="learning_development[0][ld_type]">
                                        <option value="">Select</option>
                                        <?php foreach (['Managerial','Supervisory','Technical','Foundation'] as $t): ?>
                                            <option value="<?= e($t) ?>"><?= e($t) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td><input type="text" name="learning_development[0][conducted_by]"></td>
                                <td><button type="button" class="btn-remove-row" onclick="removeRow(this)" title="Remove"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button></td>
                            </tr>
                        </tbody>
                    </table>
                    <button type="button" class="btn btn-secondary" style="margin-top:8px;" onclick="addLdRow()">+ Add L&amp;D</button>
                </div><!-- /panel7 -->

                <div class="pds-panel" id="panel8">
                    <p class="section-heading">Special Skills and Hobbies</p>
                    <div id="skills-list">
                        <div class="other-row" style="display:flex; gap:8px; margin-bottom:6px;">
                            <input type="text" name="other_info[0][value]" placeholder="e.g. Photography, Chess" style="flex:1;">
                            <input type="hidden" name="other_info[0][info_type]" value="skill">
                            <button type="button" class="btn-remove-row" onclick="removeOtherRow(this)"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
                        </div>
                    </div>
                    <button type="button" class="btn btn-secondary" style="margin-top:4px;" onclick="addOtherRow('skill','skills-list')">+ Add Skill</button>

                    <p class="section-heading">Non-Academic Distinctions / Recognition</p>
                    <div id="recognition-list">
                        <div class="other-row" style="display:flex; gap:8px; margin-bottom:6px;">
                            <input type="text" name="other_info[100][value]" placeholder="e.g. Most Outstanding Employee 2023" style="flex:1;">
                            <input type="hidden" name="other_info[100][info_type]" value="recognition">
                            <button type="button" class="btn-remove-row" onclick="removeOtherRow(this)"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
                        </div>
                    </div>
                    <button type="button" class="btn btn-secondary" style="margin-top:4px;" onclick="addOtherRow('recognition','recognition-list')">+ Add Recognition</button>

                    <p class="section-heading">Membership in Association/Organization</p>
                    <div id="membership-list">
                        <div class="other-row" style="display:flex; gap:8px; margin-bottom:6px;">
                            <input type="text" name="other_info[200][value]" placeholder="e.g. Philippine Nurses Association" style="flex:1;">
                            <input type="hidden" name="other_info[200][info_type]" value="membership">
                            <button type="button" class="btn-remove-row" onclick="removeOtherRow(this)"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
                        </div>
                    </div>
                    <button type="button" class="btn btn-secondary" style="margin-top:4px;" onclick="addOtherRow('membership','membership-list')">+ Add Membership</button>
                </div><!-- /panel8 -->

                <div class="pds-panel" id="panel9">
                    <p class="section-heading">Character References</p>
                    <p style="font-size:12px; color:var(--text-muted);">List at least 3 character references. Not related by consanguinity or affinity.</p>
                    <table class="dynamic-table" id="ref-table">
                        <thead><tr><th>Name</th><th>Address</th><th>Telephone No.</th><th style="width:40px;"></th></tr></thead>
                        <tbody id="ref-body">
                            <?php for ($r = 0; $r < 3; $r++): ?>
                            <tr>
                                <td><input type="text" name="references[<?= $r ?>][ref_name]"></td>
                                <td><input type="text" name="references[<?= $r ?>][ref_address]"></td>
                                <td><input type="text" name="references[<?= $r ?>][ref_telephone]"></td>
                                <td><button type="button" class="btn-remove-row" onclick="removeRow(this)" title="Remove"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button></td>
                            </tr>
                            <?php endfor; ?>
                        </tbody>
                    </table>
                    <button type="button" class="btn btn-secondary" style="margin-top:8px;" onclick="addRefRow()">+ Add Reference</button>
                </div><!-- /panel9 -->

                <div class="pds-panel" id="panel10">
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
                    ?>
                    <div style="margin-bottom:var(--space-4); padding:var(--space-3); background:var(--surface-2); border-radius:var(--radius); border:1px solid var(--border);">
                        <p style="font-size:13px; margin-bottom:8px;"><strong><?= $n ?>.</strong> <?= htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
                        <div style="display:flex; gap:var(--space-4); align-items:center; flex-wrap:wrap;">
                            <label style="display:flex; align-items:center; gap:6px; cursor:pointer;">
                                <input type="radio" name="<?= e($ansKey) ?>" value="Yes" <?= $v($ansKey) === 'Yes' ? 'checked' : '' ?>> Yes
                            </label>
                            <label style="display:flex; align-items:center; gap:6px; cursor:pointer;">
                                <input type="radio" name="<?= e($ansKey) ?>" value="No" <?= $v($ansKey) === 'No' ? 'checked' : '' ?>> No
                            </label>
                            <label style="flex:1; min-width:200px;">
                                <span class="field-label" style="font-size:11px;">If Yes, give details:</span>
                                <input type="text" name="<?= e($detailsKey) ?>" value="<?= e($v($detailsKey)) ?>" placeholder="Details...">
                            </label>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div><!-- /panel10 -->

            </div><!-- /.pds-tabs -->

            <div class="card-footer" style="display:flex; gap:var(--space-3); justify-content:flex-end;">
                <a class="btn btn-secondary" href="/pds">Cancel</a>
                <button type="submit" name="status" value="Draft" class="btn btn-secondary">Save as Draft</button>
                <button type="submit" name="status" value="Complete" class="btn btn-primary">Submit &amp; Complete</button>
            </div>
        </div>
    </form>
</section>

<script>
var rowCounters = { education: 1, civil_service: 1, work_experience: 1, voluntary_work: 1, learning_development: 1, references: 3, other_info: { skill: 1, recognition: 101, membership: 201 } };

function removeRow(btn) {
    var row = btn.closest('tr');
    if (row && row.parentElement && row.parentElement.rows.length > 1) {
        row.remove();
    }
}

function removeOtherRow(btn) {
    var row = btn.closest('.other-row');
    if (row) row.remove();
}

function addRow(prefix, tbodyId, fields) {
    var tbody = document.getElementById(tbodyId);
    var idx = rowCounters[prefix]++;
    var tr = document.createElement('tr');
    tr.innerHTML = fields.map(function(f) {
        return '<td><input type="text" name="' + prefix + '[' + idx + '][' + f + ']"></td>';
    }).join('') + '<td><button type="button" class="btn-remove-row" onclick="removeRow(this)" title="Remove">' +
    '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button></td>';
    tbody.appendChild(tr);
}

function addEduRow() {
    var tbody = document.getElementById('education-body');
    var idx = rowCounters.education++;
    var levels = ['Elementary','Secondary','Vocational','College','Graduate'];
    var opts = levels.map(function(l) { return '<option value="' + l + '">' + l + '</option>'; }).join('');
    var tr = document.createElement('tr');
    tr.innerHTML =
        '<td><select name="education[' + idx + '][level]">' + opts + '</select></td>' +
        '<td><input type="text" name="education[' + idx + '][school_name]"></td>' +
        '<td><input type="text" name="education[' + idx + '][degree_course]"></td>' +
        '<td><input type="text" name="education[' + idx + '][period_from]" placeholder="YYYY"></td>' +
        '<td><input type="text" name="education[' + idx + '][period_to]" placeholder="YYYY"></td>' +
        '<td><input type="text" name="education[' + idx + '][units_earned]"></td>' +
        '<td><input type="text" name="education[' + idx + '][year_graduated]" placeholder="YYYY"></td>' +
        '<td><input type="text" name="education[' + idx + '][scholarship_honors]"></td>' +
        '<td><button type="button" class="btn-remove-row" onclick="removeRow(this)"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button></td>';
    tbody.appendChild(tr);
}

function addCsRow() {
    var tbody = document.getElementById('cs-body');
    var idx = rowCounters.civil_service++;
    var tr = document.createElement('tr');
    tr.innerHTML =
        '<td><input type="text" name="civil_service[' + idx + '][career_service]"></td>' +
        '<td><input type="text" name="civil_service[' + idx + '][rating]"></td>' +
        '<td><input type="date" name="civil_service[' + idx + '][exam_date]"></td>' +
        '<td><input type="text" name="civil_service[' + idx + '][exam_place]"></td>' +
        '<td><input type="text" name="civil_service[' + idx + '][license_number]"></td>' +
        '<td><input type="date" name="civil_service[' + idx + '][license_validity]"></td>' +
        '<td><button type="button" class="btn-remove-row" onclick="removeRow(this)"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button></td>';
    tbody.appendChild(tr);
}

function addWeRow() {
    var tbody = document.getElementById('we-body');
    var idx = rowCounters.work_experience++;
    var tr = document.createElement('tr');
    tr.innerHTML =
        '<td><input type="date" name="work_experience[' + idx + '][date_from]"></td>' +
        '<td><input type="date" name="work_experience[' + idx + '][date_to]"></td>' +
        '<td><input type="text" name="work_experience[' + idx + '][position_title]"></td>' +
        '<td><input type="text" name="work_experience[' + idx + '][department_agency]"></td>' +
        '<td><input type="number" step="0.01" name="work_experience[' + idx + '][monthly_salary]"></td>' +
        '<td><input type="text" name="work_experience[' + idx + '][salary_grade]"></td>' +
        '<td><input type="text" name="work_experience[' + idx + '][appointment_status]"></td>' +
        '<td style="text-align:center;"><input type="checkbox" name="work_experience[' + idx + '][is_govt_service]" value="1"></td>' +
        '<td><button type="button" class="btn-remove-row" onclick="removeRow(this)"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button></td>';
    tbody.appendChild(tr);
}

function addVwRow() {
    var tbody = document.getElementById('vw-body');
    var idx = rowCounters.voluntary_work++;
    var tr = document.createElement('tr');
    tr.innerHTML =
        '<td><input type="text" name="voluntary_work[' + idx + '][organization]"></td>' +
        '<td><input type="date" name="voluntary_work[' + idx + '][date_from]"></td>' +
        '<td><input type="date" name="voluntary_work[' + idx + '][date_to]"></td>' +
        '<td><input type="number" name="voluntary_work[' + idx + '][hours_no]"></td>' +
        '<td><input type="text" name="voluntary_work[' + idx + '][nature_of_work]"></td>' +
        '<td><button type="button" class="btn-remove-row" onclick="removeRow(this)"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button></td>';
    tbody.appendChild(tr);
}

function addLdRow() {
    var tbody = document.getElementById('ld-body');
    var idx = rowCounters.learning_development++;
    var types = ['Managerial','Supervisory','Technical','Foundation'];
    var opts = '<option value="">Select</option>' + types.map(function(t) { return '<option value="' + t + '">' + t + '</option>'; }).join('');
    var tr = document.createElement('tr');
    tr.innerHTML =
        '<td><input type="text" name="learning_development[' + idx + '][title]"></td>' +
        '<td><input type="date" name="learning_development[' + idx + '][date_from]"></td>' +
        '<td><input type="date" name="learning_development[' + idx + '][date_to]"></td>' +
        '<td><input type="number" name="learning_development[' + idx + '][hours_no]"></td>' +
        '<td><select name="learning_development[' + idx + '][ld_type]">' + opts + '</select></td>' +
        '<td><input type="text" name="learning_development[' + idx + '][conducted_by]"></td>' +
        '<td><button type="button" class="btn-remove-row" onclick="removeRow(this)"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button></td>';
    tbody.appendChild(tr);
}

function addRefRow() {
    var tbody = document.getElementById('ref-body');
    var idx = rowCounters.references++;
    var tr = document.createElement('tr');
    tr.innerHTML =
        '<td><input type="text" name="references[' + idx + '][ref_name]"></td>' +
        '<td><input type="text" name="references[' + idx + '][ref_address]"></td>' +
        '<td><input type="text" name="references[' + idx + '][ref_telephone]"></td>' +
        '<td><button type="button" class="btn-remove-row" onclick="removeRow(this)"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button></td>';
    tbody.appendChild(tr);
}

function addOtherRow(type, listId) {
    var list = document.getElementById(listId);
    var idx = rowCounters.other_info[type]++;
    var div = document.createElement('div');
    div.className = 'other-row';
    div.style = 'display:flex; gap:8px; margin-bottom:6px;';
    div.innerHTML =
        '<input type="text" name="other_info[' + idx + '][value]" placeholder="" style="flex:1;">' +
        '<input type="hidden" name="other_info[' + idx + '][info_type]" value="' + type + '">' +
        '<button type="button" class="btn-remove-row" onclick="removeOtherRow(this)">' +
        '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>';
    list.appendChild(div);
}
</script>

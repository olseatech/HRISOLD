<?php
$employee   = is_array($employee ?? null) ? $employee : [];
$pds        = is_array($pds ?? null) ? $pds : [];
$children   = is_array($children ?? null) ? $children : [];
$education  = is_array($education ?? null) ? $education : [];
$cs         = is_array($civilService ?? null) ? $civilService : [];
$workExp    = is_array($workExp ?? null) ? $workExp : [];
$voluntary  = is_array($voluntary ?? null) ? $voluntary : [];
$lnd        = is_array($lnd ?? null) ? $lnd : [];
$otherInfo  = is_array($otherInfo ?? null) ? ($otherInfo[0] ?? []) : [];
$references = is_array($references ?? null) ? $references : [];
$name       = trim(($employee['first_name'] ?? '') . ' ' . ($employee['middle_name'] ?? '') . ' ' . ($employee['last_name'] ?? ''));
$today      = date('F j, Y');

$v = static function (string $key) use ($pds): string {
    return e((string) ($pds[$key] ?? ''));
};
?>
<div class="print-page">
    <div class="page-header">
        <p class="agency">Civil Service Form No. 212</p>
        <h1>Personal Data Sheet</h1>
        <p class="agency">Barangay HRIS &bull; Generated: <?= e($today) ?></p>
    </div>

    <!-- I. PERSONAL INFORMATION -->
    <table>
        <tr><td colspan="4" class="section-header">I. PERSONAL INFORMATION</td></tr>
        <tr>
            <th class="label" style="width:20%;">Surname</th><td><?= e(strtoupper(($employee['last_name']??''))) ?></td>
            <th class="label" style="width:20%;">First Name</th><td><?= e((string)($employee['first_name']??'')) ?></td>
        </tr>
        <tr>
            <th class="label">Middle Name</th><td><?= e((string)($employee['middle_name']??'')) ?></td>
            <th class="label">Name Extension</th><td><?= $v('name_extension') ?></td>
        </tr>
        <tr>
            <th class="label">Date of Birth</th><td><?= e((string)($employee['date_of_birth']??'')) ?></td>
            <th class="label">Place of Birth</th><td><?= $v('place_of_birth') ?></td>
        </tr>
        <tr>
            <th class="label">Sex</th><td><?= e((string)($employee['gender']??'')) ?></td>
            <th class="label">Civil Status</th><td><?= $v('civil_status') ?></td>
        </tr>
        <tr>
            <th class="label">Height (m)</th><td><?= $v('height_m') ?></td>
            <th class="label">Weight (kg)</th><td><?= $v('weight_kg') ?></td>
        </tr>
        <tr>
            <th class="label">Blood Type</th><td><?= $v('blood_type') ?></td>
            <th class="label">Citizenship</th><td><?= $v('citizenship') ?></td>
        </tr>
        <tr>
            <th class="label">GSIS ID No.</th><td><?= $v('gsis_id') ?></td>
            <th class="label">Pag-IBIG ID No.</th><td><?= $v('pagibig_id') ?></td>
        </tr>
        <tr>
            <th class="label">PhilHealth No.</th><td><?= $v('philhealth_id') ?></td>
            <th class="label">SSS No.</th><td><?= $v('sss_id') ?></td>
        </tr>
        <tr>
            <th class="label">TIN No.</th><td><?= $v('tin_id') ?></td>
            <th class="label">Agency Employee No.</th><td><?= e((string)($employee['employee_code']??'')) ?></td>
        </tr>
        <tr>
            <th class="label">Residential Address</th><td colspan="3"><?= $v('residential_address') ?></td>
        </tr>
        <tr>
            <th class="label">Permanent Address</th><td colspan="3"><?= $v('permanent_address') ?></td>
        </tr>
        <tr>
            <th class="label">Mobile No.</th><td><?= e((string)($employee['phone']??'')) ?></td>
            <th class="label">Email</th><td><?= e((string)($employee['email']??'')) ?></td>
        </tr>
    </table>

    <!-- II. FAMILY BACKGROUND -->
    <table style="margin-top:12px;">
        <tr><td colspan="4" class="section-header">II. FAMILY BACKGROUND</td></tr>
        <tr>
            <th class="label">Spouse Surname</th><td><?= $v('spouse_surname') ?></td>
            <th class="label">Spouse First Name</th><td><?= $v('spouse_first_name') ?></td>
        </tr>
        <tr>
            <th class="label">Spouse Occupation</th><td><?= $v('spouse_occupation') ?></td>
            <th class="label">Spouse Employer</th><td><?= $v('spouse_employer') ?></td>
        </tr>
        <tr>
            <th class="label">Father's Name</th><td><?= $v('father_full_name') ?></td>
            <th class="label">Mother's Maiden Name</th><td><?= $v('mother_full_name') ?></td>
        </tr>
    </table>

    <?php if ($children !== []): ?>
    <table style="margin-top:8px;">
        <tr><td colspan="3" class="section-header">Children</td></tr>
        <tr><th>Name</th><th>Date of Birth</th><th>Age</th></tr>
        <?php foreach ($children as $c): ?>
        <tr><td><?= e((string)($c['child_name']??'')) ?></td><td><?= e((string)($c['date_of_birth']??'')) ?></td><td><?= e((string)($c['age']??'')) ?></td></tr>
        <?php endforeach; ?>
    </table>
    <?php endif; ?>

    <!-- III. EDUCATIONAL BACKGROUND -->
    <?php if ($education !== []): ?>
    <table style="margin-top:12px;">
        <tr><td colspan="7" class="section-header">III. EDUCATIONAL BACKGROUND</td></tr>
        <tr><th>Level</th><th>School</th><th>Degree</th><th>Period From</th><th>Period To</th><th>Honors</th><th>Year Grad</th></tr>
        <?php foreach ($education as $e_row): ?>
        <tr>
            <td><?= e((string)($e_row['education_level']??'')) ?></td>
            <td><?= e((string)($e_row['school_name']??'')) ?></td>
            <td><?= e((string)($e_row['degree_course']??'')) ?></td>
            <td><?= e((string)($e_row['period_from']??'')) ?></td>
            <td><?= e((string)($e_row['period_to']??'')) ?></td>
            <td><?= e((string)($e_row['highest_honors']??'')) ?></td>
            <td><?= e((string)($e_row['year_graduated']??'')) ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
    <?php endif; ?>

    <!-- IV. CIVIL SERVICE ELIGIBILITY -->
    <?php if ($cs !== []): ?>
    <table style="margin-top:12px;">
        <tr><td colspan="6" class="section-header">IV. CIVIL SERVICE ELIGIBILITY</td></tr>
        <tr><th>Career Service / RA / Board Exam</th><th>Rating</th><th>Exam Date</th><th>Exam Place</th><th>License No.</th><th>Validity Date</th></tr>
        <?php foreach ($cs as $c_row): ?>
        <tr>
            <td><?= e((string)($c_row['career_service']??'')) ?></td>
            <td><?= e((string)($c_row['rating']??'')) ?></td>
            <td><?= e((string)($c_row['exam_date']??'')) ?></td>
            <td><?= e((string)($c_row['exam_place']??'')) ?></td>
            <td><?= e((string)($c_row['license_no']??'')) ?></td>
            <td><?= e((string)($c_row['validity_date']??'')) ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
    <?php endif; ?>

    <!-- V. WORK EXPERIENCE -->
    <?php if ($workExp !== []): ?>
    <table style="margin-top:12px;">
        <tr><td colspan="7" class="section-header">V. WORK EXPERIENCE</td></tr>
        <tr><th>Date From</th><th>Date To</th><th>Position</th><th>Department</th><th>Monthly Salary</th><th>Status</th><th>Gov't</th></tr>
        <?php foreach ($workExp as $w): ?>
        <tr>
            <td><?= e((string)($w['date_from']??'')) ?></td>
            <td><?= e((string)($w['date_to']??'Present')) ?></td>
            <td><?= e((string)($w['position_title']??'')) ?></td>
            <td><?= e((string)($w['department_agency']??'')) ?></td>
            <td><?= $w['monthly_salary']??'' ? '₱'.number_format((float)$w['monthly_salary'],2) : '' ?></td>
            <td><?= e((string)($w['appointment_status']??'')) ?></td>
            <td><?= (int)($w['is_government']??0)===1 ? 'Yes' : 'No' ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
    <?php endif; ?>

    <!-- References -->
    <?php if ($references !== []): ?>
    <table style="margin-top:12px;">
        <tr><td colspan="3" class="section-header">CHARACTER REFERENCES</td></tr>
        <tr><th>Name</th><th>Position</th><th>Contact</th></tr>
        <?php foreach ($references as $ref): ?>
        <tr>
            <td><?= e((string)($ref['full_name']??'')) ?></td>
            <td><?= e((string)($ref['position_title']??'')) ?></td>
            <td><?= e((string)($ref['contact_no']??'')) ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
    <?php endif; ?>

    <div class="signature-block" style="margin-top:24px;">
        <div class="signature-line">
            <br><br>
            <strong><?= e(strtoupper($name)) ?></strong><br>
            <span class="label">Signature over Printed Name / Date</span>
        </div>
        <div class="signature-line">
            <br><br>
            <strong>______________________________</strong><br>
            <span class="label">Administering Officer</span>
        </div>
    </div>

    <p style="margin-top:16px; font-size:9pt; color:#777; text-align:center; border-top:1px solid #ccc; padding-top:6px;">
        CS Form No. 212 (Revised 2017) &bull; Generated by Barangay HRIS &bull; <?= e($today) ?>
    </p>
</div>

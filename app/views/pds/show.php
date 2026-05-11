<?php
$pdsData  = is_array($pds ?? null) ? $pds : [];
$sections = is_array($sections ?? null) ? $sections : [];
$pdsId    = (int) ($pdsData['id'] ?? 0);
$canUpdate = can('pds.update');
$canDelete = can('pds.delete');

$statusClass = ($pdsData['status'] ?? '') === 'Complete' ? 'badge-success' : 'badge-warning';

$childRows = static function (string $section) use ($sections): array {
    return is_array($sections[$section] ?? null) ? $sections[$section] : [];
};

$val = static function (string $key) use ($pdsData): string {
    return (string) ($pdsData[$key] ?? '');
};

$addr = static function (string $prefix) use ($pdsData): string {
    $parts = [];
    foreach (['house','street','subdivision','barangay','city','province','zip'] as $part) {
        $v = trim((string) ($pdsData[$prefix . '_' . $part] ?? ''));
        if ($v !== '') $parts[] = $v;
    }
    return implode(', ', $parts);
};
?>

<section class="page">
    <header class="page-banner">
        <div class="page-banner-copy">
            <p class="page-banner-kicker">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                Personal Data Sheet &mdash; <?= e($val('employee_code')) ?>
            </p>
            <h2 class="page-banner-title">
                <?= e($val('surname') !== '' ? $val('surname') . ', ' . $val('first_name') : ($val('emp_last') . ', ' . $val('emp_first'))) ?>
                <?php if ($val('name_extension') !== ''): ?><small><?= e($val('name_extension')) ?></small><?php endif; ?>
            </h2>
            <div class="page-banner-meta">
                <span class="badge <?= e($statusClass) ?>"><?= e($val('status') ?: 'Draft') ?></span>
                <span class="badge badge-blue">CS Form 212</span>
                <span class="badge">Updated: <?= e(format_date($val('updated_at'))) ?></span>
            </div>
        </div>
        <div class="page-banner-actions">
            <a class="btn btn-secondary" href="/pds">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                Back to List
            </a>
            <?php if ($canUpdate): ?>
                <a class="btn btn-primary" href="/pds/<?= $pdsId ?>/edit">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    Edit PDS
                </a>
            <?php endif; ?>
            <?php if ($canDelete): ?>
                <form method="post" action="/pds/<?= $pdsId ?>/delete" onsubmit="return confirm('Permanently delete this PDS?');" style="display:inline;">
                    <input type="hidden" name="_csrf" value="<?= e(\App\Core\CSRF::token()) ?>">
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            <?php endif; ?>
        </div>
    </header>

    <?php require __DIR__ . '/../partials/alerts.php'; ?>

    <!-- Section 1: Personal Information -->
    <section class="card card-shine" style="margin-bottom:var(--space-4);">
        <div class="card-header">
            <h3>I. Personal Information</h3>
        </div>
        <div class="card-body">
            <div class="detail-grid" style="display:grid; grid-template-columns:repeat(auto-fill,minmax(220px,1fr)); gap:var(--space-3);">
                <?php
                $fields = [
                    'Surname'       => $val('surname'),
                    'First Name'    => $val('first_name'),
                    'Middle Name'   => $val('middle_name'),
                    'Extension'     => $val('name_extension'),
                    'Date of Birth' => format_date($val('birthdate')),
                    'Place of Birth'=> $val('birthplace'),
                    'Sex'           => $val('sex'),
                    'Civil Status'  => $val('civil_status'),
                    'Height (m)'    => $val('height'),
                    'Weight (kg)'   => $val('weight'),
                    'Blood Type'    => $val('blood_type'),
                    'Dual Citizen'  => $val('dual_citizenship') === '1' ? 'Yes' : 'No',
                    'Citizenship By'=> $val('citizenship_by'),
                    'Country'       => $val('citizenship_country'),
                ];
                foreach ($fields as $label => $value):
                ?>
                <div>
                    <div style="font-size:11px; text-transform:uppercase; letter-spacing:.05em; color:var(--text-muted); margin-bottom:2px;"><?= e($label) ?></div>
                    <div style="font-weight:500;"><?= e($value !== '' ? $value : '-') ?></div>
                </div>
                <?php endforeach; ?>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:var(--space-4); margin-top:var(--space-4);">
                <div>
                    <div style="font-size:11px; text-transform:uppercase; letter-spacing:.05em; color:var(--text-muted); margin-bottom:4px;">Residential Address</div>
                    <div><?= e($addr('res') ?: '-') ?></div>
                </div>
                <div>
                    <div style="font-size:11px; text-transform:uppercase; letter-spacing:.05em; color:var(--text-muted); margin-bottom:4px;">Permanent Address</div>
                    <div><?= e($addr('per') ?: '-') ?></div>
                </div>
            </div>

            <div style="display:grid; grid-template-columns:repeat(auto-fill,minmax(200px,1fr)); gap:var(--space-3); margin-top:var(--space-4);">
                <?php
                $contacts = [
                    'Telephone'        => $val('telephone'),
                    'Mobile'           => $val('mobile'),
                    'Personal Email'   => $val('personal_email'),
                    'GSIS ID'          => $val('gsis_id'),
                    'Pag-IBIG ID'      => $val('pagibig_id'),
                    'PhilHealth No.'   => $val('philhealth_id'),
                    'SSS No.'          => $val('sss_no'),
                    'TIN No.'          => $val('tin_no'),
                    'Agency Emp. No.'  => $val('agency_employee_no'),
                ];
                foreach ($contacts as $label => $value):
                ?>
                <div>
                    <div style="font-size:11px; text-transform:uppercase; letter-spacing:.05em; color:var(--text-muted); margin-bottom:2px;"><?= e($label) ?></div>
                    <div style="font-weight:500;"><?= e($value !== '' ? $value : '-') ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Section 2: Family Background -->
    <section class="card card-shine" style="margin-bottom:var(--space-4);">
        <div class="card-header"><h3>II. Family Background</h3></div>
        <div class="card-body">
            <p style="font-size:12px; font-weight:600; text-transform:uppercase; letter-spacing:.06em; color:var(--text-muted); margin-bottom:8px;">Spouse</p>
            <div style="display:grid; grid-template-columns:repeat(auto-fill,minmax(200px,1fr)); gap:var(--space-3);">
                <?php
                $spouseFields = [
                    'Surname'          => $val('spouse_surname'),
                    'First Name'       => $val('spouse_firstname'),
                    'Middle Name'      => $val('spouse_middlename'),
                    'Extension'        => $val('spouse_extension'),
                    'Occupation'       => $val('spouse_occupation'),
                    'Employer'         => $val('spouse_employer'),
                    'Business Address' => $val('spouse_business_address'),
                    'Telephone'        => $val('spouse_telephone'),
                ];
                foreach ($spouseFields as $label => $value):
                ?>
                <div>
                    <div style="font-size:11px; text-transform:uppercase; letter-spacing:.05em; color:var(--text-muted); margin-bottom:2px;"><?= e($label) ?></div>
                    <div style="font-weight:500;"><?= e($value !== '' ? $value : '-') ?></div>
                </div>
                <?php endforeach; ?>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:var(--space-4); margin-top:var(--space-4);">
                <div>
                    <p style="font-size:12px; font-weight:600; text-transform:uppercase; letter-spacing:.06em; color:var(--text-muted); margin-bottom:8px;">Father</p>
                    <div><?= e(trim($val('father_surname') . ' ' . $val('father_firstname') . ' ' . $val('father_middlename') . ' ' . $val('father_extension')) ?: '-') ?></div>
                </div>
                <div>
                    <p style="font-size:12px; font-weight:600; text-transform:uppercase; letter-spacing:.06em; color:var(--text-muted); margin-bottom:8px;">Mother (Maiden Name)</p>
                    <div><?= e(trim($val('mother_surname') . ' ' . $val('mother_firstname') . ' ' . $val('mother_middlename')) ?: '-') ?></div>
                </div>
            </div>

            <?php $crows = $childRows('children'); ?>
            <?php if ($crows !== []): ?>
                <p style="font-size:12px; font-weight:600; text-transform:uppercase; letter-spacing:.06em; color:var(--text-muted); margin:var(--space-4) 0 8px;">Children</p>
                <table class="table" style="width:auto;">
                    <thead><tr><th>Name</th><th>Date of Birth</th></tr></thead>
                    <tbody>
                        <?php foreach ($crows as $crow): ?>
                            <tr>
                                <td><?= e((string) ($crow['child_name'] ?? '-')) ?></td>
                                <td><?= e(format_date((string) ($crow['child_dob'] ?? ''))) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </section>

    <!-- Section 3: Education -->
    <?php $eduRows = $childRows('education'); ?>
    <section class="card card-shine" style="margin-bottom:var(--space-4);">
        <div class="card-header"><h3>III. Educational Background</h3></div>
        <div class="card-body">
            <?php if ($eduRows === []): ?>
                <p class="text-muted">No educational background recorded.</p>
            <?php else: ?>
                <div class="table-wrap no-border no-radius shadow-none">
                    <table class="table">
                        <thead><tr><th>Level</th><th>School Name</th><th>Degree/Course</th><th>Period</th><th>Units</th><th>Year Grad.</th><th>Honors</th></tr></thead>
                        <tbody>
                            <?php foreach ($eduRows as $er): ?>
                                <tr>
                                    <td><?= e((string) ($er['level'] ?? '-')) ?></td>
                                    <td><?= e((string) ($er['school_name'] ?? '-')) ?></td>
                                    <td><?= e((string) ($er['degree_course'] ?? '-')) ?></td>
                                    <td><?= e((string) ($er['period_from'] ?? '')) ?>&ndash;<?= e((string) ($er['period_to'] ?? '')) ?></td>
                                    <td><?= e((string) ($er['units_earned'] ?? '-')) ?></td>
                                    <td><?= e((string) ($er['year_graduated'] ?? '-')) ?></td>
                                    <td><?= e((string) ($er['scholarship_honors'] ?? '-')) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Section 4: Civil Service -->
    <?php $csRows = $childRows('civil_service'); ?>
    <section class="card card-shine" style="margin-bottom:var(--space-4);">
        <div class="card-header"><h3>IV. Civil Service Eligibility</h3></div>
        <div class="card-body">
            <?php if ($csRows === []): ?>
                <p class="text-muted">No civil service eligibility recorded.</p>
            <?php else: ?>
                <div class="table-wrap no-border no-radius shadow-none">
                    <table class="table">
                        <thead><tr><th>Career Service / RA 1080</th><th>Rating</th><th>Exam Date</th><th>Exam Place</th><th>License No.</th><th>License Validity</th></tr></thead>
                        <tbody>
                            <?php foreach ($csRows as $cr): ?>
                                <tr>
                                    <td><?= e((string) ($cr['career_service'] ?? '-')) ?></td>
                                    <td><?= e((string) ($cr['rating'] ?? '-')) ?></td>
                                    <td><?= e(format_date((string) ($cr['exam_date'] ?? ''))) ?></td>
                                    <td><?= e((string) ($cr['exam_place'] ?? '-')) ?></td>
                                    <td><?= e((string) ($cr['license_number'] ?? '-')) ?></td>
                                    <td><?= e(format_date((string) ($cr['license_validity'] ?? ''))) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Section 5: Work Experience -->
    <?php $weRows = $childRows('work_experience'); ?>
    <section class="card card-shine" style="margin-bottom:var(--space-4);">
        <div class="card-header"><h3>V. Work Experience</h3></div>
        <div class="card-body">
            <?php if ($weRows === []): ?>
                <p class="text-muted">No work experience recorded.</p>
            <?php else: ?>
                <div class="table-wrap no-border no-radius shadow-none">
                    <table class="table">
                        <thead><tr><th>Date From</th><th>Date To</th><th>Position Title</th><th>Agency/Company</th><th>Monthly Salary</th><th>SG</th><th>Appointment</th><th>Govt?</th></tr></thead>
                        <tbody>
                            <?php foreach ($weRows as $wr): ?>
                                <tr>
                                    <td><?= e(format_date((string) ($wr['date_from'] ?? ''))) ?></td>
                                    <td><?= ($wr['date_to'] ?? '') !== '' ? e(format_date((string) $wr['date_to'])) : 'Present' ?></td>
                                    <td><?= e((string) ($wr['position_title'] ?? '-')) ?></td>
                                    <td><?= e((string) ($wr['department_agency'] ?? '-')) ?></td>
                                    <td><?= ($wr['monthly_salary'] ?? '') !== '' ? e(format_currency((string) $wr['monthly_salary'])) : '-' ?></td>
                                    <td><?= e((string) ($wr['salary_grade'] ?? '-')) ?></td>
                                    <td><?= e((string) ($wr['appointment_status'] ?? '-')) ?></td>
                                    <td><?= ($wr['is_govt_service'] ?? 0) ? '<span class="badge badge-success">Yes</span>' : '<span class="badge badge-neutral">No</span>' ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Section 6: Voluntary Work -->
    <?php $vwRows = $childRows('voluntary_work'); ?>
    <section class="card card-shine" style="margin-bottom:var(--space-4);">
        <div class="card-header"><h3>VI. Voluntary Work</h3></div>
        <div class="card-body">
            <?php if ($vwRows === []): ?>
                <p class="text-muted">No voluntary work recorded.</p>
            <?php else: ?>
                <div class="table-wrap no-border no-radius shadow-none">
                    <table class="table">
                        <thead><tr><th>Organization</th><th>From</th><th>To</th><th>Hours</th><th>Nature of Work</th></tr></thead>
                        <tbody>
                            <?php foreach ($vwRows as $vr): ?>
                                <tr>
                                    <td><?= e((string) ($vr['organization'] ?? '-')) ?></td>
                                    <td><?= e(format_date((string) ($vr['date_from'] ?? ''))) ?></td>
                                    <td><?= e(format_date((string) ($vr['date_to'] ?? ''))) ?></td>
                                    <td><?= e((string) ($vr['hours_no'] ?? '-')) ?></td>
                                    <td><?= e((string) ($vr['nature_of_work'] ?? '-')) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Section 7: L&D -->
    <?php $ldRows = $childRows('learning_development'); ?>
    <section class="card card-shine" style="margin-bottom:var(--space-4);">
        <div class="card-header"><h3>VII. Learning and Development</h3></div>
        <div class="card-body">
            <?php if ($ldRows === []): ?>
                <p class="text-muted">No L&amp;D trainings recorded.</p>
            <?php else: ?>
                <div class="table-wrap no-border no-radius shadow-none">
                    <table class="table">
                        <thead><tr><th>Title</th><th>From</th><th>To</th><th>Hours</th><th>Type</th><th>Conducted By</th></tr></thead>
                        <tbody>
                            <?php foreach ($ldRows as $lr): ?>
                                <tr>
                                    <td><?= e((string) ($lr['title'] ?? '-')) ?></td>
                                    <td><?= e(format_date((string) ($lr['date_from'] ?? ''))) ?></td>
                                    <td><?= e(format_date((string) ($lr['date_to'] ?? ''))) ?></td>
                                    <td><?= e((string) ($lr['hours_no'] ?? '-')) ?></td>
                                    <td><?= e((string) ($lr['ld_type'] ?? '-')) ?></td>
                                    <td><?= e((string) ($lr['conducted_by'] ?? '-')) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Section 8: Other Info -->
    <?php $oiRows = $childRows('other_info'); ?>
    <section class="card card-shine" style="margin-bottom:var(--space-4);">
        <div class="card-header"><h3>VIII. Other Information</h3></div>
        <div class="card-body" style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:var(--space-4);">
            <?php
            $oiByType = ['skill' => [], 'recognition' => [], 'membership' => []];
            foreach ($oiRows as $oi) {
                $t = $oi['info_type'] ?? 'skill';
                if (isset($oiByType[$t])) $oiByType[$t][] = $oi['value'];
            }
            $oiLabels = ['skill' => 'Special Skills', 'recognition' => 'Non-Academic Distinctions / Recognition', 'membership' => 'Membership in Association'];
            foreach ($oiByType as $type => $values):
            ?>
            <div>
                <p style="font-size:12px; font-weight:600; text-transform:uppercase; letter-spacing:.06em; color:var(--text-muted); margin-bottom:8px;"><?= e($oiLabels[$type]) ?></p>
                <?php if ($values === []): ?>
                    <p style="color:var(--text-muted); font-size:13px;">None recorded.</p>
                <?php else: ?>
                    <ul style="margin:0; padding-left:var(--space-4);">
                        <?php foreach ($values as $v): ?>
                            <li><?= e((string) $v) ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Section 9: References -->
    <?php $refRows = $childRows('references'); ?>
    <section class="card card-shine" style="margin-bottom:var(--space-4);">
        <div class="card-header"><h3>IX. Character References</h3></div>
        <div class="card-body">
            <?php if ($refRows === []): ?>
                <p class="text-muted">No references recorded.</p>
            <?php else: ?>
                <div class="table-wrap no-border no-radius shadow-none">
                    <table class="table">
                        <thead><tr><th>Name</th><th>Address</th><th>Telephone</th></tr></thead>
                        <tbody>
                            <?php foreach ($refRows as $rr): ?>
                                <tr>
                                    <td><?= e((string) ($rr['ref_name'] ?? '-')) ?></td>
                                    <td><?= e((string) ($rr['ref_address'] ?? '-')) ?></td>
                                    <td><?= e((string) ($rr['ref_telephone'] ?? '-')) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Section 10: Security Questions -->
    <section class="card card-shine" style="margin-bottom:var(--space-4);">
        <div class="card-header"><h3>X. Security / Clearance Questions</h3></div>
        <div class="card-body">
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
                $answer  = $val('q' . $n . '_answer');
                $details = $val('q' . $n . '_details');
                $ansClass = $answer === 'Yes' ? 'badge-danger' : ($answer === 'No' ? 'badge-success' : 'badge-neutral');
            ?>
            <div style="display:flex; gap:var(--space-3); align-items:flex-start; padding:var(--space-3) 0; border-bottom:1px solid var(--border);">
                <span style="font-size:12px; color:var(--text-muted); min-width:20px;"><?= $n ?>.</span>
                <div style="flex:1;">
                    <p style="font-size:13px; margin-bottom:4px;"><?= htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
                    <?php if ($details !== ''): ?>
                        <p style="font-size:12px; color:var(--text-muted);">Details: <?= e($details) ?></p>
                    <?php endif; ?>
                </div>
                <span class="badge <?= e($ansClass) ?>"><?= e($answer ?: 'N/A') ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
</section>

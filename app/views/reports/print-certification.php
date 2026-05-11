<?php
$employee = is_array($employee ?? null) ? $employee : [];
$current  = is_array($current ?? null) ? $current : null;
$name     = trim(($employee['first_name'] ?? '') . ' ' . ($employee['middle_name'] ?? '') . ' ' . ($employee['last_name'] ?? ''));
$today    = date('F j, Y');
$year     = date('Y');
?>
<div class="print-page" style="text-align:center; padding-top:20mm;">

    <div style="margin-bottom:20px;">
        <!-- Seal placeholder -->
        <div style="width:80px; height:80px; border:2px solid #000; border-radius:50%; margin:0 auto 8px; display:flex; align-items:center; justify-content:center; font-size:9pt; color:#555;">SEAL</div>
        <p style="font-size:10pt; color:#555;">Republic of the Philippines</p>
        <h1 style="font-size:16pt; margin:8px 0;">EMPLOYEE CERTIFICATION</h1>
        <p style="font-size:10pt; color:#555;">Barangay Human Resources Information System</p>
    </div>

    <hr style="border:1px solid #000; margin:20px 0;">

    <p style="font-size:12pt; line-height:2; text-align:justify; padding:0 20mm;">
        This is to certify that <strong style="text-transform:uppercase;"><?= e($name) ?></strong>,
        bearing Employee Code <strong><?= e((string)($employee['employee_code']??'-')) ?></strong>,
        is/was connected with this office
        <?php if ($current): ?>
            holding the position of <strong><?= e((string)($current['position_title']??'')) ?></strong>
            under <strong><?= e((string)($current['appointment_status']??'')) ?></strong> appointment status
            in the <strong><?= e((string)($current['office_unit']??'')) ?></strong>
            effective <strong><?= e((string)($current['date_from']??'')) ?></strong> to present.
        <?php else: ?>
            as an employee of this organization.
        <?php endif; ?>
    </p>

    <p style="font-size:12pt; line-height:2; text-align:justify; padding:0 20mm; margin-top:16px;">
        This certification is issued upon the request of the above-named employee for
        <strong>whatever legal purpose it may serve</strong>.
    </p>

    <p style="font-size:12pt; margin-top:20px;">
        Issued this <strong><?= e($today) ?></strong> at the Office of Human Resources.
    </p>

    <div class="signature-block" style="padding:0 20mm; margin-top:48px; display:flex; justify-content:flex-end;">
        <div style="text-align:center; width:220px;">
            <br><br>
            <strong style="border-top:2px solid #000; padding-top:6px; display:block; font-size:13pt;">________________________</strong>
            <p style="font-size:10pt;">HR Officer / Authorized Signatory</p>
            <p style="font-size:9pt; color:#555;">Barangay HRIS Administrator</p>
        </div>
    </div>

    <p style="margin-top:40px; font-size:9pt; color:#777; border-top:1px solid #ccc; padding-top:8px;">
        This certification is computer-generated and valid without signature unless otherwise required. &bull; <?= e($today) ?>
    </p>
</div>

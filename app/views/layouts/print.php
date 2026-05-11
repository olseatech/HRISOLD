<?php declare(strict_types=1); ?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e(($title ?? 'Print')) ?> | Barangay HRIS</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Times New Roman', Times, serif; font-size: 11pt; color: #000; background: #fff; padding: 0; }
        .print-page { width: 215.9mm; min-height: 279.4mm; padding: 12mm 15mm; margin: 0 auto; }
        h1 { font-size: 14pt; text-align: center; text-transform: uppercase; letter-spacing: .05em; margin-bottom: 4px; }
        h2 { font-size: 12pt; text-align: center; margin-bottom: 4px; }
        h3 { font-size: 11pt; font-weight: bold; margin: 12px 0 6px; border-bottom: 1px solid #000; padding-bottom: 3px; }
        p  { line-height: 1.5; margin-bottom: 6px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 12px; font-size: 10pt; }
        th { background: #f0f0f0; border: 1px solid #999; padding: 4px 6px; text-align: left; font-size: 9pt; text-transform: uppercase; }
        td { border: 1px solid #999; padding: 4px 6px; vertical-align: top; }
        .section-header { background: #ddd; font-weight: bold; text-transform: uppercase; font-size: 9pt; padding: 4px 6px; border: 1px solid #999; }
        .label { font-size: 9pt; color: #555; }
        .value { font-weight: bold; }
        .page-header { text-align: center; margin-bottom: 16px; border-bottom: 2px solid #000; padding-bottom: 8px; }
        .page-header .agency { font-size: 10pt; color: #555; }
        .signature-block { display: flex; gap: 40px; margin-top: 24px; }
        .signature-line { flex: 1; border-top: 1px solid #000; padding-top: 4px; text-align: center; font-size: 10pt; }
        .two-col { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        .field-pair { margin-bottom: 6px; }
        .field-label { font-size: 9pt; color: #555; display: block; }
        .field-value { font-weight: 600; border-bottom: 1px solid #ccc; padding-bottom: 2px; min-height: 18px; display: block; }
        .print-btn { position: fixed; bottom: 20px; right: 20px; padding: 10px 20px; background: #0b3d91; color: #fff; border: none; border-radius: 6px; font-size: 13px; cursor: pointer; font-family: sans-serif; }
        @media print {
            body { padding: 0; }
            .print-btn { display: none; }
            .print-page { width: 100%; padding: 8mm 12mm; }
            @page { size: Letter portrait; margin: 0; }
        }
    </style>
</head>
<body>
    <?php require $contentView; ?>
    <button class="print-btn" onclick="window.print()">🖨 Print</button>
</body>
</html>

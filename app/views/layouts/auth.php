<?php
declare(strict_types=1);
$app = config('app');
$assetVersion = (string) max(
    (int) (filemtime(__DIR__ . '/../../../public/assets/css/variables.css') ?: 1),
    (int) (filemtime(__DIR__ . '/../../../public/assets/css/base.css') ?: 1),
    (int) (filemtime(__DIR__ . '/../../../public/assets/css/components.css') ?: 1)
);
?>
<!doctype html>
<html lang="en" style="color-scheme: light;">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e(($title ?? 'Authentication') . ' | ' . $app['name']) ?></title>
    <meta name="theme-color" content="#0b3d91">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/variables.css?v=<?= e($assetVersion) ?>">
    <link rel="stylesheet" href="/assets/css/base.css?v=<?= e($assetVersion) ?>">
    <link rel="stylesheet" href="/assets/css/components.css?v=<?= e($assetVersion) ?>">
</head>
<body class="auth-page">
    <main class="auth-shell">
        <?php require $contentView; ?>
    </main>
    <footer class="auth-footer">
        <strong>Barangay HRIS</strong> &middot; Official Government Portal &middot; &copy; <?= e(date('Y')) ?>
    </footer>
</body>
</html>

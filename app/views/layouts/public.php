<?php

declare(strict_types=1);

$app = config('app');
$assetVersion = (string) max(
    (int) (filemtime(__DIR__ . '/../../../public/assets/css/variables.css') ?: 1),
    (int) (filemtime(__DIR__ . '/../../../public/assets/css/base.css') ?: 1),
    (int) (filemtime(__DIR__ . '/../../../public/assets/css/components.css') ?: 1),
    (int) (filemtime(__DIR__ . '/../../../public/assets/css/responsive.css') ?: 1),
    (int) (filemtime(__DIR__ . '/../../../public/assets/css/marketing.css') ?: 1),
    (int) (filemtime(__DIR__ . '/../../../public/assets/js/marketing.js') ?: 1)
);
?>
<!doctype html>
<html lang="en" style="color-scheme: light;">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e(($title ?? 'HRIS') . ' | ' . $app['name']) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
    <script>
        (function () {
            var root = document.documentElement;
            root.classList.remove('dark');
            root.style.colorScheme = 'light';
        })();

        tailwind = {
            config: {
                darkMode: 'class',
                corePlugins: {
                    preflight: false
                },
                theme: {
                    extend: {
                        fontFamily: {
                            sans: ['Inter', 'Manrope', 'Segoe UI', 'system-ui', 'sans-serif'],
                            display: ['Plus Jakarta Sans', 'Inter', 'sans-serif']
                        }
                    }
                }
            }
        };
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="<?= e(asset_url('/assets/css/variables.css') . '?v=' . rawurlencode($assetVersion)) ?>">
    <link rel="stylesheet" href="<?= e(asset_url('/assets/css/base.css') . '?v=' . rawurlencode($assetVersion)) ?>">
    <link rel="stylesheet" href="<?= e(asset_url('/assets/css/components.css') . '?v=' . rawurlencode($assetVersion)) ?>">
    <link rel="stylesheet" href="<?= e(asset_url('/assets/css/responsive.css') . '?v=' . rawurlencode($assetVersion)) ?>">
    <link rel="stylesheet" href="<?= e(asset_url('/assets/css/marketing.css') . '?v=' . rawurlencode($assetVersion)) ?>">
</head>
<body class="marketing-page font-sans text-slate-900">
    <?php require $contentView; ?>

    <script src="<?= e(asset_url('/assets/js/marketing.js') . '?v=' . rawurlencode($assetVersion)) ?>"></script>
</body>
</html>

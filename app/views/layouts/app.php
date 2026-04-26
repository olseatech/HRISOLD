<?php
declare(strict_types=1);

$app = config('app');
$lockModalEnabled = subscription_lock_modal_enabled();
$assetVersion = (string) max(
    (int) (filemtime(__DIR__ . '/../../../public/assets/css/variables.css') ?: 1),
    (int) (filemtime(__DIR__ . '/../../../public/assets/css/base.css') ?: 1),
    (int) (filemtime(__DIR__ . '/../../../public/assets/css/layout.css') ?: 1),
    (int) (filemtime(__DIR__ . '/../../../public/assets/css/components.css') ?: 1),
    (int) (filemtime(__DIR__ . '/../../../public/assets/css/dashboard.css') ?: 1),
    (int) (filemtime(__DIR__ . '/../../../public/assets/css/attendance.css') ?: 1),
    (int) (filemtime(__DIR__ . '/../../../public/assets/css/leave.css') ?: 1),
    (int) (filemtime(__DIR__ . '/../../../public/assets/css/payroll.css') ?: 1),
    (int) (filemtime(__DIR__ . '/../../../public/assets/css/settings.css') ?: 1),
    (int) (filemtime(__DIR__ . '/../../../public/assets/css/responsive.css') ?: 1),
    (int) (filemtime(__DIR__ . '/../../../public/assets/css/employees.css') ?: 1),
    (int) (filemtime(__DIR__ . '/../../../public/assets/css/billing.css') ?: 1),
    (int) (filemtime(__DIR__ . '/../../../public/assets/css/lock-modal.css') ?: 1),
    (int) (filemtime(__DIR__ . '/../../../public/assets/js/app.js') ?: 1),
    (int) (filemtime(__DIR__ . '/../../../public/assets/js/dashboard.js') ?: 1),
    (int) (filemtime(__DIR__ . '/../../../public/assets/js/lock-modal.js') ?: 1)
);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e(($title ?? 'Dashboard') . ' | ' . $app['name']) ?></title>
    <meta name="theme-color" content="#0b3d91">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">

    <!-- Design System & Layout -->
    <link rel="stylesheet" href="/assets/css/variables.css?v=<?= e($assetVersion) ?>">
    <link rel="stylesheet" href="/assets/css/base.css?v=<?= e($assetVersion) ?>">
    <link rel="stylesheet" href="/assets/css/layout.css?v=<?= e($assetVersion) ?>">
    <link rel="stylesheet" href="/assets/css/components.css?v=<?= e($assetVersion) ?>">

    <!-- Module Specific -->
    <link rel="stylesheet" href="/assets/css/dashboard.css?v=<?= e($assetVersion) ?>">
    <link rel="stylesheet" href="/assets/css/attendance.css?v=<?= e($assetVersion) ?>">
    <link rel="stylesheet" href="/assets/css/leave.css?v=<?= e($assetVersion) ?>">
    <link rel="stylesheet" href="/assets/css/payroll.css?v=<?= e($assetVersion) ?>">
    <link rel="stylesheet" href="/assets/css/settings.css?v=<?= e($assetVersion) ?>">
    <link rel="stylesheet" href="/assets/css/employees.css?v=<?= e($assetVersion) ?>">
    <link rel="stylesheet" href="/assets/css/billing.css?v=<?= e($assetVersion) ?>">
    <link rel="stylesheet" href="/assets/css/lock-modal.css?v=<?= e($assetVersion) ?>">
    <link rel="stylesheet" href="/assets/css/responsive.css?v=<?= e($assetVersion) ?>">
</head>
<body class="app-body">
    <div class="app-shell">
        <?php require __DIR__ . '/../partials/sidebar.php'; ?>

        <main class="app-main">
            <?php require __DIR__ . '/../partials/topbar.php'; ?>

            <section class="app-content">
                <?php require $contentView; ?>
            </section>

            <?php if ($lockModalEnabled): ?>
                <div class="lock-modal-overlay" id="featureLockModal" hidden
                     role="dialog" aria-modal="true"
                     aria-labelledby="lockModalTitle"
                     aria-describedby="lockModalMessage">
                    <div class="lock-modal" data-lock-dialog>
                        <button class="lock-modal-close" type="button"
                                data-lock-close aria-label="Close">&times;</button>

                        <p class="lock-modal-kicker">Feature Lock</p>
                        <h2 id="lockModalTitle" class="lock-modal-title">
                            Upgrade required for this module
                        </h2>

                        <p class="lock-modal-feature" data-lock-feature>
                            Module access is locked.
                        </p>
                        <p id="lockModalMessage" class="lock-modal-message" data-lock-message>
                            Upgrade your plan in Billing to unlock this module.
                        </p>

                        <div class="lock-modal-actions">
                            <a class="lock-modal-btn lock-modal-btn-primary"
                               href="/billing">Open Billing and Upgrade</a>
                            <button class="lock-modal-btn lock-modal-btn-muted"
                                    type="button" data-lock-close>Close</button>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php require __DIR__ . '/../partials/footer.php'; ?>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    <script src="/assets/js/app.js?v=<?= e($assetVersion) ?>"></script>
    <script src="/assets/js/dashboard.js?v=<?= e($assetVersion) ?>"></script>
    <script src="/assets/js/lock-modal.js?v=<?= e($assetVersion) ?>"></script>
</body>
</html>

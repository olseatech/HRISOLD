<?php
$errors = is_array($errors ?? null) ? $errors : [];
$err    = static function (string $key) use ($errors): string {
    return isset($errors[$key])
        ? '<span class="field-error">' . htmlspecialchars((string) $errors[$key], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</span>'
        : '';
};
?>
<section class="page">
    <header class="page-banner">
        <div class="page-banner-copy">
            <p class="page-banner-kicker">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                Security
            </p>
            <h2 class="page-banner-title">Change Password</h2>
            <p class="page-banner-sub">Update your account password. Use a strong password at least 8 characters long.</p>
        </div>
        <div class="page-banner-actions">
            <a class="btn btn-secondary" href="/dashboard">← Back to Dashboard</a>
        </div>
    </header>

    <?php require __DIR__ . '/../partials/alerts.php'; ?>

    <div style="max-width:480px;">
        <section class="card">
            <div class="card-header">
                <div class="card-header-copy">
                    <h3>Update Password</h3>
                    <p>Enter your current password to confirm your identity, then set a new one.</p>
                </div>
            </div>
            <div class="card-body" style="padding:var(--space-5);">
                <form method="post" action="/change-password" novalidate>
                    <input type="hidden" name="_csrf" value="<?= e((string) ($csrf ?? '')) ?>">

                    <div style="margin-bottom:var(--space-4);">
                        <label class="field">
                            <span>Current Password <em class="text-red">*</em></span>
                            <input type="password" name="current_password" autocomplete="current-password" required>
                            <?= $err('current_password') ?>
                        </label>
                    </div>

                    <div style="margin-bottom:var(--space-4);">
                        <label class="field">
                            <span>New Password <em class="text-red">*</em></span>
                            <input type="password" name="new_password" autocomplete="new-password" required minlength="8">
                            <?= $err('new_password') ?>
                            <small style="color:var(--text-muted);">Minimum 8 characters.</small>
                        </label>
                    </div>

                    <div style="margin-bottom:var(--space-5);">
                        <label class="field">
                            <span>Confirm New Password <em class="text-red">*</em></span>
                            <input type="password" name="confirm_password" autocomplete="new-password" required>
                            <?= $err('confirm_password') ?>
                        </label>
                    </div>

                    <button class="btn btn-primary" type="submit">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                        Change Password
                    </button>
                </form>
            </div>
        </section>
    </div>
</section>

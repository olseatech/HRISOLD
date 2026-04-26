<?php
$publicRoot = dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'public';
$sealCandidates = [
    '/assets/images/official-seal.svg' => $publicRoot . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'official-seal.svg',
    '/assets/images/official-seal.png' => $publicRoot . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'official-seal.png',
    '/assets/images/government-seal.svg' => $publicRoot . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'government-seal.svg',
    '/assets/images/government-seal.png' => $publicRoot . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'government-seal.png',
];
$officialSealUrl = null;
foreach ($sealCandidates as $url => $candidatePath) {
    if (is_file($candidatePath)) {
        $officialSealUrl = $url;
        break;
    }
}
?>

<div class="login-card">

    <!-- ── Logo / Identity mark ─────────────────────────── -->
    <div class="login-card-logo" aria-hidden="true">
        <div class="login-logo-circle">
            <?php if ($officialSealUrl !== null): ?>
                <img src="<?= e($officialSealUrl) ?>" alt="">
            <?php else: ?>
                <!-- Shield placeholder — replaced by official-seal.svg when available -->
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                    <path d="M9 12l2 2 4-4"/>
                </svg>
            <?php endif; ?>
        </div>
        <span class="login-logo-name">Barangay HRIS</span>
    </div>

    <!-- ── Card body ─────────────────────────────────────── -->
    <div class="login-card-body">

        <!-- Back to home -->
        <a href="/" class="login-back-link">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 19l-7-7 7-7"/></svg>
            Back to home
        </a>

        <!-- Heading -->
        <div class="login-card-head">
            <p class="login-kicker">Secure Gateway</p>
            <h1 class="login-title">Sign in to your account</h1>
            <p class="login-desc">Authorized personnel only. All sessions are monitored and logged.</p>
        </div>

        <!-- Flash alerts -->
        <div class="panel-alerts"><?php require __DIR__ . '/../partials/alerts.php'; ?></div>

        <!-- Login form -->
        <form method="post" action="/login" class="login-form" novalidate>
            <input type="hidden" name="_csrf" value="<?= e((string) ($csrf ?? '')) ?>">

            <div class="login-field">
                <label class="field-label" for="identity">Username or Email</label>
                <input
                    id="identity"
                    name="identity"
                    type="text"
                    required
                    autocomplete="username"
                    placeholder="e.g. j.delacruz"
                    spellcheck="false">
            </div>

            <div class="login-field">
                <label class="field-label" for="password">Password</label>
                <div class="password-wrap">
                    <input
                        id="password"
                        name="password"
                        type="password"
                        required
                        autocomplete="current-password"
                        placeholder="••••••••">
                    <button
                        type="button"
                        class="password-toggle"
                        data-password-toggle
                        aria-controls="password"
                        aria-label="Show password">Show</button>
                </div>
            </div>

            <button type="submit" class="login-submit">Sign in to Portal</button>
        </form>

        <!-- Card footer -->
        <div class="login-card-foot">
            <p class="seed-note">
                <strong>Testing Credentials</strong><br>
                Username: <code>superadmin</code> &nbsp;/&nbsp; Password: <code>Admin@123</code>
            </p>
            <div class="panel-meta">
                <span>SSL Active</span>
                <span>CSRF Protected</span>
                <span>Audit On</span>
            </div>
            <div class="login-forgot-wrap">
                <a href="/forgot-password" class="login-forgot-link">Forgot your password?</a>
            </div>
        </div>

        <p class="login-legal">
            Property of Barangay Administrative Services &middot; &copy; <?= date('Y') ?>
        </p>

    </div><!-- /.login-card-body -->

</div><!-- /.login-card -->

<script>
(function () {
    var toggle = document.querySelector('[data-password-toggle]');
    var input  = document.getElementById('password');
    if (!toggle || !input) return;
    toggle.addEventListener('click', function () {
        var isPassword = input.type === 'password';
        input.type = isPassword ? 'text' : 'password';
        toggle.textContent = isPassword ? 'Hide' : 'Show';
        toggle.setAttribute('aria-label', isPassword ? 'Hide password' : 'Show password');
    });
})();
</script>

<section class="auth-page">
    <div class="auth-shell">
        <div class="auth-card" style="max-width: 480px; margin: 0 auto;">
            <header style="display: grid; gap: 8px; margin-bottom: 24px;">
                <p style="display: flex; align-items: center; gap: 8px; color: var(--blue-700); font-size: var(--text-xs); font-weight: 800; text-transform: uppercase; letter-spacing: 0.1em; margin: 0;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    Account Security
                </p>
                <h1 style="font-family: var(--font-display); font-size: var(--text-2xl); font-weight: 800; color: var(--slate-900); line-height: 1.1; letter-spacing: -0.02em; margin: 0;">
                    Recover your account
                </h1>
                <p style="color: var(--slate-500); font-size: var(--text-sm); line-height: 1.6; margin: 0;">
                    Automated password reset is restricted for security compliance. Please follow the instructions below.
                </p>
            </header>

            <div class="alert alert-info" style="border-radius: var(--radius-lg); padding: var(--space-4); border-left-width: 4px; display: flex; gap: var(--space-3); margin-bottom: 24px;">
                <div style="flex-shrink: 0; color: var(--blue-600); margin-top: 2px;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                </div>
                <div>
                    <strong style="display: block; color: var(--blue-900); font-size: var(--text-sm); margin-bottom: 4px;">Contact Administrator</strong>
                    <p style="margin: 0; color: var(--blue-800); font-size: var(--text-xs); line-height: 1.5; opacity: 0.85;">
                        To maintain system integrity, password resets must be initiated by your department's IT administrator or HR supervisor.
                    </p>
                </div>
            </div>

            <div style="border-top: 1px solid var(--slate-100); padding-top: 24px; display: flex; align-items: center; justify-content: space-between;">
                <a href="/login" class="btn btn-ghost" style="padding-left: 0;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                    Back to sign in
                </a>
                
                <span style="color: var(--slate-400); font-size: var(--text-xs); font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em;">
                    Ref: #RECOVERY-PHASE-1
                </span>
            </div>
        </div>

        <footer style="text-align: center; margin-top: 32px; color: var(--slate-400); font-size: 0.75rem; letter-spacing: 0.01em;">
            &copy; <?= date('Y') ?> HRIS Management Systems. All rights reserved.
        </footer>
    </div>
</section>

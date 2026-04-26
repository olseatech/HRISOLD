<?php
$planRows      = is_array($plans ?? null) ? $plans : [];
$hasPlans      = $planRows !== [];
$isTestingMode = is_subscription_testing_mode();
$planCount     = max(1, count($planRows));

/* ── Data ──────────────────────────────────────────────────── */

$featureDefinitions = [
    [
        'name'     => 'Attendance tracking',
        'desc'     => 'Real-time clock-in and clock-out logging per employee with shift scheduling, overtime detection, and daily reconciliation against approved schedules.',
        'category' => 'Attendance',
        'badge'    => 'mk-badge-attendance',
        'icon'     => 'clock',
        'color'    => 'is-blue',
    ],
    [
        'name'     => 'Leave management',
        'desc'     => 'Multi-type leave request workflow covering filing, manager approval, HR override, balance computation, and calendar blocking with automatic payroll deduction triggers.',
        'category' => 'Leave',
        'badge'    => 'mk-badge-leave',
        'icon'     => 'calendar',
        'color'    => 'is-teal',
    ],
    [
        'name'     => 'Payroll computation',
        'desc'     => 'End-to-end payroll run engine that calculates gross pay, statutory deductions (SSS, PhilHealth, Pag-IBIG, BIR), net pay, and generates per-employee payslips per cutoff.',
        'category' => 'Payroll',
        'badge'    => 'mk-badge-payroll',
        'icon'     => 'banknotes',
        'color'    => 'is-amber',
    ],
    [
        'name'     => 'Employee records',
        'desc'     => 'Centralized employee profile database storing personal info, employment history, department assignments, job levels, salary grades, and document attachments.',
        'category' => 'Core HR',
        'badge'    => 'mk-badge-core',
        'icon'     => 'users',
        'color'    => 'is-blue',
    ],
    [
        'name'     => 'Onboarding workflow',
        'desc'     => 'Structured new-hire checklist that sequences document submission, credential provisioning, orientation scheduling, and probation tracking under HR and direct manager oversight.',
        'category' => 'Core HR',
        'badge'    => 'mk-badge-core',
        'icon'     => 'rocket',
        'color'    => 'is-green',
    ],
    [
        'name'     => 'Role-based access',
        'desc'     => 'Permission layer that controls which modules, records, and actions each user can see or perform — enforced across every view based on assigned role and subscription plan coverage.',
        'category' => 'Admin',
        'badge'    => 'mk-badge-admin',
        'icon'     => 'shield',
        'color'    => 'is-coral',
    ],
    [
        'name'     => 'Approval routing',
        'desc'     => 'Configurable multi-level approval chains for leave, overtime, reimbursements, and schedule changes — with escalation rules, deadline reminders, and full audit trails.',
        'category' => 'Admin',
        'badge'    => 'mk-badge-admin',
        'icon'     => 'route',
        'color'    => 'is-blue',
    ],
    [
        'name'     => 'Reporting and analytics',
        'desc'     => 'Pre-built and custom report builder covering headcount, attendance summaries, leave utilization, payroll cost breakdown, and compliance output for government filings.',
        'category' => 'Admin',
        'badge'    => 'mk-badge-admin',
        'icon'     => 'chart',
        'color'    => 'is-teal',
    ],
];

$moduleMatrix = [
    ['module' => 'Attendance tracking',  'starter' => true,  'growth' => true,  'enterprise' => true],
    ['module' => 'Leave management',     'starter' => true,  'growth' => true,  'enterprise' => true],
    ['module' => 'Payroll computation',  'starter' => false, 'growth' => true,  'enterprise' => true],
    ['module' => 'Employee records',     'starter' => true,  'growth' => true,  'enterprise' => true],
    ['module' => 'Onboarding workflow',  'starter' => false, 'growth' => true,  'enterprise' => true],
    ['module' => 'Role-based access',    'starter' => true,  'growth' => true,  'enterprise' => true],
    ['module' => 'Approval routing',     'starter' => false, 'growth' => true,  'enterprise' => true],
    ['module' => 'Reporting / analytics','starter' => false, 'growth' => false, 'enterprise' => true],
];

/* SVG icon helper */
$svgIcons = [
    'clock' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>',
    'calendar' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>',
    'banknotes' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="6" width="20" height="12" rx="2"/><circle cx="12" cy="12" r="2"/><path d="M6 12h.01M18 12h.01"/></svg>',
    'users' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
    'rocket' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4.5 16.5c-1.5 1.26-2 5-2 5s3.74-.5 5-2c.71-.84.7-2.13-.09-2.91a2.18 2.18 0 0 0-2.91-.09z"/><path d="m12 15-3-3a22 22 0 0 1 2-3.95A12.88 12.88 0 0 1 22 2c0 2.72-.78 7.5-6 11a22.35 22.35 0 0 1-4 2z"/><path d="M9 12H4s.55-3.03 2-4c1.62-1.08 5 0 5 0"/><path d="M12 15v5s3.03-.55 4-2c1.08-1.62 0-5 0-5"/></svg>',
    'shield' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/></svg>',
    'route' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="6" cy="19" r="3"/><path d="M9 19h8.5a3.5 3.5 0 0 0 0-7h-11a3.5 3.5 0 0 1 0-7H15"/><circle cx="18" cy="5" r="3"/></svg>',
    'chart' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>',
    'check-shield' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>',
    'uptime' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>',
    'headset' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 18v-6a9 9 0 0 1 18 0v6"/><path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"/></svg>',
];
?>

<div class="mk-shell mk-shell-landing">

    <!-- ── Top bar ──────────────────────────────────────────── -->
    <header class="mk-topbar" data-reveal style="--mk-delay: 0s;">

        <a class="mk-brand" href="/">
            <span class="mk-brand-dot" aria-hidden="true"></span>
            <span>
                <strong class="font-display">HRIS Cloud</strong>
                <small><?= $isTestingMode ? 'Testing Access Mode' : 'Production Billing Mode' ?></small>
            </span>
        </a>

        <nav class="mk-nav" aria-label="Main navigation">
            <a href="#features">Features</a>
            <a href="#modules">Modules</a>
            <a href="/pricing">Pricing</a>
            <a href="/login">Sign in</a>
            <a href="/pricing" class="mk-nav-cta">Start free</a>
        </nav>

    </header>

    <?php require __DIR__ . '/../partials/alerts.php'; ?>

    <!-- ── Hero — Immersive gradient mesh ──────────────────── -->
    <section
        class="mk-hero-panel"
        aria-labelledby="mk-hero-title"
        data-reveal
        style="--mk-delay: .05s;"
    >
        <div class="mk-hero-main">

            <p class="mk-kicker">Daily Command Center</p>

            <h1 id="mk-hero-title" class="font-display">
                Run your <span class="mk-gradient-text">HR operations</span> from one dashboard-ready workspace.
            </h1>

            <p class="mk-hero-summary">
                <?= $isTestingMode
                    ? 'Select a plan, unlock modules by feature entitlement, and validate attendance, leave, and payroll flow in testing mode.'
                    : 'Move from onboarding to payroll with one consistent command layer where permissions, approvals, and billing stay aligned.' ?>
            </p>

            <div class="mk-hero-actions">
                <a href="/pricing" class="mk-btn mk-btn-primary">Start your plan</a>
                <a href="/login"   class="mk-btn mk-btn-muted">Open workspace</a>
            </div>

            <!-- Trust badges -->
            <div class="mk-trust-row" aria-label="Trust signals">
                <span class="mk-trust-badge">
                    <?= $svgIcons['check-shield'] ?>
                    SOC 2 Compliant
                </span>
                <span class="mk-trust-badge">
                    <?= $svgIcons['uptime'] ?>
                    99.9% Uptime
                </span>
                <span class="mk-trust-badge">
                    <?= $svgIcons['headset'] ?>
                    24/7 Support
                </span>
            </div>

            <div class="mk-hero-tags" aria-label="Highlights">
                <span class="mk-hero-tag is-blue">Attendance Live</span>
                <span class="mk-hero-tag is-teal">Leave Workflow</span>
                <span class="mk-hero-tag is-coral">Payroll Queue</span>
            </div>

        </div>

        <aside class="mk-hero-side" aria-label="Platform summary">

            <p class="mk-side-label">Active plan tiers</p>
            <p class="mk-side-value"><?= e((string) $planCount) ?></p>
            <p class="mk-side-meta">Choose a tier and continue to sign-in to activate your access path.</p>

            <dl class="mk-hero-stats">
                <div class="mk-hero-stat">
                    <dt>Mode</dt>
                    <dd><?= $isTestingMode ? 'Testing' : 'Production' ?></dd>
                </div>
                <div class="mk-hero-stat">
                    <dt>Module lock</dt>
                    <dd><?= $isTestingMode ? 'Feature + role' : 'Subscription + role' ?></dd>
                </div>
                <div class="mk-hero-stat">
                    <dt>Billing cycle</dt>
                    <dd>Quarterly</dd>
                </div>
            </dl>

        </aside>
    </section>

    <!-- ── Social proof strip ──────────────────────────────── -->
    <section
        class="mk-social-proof"
        aria-label="Platform statistics"
        data-reveal
        style="--mk-delay: .06s;"
    >
        <div class="mk-proof-item">
            <p class="mk-proof-number"><span class="mk-counter" data-count-to="400">0</span>+</p>
            <p class="mk-proof-label">Teams onboarded</p>
        </div>
        <div class="mk-proof-item">
            <p class="mk-proof-number"><span class="mk-counter" data-count-to="8">0</span></p>
            <p class="mk-proof-label">Core modules</p>
        </div>
        <div class="mk-proof-item">
            <p class="mk-proof-number"><span class="mk-counter" data-count-to="99">0</span>%</p>
            <p class="mk-proof-label">Uptime SLA</p>
        </div>
        <div class="mk-proof-item">
            <p class="mk-proof-number"><span class="mk-counter" data-count-to="24">0</span>/7</p>
            <p class="mk-proof-label">Support access</p>
        </div>
    </section>

    <!-- ── Platform definitions — Bento-box grid ──────────── -->
    <section
        id="features"
        class="mk-bento-panel"
        aria-labelledby="mk-definitions-title"
        data-reveal
        style="--mk-delay: .08s;"
    >
        <div class="mk-section-head">
            <p class="mk-kicker">Platform definitions</p>
            <h2 id="mk-definitions-title" class="font-display">What each part of the platform does</h2>
            <p>Every core workflow — defined clearly so your team knows what they're activating.</p>
        </div>

        <div class="mk-bento-grid">
            <?php foreach ($featureDefinitions as $i => $def):
                $cardDelay = number_format(0.1 + ($i * 0.04), 2, '.', '');
            ?>
                <div
                    class="mk-bento-card"
                    data-scroll-reveal
                    style="transition-delay: <?= e($cardDelay) ?>s;"
                >
                    <div class="mk-bento-card-head">
                        <div class="mk-bento-icon <?= e($def['color']) ?>">
                            <?= $svgIcons[$def['icon']] ?? '' ?>
                        </div>
                    </div>
                    <h3><?= e($def['name']) ?></h3>
                    <p><?= e($def['desc']) ?></p>
                    <span class="mk-badge <?= e($def['badge']) ?>"><?= e($def['category']) ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- ── Module access matrix ───────────────────────────── -->
    <section
        id="modules"
        class="mk-modules-panel"
        aria-labelledby="mk-modules-title"
        data-reveal
        style="--mk-delay: .1s;"
    >
        <div class="mk-section-head">
            <p class="mk-kicker">Module access matrix</p>
            <h2 id="mk-modules-title" class="font-display">Which modules are included per plan</h2>
            <p>Feature availability by subscription tier — choose the coverage that fits your rollout.</p>
        </div>

        <div class="mk-modules-matrix" aria-label="Module access by plan">
            <div class="mk-matrix-header">
                <div class="mk-matrix-label">Module</div>
                <div class="mk-matrix-col">Starter</div>
                <div class="mk-matrix-col">Growth</div>
                <div class="mk-matrix-col">Enterprise</div>
            </div>

            <?php foreach ($moduleMatrix as $row): ?>
                <div class="mk-matrix-row">
                    <div class="mk-matrix-label"><?= e($row['module']) ?></div>
                    <?php foreach (['starter', 'growth', 'enterprise'] as $tier): ?>
                        <div class="mk-matrix-col">
                            <?php if ($row[$tier]): ?>
                                <span class="mk-check-icon" aria-label="Included" role="img">
                                    <svg viewBox="0 0 10 10" fill="none" aria-hidden="true">
                                        <polyline
                                            points="2,5 4.5,7.5 8,2.5"
                                            stroke="#16a34a"
                                            stroke-width="1.5"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        />
                                    </svg>
                                </span>
                            <?php else: ?>
                                <span class="mk-dash-icon" aria-label="Not included" role="img">
                                    <span aria-hidden="true"></span>
                                </span>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <p class="mk-footnote">
            Need detailed comparison? <a href="/pricing">Open full pricing and module matrix.</a>
        </p>
    </section>

    <!-- ── Final CTA — Immersive gradient ─────────────────── -->
    <section
        class="mk-final-cta-panel"
        data-reveal
        style="--mk-delay: .12s;"
    >
        <div class="mk-cta-text">
            <p class="mk-kicker">Launch Fast</p>
            <h2 class="font-display">
                Join <?= e((string) $planCount) ?>00+ teams running their HR from one execution-ready workspace.
            </h2>
            <p>
                <?= $isTestingMode
                    ? 'Pick your plan, sign in, and validate feature-lock behavior before production cutover.'
                    : 'Pick your plan, sign in, and continue through production-ready subscription controls.' ?>
            </p>
        </div>
        <div class="mk-cta-actions">
            <a class="mk-btn mk-btn-primary" href="/pricing">Choose a plan</a>
            <a class="mk-btn mk-btn-muted"   href="/login">Go to sign in</a>
        </div>
    </section>

</div>
<?php
$planRows = is_array($plans ?? null) ? $plans : [];
$isTestingMode = is_subscription_testing_mode();
$defaultPlanId = 0;
$defaultPlanName = '';

foreach ($planRows as $plan) {
    if ((int) ($plan['is_contact_only'] ?? 0) === 0) {
        $defaultPlanId = (int) ($plan['id'] ?? 0);
        $defaultPlanName = display_plan_name_for_access((string) ($plan['plan_name'] ?? 'Plan'));
        break;
    }
}

if ($defaultPlanId === 0 && $planRows !== []) {
    $defaultPlanId = (int) ($planRows[0]['id'] ?? 0);
    $defaultPlanName = display_plan_name_for_access((string) ($planRows[0]['plan_name'] ?? 'Plan'));
}

$hasPlans = $planRows !== [];

$comparisonRows = [
    'employees' => 'Employee management',
    'attendance' => 'Attendance tracking',
    'leave' => 'Leave workflows',
    'payroll' => 'Payroll setup',
    'settings' => 'Settings administration',
    'priority_support' => 'Priority support',
];

$planFeatures = [];
foreach ($planRows as $plan) {
    $decoded = json_decode((string) ($plan['feature_flags'] ?? '[]'), true);
    $planFeatures[(int) ($plan['id'] ?? 0)] = is_array($decoded) ? $decoded : [];
}
?>

<div class="mk-shell mk-shell-pricing">
    <header class="mk-topbar" data-reveal style="--mk-delay: 0s;">
        <a class="mk-brand" href="/">
            <span class="mk-brand-dot" aria-hidden="true"></span>
            <span>
                <strong class="font-display">HRIS Cloud</strong>
                <small><?= $isTestingMode ? 'Testing Access Mode' : 'Production Billing Mode' ?></small>
            </span>
        </a>

        <nav class="mk-nav" aria-label="Pricing navigation">
            <a href="/">Home</a>
            <a href="#pricing-plans">Plans</a>
            <a href="#pricing-compare">Compare</a>
            <a href="/login" class="mk-nav-cta">Sign in</a>
        </nav>
    </header>

    <?php require __DIR__ . '/../partials/alerts.php'; ?>

    <!-- ── Pricing hero ────────────────────────────────────── -->
    <section class="mk-pricing-hero" aria-labelledby="mk-pricing-title" data-reveal style="--mk-delay: .05s;">
        <div class="mk-pricing-hero-main">
            <p class="mk-kicker">Pricing and Access</p>
            <h1 id="mk-pricing-title" class="font-display">
                <?= $isTestingMode
                    ? 'Select the plan that controls your testing module access.'
                    : 'Select the quarterly plan that fits your HR operations stage.' ?>
            </h1>
            <p>
                <?= $isTestingMode
                    ? 'Your selected plan applies feature locks immediately. Checkout simulation remains optional for billing-state validation.'
                    : 'Choose a plan, then continue through billing controls to activate production access.' ?>
            </p>

            <!-- Progress stepper (commitment-consistency) -->
            <div class="mk-stepper" aria-label="Selection progress">
                <div class="mk-step is-active">
                    <span class="mk-step-number">1</span>
                    <span>Choose Plan</span>
                </div>
                <span class="mk-step-divider" aria-hidden="true"></span>
                <div class="mk-step">
                    <span class="mk-step-number">2</span>
                    <span>Create Account</span>
                </div>
                <span class="mk-step-divider" aria-hidden="true"></span>
                <div class="mk-step">
                    <span class="mk-step-number">3</span>
                    <span>Start Working</span>
                </div>
            </div>

            <div class="mk-hero-tags" aria-label="Pricing highlights">
                <span class="mk-hero-tag is-blue">Quarterly Billing</span>
                <span class="mk-hero-tag is-teal">Role-Aware Access</span>
                <span class="mk-hero-tag is-coral">Feature Entitlement</span>
            </div>
        </div>

        <aside class="mk-pricing-hero-side" aria-label="Mode guidance">
            <p class="mk-side-label">Mode</p>
            <p class="mk-pricing-mode"><?= $isTestingMode ? 'Testing Access Mode' : 'Production Billing Mode' ?></p>
            <p class="mk-pricing-mode-note">
                <?= $isTestingMode
                    ? 'Module availability = plan feature + role permission.'
                    : 'Module availability = valid subscription + plan feature + role permission.' ?>
            </p>
        </aside>
    </section>

    <?php if (!$hasPlans): ?>
        <section class="mk-pricing-form" data-reveal style="--mk-delay: .1s;">
            <div class="alert alert-danger">No active plans were found. Run roles and demo seed files, then refresh this page.</div>
            <div class="mk-pricing-actions">
                <a class="mk-btn mk-btn-muted" href="/">Return to landing</a>
            </div>
        </section>
    <?php else: ?>
        <form class="mk-pricing-form" method="post" action="/subscribe" data-plan-picker data-plan-modal-form data-reveal style="--mk-delay: .1s;">
            <input type="hidden" name="_csrf" value="<?= e((string) ($csrf ?? '')) ?>">
            <input type="hidden" name="billing_cycle" value="quarterly">

            <div class="mk-selection-summary" data-selection-summary>
                <div class="mk-selection-summary-head">
                    <p class="mk-kicker">Selection Preview</p>
                    <p class="mk-selection-plan" data-selection-plan><?= e($defaultPlanName !== '' ? $defaultPlanName : 'No plan selected') ?></p>
                    <p class="mk-selection-note">
                        <?= $isTestingMode
                            ? 'Selected plan immediately controls testing module access. Simulated checkout is optional.'
                            : 'Quarterly billing remains fixed in this release. Enterprise follows contact-sales onboarding.' ?>
                    </p>
                    <p class="mk-selection-social">Most teams begin with <?= e(normal_plan_name()) ?> and move to Growth once payroll and settings become core daily workflows.</p>
                </div>

                <div class="mk-plan-cards mk-plan-cards-pricing" id="pricing-plans" aria-label="Plan options">
                    <?php foreach ($planRows as $index => $plan): ?>
                        <?php
                        $planId = (int) ($plan['id'] ?? 0);
                        $planDisplayName = display_plan_name_for_access((string) ($plan['plan_name'] ?? 'Plan'));
                        $features = json_decode((string) ($plan['feature_flags'] ?? '[]'), true);
                        $featureItems = is_array($features) ? $features : [];
                        $featureCount = count($featureItems);
                        $isContactOnly = (int) ($plan['is_contact_only'] ?? 0) === 1;
                        $isChecked = $defaultPlanId === $planId;
                        $planCode = strtoupper((string) ($plan['plan_code'] ?? ''));
                        $planHook = 'Reliable entry point for launching core HR workflows.';
                        $confidence = 86;
                        $cardDelay = number_format(0.14 + ($index * 0.06), 2, '.', '');

                        $tierClass = 'is-starter';
                        $tierTag = 'Starter';

                        if (str_contains($planCode, 'GROWTH')) {
                            $tierClass = 'is-recommended';
                            $tierTag = 'Most Popular';
                            $planHook = 'Most selected for complete operations rollout and daily use.';
                            $confidence = 94;
                        } elseif (str_contains($planCode, 'ENTERPRISE')) {
                            $tierClass = 'is-enterprise';
                            $tierTag = 'Contact sales';
                            $planHook = 'Designed for mature teams requiring governance and bespoke support.';
                            $confidence = 98;
                        }

                        $priceRaw = (float) ($plan['price_amount'] ?? 0);
                        $monthlyEquiv = $priceRaw / 3;
                        $modalPrice = $isContactOnly
                            ? 'Contact Sales'
                            : 'PHP ' . number_format($priceRaw, 2) . ' / quarter';
                        ?>
                        <label
                            class="mk-plan-card <?= e($tierClass) ?> <?= $isChecked ? 'is-selected' : '' ?>"
                            data-plan-card
                            data-plan-name="<?= e($planDisplayName) ?>"
                            data-plan-price="<?= e($modalPrice) ?>"
                            data-plan-hook="<?= e($planHook) ?>"
                            data-scroll-reveal
                            style="transition-delay: <?= e($cardDelay) ?>s;"
                        >
                            <input
                                type="radio"
                                name="plan_id"
                                value="<?= $planId ?>"
                                <?= $isChecked ? 'checked' : '' ?>
                                <?= $index === 0 ? 'required' : '' ?>
                                class="mk-plan-input"
                            >

                            <span class="mk-plan-tag"><?= e($tierTag) ?></span>

                            <div class="mk-plan-head">
                                <p class="mk-plan-name font-display"><?= e($planDisplayName) ?></p>
                                <p class="mk-plan-price">
                                    <?php if ($isContactOnly): ?>
                                        Contact Sales
                                    <?php else: ?>
                                        PHP <?= e(number_format($monthlyEquiv, 2)) ?>
                                        <span>/ month</span>
                                    <?php endif; ?>
                                </p>
                                <?php if (!$isContactOnly): ?>
                                    <p class="mk-plan-anchor">Billed as PHP <?= e(number_format($priceRaw, 2)) ?> per quarter</p>
                                <?php endif; ?>
                                <div class="mk-plan-meta">
                                    <span><strong><?= e((string) max(1, $featureCount)) ?></strong> modules</span>
                                    <span><?= $isContactOnly ? 'Guided onboarding' : 'Self-serve rollout' ?></span>
                                </div>
                            </div>

                            <p class="mk-plan-desc"><?= e((string) ($plan['description'] ?? '')) ?></p>

                            <div class="mk-plan-psych">
                                <p class="mk-plan-social <?= e($tierClass) ?>"><?= e($planHook) ?></p>
                                <?php if (!$isContactOnly): ?>
                                    <div class="mk-plan-confidence" aria-label="Plan confidence score">
                                        <div class="mk-plan-confidence-head">
                                            <span>Adoption confidence</span>
                                            <strong><?= e((string) $confidence) ?>%</strong>
                                        </div>
                                        <span class="mk-plan-meter" aria-hidden="true" style="--mk-meter-width: <?= e((string) $confidence) ?>%"><span></span></span>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <ul class="mk-plan-list">
                                <?php foreach (array_slice($featureItems, 0, 6) as $feature): ?>
                                    <li><?= e(ucwords(str_replace('_', ' ', (string) $feature))) ?></li>
                                <?php endforeach; ?>
                            </ul>

                            <div class="mk-plan-foot">
                                <?php if ($isContactOnly): ?>
                                    <span class="mk-pill">Contact-sales flow</span>
                                <?php else: ?>
                                    <span class="mk-pill">Selectable tier</span>
                                <?php endif; ?>
                                <span class="mk-plan-foot-score">Suitability <?= e((string) $confidence) ?>%</span>
                            </div>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <p class="mk-live-region" aria-live="polite"></p>

            <div class="mk-pricing-actions">
                <button
                    class="mk-btn mk-btn-primary"
                    type="submit"
                    data-submit-label="<?= $isTestingMode ? 'Select Plan for Testing Access' : 'Select Plan and Continue' ?>"
                    data-loading-label="Processing selection..."
                >
                    <?= $isTestingMode ? 'Select Plan for Testing Access' : 'Select Plan and Continue' ?>
                </button>
                <a class="mk-btn mk-btn-muted" href="/login">Already have an account</a>
            </div>
        </form>

        <aside class="mk-pricing-side" aria-label="Selection details">
            <h2 class="font-display">How rollout works</h2>

            <div class="mk-side-block">
                <h3><span class="mk-step-num">1</span> Select plan</h3>
                <p><?= $isTestingMode ? 'Choose the plan that unlocks modules for your test run.' : 'Choose the quarterly tier for your workspace.' ?></p>
            </div>

            <div class="mk-side-block">
                <h3><span class="mk-step-num">2</span> Sign in and continue</h3>
                <p>Open the workspace and validate your routing, approvals, and operational flow.</p>
            </div>

            <div class="mk-side-block">
                <h3><span class="mk-step-num">3</span> Validate billing behavior</h3>
                <p><?= $isTestingMode ? 'Run checkout simulation only when you need billing outcome tests.' : 'Complete billing flow to activate production access.' ?></p>
            </div>
        </aside>
    <?php endif; ?>

    <!-- ── Comparison table ────────────────────────────────── -->
    <?php if ($hasPlans): ?>
        <section class="mk-compare-section" id="pricing-compare" aria-labelledby="mk-compare-title" data-reveal style="--mk-delay: .16s;">
            <div class="mk-section-head">
                <p class="mk-kicker">Detailed Comparison</p>
                <h2 id="mk-compare-title" class="font-display">Review plan coverage before rollout.</h2>
            </div>

            <div class="mk-compare-table-wrap">
                <table class="mk-compare-table">
                    <thead>
                        <tr>
                            <th>Capability</th>
                            <?php foreach ($planRows as $plan): ?>
                                <th><?= e(display_plan_name_for_access((string) ($plan['plan_name'] ?? 'Plan'))) ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($comparisonRows as $featureKey => $label): ?>
                            <tr>
                                <th><?= e($label) ?></th>
                                <?php foreach ($planRows as $plan): ?>
                                    <?php
                                    $planId = (int) ($plan['id'] ?? 0);
                                    $enabled = in_array($featureKey, $planFeatures[$planId] ?? [], true);
                                    ?>
                                    <td class="<?= $enabled ? 'is-on' : 'is-off' ?>"><?= $enabled ? 'Included' : 'Not included' ?></td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    <?php endif; ?>

    <!-- ── FAQ + Final CTA ─────────────────────────────────── -->
    <section class="mk-pricing-bottom" data-reveal style="--mk-delay: .2s;">
        <div class="mk-faq" aria-labelledby="mk-faq-title">
            <h2 id="mk-faq-title" class="font-display">FAQ</h2>

            <div class="mk-faq-item">
                <button class="mk-faq-question" type="button" aria-expanded="false">
                    Can we enable monthly billing now?
                    <svg class="mk-faq-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                </button>
                <div class="mk-faq-answer">
                    <p>No. Monthly billing is intentionally disabled for this release. Quarterly billing ensures operational stability and simpler access management.</p>
                </div>
            </div>

            <div class="mk-faq-item">
                <button class="mk-faq-question" type="button" aria-expanded="false">
                    Is payment processed in this environment?
                    <svg class="mk-faq-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                </button>
                <div class="mk-faq-answer">
                    <p>
                        <?= $isTestingMode
                            ? 'No. Checkout outcomes are simulated and optional in testing mode.'
                            : 'Checkout behavior is controlled by production billing flow.' ?>
                    </p>
                </div>
            </div>

            <div class="mk-faq-item">
                <button class="mk-faq-question" type="button" aria-expanded="false">
                    Can Enterprise self-checkout immediately?
                    <svg class="mk-faq-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                </button>
                <div class="mk-faq-answer">
                    <p>Enterprise follows a contact-sales path during this phase. A dedicated onboarding specialist will guide your team through the setup.</p>
                </div>
            </div>

            <div class="mk-faq-item">
                <button class="mk-faq-question" type="button" aria-expanded="false">
                    Can I upgrade my plan later?
                    <svg class="mk-faq-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                </button>
                <div class="mk-faq-answer">
                    <p>Yes. You can upgrade at any time without losing your existing data or configurations. The difference is prorated for the remaining quarter.</p>
                </div>
            </div>

            <div class="mk-faq-item">
                <button class="mk-faq-question" type="button" aria-expanded="false">
                    What happens when my subscription expires?
                    <svg class="mk-faq-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                </button>
                <div class="mk-faq-answer">
                    <p>Your data remains safe and accessible in read-only mode. Module access is paused until you renew. No data is ever deleted due to subscription expiry.</p>
                </div>
            </div>
        </div>

        <aside class="mk-final-cta" aria-label="Pricing call to action">
            <p class="mk-kicker">Start rollout</p>
            <h2 class="font-display">Pick a tier and launch your modern HRIS workspace with confidence.</h2>
            <p>Align plan coverage with role permissions, then validate workflow and billing behavior in one controlled environment.</p>
            <div class="mk-pricing-actions">
                <a class="mk-btn mk-btn-primary" href="/login">Sign in</a>
                <a class="mk-btn mk-btn-muted" href="/">Back to landing</a>
            </div>
        </aside>
    </section>

    <!-- ── Plan decision modal ─────────────────────────────── -->
    <div class="mk-plan-modal-backdrop" id="planDecisionModal" hidden>
        <div class="mk-plan-modal" role="dialog" aria-modal="true" aria-labelledby="mk-plan-modal-title" aria-describedby="mk-plan-modal-copy">
            <button class="mk-plan-modal-close" type="button" data-plan-modal-close aria-label="Close plan modal">&times;</button>
            <p class="mk-kicker">Plan decision</p>
            <h2 id="mk-plan-modal-title" class="font-display">Confirm your plan before continuing</h2>
            <p class="mk-plan-modal-name" data-plan-modal-name>Selected plan</p>
            <p class="mk-plan-modal-price" data-plan-modal-price>Pricing</p>
            <p id="mk-plan-modal-copy" class="mk-plan-modal-copy" data-plan-modal-hook>Plan recommendation</p>

            <ul class="mk-plan-modal-points">
                <li>You can upgrade later without losing your current setup.</li>
                <li>Feature locks stay visible so teams always know what is next.</li>
                <li>Billing and role permissions remain aligned after selection.</li>
            </ul>

            <div class="mk-plan-modal-actions">
                <button class="mk-btn mk-btn-primary" type="button" data-plan-modal-confirm>Continue with this plan</button>
                <button class="mk-btn mk-btn-muted" type="button" data-plan-modal-close>Review again</button>
            </div>
        </div>
    </div>

    <div class="mk-loading-overlay" id="planLoadingOverlay" hidden aria-live="polite">
        <div class="mk-loading-card" role="status">
            <span class="mk-loading-spinner" aria-hidden="true"></span>
            <p class="mk-loading-title">Applying your plan selection...</p>
            <p class="mk-loading-copy">Preparing access logic and routing your next step.</p>
        </div>
    </div>
</div>

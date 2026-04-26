<?php
$planRows = is_array($plans ?? null) ? $plans : [];
$transactionRows = is_array($transactions ?? null) ? $transactions : [];
$historyRows = is_array($history ?? null) ? $history : [];
$currentSubscription = is_array($current ?? null) ? $current : null;
$hasPlans = $planRows !== [];
$status = is_array($statusSummary ?? null) ? $statusSummary : [
    'label'    => 'Unavailable',
    'is_valid' => false,
    'tone'     => 'danger',
    'message'  => 'Subscription details are not available.',
];

$statusTone         = (string) ($status['tone'] ?? 'danger');
$selectedId         = (int) ($selectedPlanId ?? 0);
$selectedCycleValue = (string) ($selectedCycle ?? 'quarterly');
$canManage          = (bool) ($canManageBilling ?? false);
$isTestingMode      = (bool) ($isTestingMode ?? is_subscription_testing_mode());

if ($isTestingMode && !(bool) ($status['is_valid'] ?? false)) {
    $status = [
        'label'    => 'Testing Access Mode',
        'is_valid' => true,
        'tone'     => 'warning',
        'message'  => 'Payment enforcement is disabled in testing mode. Module access follows role permissions and selected plan feature locks.',
    ];
    $statusTone = 'warning';
}
?>

<section class="bill-page">

    <!-- ── Hero ──────────────────────────────────────────── -->
    <header class="bill-hero">
        <div>
            <p class="bill-kicker">Billing</p>
            <h2 class="bill-title font-display">
                <?= $isTestingMode ? 'Access and billing control center' : 'Subscription control center' ?>
            </h2>
            <p class="bill-subtitle">
                <?= $isTestingMode
                    ? 'Select a plan to control feature locks. Checkout simulation is available but not required for testing access.'
                    : 'Select your quarterly plan, run checkout simulation, and keep module access in sync with subscription state.' ?>
            </p>
        </div>
        <div class="bill-hero-actions">
            <?php if ($isTestingMode || (bool) ($status['is_valid'] ?? false)): ?>
                <a class="bill-btn bill-btn-primary" href="<?= e((string) ($continuePath ?? '/dashboard')) ?>">
                    Continue to workspace
                </a>
            <?php endif; ?>
            <a class="bill-btn bill-btn-muted" href="/pricing">View public pricing</a>
        </div>
    </header>

    <?php require __DIR__ . '/../partials/alerts.php'; ?>

    <!-- ── Tabbed panel ──────────────────────────────────── -->
    <div class="bill-tabs-wrap">

        <nav class="bill-tabs-nav" role="tablist" aria-label="Billing sections">
            <button
                class="bill-tab-btn is-active"
                role="tab"
                aria-selected="true"
                aria-controls="bill-panel-plans"
                id="bill-tab-plans"
                onclick="billSwitchTab('plans')"
            >
                <?= $isTestingMode ? 'Plan selection' : 'Plan selection' ?>
                <small>Choose &amp; checkout</small>
            </button>
            <button
                class="bill-tab-btn"
                role="tab"
                aria-selected="false"
                aria-controls="bill-panel-status"
                id="bill-tab-status"
                onclick="billSwitchTab('status')"
            >
                Subscription status
                <small>Current state</small>
            </button>
            <button
                class="bill-tab-btn"
                role="tab"
                aria-selected="false"
                aria-controls="bill-panel-transactions"
                id="bill-tab-transactions"
                onclick="billSwitchTab('transactions')"
            >
                Transactions
                <small>Checkout history</small>
            </button>
            <button
                class="bill-tab-btn"
                role="tab"
                aria-selected="false"
                aria-controls="bill-panel-history"
                id="bill-tab-history"
                onclick="billSwitchTab('history')"
            >
                Subscription history
                <small>Lifecycle records</small>
            </button>
        </nav>

        <!-- ── Panel: Plan selection ─────────────────────── -->
        <div
            id="bill-panel-plans"
            class="bill-tab-panel is-active"
            role="tabpanel"
            aria-labelledby="bill-tab-plans"
        >
            <div class="bill-head" style="margin-bottom:4px;">
                <h3><?= $isTestingMode
                    ? 'Select a testing plan and optionally run checkout simulation'
                    : 'Select plan and run test checkout' ?></h3>
                <p>
                    <?= $isTestingMode
                        ? 'Your selected plan controls feature-locked modules immediately. Billing simulation is optional in this mode.'
                        : 'Quarterly billing is fixed for this release.' ?>
                </p>
            </div>

            <?php if (!$hasPlans): ?>
                <div class="alert alert-danger">
                    No active plans are available. Seed plans in the current database and refresh this page.
                </div>
            <?php else: ?>
                <form method="post" action="/billing/checkout" data-plan-picker>
                    <input type="hidden" name="_csrf" value="<?= e((string) ($csrf ?? '')) ?>">

                    <div class="bill-plan-grid">
                        <?php foreach ($planRows as $index => $plan): ?>
                            <?php
                            $planId          = (int) ($plan['id'] ?? 0);
                            $planDisplayName = display_plan_name_for_access((string) ($plan['plan_name'] ?? 'Plan'));
                            $isChecked       = $selectedId === $planId || ($selectedId === 0 && (int) ($plan['is_contact_only'] ?? 0) === 0);
                            $features        = json_decode((string) ($plan['feature_flags'] ?? '[]'), true);
                            $featureItems    = is_array($features) ? $features : [];
                            $isContactOnly   = (int) ($plan['is_contact_only'] ?? 0) === 1;
                            $planCode        = strtoupper((string) ($plan['plan_code'] ?? ''));

                            $tierClass = 'is-starter';
                            $tierTag   = 'Starter tier';

                            if (str_contains($planCode, 'GROWTH')) {
                                $tierClass = 'is-recommended';
                                $tierTag   = 'Recommended';
                            } elseif (str_contains($planCode, 'ENTERPRISE')) {
                                $tierClass = 'is-enterprise';
                                $tierTag   = 'Contact sales';
                            }
                            ?>
                            <label
                                class="bill-plan-card <?= e($tierClass) ?> <?= $isChecked ? 'is-selected' : '' ?>"
                                data-plan-card
                                data-plan-name="<?= e($planDisplayName) ?>"
                            >
                                <input
                                    class="bill-plan-input"
                                    type="radio"
                                    name="plan_id"
                                    value="<?= $planId ?>"
                                    <?= $isChecked ? 'checked' : '' ?>
                                    <?= $index === 0 ? 'required' : '' ?>
                                >

                                <span class="bill-plan-tag <?= e($tierClass) ?>"><?= e($tierTag) ?></span>

                                <div class="bill-plan-top">
                                    <span class="bill-plan-name font-display"><?= e($planDisplayName) ?></span>
                                    <span class="bill-plan-price">
                                        <?php if ($isContactOnly): ?>
                                            Contact Sales
                                        <?php else: ?>
                                            <?= 'PHP ' . e(number_format((float) ($plan['price_amount'] ?? 0), 2)) ?>
                                            <small>/ quarter</small>
                                        <?php endif; ?>
                                    </span>
                                </div>

                                <p><?= e((string) ($plan['description'] ?? '')) ?></p>

                                <ul>
                                    <?php foreach (array_slice($featureItems, 0, 4) as $feature): ?>
                                        <li><?= e(ucwords(str_replace('_', ' ', (string) $feature))) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </label>
                        <?php endforeach; ?>
                    </div>

                    <div class="bill-form-row">
                        <label for="billing_cycle">Billing cycle</label>
                        <select id="billing_cycle" name="billing_cycle">
                            <option value="quarterly" <?= $selectedCycleValue === 'quarterly' ? 'selected' : '' ?>>
                                Quarterly
                            </option>
                        </select>
                    </div>

                    <div class="bill-form-row" style="margin-top:8px;">
                        <label for="test_mode">Checkout test mode</label>
                        <select id="test_mode" name="test_mode">
                            <option value="test_success">test_success (activate)</option>
                            <option value="test_pending">test_pending (awaiting payment)</option>
                            <option value="test_fail">test_fail (failed)</option>
                        </select>
                    </div>

                    <p class="bill-live-region" aria-live="polite"></p>

                    <div style="margin-top:14px;">
                        <button
                            class="bill-btn bill-btn-primary"
                            type="submit"
                            data-submit-label="<?= $isTestingMode ? 'Apply Plan and Simulate Checkout' : 'Run Test Checkout' ?>"
                            data-loading-label="Processing checkout..."
                        >
                            <?= $isTestingMode ? 'Apply Plan and Simulate Checkout' : 'Run Test Checkout' ?>
                        </button>
                    </div>
                </form>
            <?php endif; ?>
        </div>

        <!-- ── Panel: Subscription status ───────────────── -->
        <div
            id="bill-panel-status"
            class="bill-tab-panel"
            role="tabpanel"
            aria-labelledby="bill-tab-status"
        >
            <article class="bill-card bill-status bill-status-<?= e($statusTone) ?>" style="margin-bottom:16px;">
                <p class="bill-status-label">
                    <?= $isTestingMode ? 'Current Access Mode' : 'Current Subscription Status' ?>
                </p>
                <h3><?= e((string) ($status['label'] ?? 'Unavailable')) ?></h3>
                <p><?= e((string) ($status['message'] ?? '')) ?></p>

                <?php if ($currentSubscription !== null): ?>
                    <dl class="bill-status-meta">
                        <div>
                            <dt>Plan</dt>
                            <dd><?= e((string) ($currentSubscription['plan_name'] ?? '-')) ?></dd>
                        </div>
                        <div>
                            <dt>Cycle</dt>
                            <dd><?= e((string) ($currentSubscription['billing_cycle'] ?? '-')) ?></dd>
                        </div>
                        <div>
                            <dt>Ends at</dt>
                            <dd><?= e((string) ($currentSubscription['ends_at'] ?? '-')) ?></dd>
                        </div>
                        <div>
                            <dt>Trial ends</dt>
                            <dd><?= e((string) ($currentSubscription['trial_ends_at'] ?? '-')) ?></dd>
                        </div>
                    </dl>
                <?php endif; ?>
            </article>

            <?php if ($canManage && $currentSubscription !== null && in_array(
                (string) ($currentSubscription['status'] ?? ''),
                ['trialing', 'active', 'past_due'],
                true
            )): ?>
                <article class="bill-card">
                    <div class="bill-head">
                        <h3>Admin billing actions</h3>
                        <p>Cancel the current subscription if needed.</p>
                    </div>
                    <form method="post" action="/billing/cancel" class="bill-manage-form">
                        <input type="hidden" name="_csrf" value="<?= e((string) ($csrf ?? '')) ?>">
                        <label for="cancel_reason">Cancellation reason (optional)</label>
                        <input
                            id="cancel_reason"
                            type="text"
                            name="cancel_reason"
                            placeholder="Reason for cancellation"
                        >
                        <div>
                            <button class="bill-btn bill-btn-danger" type="submit">
                                Cancel Current Subscription
                            </button>
                        </div>
                    </form>
                </article>
            <?php endif; ?>
        </div>

        <!-- ── Panel: Transactions ───────────────────────── -->
        <div
            id="bill-panel-transactions"
            class="bill-tab-panel"
            role="tabpanel"
            aria-labelledby="bill-tab-transactions"
        >
            <div class="bill-head" style="margin-bottom:12px;">
                <h3>Recent checkout transactions</h3>
                <p>Latest simulated checkout attempts and outcomes.</p>
            </div>
            <div class="bill-table-wrap">
                <table class="bill-table">
                    <thead>
                        <tr>
                            <th>Reference</th>
                            <th>Plan</th>
                            <th>Mode</th>
                            <th>Status</th>
                            <th>Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($transactionRows === []): ?>
                            <tr>
                                <td colspan="5" class="bill-empty">No checkout transactions yet.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($transactionRows as $row): ?>
                                <tr>
                                    <td><?= e((string) ($row['reference_code'] ?? '-')) ?></td>
                                    <td><?= e((string) ($row['plan_name'] ?? '-')) ?></td>
                                    <td><?= e((string) ($row['test_mode'] ?? '-')) ?></td>
                                    <td>
                                        <span class="bill-pill bill-pill-<?= e((string) ($row['status'] ?? 'pending')) ?>">
                                            <?= e(strtoupper((string) ($row['status'] ?? 'pending'))) ?>
                                        </span>
                                    </td>
                                    <td><?= e((string) ($row['created_at'] ?? '-')) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ── Panel: Subscription history ──────────────── -->
        <div
            id="bill-panel-history"
            class="bill-tab-panel"
            role="tabpanel"
            aria-labelledby="bill-tab-history"
        >
            <div class="bill-head" style="margin-bottom:12px;">
                <h3>Subscription history</h3>
                <p>Recent lifecycle records for this company.</p>
            </div>
            <div class="bill-table-wrap">
                <table class="bill-table">
                    <thead>
                        <tr>
                            <th>Plan</th>
                            <th>Status</th>
                            <th>Start</th>
                            <th>End</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($historyRows === []): ?>
                            <tr>
                                <td colspan="4" class="bill-empty">No subscription history yet.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($historyRows as $row): ?>
                                <tr>
                                    <td><?= e((string) ($row['plan_name'] ?? '-')) ?></td>
                                    <td>
                                        <span class="bill-pill bill-pill-<?= e((string) ($row['status'] ?? 'pending')) ?>">
                                            <?= e(strtoupper((string) ($row['status'] ?? '-'))) ?>
                                        </span>
                                    </td>
                                    <td><?= e((string) ($row['starts_at'] ?? '-')) ?></td>
                                    <td><?= e((string) ($row['ends_at'] ?? '-')) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div><!-- /.bill-tabs-wrap -->

    <!-- ── Tab switching JS ──────────────────────────────── -->
    <script>
    function billSwitchTab(name) {
        var tabs   = document.querySelectorAll('.bill-tab-btn');
        var panels = document.querySelectorAll('.bill-tab-panel');
        var order  = ['plans', 'status', 'transactions', 'history'];

        tabs.forEach(function (btn, i) {
            var active = order[i] === name;
            btn.classList.toggle('is-active', active);
            btn.setAttribute('aria-selected', active ? 'true' : 'false');
        });

        panels.forEach(function (panel) {
            panel.classList.toggle('is-active', panel.id === 'bill-panel-' + name);
        });
    }

    // Highlight selected plan card on radio change
    document.addEventListener('change', function (e) {
        if (e.target && e.target.name === 'plan_id') {
            document.querySelectorAll('[data-plan-card]').forEach(function (card) {
                card.classList.toggle('is-selected', card.contains(e.target));
            });
        }
    });

    // Loading state on checkout submit
    document.addEventListener('submit', function (e) {
        var form = e.target;
        if (!form.hasAttribute('data-plan-picker')) return;
        var btn = form.querySelector('[data-submit-label]');
        if (!btn) return;
        btn.disabled = true;
        btn.textContent = btn.dataset.loadingLabel || 'Processing…';
        var region = form.querySelector('.bill-live-region');
        if (region) region.textContent = 'Processing checkout, please wait.';
    });
    </script>

</section>
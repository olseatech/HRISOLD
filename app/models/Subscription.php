<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use DateTimeImmutable;

final class Subscription extends Model
{
    private const FEATURE_ROUTE_MAP = [
        '/employees' => 'employees',
        '/attendance' => 'attendance',
        '/leave' => 'leave',
        '/payroll' => 'payroll',
        '/settings' => 'settings',
    ];

    private const FEATURE_LABELS = [
        'employees' => 'Employee management',
        'attendance' => 'Attendance tracking',
        'leave' => 'Leave workflows',
        'payroll' => 'Payroll setup',
        'settings' => 'Settings administration',
        'priority_support' => 'Priority support',
    ];

    public function publicPlans(): array
    {
        $plans = $this->fetchAll(
            'SELECT id, plan_code, plan_name, description, billing_cycle, interval_months,
                    price_amount, currency, employee_limit, is_contact_only, feature_flags, sort_order
             FROM hris_subscription_plans
             WHERE is_active = 1
             ORDER BY sort_order ASC, id ASC'
        );

        if ($plans !== []) {
            return $plans;
        }

        $this->seedDefaultPlansIfEmpty();

        return $this->fetchAll(
            'SELECT id, plan_code, plan_name, description, billing_cycle, interval_months,
                    price_amount, currency, employee_limit, is_contact_only, feature_flags, sort_order
             FROM hris_subscription_plans
             WHERE is_active = 1
             ORDER BY sort_order ASC, id ASC'
        );
    }

    public function findPlanById(int $planId): ?array
    {
        return $this->fetchOne(
            'SELECT id, plan_code, plan_name, description, billing_cycle, interval_months,
                    price_amount, currency, employee_limit, is_contact_only, feature_flags, sort_order
             FROM hris_subscription_plans
             WHERE id = :id AND is_active = 1
             LIMIT 1',
            ['id' => $planId]
        );
    }

    public function defaultTestingPlan(): ?array
    {
        $plans = $this->publicPlans();

        foreach ($plans as $plan) {
            if ((int) ($plan['is_contact_only'] ?? 0) === 0) {
                return $plan;
            }
        }

        return $plans[0] ?? null;
    }

    public function resolveCompanyIdForUser(int $userId): ?int
    {
        $row = $this->fetchOne(
            'SELECT b.company_id
             FROM hris_users u
             LEFT JOIN hris_employees e ON e.id = u.employee_id
             LEFT JOIN hris_departments d ON d.id = e.department_id
             LEFT JOIN hris_branches b ON b.id = d.branch_id
             WHERE u.id = :user_id
             LIMIT 1',
            ['user_id' => $userId]
        );

        if ($row && isset($row['company_id']) && $row['company_id'] !== null) {
            return (int) $row['company_id'];
        }

        $fallback = $this->fetchOne('SELECT id FROM hris_companies ORDER BY id ASC LIMIT 1');

        return isset($fallback['id']) ? (int) $fallback['id'] : null;
    }

    public function currentSubscriptionForCompany(int $companyId): ?array
    {
        return $this->fetchOne(
            'SELECT s.*, p.plan_name, p.plan_code, p.price_amount, p.currency, p.employee_limit, p.is_contact_only, p.feature_flags
             FROM hris_company_subscriptions s
             INNER JOIN hris_subscription_plans p ON p.id = s.plan_id
             WHERE s.company_id = :company_id
             ORDER BY
                CASE s.status
                    WHEN \'active\' THEN 1
                    WHEN \'trialing\' THEN 2
                    WHEN \'past_due\' THEN 3
                    WHEN \'canceled\' THEN 4
                    WHEN \'expired\' THEN 5
                    ELSE 6
                END,
                s.id DESC
             LIMIT 1',
            ['company_id' => $companyId]
        );
    }

    public function featureKeyForPath(string $path): ?string
    {
        $normalizedPath = rtrim($path, '/');

        if ($normalizedPath === '') {
            $normalizedPath = '/';
        }

        foreach (self::FEATURE_ROUTE_MAP as $prefix => $featureKey) {
            if ($normalizedPath === $prefix || str_starts_with($normalizedPath, $prefix . '/')) {
                return $featureKey;
            }
        }

        return null;
    }

    public function featureLabel(string $featureKey): string
    {
        if (isset(self::FEATURE_LABELS[$featureKey])) {
            return self::FEATURE_LABELS[$featureKey];
        }

        return ucwords(str_replace('_', ' ', $featureKey));
    }

    /**
     * @return array{allowed: bool, feature_key: ?string, feature_label: ?string, plan_name: ?string}
     */
    public function accessDecisionForPath(string $path, ?int $companyId, ?int $fallbackPlanId = null): array
    {
        $featureKey = $this->featureKeyForPath($path);

        if ($featureKey === null) {
            return [
                'allowed' => true,
                'feature_key' => null,
                'feature_label' => null,
                'plan_name' => null,
            ];
        }

        $plan = $this->entitlementPlanForCompany($companyId, $fallbackPlanId);
        $featureFlags = $this->featureFlagsFromPlan($plan);

        return [
            'allowed' => in_array($featureKey, $featureFlags, true),
            'feature_key' => $featureKey,
            'feature_label' => $this->featureLabel($featureKey),
            'plan_name' => is_array($plan) ? (string) ($plan['plan_name'] ?? '') : null,
        ];
    }

    public function subscriptionHistoryForCompany(int $companyId, int $limit = 10): array
    {
        return $this->fetchAll(
            'SELECT s.id, s.status, s.billing_cycle, s.starts_at, s.ends_at, s.trial_ends_at,
                    s.activated_at, s.canceled_at, s.cancel_reason,
                    p.plan_name, p.plan_code, p.price_amount, p.currency
             FROM hris_company_subscriptions s
             INNER JOIN hris_subscription_plans p ON p.id = s.plan_id
             WHERE s.company_id = :company_id
             ORDER BY s.id DESC
             LIMIT ' . max(1, (int) $limit),
            ['company_id' => $companyId]
        );
    }

    public function transactionsForCompany(int $companyId, int $limit = 20): array
    {
        return $this->fetchAll(
            'SELECT t.id, t.test_mode, t.status, t.amount, t.currency, t.reference_code,
                    t.notes, t.processed_at, t.created_at,
                    p.plan_name, p.plan_code
             FROM hris_subscription_transactions t
             INNER JOIN hris_subscription_plans p ON p.id = t.plan_id
             WHERE t.company_id = :company_id
             ORDER BY t.id DESC
             LIMIT ' . max(1, (int) $limit),
            ['company_id' => $companyId]
        );
    }

    public function hasValidAccessForCompany(int $companyId): bool
    {
        $subscription = $this->currentSubscriptionForCompany($companyId);

        if ($subscription === null) {
            return false;
        }

        return $this->isSubscriptionCurrentlyValid($subscription);
    }

    private function entitlementPlanForCompany(?int $companyId, ?int $fallbackPlanId = null): ?array
    {
        if ($companyId !== null) {
            $current = $this->currentSubscriptionForCompany($companyId);

            if (is_array($current)) {
                return $current;
            }
        }

        if ($fallbackPlanId !== null && $fallbackPlanId > 0) {
            return $this->findPlanById($fallbackPlanId);
        }

        return $this->defaultTestingPlan();
    }

    private function featureFlagsFromPlan(?array $plan): array
    {
        if (!is_array($plan)) {
            return [];
        }

        $decoded = json_decode((string) ($plan['feature_flags'] ?? '[]'), true);

        if (!is_array($decoded)) {
            return [];
        }

        $features = [];

        foreach ($decoded as $feature) {
            if (!is_string($feature)) {
                continue;
            }

            $value = trim($feature);

            if ($value === '') {
                continue;
            }

            $features[] = strtolower($value);
        }

        return array_values(array_unique($features));
    }

    public function statusSummary(?array $subscription): array
    {
        if ($subscription === null) {
            return [
                'key' => 'none',
                'label' => 'No subscription',
                'is_valid' => false,
                'tone' => 'danger',
                'message' => 'No active trial or subscription found for this company.',
            ];
        }

        $status = (string) ($subscription['status'] ?? 'none');
        $isValid = $this->isSubscriptionCurrentlyValid($subscription);

        if ($isValid && $status === 'active') {
            return [
                'key' => 'active',
                'label' => 'Active',
                'is_valid' => true,
                'tone' => 'success',
                'message' => 'Your subscription is active. Access to protected modules is enabled.',
            ];
        }

        if ($isValid && $status === 'trialing') {
            $trialEnds = (string) ($subscription['trial_ends_at'] ?? '');

            return [
                'key' => 'trialing',
                'label' => 'Trialing',
                'is_valid' => true,
                'tone' => 'warning',
                'message' => $trialEnds !== ''
                    ? 'Trial active until ' . $trialEnds . '.'
                    : 'Trial active. Set a paid plan before trial expiry.',
            ];
        }

        return match ($status) {
            'past_due' => [
                'key' => 'past_due',
                'label' => 'Past Due',
                'is_valid' => false,
                'tone' => 'danger',
                'message' => 'Payment is overdue. Complete checkout to restore access.',
            ],
            'canceled' => [
                'key' => 'canceled',
                'label' => 'Canceled',
                'is_valid' => false,
                'tone' => 'danger',
                'message' => 'This subscription was canceled. Select a plan to reactivate access.',
            ],
            'expired', 'trialing', 'active' => [
                'key' => 'expired',
                'label' => 'Expired',
                'is_valid' => false,
                'tone' => 'danger',
                'message' => 'The subscription period has expired. Select a plan to continue.',
            ],
            default => [
                'key' => 'none',
                'label' => 'Unavailable',
                'is_valid' => false,
                'tone' => 'danger',
                'message' => 'Subscription details are not available. Please contact an administrator.',
            ],
        };
    }

    public function processTestCheckout(int $companyId, int $planId, string $testMode): array
    {
        $plan = $this->findPlanById($planId);

        if ($plan === null) {
            return [
                'result' => 'error',
                'message' => 'Selected plan does not exist.',
            ];
        }

        if ((int) ($plan['is_contact_only'] ?? 0) === 1) {
            return [
                'result' => 'contact_only',
                'message' => 'Enterprise plan is currently contact-sales only for testing.',
                'plan' => $plan,
            ];
        }

        $transactionStatus = $this->transactionStatusForMode($testMode);
        $processedAt = $transactionStatus === 'pending' ? null : date('Y-m-d H:i:s');
        $subscriptionId = null;

        $this->db->beginTransaction();

        try {
            $active = $this->activeOrTrialForCompany($companyId);

            if ($testMode === 'test_success') {
                $startsAt = date('Y-m-d');
                $endsAt = date('Y-m-d', strtotime('+3 months'));

                if ($active !== null) {
                    $subscriptionId = (int) $active['id'];

                    $this->execute(
                        'UPDATE hris_company_subscriptions SET
                            plan_id = :plan_id,
                            billing_cycle = :billing_cycle,
                            status = :status,
                            starts_at = :starts_at,
                            ends_at = :ends_at,
                            trial_ends_at = NULL,
                            activated_at = NOW(),
                            canceled_at = NULL,
                            cancel_reason = NULL,
                            metadata = :metadata
                         WHERE id = :id',
                        [
                            'id' => $subscriptionId,
                            'plan_id' => (int) $plan['id'],
                            'billing_cycle' => 'quarterly',
                            'status' => 'active',
                            'starts_at' => $startsAt,
                            'ends_at' => $endsAt,
                            'metadata' => json_encode(['source' => 'test_checkout', 'mode' => $testMode], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                        ]
                    );
                } else {
                    $this->execute(
                        'INSERT INTO hris_company_subscriptions (
                            company_id, plan_id, billing_cycle, status,
                            starts_at, ends_at, trial_ends_at, activated_at, metadata
                         ) VALUES (
                            :company_id, :plan_id, :billing_cycle, :status,
                            :starts_at, :ends_at, NULL, NOW(), :metadata
                         )',
                        [
                            'company_id' => $companyId,
                            'plan_id' => (int) $plan['id'],
                            'billing_cycle' => 'quarterly',
                            'status' => 'active',
                            'starts_at' => $startsAt,
                            'ends_at' => $endsAt,
                            'metadata' => json_encode(['source' => 'test_checkout', 'mode' => $testMode], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                        ]
                    );

                    $subscriptionId = (int) $this->db->lastInsertId();
                }

                $this->execute(
                    'UPDATE hris_company_subscriptions
                     SET status = :status,
                         ends_at = COALESCE(ends_at, CURDATE())
                     WHERE company_id = :company_id
                       AND id <> :id
                       AND status IN (\"trialing\", \"active\")',
                    [
                        'status' => 'expired',
                        'company_id' => $companyId,
                        'id' => $subscriptionId,
                    ]
                );
            }

            if ($testMode !== 'test_success' && $active !== null) {
                $subscriptionId = (int) $active['id'];
            }

            $referenceCode = $this->generateReferenceCode();
            $payload = json_encode(
                [
                    'mode' => $testMode,
                    'result' => $transactionStatus,
                    'plan_code' => (string) ($plan['plan_code'] ?? ''),
                ],
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            );

            $this->execute(
                'INSERT INTO hris_subscription_transactions (
                    company_subscription_id, company_id, plan_id, provider, test_mode, status,
                    amount, currency, reference_code, notes, payload, processed_at
                 ) VALUES (
                    :company_subscription_id, :company_id, :plan_id, :provider, :test_mode, :status,
                    :amount, :currency, :reference_code, :notes, :payload, :processed_at
                 )',
                [
                    'company_subscription_id' => $subscriptionId,
                    'company_id' => $companyId,
                    'plan_id' => (int) $plan['id'],
                    'provider' => 'test',
                    'test_mode' => $testMode,
                    'status' => $transactionStatus,
                    'amount' => (float) $plan['price_amount'],
                    'currency' => (string) ($plan['currency'] ?? 'PHP'),
                    'reference_code' => $referenceCode,
                    'notes' => 'Checkout simulated via test provider',
                    'payload' => is_string($payload) ? $payload : null,
                    'processed_at' => $processedAt,
                ]
            );

            $transactionId = (int) $this->db->lastInsertId();

            $this->db->commit();

            return [
                'result' => $transactionStatus,
                'message' => $this->checkoutMessageForStatus($transactionStatus),
                'transaction_id' => $transactionId,
                'subscription_id' => $subscriptionId,
                'reference_code' => $referenceCode,
                'plan' => $plan,
            ];
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function cancelCurrentSubscription(int $companyId, string $reason): bool
    {
        $active = $this->activeOrTrialForCompany($companyId);

        if ($active === null) {
            return false;
        }

        return $this->execute(
            'UPDATE hris_company_subscriptions
             SET status = :status,
                 canceled_at = NOW(),
                 cancel_reason = :cancel_reason,
                 ends_at = COALESCE(ends_at, CURDATE())
             WHERE id = :id',
            [
                'status' => 'canceled',
                'cancel_reason' => $reason !== '' ? $reason : 'Canceled from billing screen',
                'id' => (int) $active['id'],
            ]
        );
    }

    private function activeOrTrialForCompany(int $companyId): ?array
    {
        return $this->fetchOne(
            'SELECT *
             FROM hris_company_subscriptions
             WHERE company_id = :company_id
               AND status IN (\"trialing\", \"active\")
             ORDER BY id DESC
             LIMIT 1',
            ['company_id' => $companyId]
        );
    }

    private function checkoutMessageForStatus(string $status): string
    {
        return match ($status) {
            'success' => 'Subscription activated successfully for the current quarter.',
            'pending' => 'Checkout is pending. Complete payment to activate access.',
            'failed' => 'Checkout failed in test mode. Please retry with another test outcome.',
            default => 'Checkout state is unknown. Please retry.',
        };
    }

    private function transactionStatusForMode(string $mode): string
    {
        return match ($mode) {
            'test_success' => 'success',
            'test_fail' => 'failed',
            default => 'pending',
        };
    }

    private function isSubscriptionCurrentlyValid(array $subscription): bool
    {
        $status = (string) ($subscription['status'] ?? '');
        $today = new DateTimeImmutable('today');

        if ($status === 'active') {
            $endsAt = $this->parseDate((string) ($subscription['ends_at'] ?? ''));

            return $endsAt === null || $endsAt >= $today;
        }

        if ($status === 'trialing') {
            $trialEndsAt = $this->parseDate((string) ($subscription['trial_ends_at'] ?? ''));

            return $trialEndsAt === null || $trialEndsAt >= $today;
        }

        return false;
    }

    private function parseDate(string $value): ?DateTimeImmutable
    {
        if ($value === '') {
            return null;
        }

        try {
            return new DateTimeImmutable($value);
        } catch (\Throwable) {
            return null;
        }
    }

    private function generateReferenceCode(): string
    {
        try {
            $suffix = strtoupper(bin2hex(random_bytes(4)));
        } catch (\Throwable) {
            $suffix = strtoupper(dechex((int) (microtime(true) * 1000000)));
        }

        return 'TXN-' . $suffix . '-' . time();
    }

    private function seedDefaultPlansIfEmpty(): void
    {
        $defaultPlans = [
            [
                'plan_code' => 'STARTER-Q',
                'plan_name' => 'Starter',
                'description' => 'Core HR workflow for small teams',
                'billing_cycle' => 'quarterly',
                'interval_months' => 3,
                'price_amount' => 2999.00,
                'currency' => 'PHP',
                'employee_limit' => 25,
                'is_contact_only' => 0,
                'feature_flags' => '["employees","attendance","leave"]',
                'is_active' => 1,
                'sort_order' => 10,
            ],
            [
                'plan_code' => 'GROWTH-Q',
                'plan_name' => 'Growth',
                'description' => 'Expanded HR operations with payroll and admin controls',
                'billing_cycle' => 'quarterly',
                'interval_months' => 3,
                'price_amount' => 6999.00,
                'currency' => 'PHP',
                'employee_limit' => 200,
                'is_contact_only' => 0,
                'feature_flags' => '["employees","attendance","leave","payroll","settings"]',
                'is_active' => 1,
                'sort_order' => 20,
            ],
            [
                'plan_code' => 'ENTERPRISE-Q',
                'plan_name' => 'Enterprise',
                'description' => 'Custom enterprise setup with dedicated support',
                'billing_cycle' => 'quarterly',
                'interval_months' => 3,
                'price_amount' => 19999.00,
                'currency' => 'PHP',
                'employee_limit' => null,
                'is_contact_only' => 1,
                'feature_flags' => '["employees","attendance","leave","payroll","settings","priority_support"]',
                'is_active' => 1,
                'sort_order' => 30,
            ],
        ];

        foreach ($defaultPlans as $plan) {
            $this->execute(
                'INSERT INTO hris_subscription_plans (
                    plan_code, plan_name, description, billing_cycle, interval_months,
                    price_amount, currency, employee_limit, is_contact_only, feature_flags, is_active, sort_order
                 ) VALUES (
                    :plan_code, :plan_name, :description, :billing_cycle, :interval_months,
                    :price_amount, :currency, :employee_limit, :is_contact_only, :feature_flags, :is_active, :sort_order
                 )
                 ON DUPLICATE KEY UPDATE
                    plan_name = VALUES(plan_name),
                    description = VALUES(description),
                    billing_cycle = VALUES(billing_cycle),
                    interval_months = VALUES(interval_months),
                    price_amount = VALUES(price_amount),
                    currency = VALUES(currency),
                    employee_limit = VALUES(employee_limit),
                    is_contact_only = VALUES(is_contact_only),
                    feature_flags = VALUES(feature_flags),
                    is_active = VALUES(is_active),
                    sort_order = VALUES(sort_order)',
                [
                    'plan_code' => $plan['plan_code'],
                    'plan_name' => $plan['plan_name'],
                    'description' => $plan['description'],
                    'billing_cycle' => $plan['billing_cycle'],
                    'interval_months' => $plan['interval_months'],
                    'price_amount' => $plan['price_amount'],
                    'currency' => $plan['currency'],
                    'employee_limit' => $plan['employee_limit'],
                    'is_contact_only' => $plan['is_contact_only'],
                    'feature_flags' => $plan['feature_flags'],
                    'is_active' => $plan['is_active'],
                    'sort_order' => $plan['sort_order'],
                ]
            );
        }
    }
}

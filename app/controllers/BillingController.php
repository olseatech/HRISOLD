<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Audit;
use App\Core\Auth;
use App\Core\Controller;
use App\Core\CSRF;
use App\Core\Session;
use App\Core\Validator;
use App\Models\Subscription;

final class BillingController extends Controller
{
    private const TRANSACTION_LIMIT = 12;
    private const HISTORY_LIMIT = 8;

    private Subscription $subscriptions;

    public function __construct()
    {
        $this->subscriptions = new Subscription();
    }

    public function index(): void
    {
        $user = Auth::user();

        if ($user === null) {
            $this->redirect('/login');
        }

        $companyId = $this->subscriptions->resolveCompanyIdForUser((int) $user['id']);
        $current = $companyId !== null ? $this->subscriptions->currentSubscriptionForCompany($companyId) : null;

        $selectedPlanId = (int) (Session::get('billing.pending_plan_id', (int) ($current['plan_id'] ?? 0)));
        $selectedCycle = (string) Session::get('billing.pending_cycle', 'quarterly');

        $this->view('billing/index', [
            'title' => 'Billing & Subscription',
            'csrf' => CSRF::token(),
            'user' => $user,
            'companyId' => $companyId,
            'current' => $current,
            'statusSummary' => $this->subscriptions->statusSummary($current),
            'plans' => $this->subscriptions->publicPlans(),
            'transactions' => $companyId !== null ? $this->subscriptions->transactionsForCompany($companyId, self::TRANSACTION_LIMIT) : [],
            'history' => $companyId !== null ? $this->subscriptions->subscriptionHistoryForCompany($companyId, self::HISTORY_LIMIT) : [],
            'selectedPlanId' => $selectedPlanId,
            'selectedCycle' => $selectedCycle,
            'canManageBilling' => can('billing.manage'),
            'continuePath' => role_landing_path($user),
            'isTestingMode' => is_subscription_testing_mode(),
            'success' => Session::pullFlash('success'),
            'error' => Session::pullFlash('error'),
            'errors' => Session::pullFlash('errors', []),
            'old' => Session::pullFlash('old', []),
        ]);
    }

    public function checkout(): void
    {
        $user = Auth::user();

        if ($user === null) {
            $this->redirect('/login');
        }

        $companyId = $this->subscriptions->resolveCompanyIdForUser((int) $user['id']);

        if ($companyId === null) {
            Session::flash('error', 'Company profile is missing for the authenticated user.');
            $this->redirect('/billing');
        }

        $data = [
            'plan_id' => trim((string) ($_POST['plan_id'] ?? '')),
            'billing_cycle' => trim((string) ($_POST['billing_cycle'] ?? 'quarterly')),
            'test_mode' => trim((string) ($_POST['test_mode'] ?? 'test_success')),
        ];

        $errors = Validator::required($data, ['plan_id', 'billing_cycle', 'test_mode']);
        $errors = array_merge($errors, Validator::inSet($data, 'billing_cycle', ['quarterly']));
        $errors = array_merge($errors, Validator::inSet($data, 'test_mode', ['test_success', 'test_pending', 'test_fail']));

        if ($data['plan_id'] !== '' && !ctype_digit($data['plan_id'])) {
            $errors['plan_id'] = 'Selected plan is invalid.';
        }

        if ($errors !== []) {
            Session::flash('errors', $errors);
            Session::flash('old', $data);
            Session::flash('error', 'Please fix billing form errors and try again.');
            $this->redirect('/billing');
        }

        $planId = (int) $data['plan_id'];
        $plan = $this->subscriptions->findPlanById($planId);

        if ($plan === null) {
            Session::flash('error', 'Selected plan is unavailable.');
            $this->redirect('/billing');
        }

        Session::set('billing.pending_plan_id', $planId);
        Session::set('billing.pending_cycle', 'quarterly');

        if ((int) ($plan['is_contact_only'] ?? 0) === 1) {
            Audit::log('billing', 'PLAN_SELECT', $planId, null, [
                'source' => 'billing_screen',
                'plan_code' => (string) ($plan['plan_code'] ?? ''),
                'billing_cycle' => 'quarterly',
                'mode' => 'contact_only',
            ]);

            Session::flash('success', 'Enterprise plan is contact-sales only for now. Reach out to proceed.');
            $this->redirect('/billing');
        }

        try {
            $result = $this->subscriptions->processTestCheckout($companyId, $planId, $data['test_mode']);
        } catch (\Throwable) {
            Session::flash('error', 'Checkout simulation failed unexpectedly. Please retry.');
            $this->redirect('/billing');
            return;
        }

        $transactionId = isset($result['transaction_id']) ? (int) $result['transaction_id'] : null;

        Audit::log('billing', 'CHECKOUT', $transactionId, null, [
            'plan_id' => $planId,
            'plan_code' => (string) ($plan['plan_code'] ?? ''),
            'billing_cycle' => 'quarterly',
            'test_mode' => $data['test_mode'],
            'result' => (string) ($result['result'] ?? 'unknown'),
            'reference_code' => (string) ($result['reference_code'] ?? ''),
        ]);

        $checkoutResult = (string) ($result['result'] ?? 'unknown');

        if ($checkoutResult === 'success') {
            Session::remove('billing.pending_plan_id');
            Session::remove('billing.pending_cycle');
            Session::flash('success', (string) ($result['message'] ?? 'Subscription activated successfully.'));
            $this->redirect('/billing');
        }

        if ($checkoutResult === 'pending') {
            if (is_subscription_testing_mode()) {
                Session::flash('success', 'Checkout simulation is pending. Testing access continues based on your selected plan.');
            } else {
                Session::flash('error', (string) ($result['message'] ?? 'Checkout is pending. Please complete payment.'));
            }
            $this->redirect('/billing');
        }

        if ($checkoutResult === 'failed') {
            if (is_subscription_testing_mode()) {
                Session::flash('error', 'Checkout simulation failed. Testing access still follows your selected plan, and you can retry anytime.');
            } else {
                Session::flash('error', (string) ($result['message'] ?? 'Checkout failed. Retry with another test mode.'));
            }
            $this->redirect('/billing');
        }

        Session::flash('error', 'Unable to process checkout result. Please retry.');
        $this->redirect('/billing');
    }

    public function cancel(): void
    {
        $user = Auth::user();

        if ($user === null) {
            $this->redirect('/login');
        }

        $companyId = $this->subscriptions->resolveCompanyIdForUser((int) $user['id']);

        if ($companyId === null) {
            Session::flash('error', 'Company profile is missing.');
            $this->redirect('/billing');
        }

        $reason = trim((string) ($_POST['cancel_reason'] ?? ''));
        $before = $this->subscriptions->currentSubscriptionForCompany($companyId);

        if ($before === null) {
            Session::flash('error', 'There is no subscription to cancel.');
            $this->redirect('/billing');
        }

        if (!in_array((string) ($before['status'] ?? ''), ['trialing', 'active', 'past_due'], true)) {
            Session::flash('error', 'Only trialing, active, or past due subscriptions can be canceled.');
            $this->redirect('/billing');
        }

        if (!$this->subscriptions->cancelCurrentSubscription($companyId, $reason)) {
            Session::flash('error', 'Unable to cancel subscription at this time.');
            $this->redirect('/billing');
        }

        $after = $this->subscriptions->currentSubscriptionForCompany($companyId);

        Audit::log('billing', 'CANCEL', (int) ($before['id'] ?? 0), $before, $after);

        Session::flash('success', 'Subscription canceled successfully. Select a plan to reactivate access.');
        $this->redirect('/billing');
    }

    public function continueToApp(): void
    {
        $user = Auth::user();

        if ($user === null) {
            $this->redirect('/login');
        }

        if (is_subscription_testing_mode()) {
            $this->redirect(role_landing_path($user));
        }

        $companyId = $this->subscriptions->resolveCompanyIdForUser((int) $user['id']);

        if ($companyId === null || !$this->subscriptions->hasValidAccessForCompany($companyId)) {
            Session::flash('error', 'Subscription access is not active. Complete billing to continue.');
            $this->redirect('/billing');
        }

        $this->redirect(role_landing_path($user));
    }
}

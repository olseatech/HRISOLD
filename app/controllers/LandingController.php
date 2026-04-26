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

final class LandingController extends Controller
{
    private Subscription $subscriptions;

    public function __construct()
    {
        $this->subscriptions = new Subscription();
    }

    public function index(): void
    {
        if (Auth::check()) {
            $this->redirect(post_auth_entry_path(Auth::user()));
        }

        $this->view('public/landing', [
            'title' => 'HRIS Platform',
            'csrf' => CSRF::token(),
            'plans' => $this->subscriptions->publicPlans(),
            'success' => Session::pullFlash('success'),
            'error' => Session::pullFlash('error'),
            'errors' => Session::pullFlash('errors', []),
        ], 'public');
    }

    public function pricing(): void
    {
        if (Auth::check()) {
            $this->redirect(post_auth_entry_path(Auth::user()));
        }

        $this->view('public/pricing', [
            'title' => 'Plans and Feature Access',
            'csrf' => CSRF::token(),
            'plans' => $this->subscriptions->publicPlans(),
            'success' => Session::pullFlash('success'),
            'error' => Session::pullFlash('error'),
            'errors' => Session::pullFlash('errors', []),
        ], 'public');
    }

    public function subscribe(): void
    {
        $data = [
            'plan_id' => trim((string) ($_POST['plan_id'] ?? '')),
            'billing_cycle' => trim((string) ($_POST['billing_cycle'] ?? 'quarterly')),
        ];

        $availablePlans = $this->subscriptions->publicPlans();

        if ($availablePlans === []) {
            Session::flash('error', 'No active quarterly plans are available. Re-run the seed scripts and try again.');
            $this->redirect('/pricing');
        }

        if ($data['plan_id'] === '') {
            $fallback = null;

            foreach ($availablePlans as $candidate) {
                if ((int) ($candidate['is_contact_only'] ?? 0) === 0) {
                    $fallback = $candidate;
                    break;
                }
            }

            if ($fallback === null) {
                $fallback = $availablePlans[0] ?? null;
            }

            if (is_array($fallback) && isset($fallback['id'])) {
                $data['plan_id'] = (string) (int) $fallback['id'];
            }
        }

        $errors = [];

        if ($data['plan_id'] === '') {
            $errors['plan_id'] = 'Please select a plan before continuing.';
        }

        if ($data['billing_cycle'] === '') {
            $errors['billing_cycle'] = 'Billing cycle is required.';
        }

        $errors = array_merge($errors, Validator::inSet($data, 'billing_cycle', ['quarterly']));

        if ($data['plan_id'] !== '' && !ctype_digit($data['plan_id'])) {
            $errors['plan_id'] = 'Selected plan is invalid.';
        }

        if ($errors !== []) {
            Session::flash('errors', $errors);
            Session::flash('error', 'Unable to process plan selection.');
            $this->redirect('/pricing');
        }

        $plan = $this->subscriptions->findPlanById((int) $data['plan_id']);

        if (!$plan) {
            Session::flash('error', 'Selected plan is unavailable.');
            $this->redirect('/pricing');
        }

        Session::set('billing.pending_plan_id', (int) $plan['id']);
        Session::set('billing.pending_cycle', 'quarterly');

        Audit::log('billing', 'PLAN_SELECT', (int) $plan['id'], null, [
            'source' => 'public_landing',
            'plan_code' => (string) ($plan['plan_code'] ?? ''),
            'billing_cycle' => 'quarterly',
        ]);

        if ((int) ($plan['is_contact_only'] ?? 0) === 1) {
            Session::flash('success', 'Enterprise plan selected. Continue to Billing for assisted onboarding steps.');
        } else {
            if (is_subscription_testing_mode()) {
                Session::flash('success', 'Plan selected. Testing access profile is now active for included modules. Checkout simulation is optional.');
            } else {
                Session::flash('success', 'Plan selected. Sign in to complete quarterly subscription checkout.');
            }
        }

        if (Auth::check()) {
            $this->redirect(post_auth_entry_path(Auth::user()));
        }

        $this->redirect('/login');
    }
}

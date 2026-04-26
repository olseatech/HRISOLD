<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Auth;
use App\Core\Session;
use App\Models\Subscription;

final class SubscriptionMiddleware
{
    public static function handle(): bool
    {
        if (!Auth::check()) {
            header('Location: /login');
            return false;
        }

        $path = (string) (parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH) ?: '/');
        $path = rtrim($path, '/') ?: '/';

        if ($path === '/billing' || str_starts_with($path, '/billing/')) {
            return true;
        }

        $userId = Auth::id();

        if ($userId === null) {
            Session::flash('error', 'Session context is invalid. Please sign in again.');
            header('Location: /login');
            return false;
        }

        $subscription = new Subscription();
        $companyId = $subscription->resolveCompanyIdForUser($userId);

        $paymentRequired = \subscription_payment_required();
        $featureLocksEnabled = \subscription_feature_locks_enabled();
        $isTestingMode = \is_subscription_testing_mode();

        if ($paymentRequired) {
            if ($companyId === null) {
                Session::flash('error', 'Company profile was not found. Please configure billing access first.');
                header('Location: /billing');
                return false;
            }

            if (!$subscription->hasValidAccessForCompany($companyId)) {
                Session::flash('error', 'Your subscription is not active. Complete billing to continue.');
                header('Location: /billing');
                return false;
            }
        }

        if (!$featureLocksEnabled) {
            return true;
        }

        $fallbackPlanId = (int) Session::get('billing.pending_plan_id', 0);

        if ($isTestingMode && $fallbackPlanId <= 0) {
            $defaultPlan = $subscription->defaultTestingPlan();

            if (is_array($defaultPlan) && isset($defaultPlan['id'])) {
                $fallbackPlanId = (int) $defaultPlan['id'];
                Session::set('billing.pending_plan_id', $fallbackPlanId);
                Session::set('billing.pending_cycle', 'quarterly');
            }
        }

        $companyIdForFeatureLock = $isTestingMode ? null : $companyId;
        $decision = $subscription->accessDecisionForPath(
            $path,
            $companyIdForFeatureLock,
            $fallbackPlanId > 0 ? $fallbackPlanId : null
        );

        if ((bool) ($decision['allowed'] ?? false)) {
            return true;
        }

        $featureLabel = (string) ($decision['feature_label'] ?? 'This module');
        $planName = trim((string) ($decision['plan_name'] ?? ''));

        if ($isTestingMode) {
            $planContext = $planName !== '' ? ' in your selected testing plan (' . $planName . ')' : ' in your selected testing plan';
            Session::flash('error', $featureLabel . ' is locked' . $planContext . '. Choose another plan in Billing to unlock this module.');
        } else {
            $planContext = $planName !== '' ? ' in your current plan (' . $planName . ')' : ' in your current plan';
            Session::flash('error', $featureLabel . ' is not included' . $planContext . '. Update your plan in Billing to continue.');
        }

        header('Location: /billing');

        return false;
    }
}

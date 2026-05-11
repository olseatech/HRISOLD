<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Audit;
use App\Core\Auth;
use App\Core\Controller;
use App\Core\CSRF;
use App\Core\Session;
use App\Models\User;

final class AuthController extends Controller
{
    /** Max login attempts per IP within the rate-limit window */
    private const IP_RATE_LIMIT     = 20;
    /** Window size in seconds (5 minutes) */
    private const IP_RATE_WINDOW    = 300;

    public function showLogin(): void
    {
        if (Auth::check()) {
            if (super_admin_only_mode_enabled() && !is_super_admin_user(Auth::user())) {
                Auth::logout();
                Session::flash('error', 'Only the Super Admin account can access this environment right now.');
                $this->redirect('/login');
            }

            $this->redirect(post_auth_entry_path(Auth::user()));
        }

        $this->view('auth/login', [
            'title' => 'Sign in',
            'csrf'  => CSRF::token(),
            'error' => Session::pullFlash('error'),
        ], 'auth');
    }

    public function login(): void
    {
        // ── IP-based rate limiting ────────────────────────────────
        $this->enforceIpRateLimit();

        // ── CSRF check ────────────────────────────────────────────
        $token = $_POST['_csrf'] ?? null;
        if (!CSRF::verify(is_string($token) ? $token : null)) {
            Session::flash('error', 'Your session token is invalid. Please try again.');
            $this->redirect('/login');
        }

        $identity = trim((string) ($_POST['identity'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        if ($identity === '' || $password === '') {
            Session::flash('error', 'Username or email and password are required.');
            $this->redirect('/login');
        }

        // Generic message prevents leaking whether account exists or is locked
        if (!Auth::attempt($identity, $password)) {
            Session::flash('error', 'Invalid credentials. Please try again.');
            $this->redirect('/login');
        }

        $user = Auth::user();

        if (super_admin_only_mode_enabled() && !is_super_admin_user($user)) {
            Auth::logout();
            Session::flash('error', 'Only the Super Admin account can access this environment right now.');
            $this->redirect('/login');
        }

        // Successful login — clear IP rate limit counter
        $this->clearIpRateLimit();

        $this->redirect(post_auth_entry_path($user));
    }

    public function logout(): void
    {
        Auth::logout();
        $this->redirect('/login');
    }

    public function showChangePassword(): void
    {
        $this->view('auth/change-password', [
            'title'  => 'Change Password',
            'csrf'   => CSRF::token(),
            'error'  => Session::pullFlash('error'),
            'errors' => Session::pullFlash('errors', []),
            'success'=> Session::pullFlash('success'),
        ]);
    }

    public function changePassword(): void
    {
        $token = $_POST['_csrf'] ?? null;
        if (!CSRF::verify(is_string($token) ? $token : null)) {
            Session::flash('error', 'Session token invalid. Please try again.');
            $this->redirect('/change-password');
        }

        $authUser    = Auth::user();
        $userId      = (int) ($authUser['id'] ?? 0);
        $currentPass = (string) ($_POST['current_password'] ?? '');
        $newPass     = (string) ($_POST['new_password'] ?? '');
        $confirmPass = (string) ($_POST['confirm_password'] ?? '');

        $errors = [];

        if ($currentPass === '') {
            $errors['current_password'] = 'Current password is required.';
        }
        if (strlen($newPass) < 8) {
            $errors['new_password'] = 'New password must be at least 8 characters.';
        }
        if ($newPass !== $confirmPass) {
            $errors['confirm_password'] = 'Passwords do not match.';
        }

        if ($errors !== []) {
            Session::flash('errors', $errors);
            Session::flash('error', 'Please fix the errors below.');
            $this->redirect('/change-password');
        }

        $userModel = new User();
        $user      = $userModel->findById($userId);

        if (!$user || !password_verify($currentPass, (string) ($user['password_hash'] ?? ''))) {
            Session::flash('errors', ['current_password' => 'Current password is incorrect.']);
            Session::flash('error', 'Current password is incorrect.');
            $this->redirect('/change-password');
        }

        $userModel->updatePassword($userId, password_hash($newPass, PASSWORD_BCRYPT));
        Audit::log('auth', 'UPDATE', $userId, null, ['action' => 'password_changed']);

        Session::flash('success', 'Password changed successfully.');
        $this->redirect('/change-password');
    }

    // ── IP rate limiting helpers ──────────────────────────────────

    private function enforceIpRateLimit(): void
    {
        $ip        = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $cacheFile = $this->ipCacheFile($ip);
        $data      = $this->readRateLimitFile($cacheFile);

        // Reset window if it has expired
        if ((time() - (int) ($data['window_start'] ?? 0)) > self::IP_RATE_WINDOW) {
            $data = ['count' => 0, 'window_start' => time()];
        }

        $data['count'] = ((int) ($data['count'] ?? 0)) + 1;
        $this->writeRateLimitFile($cacheFile, $data);

        if ((int) $data['count'] > self::IP_RATE_LIMIT) {
            http_response_code(429);
            Session::flash('error', 'Too many login attempts. Please wait a few minutes before trying again.');
            $this->redirect('/login');
        }
    }

    private function clearIpRateLimit(): void
    {
        $ip        = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $cacheFile = $this->ipCacheFile($ip);

        if (file_exists($cacheFile)) {
            @unlink($cacheFile);
        }
    }

    private function ipCacheFile(string $ip): string
    {
        $dir = dirname(__DIR__, 2) . '/storage/cache';

        return $dir . '/rl_' . md5($ip) . '.json';
    }

    private function readRateLimitFile(string $path): array
    {
        if (!file_exists($path)) {
            return ['count' => 0, 'window_start' => time()];
        }

        $raw = @file_get_contents($path);
        if ($raw === false) {
            return ['count' => 0, 'window_start' => time()];
        }

        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : ['count' => 0, 'window_start' => time()];
    }

    private function writeRateLimitFile(string $path, array $data): void
    {
        $dir = dirname($path);

        if (!is_dir($dir) || !is_writable($dir)) {
            return; // Fail open — don't block login if storage unavailable
        }

        file_put_contents($path, json_encode($data), LOCK_EX);
    }
}

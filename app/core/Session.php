<?php

declare(strict_types=1);

namespace App\Core;

final class Session
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        session_name((string) env('SESSION_NAME', 'hris_session'));

        session_set_cookie_params([
            'lifetime' => ((int) env('SESSION_LIFETIME', 120)) * 60,
            'path'     => '/',
            'domain'   => '',
            'secure'   => (bool) env('SESSION_SECURE', false),
            'httponly' => true,
            'samesite' => (string) env('SESSION_SAMESITE', 'Strict'),
        ]);

        session_start();

        if (!isset($_SESSION['_started_at'])) {
            $_SESSION['_started_at'] = time();
        }

        // Track last activity for server-side expiry enforcement
        if (!isset($_SESSION['_last_activity'])) {
            $_SESSION['_last_activity'] = time();
        }
    }

    /**
     * Enforce server-side session expiry independent of cookie lifetime.
     * Destroys and restarts the session if the user has been inactive beyond SESSION_LIFETIME.
     * Call this after Session::start() on every authenticated request.
     */
    public static function checkExpiry(): void
    {
        $lifetime        = ((int) env('SESSION_LIFETIME', 120)) * 60; // seconds
        $lastActivity    = isset($_SESSION['_last_activity']) ? (int) $_SESSION['_last_activity'] : null;

        if ($lastActivity !== null && (time() - $lastActivity) > $lifetime) {
            // Clear permission cache and auth data before destroying
            self::destroy();
            self::start();
            return;
        }

        $_SESSION['_last_activity'] = time();
    }

    public static function regenerate(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            self::start();
        }

        session_regenerate_id(true);
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function has(string $key): bool
    {
        return array_key_exists($key, $_SESSION);
    }

    public static function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public static function flash(string $key, mixed $value): void
    {
        self::set('_flash.' . $key, $value);
    }

    public static function pullFlash(string $key, mixed $default = null): mixed
    {
        $flashKey = '_flash.' . $key;
        $value    = self::get($flashKey, $default);
        self::remove($flashKey);

        return $value;
    }

    public static function destroy(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return;
        }

        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                (bool) $params['secure'],
                (bool) $params['httponly']
            );
        }

        session_destroy();
    }
}

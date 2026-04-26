<?php

declare(strict_types=1);

namespace App\Core;

use DateTimeImmutable;

final class Auth
{
    public static function check(): bool
    {
        return Session::has('auth_user');
    }

    public static function user(): ?array
    {
        $user = Session::get('auth_user');

        return is_array($user) ? $user : null;
    }

    public static function id(): ?int
    {
        $user = self::user();

        return isset($user['id']) ? (int) $user['id'] : null;
    }

    public static function attempt(string $identity, string $password): bool
    {
        $db = Database::connection();

        $stmt = $db->prepare(
            'SELECT u.id, u.username, u.email, u.password_hash, u.role_id, u.employee_id, u.is_active, u.failed_attempts, u.locked_until, r.role_name
             FROM hris_users u
             INNER JOIN hris_roles r ON r.id = u.role_id
             WHERE u.username = :username OR u.email = :email
             LIMIT 1'
        );

        $stmt->execute([
            'username' => $identity,
            'email'    => $identity,
        ]);
        $user = $stmt->fetch();

        if (!$user || (int) $user['is_active'] !== 1) {
            // Constant-time return to prevent username enumeration via timing
            password_verify($password, '$2y$12$dummyhashfortimingnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnn');
            return false;
        }

        if (self::isLocked($user)) {
            return false;
        }

        if (!password_verify($password, (string) $user['password_hash'])) {
            self::incrementFailedAttempts((int) $user['id'], (int) $user['failed_attempts']);

            // Log failed attempt with IP for security audit
            Audit::log('auth', 'LOGIN_FAILED', (int) $user['id'], null, [
                'username' => $identity,
                'ip'       => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            ]);

            return false;
        }

        self::clearFailedAttempts((int) $user['id']);

        $db->prepare('UPDATE hris_users SET last_login = NOW() WHERE id = :id')
            ->execute(['id' => (int) $user['id']]);

        Session::regenerate();

        // Clear any stale permission cache from a previous session
        Session::remove('_permissions');

        Session::set('auth_user', [
            'id'          => (int) $user['id'],
            'username'    => (string) $user['username'],
            'email'       => (string) $user['email'],
            'role_id'     => (int) $user['role_id'],
            'employee_id' => isset($user['employee_id']) && $user['employee_id'] !== null
                ? (int) $user['employee_id']
                : null,
            'role_name'   => (string) $user['role_name'],
        ]);

        Audit::log('auth', 'LOGIN', (int) $user['id'], null, [
            'username' => (string) $user['username'],
            'email'    => (string) $user['email'],
            'ip'       => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        ]);

        return true;
    }

    public static function logout(): void
    {
        $currentUser = self::user();
        Audit::log('auth', 'LOGOUT', isset($currentUser['id']) ? (int) $currentUser['id'] : null, $currentUser, null);

        Session::remove('auth_user');
        Session::remove('_permissions'); // Clear permission cache on logout
        Session::regenerate();
    }

    /**
     * Check if the authenticated user has a given permission.
     * Results are cached in the session for the duration of the request cycle
     * to avoid repeated DB queries on every can() call.
     */
    public static function can(string $permissionKey): bool
    {
        $user = self::user();
        if (!$user) {
            return false;
        }

        // Return cached result if available
        $cached = Session::get('_permissions', null);
        if (is_array($cached) && array_key_exists($permissionKey, $cached)) {
            return (bool) $cached[$permissionKey];
        }

        $db   = Database::connection();
        $stmt = $db->prepare(
            'SELECT 1
             FROM hris_role_permissions rp
             INNER JOIN hris_permissions p ON p.id = rp.permission_id
             WHERE rp.role_id = :role_id AND p.permission_key = :permission
             LIMIT 1'
        );

        $stmt->execute([
            'role_id'    => (int) $user['role_id'],
            'permission' => $permissionKey,
        ]);

        $result = (bool) $stmt->fetchColumn();

        // Cache result for this session to avoid redundant queries
        $cached                  = is_array($cached) ? $cached : [];
        $cached[$permissionKey]  = $result;
        Session::set('_permissions', $cached);

        return $result;
    }

    private static function isLocked(array $user): bool
    {
        if (empty($user['locked_until'])) {
            return false;
        }

        try {
            $lockedUntil = new DateTimeImmutable((string) $user['locked_until']);
            return $lockedUntil > new DateTimeImmutable('now');
        } catch (\Exception) {
            return false;
        }
    }

    private static function incrementFailedAttempts(int $userId, int $currentAttempts): void
    {
        $db          = Database::connection();
        $maxAttempts = (int) env('AUTH_MAX_ATTEMPTS', 5);
        $lockMinutes = (int) env('AUTH_LOCK_MINUTES', 15);

        $attempts = $currentAttempts + 1;

        if ($attempts >= $maxAttempts) {
            $db->prepare(
                'UPDATE hris_users
                 SET failed_attempts = 0,
                     locked_until = DATE_ADD(NOW(), INTERVAL :lock_minutes MINUTE)
                 WHERE id = :id'
            )->execute([
                'lock_minutes' => $lockMinutes,
                'id'           => $userId,
            ]);
            return;
        }

        $db->prepare('UPDATE hris_users SET failed_attempts = :attempts WHERE id = :id')
            ->execute([
                'attempts' => $attempts,
                'id'       => $userId,
            ]);
    }

    private static function clearFailedAttempts(int $userId): void
    {
        $db = Database::connection();
        $db->prepare('UPDATE hris_users SET failed_attempts = 0, locked_until = NULL WHERE id = :id')
            ->execute(['id' => $userId]);
    }
}

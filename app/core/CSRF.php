<?php

declare(strict_types=1);

namespace App\Core;

final class CSRF
{
    private const TOKEN_KEY = '_csrf_token';

    public static function token(): string
    {
        $token = Session::get(self::TOKEN_KEY);

        if (is_string($token) && $token !== '') {
            return $token;
        }

        $token = bin2hex(random_bytes(32));
        Session::set(self::TOKEN_KEY, $token);

        return $token;
    }

    public static function verify(?string $token): bool
    {
        if (!is_string($token) || $token === '') {
            return false;
        }

        $sessionToken = Session::get(self::TOKEN_KEY);

        if (!is_string($sessionToken) || $sessionToken === '') {
            return false;
        }

        return hash_equals($sessionToken, $token);
    }
}

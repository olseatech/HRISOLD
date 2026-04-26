<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\CSRF;

final class CsrfMiddleware
{
    public static function handle(?string $token): bool
    {
        return CSRF::verify($token);
    }
}

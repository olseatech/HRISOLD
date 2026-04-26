<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Auth;

final class AuthMiddleware
{
    public static function handle(): bool
    {
        return Auth::check();
    }
}

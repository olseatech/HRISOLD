<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Auth;

final class RoleMiddleware
{
    public static function handle(string $permission): bool
    {
        return Auth::can($permission);
    }
}

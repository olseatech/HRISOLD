<?php

declare(strict_types=1);

namespace App\Core;

final class Response
{
    public static function json(array $payload, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    /**
     * Redirect to a path.
     * Only relative paths starting with / are accepted to prevent open-redirect attacks.
     * Protocol-relative URLs (//evil.com) are rejected and fall back to /.
     */
    public static function redirect(string $path): void
    {
        // Reject empty, external URLs, and protocol-relative URLs
        if (
            $path === '' ||
            !str_starts_with($path, '/') ||
            str_starts_with($path, '//')
        ) {
            $path = '/';
        }

        // Strip any CR/LF characters to prevent header injection
        $path = str_replace(["\r", "\n", "\0"], '', $path);

        header('Location: ' . $path, true, 302);
        exit;
    }
}

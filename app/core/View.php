<?php

declare(strict_types=1);

namespace App\Core;

final class View
{
    public static function render(string $viewPath, array $data = []): void
    {
        if (!file_exists($viewPath)) {
            http_response_code(500);
            echo 'View file missing.';
            return;
        }

        extract($data, EXTR_SKIP);
        require $viewPath;
    }
}

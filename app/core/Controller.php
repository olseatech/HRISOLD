<?php

declare(strict_types=1);

namespace App\Core;

abstract class Controller
{
    protected function view(string $view, array $data = [], string $layout = 'app'): void
    {
        $viewPath = __DIR__ . '/../views/' . $view . '.php';
        $layoutPath = __DIR__ . '/../views/layouts/' . $layout . '.php';

        if (!file_exists($viewPath)) {
            http_response_code(500);
            echo 'View not found.';
            return;
        }

        if (!file_exists($layoutPath)) {
            http_response_code(500);
            echo 'Layout not found.';
            return;
        }

        extract($data, EXTR_SKIP);
        $contentView = $viewPath;

        require $layoutPath;
    }

    protected function redirect(string $path): void
    {
        header('Location: ' . $path);
        exit;
    }
}

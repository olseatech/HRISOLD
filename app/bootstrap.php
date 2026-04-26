<?php

declare(strict_types=1);

use App\Core\Session;

require_once __DIR__ . '/helpers/functions.php';

$basePath = dirname(__DIR__);
$envPath = $basePath . '/.env';

if (file_exists($basePath . '/vendor/autoload.php')) {
    require_once $basePath . '/vendor/autoload.php';
}

spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';

    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relativeClass = substr($class, strlen($prefix));
    if ($relativeClass === false || $relativeClass === '') {
        return;
    }

    $segments = explode('\\', $relativeClass);
    $classFile = array_pop($segments);

    if (!is_string($classFile) || $classFile === '') {
        return;
    }

    $classFile .= '.php';
    $baseDir = __DIR__ . DIRECTORY_SEPARATOR;
    $paths = [];

    if ($segments === []) {
        $paths[] = $baseDir . $classFile;
    } else {
        $originalDirectory = implode(DIRECTORY_SEPARATOR, $segments);
        $lowerDirectory = implode(DIRECTORY_SEPARATOR, array_map('strtolower', $segments));

        $paths[] = $baseDir . $originalDirectory . DIRECTORY_SEPARATOR . $classFile;
        if ($lowerDirectory !== $originalDirectory) {
            $paths[] = $baseDir . $lowerDirectory . DIRECTORY_SEPARATOR . $classFile;
        }
    }

    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

$dotenvClass = '\\Dotenv\\Dotenv';

if (class_exists($dotenvClass) && file_exists($envPath)) {
    $dotenvClass::createImmutable($basePath)->safeLoad();
} elseif (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];

    foreach ($lines as $line) {
        $line = trim($line);

        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }

        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value, " \t\n\r\0\x0B\"");

        if ($key !== '') {
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }
}

$appConfig = config('app');
date_default_timezone_set((string) ($appConfig['timezone'] ?? 'UTC'));

Session::start();

// ── Security headers ──────────────────────────────────────────────
// These are also set in .htaccess; sending here ensures coverage
// when Apache mod_headers is unavailable or app is run via CLI/other server.
if (!headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');
}

// ── Server-side session expiry ────────────────────────────────────
// Enforces timeout independently of the cookie lifetime, destroying
// idle/abandoned sessions so they cannot be hijacked later.
Session::checkExpiry();

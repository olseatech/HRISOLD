<?php

declare(strict_types=1);

namespace App\Core;

final class App
{
    private array $routes;

    public function __construct()
    {
        $this->routes = config('routes');
    }

    public function run(): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        $path = rtrim($path, '/') ?: '/';

        $resolved = $this->resolveRoute($method, $path);
        $route = $resolved['route'];
        $params = $resolved['params'];

        if (!$route) {
            http_response_code(404);
            echo '404 Not Found';
            return;
        }

        if (!$this->handleMiddleware($route['middleware'] ?? [])) {
            return;
        }

        $controllerClass = 'App\\Controllers\\' . $route['controller'];
        $methodName = $route['method'];

        if (!class_exists($controllerClass)) {
            http_response_code(500);
            echo 'Controller not found';
            return;
        }

        $controller = new $controllerClass();

        if (!method_exists($controller, $methodName)) {
            http_response_code(500);
            echo 'Controller method not found';
            return;
        }

        $controller->{$methodName}(...array_values($params));
    }

    private function resolveRoute(string $method, string $path): array
    {
        $methodRoutes = $this->routes[$method] ?? [];

        if (isset($methodRoutes[$path])) {
            return [
                'route' => $methodRoutes[$path],
                'params' => [],
            ];
        }

        foreach ($methodRoutes as $pattern => $route) {
            if (!str_contains($pattern, '{')) {
                continue;
            }

            $regex = preg_replace_callback('/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/', static function (array $matches): string {
                return '(?P<' . $matches[1] . '>[^/]+)';
            }, $pattern);

            if (!is_string($regex)) {
                continue;
            }

            $regex = '#^' . str_replace('/', '\\/', $regex) . '$#';

            if (!preg_match($regex, $path, $matches)) {
                continue;
            }

            $params = [];
            foreach ($matches as $key => $value) {
                if (!is_string($key)) {
                    continue;
                }

                $params[$key] = $value;
            }

            return [
                'route' => $route,
                'params' => $params,
            ];
        }

        return [
            'route' => null,
            'params' => [],
        ];
    }

    private function handleMiddleware(array $middlewares): bool
    {
        foreach ($middlewares as $middleware) {
            if ($middleware === 'auth') {
                if (!Auth::check()) {
                    header('Location: /login');
                    return false;
                }

                if (super_admin_only_mode_enabled() && !is_super_admin_user(Auth::user())) {
                    Auth::logout();
                    Session::flash('error', 'Only the Super Admin account can access this environment right now.');
                    header('Location: /login');
                    return false;
                }
            }

            if ($middleware === 'guest' && Auth::check()) {
                if (super_admin_only_mode_enabled() && !is_super_admin_user(Auth::user())) {
                    Auth::logout();
                    Session::flash('error', 'Only the Super Admin account can access this environment right now.');
                    header('Location: /login');
                    return false;
                }

                header('Location: ' . post_auth_entry_path(Auth::user()));
                return false;
            }

            if ($middleware === 'subscription' && Auth::check()) {
                if (!\App\Middleware\SubscriptionMiddleware::handle()) {
                    return false;
                }
            }

            if ($middleware === 'csrf' && in_array($_SERVER['REQUEST_METHOD'] ?? 'GET', ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
                $token = $_POST['_csrf'] ?? null;

                if (!CSRF::verify(is_string($token) ? $token : null)) {
                    http_response_code(419);
                    echo 'CSRF token mismatch';
                    return false;
                }
            }

            if (str_starts_with($middleware, 'permission:')) {
                $permission = substr($middleware, strlen('permission:'));

                if (!is_string($permission) || $permission === '' || !Auth::can($permission)) {
                    Session::flash('error', 'You are not authorized to access that page.');

                    $currentPath = (string) (parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH) ?: '/');
                    $currentPath = rtrim($currentPath, '/') ?: '/';

                    $redirectPath = role_landing_path(Auth::user());
                    $redirectPath = rtrim($redirectPath, '/') ?: '/';

                    if ($redirectPath === $currentPath) {
                        $redirectPath = '/';
                    }

                    if ($redirectPath === $currentPath) {
                        http_response_code(403);
                        echo '403 Forbidden';
                        return false;
                    }

                    header('Location: ' . $redirectPath);
                    return false;
                }
            }
        }

        return true;
    }
}

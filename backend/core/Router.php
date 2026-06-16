<?php
/**
 * Router
 * Registers routes and dispatches the current HTTP request to the right handler.
 *
 * Route handlers can be:
 *   - A callable:               fn(array $params) => ...
 *   - A [ClassName, 'method']:  ['UserController', 'index']
 *
 * URL parameters use {name} syntax, e.g. /api/users/{id}
 *
 * Middleware is a list of callables run before the handler.
 */
class Router
{
    private array $routes = [];

    // ── Route registration ────────────────────────────────────

    public function get(string $path, callable|array $handler, array $middleware = []): void
    {
        $this->add('GET', $path, $handler, $middleware);
    }

    public function post(string $path, callable|array $handler, array $middleware = []): void
    {
        $this->add('POST', $path, $handler, $middleware);
    }

    public function put(string $path, callable|array $handler, array $middleware = []): void
    {
        $this->add('PUT', $path, $handler, $middleware);
    }

    public function patch(string $path, callable|array $handler, array $middleware = []): void
    {
        $this->add('PATCH', $path, $handler, $middleware);
    }

    public function delete(string $path, callable|array $handler, array $middleware = []): void
    {
        $this->add('DELETE', $path, $handler, $middleware);
    }

    private function add(string $method, string $path, callable|array $handler, array $middleware): void
    {
        $this->routes[] = [
            'method'     => $method,
            'path'       => $path,
            'pattern'    => $this->compilePattern($path),
            'handler'    => $handler,
            'middleware' => $middleware,
        ];
    }

    // ── Dispatch ──────────────────────────────────────────────

    public function dispatch(): void
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        // Support method override via hidden _method field or X-HTTP-Method-Override header
        if ($method === 'POST') {
            $override = $_POST['_method'] ?? ($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] ?? null);
            if ($override) {
                $method = strtoupper($override);
            }
        }

        $uri = $this->currentUri();

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }
            if (preg_match($route['pattern'], $uri, $matches)) {
                // Named captures only
                $params = array_filter($matches, fn($k) => !is_int($k), ARRAY_FILTER_USE_KEY);

                // Run middleware
                foreach ($route['middleware'] as $mw) {
                    if (is_callable($mw)) {
                        $mw($params);
                    } elseif (is_string($mw) && method_exists(Middleware::class, $mw)) {
                        Middleware::$mw();
                    }
                }

                // Invoke handler
                $handler = $route['handler'];
                if (is_callable($handler)) {
                    $handler($params);
                } elseif (is_array($handler) && count($handler) === 2) {
                    [$class, $action] = $handler;
                    $controller = new $class();
                    $controller->$action($params);
                }
                return;
            }
        }

        $this->notFound($uri);
    }

    // ── Helpers ───────────────────────────────────────────────

    private function currentUri(): string
    {
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        // Strip the script's directory prefix so routes are relative
        $base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
        if ($base !== '' && str_starts_with($uri, $base)) {
            $uri = substr($uri, strlen($base));
        }
        return '/' . ltrim($uri, '/');
    }

    /** Convert /users/{id} to a named-capture regex */
    private function compilePattern(string $path): string
    {
        $pattern = preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $path);
        return '#^' . $pattern . '$#';
    }

    private function notFound(string $uri): void
    {
        http_response_code(404);
        // If the request wants JSON, respond with JSON
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        if (str_contains($accept, 'application/json') || str_starts_with($uri, '/api')) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'message' => 'Route not found']);
        } else {
            include BASE_PATH . '/app/views/errors/404.php';
        }
        exit;
    }
}

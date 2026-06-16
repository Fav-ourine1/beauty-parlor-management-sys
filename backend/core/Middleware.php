<?php
/**
 * Middleware
 * Static guards called before route handlers.
 * Each method terminates the request if the check fails.
 */
class Middleware
{
    /**
     * Require an active authenticated session.
     * API requests get a 401 JSON response; web requests are redirected to /login.
     */
    public static function auth(): void
    {
        if (empty($_SESSION['user'])) {
            self::deny('Authentication required', '/login');
        }
    }

    /**
     * Require that the session user has one of the given roles.
     * Usage in route file: fn() => Middleware::role('admin', 'staff')
     */
    public static function role(string ...$roles): void
    {
        self::auth();
        $userRole = $_SESSION['user']['role'] ?? '';
        if (!in_array($userRole, $roles, true)) {
            self::denyForbidden();
        }
    }

    /** Only accessible to admins */
    public static function admin(): void
    {
        self::role('admin');
    }

    /** Accessible to admins and staff */
    public static function staff(): void
    {
        self::role('admin', 'staff');
    }

    /** Accessible to clients (redirects logged-in users away from guest-only pages like /login) */
    public static function guest(): void
    {
        if (!empty($_SESSION['user'])) {
            redirect(self::dashboardUrl());
        }
    }

    // ── Internal helpers ──────────────────────────────────────

    private static function isApiRequest(): bool
    {
        $uri    = $_SERVER['REQUEST_URI'] ?? '';
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        return str_contains($uri, '/api') || str_contains($accept, 'application/json');
    }

    private static function deny(string $message, string $redirectTo): never
    {
        if (self::isApiRequest()) {
            http_response_code(401);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'message' => $message]);
        } else {
            $_SESSION['flash_error'] = $message;
            redirect(APP_URL . $redirectTo);
        }
        exit;
    }

    private static function denyForbidden(): never
    {
        if (self::isApiRequest()) {
            http_response_code(403);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'message' => 'Forbidden']);
        } else {
            http_response_code(403);
            include BASE_PATH . '/app/views/errors/403.php';
        }
        exit;
    }

    private static function dashboardUrl(): string
    {
        $role = $_SESSION['user']['role'] ?? 'client';
        return match ($role) {
            'admin'  => APP_URL . '/admin/dashboard',
            'staff'  => APP_URL . '/staff/dashboard',
            default  => APP_URL . '/client/dashboard',
        };
    }
}

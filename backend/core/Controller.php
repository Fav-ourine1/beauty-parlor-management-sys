<?php
/**
 * Base Controller
 * All controllers extend this. JSON response helpers + input handling.
 */
abstract class Controller
{
    // ── JSON Responses ────────────────────────────────────────

    protected function json(mixed $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    protected function success(mixed $data = null, string $message = 'OK', int $status = 200): void
    {
        $this->json(['success' => true, 'message' => $message, 'data' => $data], $status);
    }

    protected function created(mixed $data = null, string $message = 'Created'): void
    {
        $this->success($data, $message, 201);
    }

    protected function error(string $message, int $status = 400, mixed $errors = null): void
    {
        $this->json(['success' => false, 'message' => $message, 'errors' => $errors], $status);
    }

    protected function notFound(string $message = 'Resource not found'): void
    {
        $this->error($message, 404);
    }

    protected function unauthorized(string $message = 'Unauthorised'): void
    {
        $this->error($message, 401);
    }

    protected function forbidden(string $message = 'Forbidden'): void
    {
        $this->error($message, 403);
    }

    protected function serverError(string $message = 'Internal server error'): void
    {
        $this->error($message, 500);
    }

    // ── Input ─────────────────────────────────────────────────

    /** Decode and return JSON request body as associative array */
    protected function getBody(): array
    {
        $raw = file_get_contents('php://input');
        return json_decode($raw, true) ?? [];
    }

    /** Get a sanitised GET/POST param */
    protected function input(string $key, mixed $default = null): mixed
    {
        $value = $_REQUEST[$key] ?? $default;
        return is_string($value) ? trim(htmlspecialchars($value, ENT_QUOTES, 'UTF-8')) : $value;
    }

    /** Validate required fields exist and are non-empty in a data array */
    protected function validate(array $data, array $required): array
    {
        $errors = [];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
            }
        }
        return $errors;
    }

    // ── Auth helpers ──────────────────────────────────────────

    protected function currentUser(): array|null
    {
        return $_SESSION['user'] ?? null;
    }

    protected function currentUserId(): int|null
    {
        return $_SESSION['user']['id'] ?? null;
    }

    protected function hasRole(string ...$roles): bool
    {
        $userRole = $_SESSION['user']['role'] ?? null;
        return in_array($userRole, $roles, true);
    }

    protected function requireRole(string ...$roles): void
    {
        if (!$this->hasRole(...$roles)) {
            $this->forbidden('You do not have permission to perform this action');
        }
    }
}
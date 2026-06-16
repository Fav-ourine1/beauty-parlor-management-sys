<?php
/**
 * Logger — writes timestamped lines to storage/logs/app.log
 */
class Logger
{
    private static function write(string $level, string $message): void
    {
        $logFile = LOG_PATH . '/app.log';
        $line    = sprintf("[%s] [%s] %s\n", date('Y-m-d H:i:s'), strtoupper($level), $message);
        error_log($line, 3, $logFile);
    }

    public static function info(string $message): void  { self::write('INFO',  $message); }
    public static function error(string $message): void { self::write('ERROR', $message); }
    public static function warn(string $message): void  { self::write('WARN',  $message); }
    public static function debug(string $message): void
    {
        if (APP_DEBUG) {
            self::write('DEBUG', $message);
        }
    }
}

// ── Utility functions ─────────────────────────────────────────

/** Dump and die — dev only */
function dd(mixed ...$vars): never
{
    if (!APP_DEBUG) {
        http_response_code(500);
        exit;
    }
    foreach ($vars as $v) {
        echo '<pre>' . htmlspecialchars(print_r($v, true)) . '</pre>';
    }
    exit;
}

/** Redirect to a URL */
function redirect(string $url): never
{
    header('Location: ' . $url);
    exit;
}

/** Sanitise a string for safe HTML output */
function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Return a value from a nested array using dot notation, e.g. data_get($arr, 'user.name') */
function data_get(array $data, string $key, mixed $default = null): mixed
{
    $keys = explode('.', $key);
    foreach ($keys as $k) {
        if (!is_array($data) || !array_key_exists($k, $data)) {
            return $default;
        }
        $data = $data[$k];
    }
    return $data;
}

/** Format a decimal as KES currency, e.g. "KES 1,250.00" */
function kes(float $amount): string
{
    return 'KES ' . number_format($amount, 2);
}

/** Format a datetime string to a human-readable form */
function fmt_datetime(string $datetime, string $format = 'd M Y, H:i'): string
{
    return (new DateTimeImmutable($datetime))->format($format);
}

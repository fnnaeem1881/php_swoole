<?php
namespace App\Core;

class Helpers
{
    public static function env($key, $default = null)
    {
        static $env = null;
        if ($env === null) {
            $env = [];
            $path = __DIR__ . '/../../.env';
            if (file_exists($path)) {
                $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                foreach ($lines as $line) {
                    if (trim($line) === '' || strpos(trim($line), '#') === 0) continue;
                    if (!strpos($line, '=')) continue;
                    [$k, $v] = explode('=', $line, 2);
                    $env[trim($k)] = trim($v);
                }
            }
        }
        return $env[$key] ?? $default;
    }

    // filesystem path for storage
    public static function storage($path = '')
    {
        $base = realpath(__DIR__ . '/../../storage');
        if ($path === '') return $base . DIRECTORY_SEPARATOR;
        return $base . DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR);
    }

    // public asset URL
    public static function asset($path = '')
    {
        $base = rtrim(self::env('APP_URL', '/'), '/');
        return $base . '/public/' . ltrim($path, '/');
    }

    // friendly wrapper for creating public URL to storage files (optional)
    public static function storageUrl($path = '')
    {
        $base = rtrim(self::env('APP_URL', '/'), '/');
        return $base . '/storage/' . ltrim($path, '/');
    }
}

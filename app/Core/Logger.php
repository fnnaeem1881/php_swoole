<?php
namespace App\Core;

class Logger
{
    protected static $logPath;
    protected static $logChannel;
    protected static $debug;

    public static function init()
    {
        self::$logPath = getenv('LOG_PATH') ?: __DIR__ . '/../../storage/logs';
        self::$logChannel = getenv('LOG_CHANNEL') ?: 'single';
        self::$debug = getenv('APP_DEBUG') === 'true';

        if (!is_dir(self::$logPath)) {
            mkdir(self::$logPath, 0777, true);
        }

        // Set global error and exception handlers
        set_error_handler([self::class, 'handleError']);
        set_exception_handler([self::class, 'handleException']);
        register_shutdown_function([self::class, 'handleShutdown']);
    }

    public static function info($message, $context = [])
    {
        self::write('INFO', $message, $context);
    }

    public static function debug($message, $context = [])
    {
        if (self::$debug) {
            self::write('DEBUG', $message, $context);
        }
    }

    public static function error($message, $context = [])
    {
        self::write('ERROR', $message, $context);
    }

    protected static function write($level, $message, $context = [])
    {
        $date = date('Y-m-d H:i:s');
        $logMessage = "[$date][$level] $message";

        if (!empty($context)) {
            $logMessage .= ' ' . json_encode($context);
        }

        $logFile = self::getLogFile();

        file_put_contents($logFile, $logMessage . PHP_EOL, FILE_APPEND);
    }

    protected static function getLogFile()
    {
        switch (self::$logChannel) {
            case 'daily':
                $file = self::$logPath . '/log-' . date('Y-m-d') . '.log';
                break;
            case 'weekly':
                $week = date('o-W'); // Year-Week
                $file = self::$logPath . "/log-week-$week.log";
                break;
            case 'monthly':
                $file = self::$logPath . '/log-' . date('Y-m') . '.log';
                break;
            case 'custom':
                $file = self::$logPath . '/custom.log';
                break;
            case 'single':
            default:
                $file = self::$logPath . '/app.log';
        }
        return $file;
    }

    // --- Global error handler ---
    public static function handleError($errno, $errstr, $errfile, $errline)
    {
        $context = ['file' => $errfile, 'line' => $errline, 'errno' => $errno];
        self::write('ERROR', $errstr, $context);
    }

    public static function handleException($exception)
    {
        $context = [
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString()
        ];
        self::write('ERROR', $exception->getMessage(), $context);
    }

    public static function handleShutdown()
    {
        $error = error_get_last();
        if ($error !== null) {
            self::write('FATAL', $error['message'], [
                'file' => $error['file'],
                'line' => $error['line'],
                'type' => $error['type']
            ]);
        }
    }
}

Logger::init();

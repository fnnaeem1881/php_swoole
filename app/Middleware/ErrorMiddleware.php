<?php
namespace App\Middleware;

use App\Core\Logger;

class ErrorMiddleware
{
    public function handle($request, $response, $next)
    {
        try {
            $next();
        } catch (\Throwable $e) {
            Logger::error($e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            $response->status(500);
            $response->end("Internal Server Error");
        }
    }
}

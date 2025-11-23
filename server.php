<?php
require __DIR__ . '/vendor/autoload.php';

use App\Core\Router;
use App\Core\Helpers;
use App\Core\Logger;


// ensure logs / storage exist
@mkdir(__DIR__ . '/storage/uploads', 0777, true);

// create router
$router = new Router();

// load route files (they receive $router variable)
require __DIR__ . '/routes/web.php';
require __DIR__ . '/routes/admin.php';

$server = new Swoole\Http\Server("0.0.0.0", 9501);

// handle fatal errors to avoid worker crash (best-effort)
set_error_handler(function($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});
register_shutdown_function(function() {
    $err = error_get_last();
    if ($err) {
        // no network available here, ensure we do not crash silently
        echo "[shutdown] Fatal: " . json_encode($err) . PHP_EOL;
    }
});

$server->on("request", function ($request, $response) use ($router) {
    try {
        $_GET = $request->get ?? [];
        $_POST = $request->post ?? [];
        $_FILES = $request->files ?? [];
        $_SERVER = array_change_key_case($request->server ?? [], CASE_LOWER);

        $router->dispatch($request->server['request_uri'], $request, $response);

        // Log non-200 responses
        $status = $response->status ?? 200;
        if ($status !== 200) {
            Logger::error("Non-200 Response", [
                'uri' => $request->server['request_uri'] ?? '',
                'method' => $request->server['request_method'] ?? '',
                'status' => $status
            ]);
        }

    } catch (\Throwable $e) {
        Logger::error($e->getMessage(), [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);

        $response->status(500);
        if (Helpers::env('APP_DEBUG', 'false') === 'true') {
            $response->end("Server Error: " . $e->getMessage() . "\n" . $e->getTraceAsString());
        } else {
            $response->end("Server Error");
        }
    }
});
echo "Swoole server started on " . Helpers::env('APP_URL', 'http://127.0.0.1:9501') . PHP_EOL;
$server->start();
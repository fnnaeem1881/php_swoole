<?php
namespace App\Core;

use App\Core\Helpers;
use App\Core\Logger;
class Router
{
    protected $routes = [];

    public function get($uri, $action, $middleware = [])
    {
        $this->addRoute('GET', $uri, $action, $middleware);
    }

    public function post($uri, $action, $middleware = [])
    {
        $this->addRoute('POST', $uri, $action, $middleware);
    }

    protected function addRoute($method, $uri, $action, $middleware = [])
    {
        $this->routes[$method][$uri] = [
            'action' => $action,
            'middleware' => $middleware
        ];
    }

    public function dispatch($uri, $request, $response)
    {
        $method = strtoupper($request->server['request_method'] ?? 'GET');
        $route = $this->routes[$method][$uri] ?? null;

        if (!$route) {
            Logger::error("Route Not Found: $uri", [
                'method' => $method,
                'uri' => $uri,
                'status' => 404
            ]);

            $response->status(404);
            if (Helpers::env('APP_DEBUG', 'false') === 'true') {
                $response->end("404 Not Found: $uri");
            } else {
                $response->end("Not Found");
            }
            return;
        }

        // run middleware chain
        foreach ($route['middleware'] as $middlewareClass) {
            if (!class_exists($middlewareClass)) continue;
            $m = new $middlewareClass;
            // middleware should return true to continue, false or array/string to stop
            $result = $m->handle($request, $response);
            if ($result === false) {
                Logger::error("Unauthorized Access Middleware", [
                    'uri' => $uri,
                    'method' => $method,
                    'status' => $response->status ?? 401
                ]);
                return;
            }
            if (is_array($result)) {
                // structured response: ['status'=>..., 'body'=>...]
                $status = $result['status'] ?? 200;
                $body = $result['body'] ?? '';
                $response->status($status);
                $response->end($body);
                return;
            }
            if (is_string($result)) {
                $response->end($result);
                return;
            }
        }

        // call controller action
        [$controller, $action] = explode('@', $route['action']);
        $controller = "App\\Controllers\\$controller";
        $controller = new $controller;

        try {
            ob_start();
            $controller->$action($request, $response);
            $output = ob_get_clean();
            $response->status($response->status ?? 200);
            $response->end($output);
        } catch (\Throwable $e) {
            \App\Core\Logger::error($e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            $response->status(500);
            if (\App\Core\Helpers::env('APP_DEBUG', 'false') === 'true') {
                $response->end("Server Error: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            } else {
                $response->end("Server Error");
            }
        }
    }
}

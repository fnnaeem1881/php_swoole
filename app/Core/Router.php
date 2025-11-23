<?php
namespace App\Core;

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
            $response->status(404);
            $response->end("404 Not Found");
            return;
        }

        // run middleware chain
        foreach ($route['middleware'] as $middlewareClass) {
            if (!class_exists($middlewareClass)) continue;
            $m = new $middlewareClass;
            // middleware should return true to continue, false or array/string to stop
            $result = $m->handle($request, $response);
            if ($result === false) {
                // middleware already ended response
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
        $controllerClass = "App\\Controllers\\{$controller}";
        if (!class_exists($controllerClass)) {
            $response->status(500);
            $response->end("Controller {$controller} not found");
            return;
        }

        $controller = new $controllerClass;

        try {
            // controller methods receive ($request, $response)
            $controller->$action($request, $response);
        } catch (\Throwable $e) {
            $response->status(500);
            if (Helpers::env('APP_DEBUG', 'false') === 'true') {
                $response->end("Error: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            } else {
                $response->end("Server Error");
            }
        }
    }
}

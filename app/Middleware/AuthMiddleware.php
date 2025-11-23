<?php
namespace App\Middleware;

use App\Core\Middleware;

class AuthMiddleware extends Middleware
{
    public function handle($request, $response)
    {
        // Simple example: check ?user=1 or header Authorization
        $authHeader = $request->header['authorization'] ?? null;

        if ($authHeader) {
            return true;
        }

        $response->status(401);
        $response->header("Content-Type", "text/plain");
        $response->end("Unauthorized");
        return false;
    }
}

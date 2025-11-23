<?php
namespace App\Core;

class Controller
{
     protected function render($file, $data, $response)
    {
        $viewFile = __DIR__ . "/../../templates/views/{$file}.php";
        if (!file_exists($viewFile)) {
            $response->status(500);
            $response->end("View not found: {$file}");
            return;
        }
        extract($data ?? []);
        // capture output to string
        ob_start();
        include $viewFile;
        $content = ob_get_clean();
        $response->header("Content-Type", "text/html");
        $response->end($content);
    }

    // helper to send plain text
    protected function text($body, $status = 200, $response = null)
    {
        if ($response) {
            $response->status($status);
            $response->header("Content-Type", "text/plain");
            $response->end($body);
        } else {
            echo $body;
        }
    }
}

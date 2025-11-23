<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Helpers;

class HomeController extends Controller
{
     public function index($request, $response)
    {
        // render 'home' view and end response
        $this->render('home', ['title' => 'Welcome to Swoole MVC'], $response);
    }

    public function upload($request, $response)
    {
        // Swoole files: $request->files
        $files = $request->files ?? [];
        if (empty($files['file']['name'])) {
            $response->status(400);
            $response->end("No file uploaded");
            return;
        }

        $tmpFile = $files['file']['tmp_name'];
        $name = basename($files['file']['name']);
        $dest = Helpers::storage("uploads/{$name}");

        if (!is_dir(dirname($dest))) mkdir(dirname($dest), 0777, true);

        // move uploaded (Swoole provides temp file)
        if (!move_uploaded_file($tmpFile, $dest)) {
            // attempt copy if move fails
            if (!copy($tmpFile, $dest)) {
                $response->status(500);
                $response->end("Failed to save file");
                return;
            }
        }

        $response->header("Content-Type", "text/plain");
        $response->end("Uploaded: " . $dest);
    }
}

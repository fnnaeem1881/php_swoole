<?php
namespace App\Controllers;

use App\Core\Controller;

class AdminController extends Controller
{
     public function index($request, $response)
    {
        $this->render('admin', ['title' => 'Admin Dashboard'], $response);
    }
}

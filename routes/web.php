<?php
use App\Core\Router;
use App\Middleware\AuthMiddleware;

$router->get('/', 'HomeController@index');
$router->post('/upload', 'HomeController@upload', [AuthMiddleware::class]);

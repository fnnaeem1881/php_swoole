<?php
use App\Core\Router;
use App\Middleware\AuthMiddleware;

$router->get('/admin', 'AdminController@index', [AuthMiddleware::class]);

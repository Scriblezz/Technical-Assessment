<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return 'Lumen is working!';
});
$router->post('/api/test', function () {
    return response()->json(['message' => 'Routing works']);
});


$router->post('/api/register', 'AuthController@register');
$router->post('/api/login', 'AuthController@login');
$router->get('/api/posts', 'PostController@index');
$router->get('/api/posts/{id}', 'PostController@show');
$router->group(['middleware' => 'jwt.auth'], function () use ($router) {
    $router->post('/api/posts', 'PostController@store');
});

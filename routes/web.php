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
    return $router->app->version();
});

$router->get('settings', ['uses' => 'ShoptetController@setting']);

$router->get('send', ['uses' => 'DepoApiController@send']);
$router->get('places', ['uses' => 'DepoApiController@places']);
$router->get('send_again', ['uses' => 'DepoApiController@sendAgain']);
$router->get('install', ['uses' => 'ShoptetController@install']);
$router->post('unistall', ['uses' => 'ShoptetController@unistall']);
$router->get('shipping_update', ['uses' => 'ShoptetController@shippingUpdate']);
$router->post('cancel', ['uses' => 'ShoptetController@cancelOrder']);
//$router->get('create_order', ['uses' => 'ShoptetController@createOrder']);
$router->post('create_order', ['uses' => 'ShoptetController@createOrder']);
$router->get('authorization', ['uses' => 'ShoptetController@code']);
$router->group(['middleware' => 'cors'], function($router)
{
    $router->post('password', ['uses' => 'UserController@password']);
});

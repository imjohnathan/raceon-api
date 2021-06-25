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

$router->get('foo', function () {
    return response()->json(['error' => 'Unauthorized'], 401, ['X-Header-One' => 'Header Value']);
});

$router->get('user[/{name}]', function ($name = null) {
    return $name;
});


$router->group(['prefix' => 'webhook/mailchimp'],  function () use ($router) {
    $router->get('/', function () {
        return response()->json(['error' => 'No Method.'], 401);
    });
    $router->get('user', 'MailchimpControllers@user');
    $router->get('store/product/create_update', 'MailchimpControllers@product');
    $router->get('store/order/create', 'MailchimpControllers@OrderCreate');

});


$router->group(['prefix' => 'web/mailchimp', 'middleware' => 'BasicAuth'],  function () use ($router) {
    $router->get( 'store/products', 'MailchimpWebControllers@products');
    $router->post('store/products', 'MailchimpWebControllers@products');
    $router->get( 'store/orders', 'MailchimpWebControllers@orders');
    $router->post('store/orders', 'MailchimpWebControllers@orders');
});


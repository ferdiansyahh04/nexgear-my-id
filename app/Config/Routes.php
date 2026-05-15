<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('HomeController');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
$routes->setAutoRoute(false);

$routes->get('/', 'HomeController::index');

$routes->get('register', 'AuthController::registerForm');
$routes->post('register', 'AuthController::register', ['filter' => 'throttle']);
$routes->get('login', 'AuthController::loginForm');
$routes->post('login', 'AuthController::login', ['filter' => 'throttle']);
$routes->post('logout', 'AuthController::logout');

$routes->get('products', 'ProductController::index');
$routes->get('collection', 'ProductController::index');
$routes->get('products/(:num)', 'ProductController::show/$1');

$routes->group('cart', static function ($routes) {
    $routes->get('/', 'CartController::index');
    $routes->post('add/(:num)', 'CartController::add/$1');
    $routes->post('update', 'CartController::update');
    $routes->post('remove/(:num)', 'CartController::remove/$1');
    $routes->post('clear', 'CartController::clear');
});

$routes->group('checkout', ['filter' => 'auth'], static function ($routes) {
    $routes->get('/', 'CheckoutController::index');
    $routes->post('place', 'CheckoutController::place');
});

$routes->group('admin', ['namespace' => 'App\Controllers\Admin', 'filter' => 'admin'], static function ($routes) {
    // Order Management
    $routes->get('orders', 'OrderController::index');
    $routes->get('orders/(:num)', 'OrderController::show/$1');

    // Product Management
    $routes->get('products', 'ProductController::index');
    $routes->get('products/create', 'ProductController::create');
    $routes->post('products', 'ProductController::store');
    $routes->get('products/(:num)/edit', 'ProductController::edit/$1');
    $routes->post('products/(:num)', 'ProductController::update/$1');
    $routes->post('products/(:num)/delete', 'ProductController::delete/$1');
});

<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('HomeController');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override(static function () {
    return view('errors/html/error_404');
});
$routes->setAutoRoute(false);

$routes->get('/', 'HomeController::index');

// SEO endpoints
$routes->get('sitemap.xml', 'SeoController::sitemap');
$routes->get('robots.txt', 'SeoController::robots');

// Help / FAQ
$routes->get('help', 'HelpController::index');

$routes->get('register', 'AuthController::registerForm');
$routes->post('register', 'AuthController::register', ['filter' => 'throttle']);
$routes->get('login', 'AuthController::loginForm');
$routes->post('login', 'AuthController::login', ['filter' => 'throttle']);
$routes->get('login/2fa', 'AuthController::twoFactorForm');
$routes->post('login/2fa', 'AuthController::twoFactorVerify', ['filter' => 'throttle']);
$routes->post('logout', 'AuthController::logout');

// Wishlist (toggle works for guest + logged-in; index requires auth)
$routes->post('wishlist/toggle/(:num)', 'WishlistController::toggle/$1');

// Stock alerts (notify when back in stock)
$routes->post('products/(:num)/stock-alert', 'StockAlertController::subscribe/$1', ['filter' => 'throttle']);

// Coupon (works for guest + logged-in; tied to session cart)
$routes->post('coupon/apply', 'CouponController::apply');
$routes->post('coupon/remove', 'CouponController::remove');

// Public storefront pages: contact + newsletter
$routes->get('contact', 'ContactController::show');
$routes->post('contact', 'ContactController::submit', ['filter' => 'throttle']);
$routes->post('newsletter/subscribe', 'NewsletterController::subscribe', ['filter' => 'throttle']);
$routes->get('newsletter/confirm', 'NewsletterController::confirm');
$routes->get('newsletter/unsubscribe', 'NewsletterController::unsubscribe');

$routes->group('account', ['filter' => 'auth'], static function ($routes) {
    $routes->get('/', 'AccountController::index');
    $routes->get('orders', 'AccountController::orders');
    $routes->get('orders/(:num)', 'AccountController::orderDetail/$1');
    $routes->get('wishlist', 'AccountController::wishlist');
    $routes->get('addresses', 'AddressController::index');
    $routes->post('addresses', 'AddressController::store');
    $routes->get('addresses/(:num)/fetch', 'AddressController::fetch/$1');
    $routes->post('addresses/(:num)', 'AddressController::update/$1');
    $routes->post('addresses/(:num)/delete', 'AddressController::delete/$1');
});

$routes->get('products', 'ProductController::index');
$routes->get('collection', 'ProductController::index');
$routes->get('products/compare', 'ProductController::compare');
$routes->get('products/search', 'ProductController::search');
$routes->get('products/(:num)/quick-view', 'ProductController::quickView/$1');
$routes->get('products/(:num)/stock', 'ProductController::stockSnapshot/$1');
$routes->get('products/(:num)', 'ProductController::show/$1');

// Reviews
$routes->post('products/(:num)/reviews', 'ReviewController::store/$1', ['filter' => 'auth']);
$routes->post('reviews/(:num)/delete', 'ReviewController::delete/$1', ['filter' => 'auth']);

$routes->group('cart', static function ($routes) {
    $routes->get('/', 'CartController::index');
    $routes->post('add/(:num)', 'CartController::add/$1');
    $routes->post('update', 'CartController::update');
    $routes->post('update-qty/(:num)', 'CartController::updateQty/$1');
    $routes->post('remove/(:num)', 'CartController::remove/$1');
    $routes->post('clear', 'CartController::clear');
});

$routes->group('checkout', ['filter' => 'auth'], static function ($routes) {
    $routes->get('/', 'CheckoutController::index');
    $routes->post('place', 'CheckoutController::place');
});

// Admin & staff readable views (orders/messages/reports/dashboard/audit)
$routes->group('admin', ['namespace' => 'App\Controllers\Admin', 'filter' => 'staff'], static function ($routes) {
    $routes->get('/', 'DashboardController::index');
    $routes->get('dashboard', 'DashboardController::index');

    // Personal security panel (per-user 2FA)
    $routes->get('security', 'SecurityController::index');
    $routes->post('security/setup/start', 'SecurityController::setupStart');
    $routes->post('security/setup/confirm', 'SecurityController::setupConfirm');
    $routes->post('security/disable', 'SecurityController::disable');
    $routes->post('security/password', 'SecurityController::changePassword', ['filter' => 'throttle']);

    // Messages — staff can read & change status
    $routes->get('messages', 'MessageController::index');
    $routes->get('messages/(:num)', 'MessageController::show/$1');
    $routes->post('messages/(:num)/status', 'MessageController::status/$1');

    // Orders — staff can read & change status
    $routes->get('orders', 'OrderController::index');
    $routes->get('orders/(:num)', 'OrderController::show/$1');
    $routes->post('orders/(:num)/status', 'OrderController::updateStatus/$1');
    $routes->get('orders/(:num)/invoice/pdf', 'ReportController::invoicePdf/$1');

    // Reports — staff can read & export
    $routes->get('reports', 'ReportController::index');
    $routes->get('reports/export/csv', 'ReportController::exportCsv');
});

// Admin-only mutation routes (catalog management, audit log, system)
$routes->group('admin', ['namespace' => 'App\Controllers\Admin', 'filter' => 'admin'], static function ($routes) {
    // Audit log
    $routes->get('audit', 'AuditController::index');

    // Categories
    $routes->get('categories', 'CategoryController::index');
    $routes->post('categories', 'CategoryController::store');
    $routes->post('categories/(:num)', 'CategoryController::update/$1');
    $routes->post('categories/(:num)/delete', 'CategoryController::delete/$1');

    // Product Management
    $routes->get('products', 'ProductController::index');
    $routes->get('products/create', 'ProductController::create');
    $routes->post('products', 'ProductController::store');
    $routes->get('products/(:num)/edit', 'ProductController::edit/$1');
    $routes->post('products/(:num)', 'ProductController::update/$1');
    $routes->post('products/(:num)/delete', 'ProductController::delete/$1');
    $routes->post('products/(:num)/images', 'ProductController::addImage/$1');
    $routes->post('products/(:num)/images/(:num)/delete', 'ProductController::deleteImage/$1/$2');
});

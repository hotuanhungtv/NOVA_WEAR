<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 *
 * Bộ lọc (filter) dùng trong file này, khai báo ở app/Config/Filters.php:
 *   customer  : phải đăng nhập tài khoản khách hàng
 *   staff     : phải đăng nhập tài khoản nhân viên / quản trị
 *   adminonly : chỉ quản trị viên (admin)
 */

// ===================== TRANG KHÁCH HÀNG =====================
$routes->get('/', 'Home::index');

// Sản phẩm + đánh giá
$routes->get('products', 'Products::index');
$routes->get('products/(:num)', 'Products::show/$1');
$routes->post('products/(:num)/review', 'Reviews::save/$1', ['filter' => 'customer']);
$routes->post('reviews/(:num)/delete', 'Reviews::delete/$1', ['filter' => 'customer']);

// Giỏ hàng (mỗi dòng = 1 tổ hợp size + màu)
$routes->get('cart', 'Cart::index');
$routes->post('cart/add', 'Cart::add');
$routes->post('cart/update', 'Cart::update');
$routes->post('cart/remove', 'Cart::remove');

// Đặt hàng
$routes->get('checkout', 'Checkout::index', ['filter' => 'customer']);
$routes->post('checkout', 'Checkout::place', ['filter' => 'customer']);
$routes->get('order-success/(:num)', 'Checkout::success/$1', ['filter' => 'customer']);

// Đăng nhập / đăng ký
$routes->get('login', 'Auth::login');
$routes->post('login', 'Auth::attempt');
$routes->get('register', 'Auth::register');
$routes->post('register', 'Auth::store');
$routes->get('logout', 'Auth::logout');

// Tài khoản + đơn hàng của tôi
$routes->get('profile', 'Account::profile', ['filter' => 'customer']);
$routes->post('profile', 'Account::updateProfile', ['filter' => 'customer']);
$routes->post('profile/password', 'Account::changePassword', ['filter' => 'customer']);
$routes->get('orders', 'Orders::index', ['filter' => 'customer']);
$routes->get('orders/(:num)', 'Orders::show/$1', ['filter' => 'customer']);
$routes->post('orders/(:num)/cancel', 'Orders::cancel/$1', ['filter' => 'customer']);

// REST API (JSON)
$routes->get('products-api', 'Api::demo');
$routes->get('api/products', 'Api::products');
$routes->get('api/categories', 'Api::categories');

// ===================== KHU VỰC QUẢN TRỊ =====================
$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], static function ($routes) {
    // Chưa cần đăng nhập
    $routes->get('login', 'Auth::login');
    $routes->post('login', 'Auth::attempt');
    $routes->get('logout', 'Auth::logout');

    // Nhân viên + admin
    $routes->group('', ['filter' => 'staff'], static function ($routes) {
        $routes->get('/', 'Dashboard::index');

        $routes->get('orders', 'Orders::index');
        $routes->get('orders/(:num)', 'Orders::show/$1');
        $routes->post('orders/(:num)/status', 'Orders::updateStatus/$1');

        $routes->get('reviews', 'Reviews::index');
        $routes->post('reviews/(:num)/toggle', 'Reviews::toggle/$1');
        $routes->post('reviews/(:num)/delete', 'Reviews::delete/$1');

        // Chỉ admin
        $routes->group('', ['filter' => 'adminonly'], static function ($routes) {
            $routes->get('products', 'Products::index');
            $routes->get('products/create', 'Products::create');
            $routes->post('products', 'Products::store');
            $routes->get('products/(:num)/edit', 'Products::edit/$1');
            $routes->post('products/(:num)', 'Products::update/$1');
            $routes->post('products/(:num)/delete', 'Products::delete/$1');

            $routes->get('categories', 'Categories::index');
            $routes->get('categories/create', 'Categories::create');
            $routes->post('categories', 'Categories::store');
            $routes->get('categories/(:num)/edit', 'Categories::edit/$1');
            $routes->post('categories/(:num)', 'Categories::update/$1');
            $routes->post('categories/(:num)/delete', 'Categories::delete/$1');

            $routes->get('shippers', 'Shippers::index');
            $routes->get('shippers/create', 'Shippers::create');
            $routes->post('shippers', 'Shippers::store');
            $routes->get('shippers/(:num)/edit', 'Shippers::edit/$1');
            $routes->post('shippers/(:num)', 'Shippers::update/$1');
            $routes->post('shippers/(:num)/delete', 'Shippers::delete/$1');

            $routes->get('customers', 'Customers::index');
            $routes->get('customers/(:num)', 'Customers::show/$1');
            $routes->post('customers/(:num)/toggle', 'Customers::toggle/$1');

            $routes->get('employees', 'Employees::index');
            $routes->post('employees', 'Employees::create');
            $routes->post('employees/(:num)/role', 'Employees::role/$1');
            $routes->post('employees/(:num)/delete', 'Employees::delete/$1');
        });
    });
});

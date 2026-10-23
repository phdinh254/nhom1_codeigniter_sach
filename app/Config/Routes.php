<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::index');

// Book CRUD (Bảo vệ bởi AuthFilter cho User/Admin; các thao tác CRUD chỉ dành cho Admin)
$routes->group('books', ['filter' => 'auth'], static function ($routes) {
    $routes->get('/', 'Book::index');

    $routes->group('', ['filter' => 'admin'], static function ($routes) {
        $routes->get('create', 'Book::create');
        $routes->post('store', 'Book::store', ['filter' => 'csrf']);
        $routes->get('edit/(:num)', 'Book::edit/$1');
        $routes->post('update/(:num)', 'Book::update/$1', ['filter' => 'csrf']);
        $routes->post('delete/(:num)', 'Book::delete/$1', ['filter' => 'csrf']);
    });
});

// Authentication & Authorization
$routes->get('login', 'Auth::login');
$routes->post('login', 'Auth::attemptLogin', ['filter' => 'csrf']);
$routes->post('logout', 'Auth::logout', ['filter' => 'csrf']);
$routes->get('dashboard', 'Dashboard::index', ['filter' => 'auth']);
$routes->get('no-access', static function () {
    return response()->setStatusCode(403)->setBody(view('errors/html/error_403'));
});

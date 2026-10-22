<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::index');

// Book CRUD
$routes->group('books', static function ($routes) {
    $routes->get('/', 'Book::index');
    $routes->get('create', 'Book::create');
    $routes->post('store', 'Book::store');
    $routes->get('edit/(:num)', 'Book::edit/$1');
    $routes->post('update/(:num)', 'Book::update/$1');
    $routes->post('delete/(:num)', 'Book::delete/$1');
});

// Authentication
$routes->get('login', 'Auth::login');
$routes->post('login', 'Auth::attemptLogin', ['filter' => 'csrf']);
$routes->post('logout', 'Auth::logout', ['filter' => 'csrf']);
$routes->get('dashboard', 'Dashboard::index');

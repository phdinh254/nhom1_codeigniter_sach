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

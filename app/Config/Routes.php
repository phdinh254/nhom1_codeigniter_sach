<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::index');

// Authentication
$routes->get('login', 'Auth::login');
$routes->post('login', 'Auth::attemptLogin', ['filter' => 'csrf']);
$routes->post('logout', 'Auth::logout', ['filter' => 'csrf']);
$routes->get('dashboard', 'Dashboard::index');

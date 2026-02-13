<?php

namespace Config;

// Create a new instance of our RouteCollection class.
$routes = Services::routes();

// Load the system's routing file first, so that the app and ENVIRONMENT
// can override as needed.
if (file_exists(SYSTEMPATH . 'Config/Routes.php')) {
    require SYSTEMPATH . 'Config/Routes.php';
}

/*
 * --------------------------------------------------------------------
 * Router Setup
 * --------------------------------------------------------------------
 */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Dashboard');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
// $routes->setAutoRoute(false);

/*
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

// 1. ROUTE UTAMA
// Mengarahkan root domain langsung ke Dashboard (akan dicegat filter auth jika belum login)
$routes->get('/', 'Dashboard::index', ['filter' => 'auth']);

// 2. ROUTE AUTH (Login/Register/Logout - Bebas Akses/Tanpa Filter)
$routes->get('login', 'Auth::login');
$routes->post('auth/process_login', 'Auth::process_login');
$routes->get('register', 'Auth::register');
$routes->post('auth/process_register', 'Auth::process_register');
$routes->get('logout', 'Auth::logout');

// 3. ROUTE GROUPS (DIPROTEKSI FILTER AUTH)
// Semua fitur di bawah ini hanya bisa diakses jika user SUDAH LOGIN

// Group Dashboard & Fitur Export
$routes->group('dashboard', ['filter' => 'auth'], function($routes) {
    $routes->get('/', 'Dashboard::index');
    $routes->post('save_transaction', 'Dashboard::save_transaction');
    // Rute Export Data
    $routes->get('export_excel', 'Dashboard::export_excel');
    $routes->get('export_pdf', 'Dashboard::export_pdf');
});

// Group Transaksi
$routes->group('transactions', ['filter' => 'auth'], function($routes) {
    $routes->get('/', 'Transactions::index');
    $routes->post('delete/(:num)', 'Transactions::delete/$1');
});

// Group Laporan
$routes->group('reports', ['filter' => 'auth'], function($routes) {
    $routes->get('/', 'Reports::index');
});

// Group Hutang & Piutang (Kasbon)
$routes->group('debts', ['filter' => 'auth'], function($routes) {
    $routes->get('/', 'Debts::index');
    $routes->post('save', 'Debts::save');
    $routes->post('mark_paid/(:num)', 'Debts::mark_paid/$1');
});

// Group Anggaran (Budgeting)
$routes->group('budgets', ['filter' => 'auth'], function($routes) {
    $routes->get('/', 'Budgets::index');
    $routes->post('save', 'Budgets::save');
    $routes->post('delete/(:num)', 'Budgets::delete/$1');
});

// Group Stok Barang (Inventory)
$routes->group('products', ['filter' => 'auth'], function($routes) {
    $routes->get('/', 'Products::index');
    $routes->post('save', 'Products::save');
    $routes->post('delete/(:num)', 'Products::delete/$1');
});

// Group Kasir / POS (Point of Sales)
$routes->group('pos', ['filter' => 'auth'], function($routes) {
    $routes->get('/', 'Pos::index');
    $routes->post('checkout', 'Pos::checkout');
});

// Group Pengaturan
$routes->group('settings', ['filter' => 'auth'], function($routes) {
    $routes->get('/', 'Settings::index');
    $routes->post('update_profile', 'Settings::update_profile');
    $routes->post('add_wallet', 'Settings::add_wallet');
});

/*
 * --------------------------------------------------------------------
 * Additional Routing
 * --------------------------------------------------------------------
 *
 * There will often be times that you need additional routing and you
 * need it to be able to override any defaults in this file. Environment
 * based routes is one such time. require() additional route files here
 * to make that happen.
 *
 * You will have access to the $routes object within that file without
 * needing to reload it.
 */
if (is_file(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php')) {
    require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}
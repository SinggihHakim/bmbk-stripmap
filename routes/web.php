<?php

/**
 * ============================================================
 * Web Routes
 * ============================================================
 * Definisikan seluruh route aplikasi di sini.
 */

$router = new Router();

// ──────────────────────────────────────────────
// Halaman Utama (Dashboard)
// ──────────────────────────────────────────────
$router->get('', 'DashboardController', 'index');

// ──────────────────────────────────────────────
// CRUD Ruas Jalan
// ──────────────────────────────────────────────
$router->get('ruas',              'RuasController', 'index');
$router->get('ruas/create',      'RuasController', 'create');
$router->post('ruas/store',      'RuasController', 'store');
$router->get('ruas/import',      'RuasController', 'importForm');
$router->post('ruas/import',     'RuasController', 'importProcess');
$router->get('ruas/edit/{id}',   'RuasController', 'edit');
$router->post('ruas/update/{id}','RuasController', 'update');
$router->get('ruas/delete/{id}', 'RuasController', 'delete');
$router->get('ruas/show/{id}',   'RuasController', 'show');

// ──────────────────────────────────────────────
// CRUD Strip Map
// ──────────────────────────────────────────────
$router->get('stripmap/{id}',              'StripmapController', 'index');
$router->get('stripmap/create/{id}',      'StripmapController', 'create');
$router->post('stripmap/store/{id}',      'StripmapController', 'store');
$router->get('stripmap/input/{id}',       'StripmapController', 'input');
$router->post('stripmap/batch/{id}',      'StripmapController', 'batch');
$router->get('stripmap/edit/{id}',        'StripmapController', 'edit');
$router->post('stripmap/update/{id}',     'StripmapController', 'update');
$router->get('stripmap/delete/{id}',      'StripmapController', 'delete');
$router->get('stripmap/preview/{id}',     'StripmapController', 'preview');

// ──────────────────────────────────────────────
// CRUD Perkerasan Jalan
// ──────────────────────────────────────────────
$router->post('perkerasan/store/{id}',    'StripmapController', 'perkerasanStore');
$router->get('perkerasan/edit/{id}',      'StripmapController', 'perkerasanEdit');
$router->post('perkerasan/update/{id}',   'StripmapController', 'perkerasanUpdate');
$router->get('perkerasan/delete/{id}',    'StripmapController', 'perkerasanDelete');


// Jalankan router
$router->dispatch();

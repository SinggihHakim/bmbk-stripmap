<?php

/**
 * ============================================================
 * Strip Map Ruas Jalan — Entry Point
 * ============================================================
 * Semua request masuk melalui file ini.
 */

// Mulai session
session_start();

// Definisikan base path aplikasi
define('BASE_PATH', dirname(__DIR__));

// Muat konfigurasi
$appConfig = require BASE_PATH . '/config/app.php';

// Error reporting (aktifkan jika debug = true)
if ($appConfig['debug']) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

// Set timezone
date_default_timezone_set($appConfig['timezone']);

// Muat autoloader
require_once BASE_PATH . '/app/helpers/Autoloader.php';

// Muat helper functions
require_once BASE_PATH . '/app/helpers/functions.php';

// Muat Router class
require_once BASE_PATH . '/app/helpers/Router.php';

// Muat Database class
require_once BASE_PATH . '/app/helpers/Database.php';

// Muat dan jalankan router
require_once BASE_PATH . '/routes/web.php';

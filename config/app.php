<?php

/**
 * ============================================================
 * Konfigurasi Aplikasi
 * ============================================================
 * Nilai dibaca dari environment variables yang dimuat di public/index.php
 * melalui file .env di root project.
 */

$baseUrl = $_ENV['APP_URL'] ?? null;
if (empty($baseUrl)) {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['SERVER_PORT'] ?? 80) == 443 ? 'https' : 'http';
    $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $dir    = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');
    $baseUrl = $scheme . '://' . $host . ($dir === '' || $dir === '.' ? '' : $dir);
}

return [
    'name'     => $_ENV['APP_NAME']     ?? 'Strip Map Ruas Jalan',
    'base_url' => $baseUrl,
    'debug'    => filter_var($_ENV['APP_DEBUG'] ?? 'false', FILTER_VALIDATE_BOOLEAN),
    'timezone' => $_ENV['APP_TIMEZONE'] ?? 'Asia/Jakarta',
];

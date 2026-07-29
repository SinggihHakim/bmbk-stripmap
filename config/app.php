<?php

/**
 * ============================================================
 * Konfigurasi Aplikasi
 * ============================================================
 * Nilai dibaca dari environment variables yang dimuat di public/index.php
 * melalui file .env di root project.
 */

return [
    'name'     => $_ENV['APP_NAME']     ?? 'Strip Map Ruas Jalan',
    'base_url' => $_ENV['APP_URL']      ?? 'http://localhost/bmbk-stripmap/public',
    'debug'    => filter_var($_ENV['APP_DEBUG'] ?? 'false', FILTER_VALIDATE_BOOLEAN),
    'timezone' => $_ENV['APP_TIMEZONE'] ?? 'Asia/Jakarta',
];

<?php

/**
 * ============================================================
 * Konfigurasi Database
 * ============================================================
 * Nilai dibaca dari environment variables yang dimuat di public/index.php
 * melalui file .env di root project.
 * Untuk konfigurasi lokal: salin .env.example ke .env dan sesuaikan.
 */

return [
    'host'    => $_ENV['DB_HOST'] ?? 'localhost',
    'port'    => (int) ($_ENV['DB_PORT'] ?? 3306),
    'dbname'  => $_ENV['DB_NAME'] ?? 'stripmap_db',
    'user'    => $_ENV['DB_USER'] ?? 'root',
    'pass'    => $_ENV['DB_PASS'] ?? '',
    'charset' => 'utf8mb4',
];

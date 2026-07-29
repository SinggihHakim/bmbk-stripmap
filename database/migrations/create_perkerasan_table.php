<?php

/**
 * ============================================================
 * Migration: Buat Tabel `perkerasan`
 * ============================================================
 * Jalankan script ini sekali untuk membuat tabel perkerasan.
 * Biasanya dipanggil dari installer atau setup script.
 *
 * Cara jalankan manual (CLI):
 *   php database/migrations/create_perkerasan_table.php
 */

define('BASE_PATH', dirname(__DIR__, 2));

// Load .env
$envFile = BASE_PATH . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#' || strpos($line, '=') === false) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        $key   = trim($key);
        $value = trim($value, " \t\"'");
        if ($key !== '' && !array_key_exists($key, $_ENV)) {
            $_ENV[$key] = $value;
            putenv("$key=$value");
        }
    }
}

require_once BASE_PATH . '/app/helpers/Database.php';
require_once BASE_PATH . '/app/models/Perkerasan.php';

try {
    Perkerasan::autoCreateTable();
    echo "✅ Tabel `perkerasan` berhasil dibuat (atau sudah ada).\n";
} catch (\Exception $e) {
    echo "❌ Gagal membuat tabel `perkerasan`: " . $e->getMessage() . "\n";
    exit(1);
}

<?php

/**
 * ============================================================
 * Class Database — Singleton PDO Connection
 * ============================================================
 * Menggunakan PDO agar lebih aman (prepared statements)
 * dan portabel dibanding mysqli.
 *
 * Cara pakai:
 *   $db  = Database::getInstance();
 *   $pdo = $db->getConnection();
 */

class Database
{
    /** @var Database|null */
    private static ?Database $instance = null;

    /** @var PDO */
    private PDO $pdo;

    /**
     * Constructor — buat koneksi PDO dari config/database.php
     */
    private function __construct()
    {
        $config = require __DIR__ . '/../../config/database.php';

        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $config['host'],
            $config['port'],
            $config['dbname'],
            $config['charset']
        );

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $this->pdo = new PDO($dsn, $config['user'], $config['pass'], $options);
        } catch (PDOException $e) {
            $appConfig = require __DIR__ . '/../../config/app.php';
            $baseUrl   = rtrim($appConfig['base_url'], '/');

            // Tampilkan halaman error yang jelas dengan link ke installer
            http_response_code(500);
            echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Database Error</title>';
            echo '<script src="https://cdn.tailwindcss.com"></script>';
            echo '<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">';
            echo '</head><body class="bg-gray-50 font-[Inter] min-h-screen flex items-center justify-center p-4">';
            echo '<div class="bg-white rounded-2xl shadow-lg border border-gray-200 max-w-md w-full p-8 text-center">';
            echo '<div class="text-5xl mb-4">🗄️</div>';
            echo '<h1 class="text-xl font-bold text-gray-900 mb-2">Database Belum Tersedia</h1>';

            if ($appConfig['debug']) {
                echo '<p class="text-sm text-red-600 bg-red-50 rounded-lg p-3 mb-4 font-mono text-left break-all">' . htmlspecialchars($e->getMessage()) . '</p>';
            }

            echo '<p class="text-sm text-gray-500 mb-6">Jalankan installer untuk membuat database dan tabel secara otomatis.</p>';
            echo '<a href="' . $baseUrl . '/install.php" class="inline-flex items-center gap-2 px-6 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-xl hover:bg-blue-700 transition-colors shadow-sm">🛠️ Jalankan Installer</a>';
            echo '</div></body></html>';
            exit;
        }
    }

    /**
     * Ambil instance singleton Database
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Ambil objek PDO untuk query
     */
    public function getConnection(): PDO
    {
        return $this->pdo;
    }

    /**
     * Cegah clone & unserialize pada singleton
     */
    private function __clone() {}

    public function __wakeup()
    {
        throw new \Exception('Cannot unserialize singleton');
    }
}

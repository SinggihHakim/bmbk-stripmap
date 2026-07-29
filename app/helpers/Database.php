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
            // Lempar exception agar ditangani di entry point (public/index.php),
            // bukan di sini — Database class tidak bertanggung jawab atas presentasi error.
            throw new \RuntimeException(
                'Koneksi database gagal: ' . $e->getMessage(),
                (int) $e->getCode(),
                $e
            );
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

<?php
/**
 * ============================================================
 * INSTALLER — Buat Database & Tabel Otomatis
 * ============================================================
 * Akses: http://localhost/bmbk-stripmap/public/install.php
 * Hapus file ini setelah instalasi berhasil.
 */

define('BASE_PATH', dirname(__DIR__));

$config = require BASE_PATH . '/config/database.php';

$messages = [];
$success  = true;

try {
    // 1. Koneksi ke MySQL TANPA database (untuk buat database baru)
    $dsn = sprintf('mysql:host=%s;port=%d;charset=%s', $config['host'], $config['port'], $config['charset']);
    $pdo = new PDO($dsn, $config['user'], $config['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    $messages[] = ['ok', 'Koneksi ke MySQL berhasil.'];

    // 2. Buat database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$config['dbname']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $messages[] = ['ok', "Database `{$config['dbname']}` berhasil dibuat / sudah ada."];

    // 3. Pilih database
    $pdo->exec("USE `{$config['dbname']}`");

    // 4. Buat tabel ruas_jalan
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `ruas_jalan` (
            `id`         INT UNSIGNED    NOT NULL AUTO_INCREMENT,
            `kode_ruas`  VARCHAR(50)     NOT NULL,
            `nama_ruas`  VARCHAR(255)    NOT NULL,
            `sta_awal`   DECIMAL(10,2)   NOT NULL DEFAULT 0.00,
            `sta_akhir`  DECIMAL(10,2)   NOT NULL DEFAULT 0.00,
            `panjang`    DECIMAL(10,2)   NOT NULL DEFAULT 0.00,
            `created_at` DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uk_kode_ruas` (`kode_ruas`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    $messages[] = ['ok', 'Tabel `ruas_jalan` berhasil dibuat / sudah ada.'];

    // 5. Buat tabel stripmap
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `stripmap` (
            `id`            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
            `ruas_id`       INT UNSIGNED    NOT NULL,
            `sta_awal`      DECIMAL(10,2)   NOT NULL DEFAULT 0.00,
            `sta_akhir`     DECIMAL(10,2)   NOT NULL DEFAULT 0.00,
            `panjang`       DECIMAL(10,2)   NOT NULL DEFAULT 0.00,
            `baik`          DECIMAL(10,2)   NOT NULL DEFAULT 0.00,
            `sedang`        DECIMAL(10,2)   NOT NULL DEFAULT 0.00,
            `rusak_ringan`  DECIMAL(10,2)   NOT NULL DEFAULT 0.00,
            `rusak_berat`   DECIMAL(10,2)   NOT NULL DEFAULT 0.00,
            `created_at`    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at`    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_ruas_id` (`ruas_id`),
            CONSTRAINT `fk_stripmap_ruas`
                FOREIGN KEY (`ruas_id`) REFERENCES `ruas_jalan` (`id`)
                ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    $messages[] = ['ok', 'Tabel `stripmap` berhasil dibuat / sudah ada.'];

    // 6. Verifikasi
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    $messages[] = ['ok', 'Tabel aktif: ' . implode(', ', $tables)];

} catch (PDOException $e) {
    $success = false;
    $messages[] = ['fail', 'ERROR: ' . $e->getMessage()];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installer — Strip Map Ruas Jalan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <script>tailwind.config={theme:{extend:{fontFamily:{sans:['Inter','system-ui','sans-serif']}}}}</script>
</head>
<body class="bg-gray-50 font-sans antialiased min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-lg">
        <div class="bg-white rounded-2xl shadow-lg border border-gray-200 overflow-hidden">
            <!-- Header -->
            <div class="px-6 py-5 border-b border-gray-100 bg-gradient-to-r from-blue-500 to-indigo-600">
                <h1 class="text-xl font-bold text-white">🛠️ Installer</h1>
                <p class="text-blue-100 text-sm mt-1">Strip Map Ruas Jalan — Setup Database</p>
            </div>

            <!-- Messages -->
            <div class="p-6 space-y-3">
                <?php foreach ($messages as [$status, $msg]): ?>
                <div class="flex items-start gap-3 p-3 rounded-xl <?= $status === 'ok' ? 'bg-green-50' : 'bg-red-50' ?>">
                    <span class="text-lg mt-0.5"><?= $status === 'ok' ? '✅' : '❌' ?></span>
                    <p class="text-sm <?= $status === 'ok' ? 'text-green-800' : 'text-red-800' ?>"><?= htmlspecialchars($msg) ?></p>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Footer -->
            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50">
                <?php if ($success): ?>
                <div class="text-center">
                    <p class="text-green-700 font-semibold text-sm mb-3">✅ Instalasi berhasil! Database siap digunakan.</p>
                    <a href="<?= dirname($_SERVER['SCRIPT_NAME']) ?>/"
                       class="inline-flex items-center gap-2 px-6 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-xl hover:bg-blue-700 transition-colors shadow-sm">
                        🚀 Buka Aplikasi
                    </a>
                    <p class="text-xs text-gray-400 mt-3">Hapus file <code>install.php</code> setelah selesai.</p>
                </div>
                <?php else: ?>
                <p class="text-red-600 text-sm text-center">
                    Instalasi gagal. Periksa konfigurasi di <code>config/database.php</code> lalu coba lagi.
                </p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>

<?php

/**
 * ============================================================
 * Helper Functions
 * ============================================================
 * Fungsi-fungsi utilitas yang digunakan di seluruh aplikasi.
 */

/**
 * Redirect ke URL tertentu
 */
function redirect(string $url): void
{
    header("Location: {$url}");
    exit;
}

/**
 * Ambil base URL dari konfigurasi (di-cache agar tidak require berkali-kali)
 */
function base_url(string $path = ''): string
{
    static $baseUrl = null;
    if ($baseUrl === null) {
        $config  = require BASE_PATH . '/config/app.php';
        $baseUrl = rtrim($config['base_url'], '/');
    }
    return $baseUrl . '/' . ltrim($path, '/');
}

/**
 * Render view dengan data — scope diisolasi via closure
 * agar variabel antar view tidak saling bocor (mencegah infinite loop).
 *
 * Ketika merender layout (views yang memiliki key 'content'), fungsi ini
 * secara otomatis menyertakan '__pageData' — salinan bersih data tanpa 'content' —
 * sehingga layout dapat meneruskan data ke child view tanpa get_defined_vars().
 */
function view(string $viewName, array $data = []): void
{
    $__viewFile = BASE_PATH . '/resources/views/' . str_replace('.', '/', $viewName) . '.php';

    if (!file_exists($__viewFile)) {
        die("View [{$viewName}] tidak ditemukan.");
    }

    // Jika data mengandung 'content' (artinya ini adalah layout call),
    // tambahkan '__pageData' sebagai data bersih untuk diteruskan ke child view.
    if (isset($data['content']) && !isset($data['__pageData'])) {
        $pageData = $data;
        unset($pageData['content']);
        $data['__pageData'] = $pageData;
    }

    // Bungkus dalam closure agar scope bersih — hanya variabel dari $data
    // yang tersedia di dalam view, tidak ada kebocoran dari scope pemanggil.
    (static function (string $__file, array $__data): void {
        extract($__data);
        require $__file;
    })($__viewFile, $data);
}

/**
 * Escape output HTML untuk mencegah XSS
 */
function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Ambil nilai dari $_POST dengan default
 */
function old(string $key, string $default = ''): string
{
    return $_POST[$key] ?? $default;
}

/**
 * Set flash message ke session
 */
function flash(string $type, string $message): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['flash'] = [
        'type'    => $type,
        'message' => $message,
    ];
}

/**
 * Ambil dan hapus flash message dari session
 */
function get_flash(): ?array
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}

/**
 * Konversi format STA (misal "2+350") ke meter (2350)
 */
function sta_to_meter(string $sta): float
{
    $sta = trim($sta);
    if (strpos($sta, '+') !== false) {
        [$km, $m] = explode('+', $sta);
        return (float)$km * 1000 + (float)$m;
    }
    return (float)$sta;
}

/**
 * Konversi meter ke format STA (misal 2350 → "2+350")
 */
function meter_to_sta(float $meter): string
{
    $km = floor($meter / 1000);
    $m  = $meter - ($km * 1000);
    return sprintf('%d+%03d', $km, $m);
}

/**
 * Format angka dengan separator ribuan
 */
function format_number(float $number, int $decimals = 0): string
{
    return number_format($number, $decimals, ',', '.');
}

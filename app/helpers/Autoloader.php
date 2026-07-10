<?php

/**
 * Autoloader sederhana
 *
 * Memuat class secara otomatis berdasarkan namespace/path maupun fallback global class.
 */

spl_autoload_register(function ($className) {
    // Mapping namespace ke direktori
    $namespaceMap = [
        'App\\Controllers\\' => BASE_PATH . '/app/controllers/',
        'App\\Models\\'      => BASE_PATH . '/app/models/',
        'App\\Services\\'    => BASE_PATH . '/app/services/',
        'App\\Helpers\\'     => BASE_PATH . '/app/helpers/',
    ];

    foreach ($namespaceMap as $namespace => $directory) {
        if (strpos($className, $namespace) === 0) {
            $relativeClass = substr($className, strlen($namespace));
            $file = $directory . str_replace('\\', '/', $relativeClass) . '.php';

            if (file_exists($file)) {
                require_once $file;
                return;
            }
        }
    }

    // Fallback: cari class global di direktori aplikasi
    $directories = [
        BASE_PATH . '/app/controllers/',
        BASE_PATH . '/app/models/',
        BASE_PATH . '/app/services/',
        BASE_PATH . '/app/helpers/',
    ];

    foreach ($directories as $directory) {
        $file = $directory . $className . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

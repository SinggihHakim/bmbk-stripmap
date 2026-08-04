<?php

/**
 * ============================================================
 * Root Entry Point Fallback
 * ============================================================
 * Memastikan request ke root folder (misal: http://localhost/bmbk-stripmap/)
 * langsung diteruskan ke public/index.php jika web server tidak meng-apply .htaccess rewrite.
 */

require_once __DIR__ . '/public/index.php';

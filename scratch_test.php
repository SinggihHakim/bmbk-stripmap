<?php
define('BASE_PATH', __DIR__);
require_once BASE_PATH . '/app/helpers/Database.php';

$pdo = Database::getInstance()->getConnection();
$stmt = $pdo->query("SELECT kabupaten_kota, COUNT(*) as total_ruas FROM ruas_jalan GROUP BY kabupaten_kota ORDER BY kabupaten_kota ASC");
print_r($stmt->fetchAll());

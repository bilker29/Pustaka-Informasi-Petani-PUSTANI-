<?php
/**
 * config/koneksi.php
 * Koneksi database yang lebih tahan banting:
 * - paksa TCP (127.0.0.1) untuk menghindari masalah socket
 * - log error ke error_log, jangan dump stack trace ke browser
 */

$host = getenv('DB_HOST') ?: 'localhost'; 
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASSWORD') ?: '';
$db   = getenv('DB_NAME') ?: 'pustani_db';

$koneksi = mysqli_connect($host, $user, $pass, $db);

try {
    $koneksi = mysqli_connect($host, $user, $pass, $db);
    $koneksi->set_charset('utf8mb4');
} catch (mysqli_sql_exception $e) {
    error_log('Database connection error: ' . $e->getMessage());
    http_response_code(500);
    echo "Database connection error. Silakan hubungi administrator.";
    exit;
}
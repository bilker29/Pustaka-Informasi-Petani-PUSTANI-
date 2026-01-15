<?php
$host = 'pustani-db';
$user = 'docker_user';
$pass = 'docker_pass';
$db   = 'pustani_db';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $koneksi = mysqli_connect($host, $user, $pass, $db);
    $koneksi->set_charset('utf8mb4');
} catch (mysqli_sql_exception $e) {
    die("Koneksi Database Gagal: " . $e->getMessage());
}

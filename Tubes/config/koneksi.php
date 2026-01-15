<?php
// Tubes/config/koneksi.php

// Pakai nama service dari docker-compose.yml
$host = 'pustani-db';
$user = 'docker_user';
$pass = 'docker_pass';
$db   = 'pustani_db';

// Aktifkan laporan error biar kita tahu kalau koneksi gagal
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $koneksi = mysqli_connect($host, $user, $pass, $db);
    $koneksi->set_charset('utf8mb4');
} catch (mysqli_sql_exception $e) {
    // Kalau ini muncul di web, berarti PHP gagal nemu database
    die("Error Koneksi ke Database: " . $e->getMessage());
}

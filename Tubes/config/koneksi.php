<?php
$host = getenv('DB_HOST') ?: 'localhost'; 
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASSWORD') ?: '';
$db   = getenv('DB_NAME') ?: 'pustani_db';

$koneksi = mysqli_connect($host, $user, $pass, $db);

if (!$koneksi) {
    // Tampilkan error hanya jika environment bukan PROD untuk keamanan
    if (getenv('APP_ENV') !== 'PROD') {
        die("Koneksi gagal: " . mysqli_connect_error());
    } else {
        die("Koneksi database bermasalah.");
    }
}
?>
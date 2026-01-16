<?php
$host = 'pustani-db';
$user = 'docker_user';
$pass = 'docker_pass';
$db   = 'pustani_db';

// AMBIL VARIABLE ENV DARI DOCKER
$env = getenv('APP_ENV') ?: 'local'; // Default ke 'local' jika tidak ada setting

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $koneksi = mysqli_connect($host, $user, $pass, $db);
    $koneksi->set_charset('utf8mb4');
} catch (mysqli_sql_exception $e) {
    // ENVIRONMENT LOGIC:
    if ($env === 'production') {
        // Jika Production: Tampilkan pesan umum (Aman)
        error_log("DB Error: " . $e->getMessage()); // Catat ke log server saja
        die("Maaf, terjadi gangguan pada server. Silahkan coba lagi nanti.");
    } else {
        // Jika Local: Tampilkan error asli untuk debugging
        die("Koneksi Database Gagal: " . $e->getMessage());
    }
}
?>
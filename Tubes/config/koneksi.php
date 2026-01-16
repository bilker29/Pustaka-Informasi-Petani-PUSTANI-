<?php
/**
 * File: koneksi.php
 * Konfigurasi koneksi database Hybrid (Bisa untuk Docker & Laragon/Localhost)
 */

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// 1. Deteksi Environment
// Jika ada env DB_HOST (dari Docker), pakai itu.
// Jika tidak ada, kita asumsikan sedang di Local Windows (Laragon/XAMPP).
$env_host = getenv('DB_HOST');

if ($env_host) {
    // --- MODE DOCKER ---
    $host = $env_host;          // 'pustani-db'
    $user = 'docker_user';      // User dari docker-compose
    $pass = 'docker_pass';      // Password dari docker-compose
    $db   = 'pustani_db';
} else {
    // --- MODE LOCAL WINDOWS (Laragon/XAMPP) ---
    // Pastikan port 3306 di docker-compose sudah dimapping (ports: - "3306:3306")
    $host = '127.0.0.1';        // Gunakan IP Loopback agar lebih cepat dari 'localhost'
    $user = 'docker_user';      // Tetap pakai user Docker karena kita connect ke DB Docker
    $pass = 'docker_pass';
    $db   = 'pustani_db';
}

try {
    $koneksi = mysqli_connect($host, $user, $pass, $db);
    mysqli_set_charset($koneksi, 'utf8mb4');
    
    // Test koneksi (Opsional, hapus nanti)
    // echo "Koneksi sukses ke: " . $host; 
    
} catch (mysqli_sql_exception $e) {
    // Tampilkan error yang jelas
    die("<h3>KONEKSI DATABASE GAGAL (Loading Terus?)</h3>" .
        "<hr>" .
        "<strong>Penyebab:</strong> Komputer tidak bisa menghubungi database.<br>" .
        "<strong>Host yang dicoba:</strong> " . $host . "<br>" .
        "<strong>Error Detail:</strong> " . $e->getMessage() . "<br><br>" .
        "<em>Tips: Jika pakai Laragon/XAMPP tapi DB di Docker, pastikan 'ports: 3306:3306' ada di docker-compose.yml</em>");
}
?>
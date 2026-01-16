<?php
/**
 * File: koneksi.php
 * Konfigurasi koneksi database Hybrid (Otomatis deteksi Docker vs Localhost/Laragon)
 * * Perbaikan:
 * 1. Menangani akses dari dalam container (pustani-db).
 * 2. Menangani akses dari Laragon/Windows (127.0.0.1).
 * 3. Error reporting yang lebih jelas.
 */

// Aktifkan mode error reporting yang ketat untuk debugging database
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// 1. Deteksi Lingkungan (Environment)
// Cek apakah variabel 'DB_HOST' ada di environment (biasanya diset oleh Docker Compose)
$env_host = getenv('DB_HOST');

if ($env_host) {
    // --- SKENARIO 1: JALAN DI DALAM DOCKER ---
    // PHP berjalan di dalam container, jadi dia kenal nama service 'pustani-db'
    $host = $env_host;          // Nilainya: 'pustani-db'
    $user = getenv('DB_USER') ?: 'docker_user';
    $pass = getenv('DB_PASSWORD') ?: 'docker_pass';
    $db   = getenv('DB_NAME') ?: 'pustani_db';
} else {
    // --- SKENARIO 2: JALAN DI LOCAL WINDOWS (LARAGON/XAMPP) ---
    // PHP berjalan di Windows, dia tidak kenal 'pustani-db'.
    // Kita harus tembak IP Loopback (127.0.0.1) ke port 3306 yang sudah dibuka Docker.
    $host = '127.0.0.1';        // PENTING: Gunakan IP ini, jangan 'localhost' biar lebih stabil
    $user = 'docker_user';      // Sama dengan yang diatur di docker-compose.yml
    $pass = 'docker_pass';      // Sama dengan yang diatur di docker-compose.yml
    $db   = 'pustani_db';
}

try {
    // 2. Mencoba Membuat Koneksi
    $koneksi = mysqli_connect($host, $user, $pass, $db);
    
    // Set charset agar support emoji dan karakter khusus
    mysqli_set_charset($koneksi, 'utf8mb4');
    
    // (Opsional) Uncomment baris di bawah ini jika ingin tes koneksi berhasil
    // echo "Koneksi Berhasil ke: " . $host; 

} catch (mysqli_sql_exception $e) {
    // 3. Menampilkan Pesan Error yang Manusiawi (Jika Gagal)
    // Menggunakan style CSS inline sederhana agar pesan error terlihat jelas di browser
    die("<div style='background-color:#f8d7da; color:#721c24; padding:20px; margin:20px; border:1px solid #f5c6cb; border-radius:8px; font-family: sans-serif;'>
            <h3 style='margin-top:0;'>⚠️ KONEKSI DATABASE GAGAL</h3>
            <p>Sistem tidak dapat terhubung ke database. Berikut detail masalahnya:</p>
            <ul style='background: #fff; padding: 15px 30px; border-radius: 5px;'>
                <li><strong>Host yang dicoba:</strong> " . htmlspecialchars($host) . "</li>
                <li><strong>User:</strong> " . htmlspecialchars($user) . "</li>
                <li><strong>Database:</strong> " . htmlspecialchars($db) . "</li>
                <li><strong>Pesan Error Sistem:</strong> " . $e->getMessage() . "</li>
            </ul>
            <p><strong>Saran Perbaikan:</strong><br>
            1. Pastikan Docker Desktop sudah berjalan dan status container <em>pustani-db</em> adalah 'Up'.<br>
            2. Jika menggunakan Laragon, pastikan tidak ada MySQL lain yang menggunakan port 3306.</p>
         </div>");
}
?>
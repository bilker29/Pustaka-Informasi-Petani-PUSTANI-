<?php
/**
 * File: koneksi.php
 * Konfigurasi koneksi database yang aman untuk Docker & Production
 */

// 1. Ambil Kredensial
// Prioritaskan variabel dari docker-compose (getenv), jika kosong pakai default string.
// PENTING: Pastikan 'pustani-db' sesuai dengan nama service database di docker-compose.yml
$host = getenv('DB_HOST') ?: 'pustani-db'; 
$user = getenv('DB_USER') ?: 'docker_user';
$pass = getenv('DB_PASSWORD') ?: 'docker_pass';
$db   = getenv('DB_NAME') ?: 'pustani_db';

// 2. Cek Environment (Local atau Production)
$env = getenv('APP_ENV') ?: 'local';

// 3. Aktifkan pelaporan error MySQLi
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // 4. Buka Koneksi
    $koneksi = mysqli_connect($host, $user, $pass, $db);
    
    // 5. Set Charset (Penting agar support emoji & karakter khusus)
    mysqli_set_charset($koneksi, 'utf8mb4');

} catch (mysqli_sql_exception $e) {
    // 6. Logika Error Handling
    if ($env === 'production') {
        // Jika Production: Jangan tampilkan error teknis ke user (Bahaya keamanan)
        error_log("DB Connection Error: " . $e->getMessage()); 
        die("Maaf, terjadi gangguan pada server. Silahkan coba lagi nanti.");
    } else {
        // Jika Local: Tampilkan error detail untuk debugging
        die("<strong>KONEKSI DATABASE GAGAL!</strong><br><hr>" .
            "Error Message: " . $e->getMessage() . "<br>" .
            "Host yang dicoba: " . $host . " (Pastikan ini nama service di docker-compose)<br>" .
            "User yang dicoba: " . $user . "<br>" .
            "Database: " . $db);
    }
}

// Opsional: Cek koneksi sukses (hanya untuk debug, hapus baris ini nanti)
// echo "Koneksi Berhasil ke " . $host;
?>

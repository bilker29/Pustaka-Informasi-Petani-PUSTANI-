<?php
// Deteksi jika berjalan di dalam Docker (Lewat Environment Variable)
if (getenv('DOCKER_ENV')) {
    $host = getenv('DB_HOST') ?: 'localhost';
    $user = getenv('DB_USER') ?: 'root';
    $pass = getenv('DB_PASS') ?: '';
    $db   = getenv('DB_NAME') ?: 'pustani_db';
} 
// Fallback ke Logika Level 3 (AWS/Laptop biasa)
else {
    if (file_exists('/var/www/pustani_prod_marker')) {
        $env = 'PROD';
    } else {
        $env = 'DEV';
    }
    // ... (kode lama Anda untuk DEV/PROD AWS Level 3)
}

$koneksi = mysqli_connect($host, $user, $pass, $db);
// ...
?>
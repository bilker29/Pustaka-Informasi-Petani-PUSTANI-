<?php

/**
 * config/koneksi.php
 */

// Gunakan 127.0.0.1 untuk menghindari error "No such file or directory" (socket error)
$host = getenv('DB_HOST') ?: '127.0.0.1';
$user = getenv('DB_USER') ?: 'docker_user';
$pass = getenv('DB_PASSWORD') ?: 'docker_pass';
$db   = getenv('DB_NAME') ?: 'pustani_db';

// Mengatur agar mysqli melempar exception saat terjadi error
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // Hanya lakukan koneksi satu kali di dalam blok try
    $koneksi = mysqli_connect($host, $user, $pass, $db);
    $koneksi->set_charset('utf8mb4');
} catch (mysqli_sql_exception $e) {
    // Log error secara internal, jangan tampilkan detail sensitif ke pengguna
    error_log('Database connection error: ' . $e->getMessage());

    http_response_code(500);
    echo "Koneksi database gagal. Silakan hubungi administrator.";
    exit;
}

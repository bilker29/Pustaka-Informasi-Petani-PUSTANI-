<?php
// Deteksi Environment: Cek apakah ada file penanda khusus di server AWS
// (File 'pustani_prod_marker' ini sudah Anda buat lewat terminal sebelumnya)
if (file_exists('/var/www/pustani_prod_marker')) {
    $env = 'PROD';
} else {
    $env = 'DEV';
}

if ($env == 'DEV') {
    // === SETTINGAN LAPTOP (LARAGON) ===
    $host = "localhost";
    $user = "root";
    $pass = "";
    $db   = "pustani_db";
} else {
    // === SETTINGAN SERVER AWS (LEVEL 3) ===
    $host = "localhost";
    
    // Ganti dengan User Database yang Anda buat di AWS (bukan root)
    $user = "admin_aws"; 
    
    // Ganti dengan Password Database AWS yang KUAT tadi
    $pass = "PasswordAWSKuat123!"; 
    
    $db   = "pustani_db";
}

$koneksi = mysqli_connect($host, $user, $pass, $db);

if (!$koneksi) {
    // Supaya aman, pesan error detail hanya muncul di laptop
    if ($env == 'DEV') {
        die("Gagal koneksi: " . mysqli_connect_error());
    } else {
        die("Mohon maaf, sistem sedang sibuk.");
    }
}
?>
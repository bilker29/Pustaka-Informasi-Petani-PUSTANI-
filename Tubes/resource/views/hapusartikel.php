<?php
session_start();
require '../../config/koneksi.php';

// Cek login
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

$id_artikel = $_GET['id'];
$user_id = $_SESSION['user_id'];
$status_hapus = "";

// 1. Cek apakah artikel ini benar milik user yang sedang login?
$query_cek = "SELECT * FROM articles WHERE id = '$id_artikel' AND user_id = '$user_id'";
$result_cek = mysqli_query($koneksi, $query_cek);

if (mysqli_num_rows($result_cek) === 1) {
    $row = mysqli_fetch_assoc($result_cek);
    $gambar_lama = $row['image'];

    // 2. Hapus gambar fisik
    if (!filter_var($gambar_lama, FILTER_VALIDATE_URL)) {
        $path_gambar = "../../public/img/img1/" . $gambar_lama;
        if (file_exists($path_gambar)) {
            unlink($path_gambar);
        }
    }

    // 3. Hapus data dari database
    // (Opsional: Hapus rating dan komentar terkait dulu jika ada relasi Foreign Key)
    mysqli_query($koneksi, "DELETE FROM article_ratings WHERE article_id = '$id_artikel'");
    mysqli_query($koneksi, "DELETE FROM article_comments WHERE article_id = '$id_artikel'");

    $query_hapus = "DELETE FROM articles WHERE id = '$id_artikel'";
    if (mysqli_query($koneksi, $query_hapus)) {
        $status_hapus = "success";
    } else {
        $status_hapus = "error";
    }
} else {
    $status_hapus = "not_found";
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menghapus...</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        body {
            background-color: #FAF1E6;
            font-family: sans-serif;
        }
    </style>
</head>

<body>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        <?php if ($status_hapus == "success") : ?>
            Swal.fire({
                title: 'Terhapus!',
                text: 'Artikel Anda berhasil dihapus.',
                icon: 'success',
                timer: 1500,
                showConfirmButton: false
            }).then(() => {
                // REDIRECT KEMBALI KE DASHBOARD
                window.location = 'dashboard.php';
            });

        <?php elseif ($status_hapus == "not_found") : ?>
            Swal.fire({
                title: 'Gagal!',
                text: 'Artikel tidak ditemukan atau Anda tidak memiliki izin.',
                icon: 'error'
            }).then(() => {
                window.location = 'dashboard.php';
            });

        <?php else : ?>
            Swal.fire({
                title: 'Error!',
                text: 'Terjadi kesalahan sistem.',
                icon: 'error'
            }).then(() => {
                window.location = 'dashboard.php';
            });
        <?php endif; ?>
    </script>

</body>

</html>
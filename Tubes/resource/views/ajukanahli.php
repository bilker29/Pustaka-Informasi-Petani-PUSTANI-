<?php
session_start();
require '../../config/koneksi.php';

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// --- LOGIKA PROSES FORM ---
if (isset($_POST['submit_pengajuan'])) {
    $keahlian = mysqli_real_escape_string($koneksi, $_POST['keahlian']);
    $bio = mysqli_real_escape_string($koneksi, $_POST['bio']);

    // Proses Upload Dokumen
    $nama_file = "";
    if (!empty($_FILES['dokumen']['name'])) {
        $ext = pathinfo($_FILES['dokumen']['name'], PATHINFO_EXTENSION);
        $nama_file = "CV_" . time() . "_" . $user_id . "." . $ext;

        // Buat folder jika belum ada
        if (!is_dir("../../public/img/img1/dokumen/")) {
            mkdir("../../public/img/img1/dokumen/", 0777, true);
        }
        move_uploaded_file($_FILES['dokumen']['tmp_name'], "../../public/img/img1/dokumen/" . $nama_file);
    }

    $update = mysqli_query($koneksi, "UPDATE users SET 
        keahlian = '$keahlian', 
        bio = '$bio', 
        status_ahli = 'pending',
        dokumen_pendukung = '$nama_file'
        WHERE id = '$user_id'");

    if ($update) {
        header("Location: profilsaya.php?status=success_apply");
        exit;
    }
}

// Ambil data user untuk tampilan
$user_query = mysqli_query($koneksi, "SELECT * FROM users WHERE id = '$user_id'");
$user_data = mysqli_fetch_assoc($user_query);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Pustani - Ajukan Ahli</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #1a3e35;
            --accent-color: #2d9d78;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #FAF1E6;
            color: #334155;
        }

        .navbar {
            height: 80px;
            background: #ffffff;
            border-bottom: 1px solid #edf2f7;
        }

        .profile-header {
            height: 200px;
            background: linear-gradient(135deg, #1a3e35 0%, #2d9d78 100%);
        }

        .form-card {
            background: white;
            border-radius: 2.5rem;
            padding: 3rem;
            box-shadow: 0 25px 50px -12px rgba(26, 62, 53, 0.12);
            margin-top: -100px;
        }

        .btn-pustani {
            background: var(--primary-color);
            color: white;
            border-radius: 50px;
            padding: 12px 30px;
            font-weight: 700;
            border: none;
            transition: 0.3s;
        }

        .btn-pustani:hover {
            background: var(--accent-color);
            color: white;
        }

        .form-control {
            border-radius: 12px;
            padding: 12px 20px;
            border: 1px solid #e2e8f0;
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container">
            <a class="navbar-brand" href="Home.php"><img src="../../public/img/img1/logo navbar.png" height="50"></a>
        </div>
    </nav>

    <div class="profile-header"></div>

    <main class="container pb-5">
        <div class="row justify-content-center">
            <div class="col-lg-7">
                <div class="form-card">
                    <div class="text-center mb-4">
                        <h2 class="fw-800" style="color: var(--primary-color);">Ajukan Diri Sebagai Ahli</h2>
                        <p class="text-muted">Lengkapi formulir di bawah untuk diverifikasi oleh tim Pustani.</p>
                    </div>

                    <form action="" method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Bidang Keahlian</label>
                            <input type="text" name="keahlian" class="form-control" placeholder="Misal: Spesialis Padi, Praktisi Hidroponik" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Bio & Pengalaman Singkat</label>
                            <textarea name="bio" class="form-control" rows="4" placeholder="Ceritakan latar belakang pendidikan atau pengalaman praktis Anda..." required></textarea>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Dokumen Pendukung (CV/Sertifikat)</label>
                            <input type="file" name="dokumen" class="form-control">
                            <small class="text-muted text-start d-block mt-1">*Format JPG/PNG/PDF. Dokumen ini membantu kami memvalidasi keahlian Anda.</small>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" name="submit_pengajuan" class="btn btn-pustani shadow">Kirim Pengajuan Sekarang</button>
                            <a href="profilsaya.php" class="btn btn-link text-muted text-decoration-none fw-bold">Kembali ke Profil</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
</body>

</html>
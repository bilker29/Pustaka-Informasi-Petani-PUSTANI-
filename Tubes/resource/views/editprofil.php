<?php
session_start();
require '../../config/koneksi.php';

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

// PROTEKSI: Admin dilarang masuk ke area user
if ($_SESSION['role'] === 'admin') {
    header("Location: admin/dashboard.php");
    exit;
}

$id = $_SESSION['user_id'];
$status_update = "";

// PROSES UPDATE
if (isset($_POST['simpan'])) {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $no_hp = mysqli_real_escape_string($koneksi, $_POST['no_hp']);
    $alamat = mysqli_real_escape_string($koneksi, $_POST['alamat']);
    $keahlian = mysqli_real_escape_string($koneksi, $_POST['keahlian']);
    $bio = mysqli_real_escape_string($koneksi, $_POST['bio']);

    $query_foto = "";
    if ($_FILES['foto']['error'] === 0) {
        $namaFile = $_FILES['foto']['name'];
        $tmpName = $_FILES['foto']['tmp_name'];
        $ekstensi = strtolower(pathinfo($namaFile, PATHINFO_EXTENSION));
        $valid = ['jpg', 'jpeg', 'png'];

        if (in_array($ekstensi, $valid)) {
            $namaBaru = uniqid() . '.' . $ekstensi;
            $folderTujuan = '../../public/img/img1/profil/';
            if (!is_dir($folderTujuan)) mkdir($folderTujuan, 0777, true);

            if (move_uploaded_file($tmpName, $folderTujuan . $namaBaru)) {
                // Hapus foto lama jika ada
                $old_data = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT foto_profil FROM users WHERE id='$id'"));
                if (!empty($old_data['foto_profil']) && file_exists($folderTujuan . $old_data['foto_profil'])) {
                    unlink($folderTujuan . $old_data['foto_profil']);
                }
                $query_foto = ", foto_profil='$namaBaru'";
            }
        }
    }

    $query = "UPDATE users SET 
              username='$username', 
              no_hp='$no_hp', 
              alamat='$alamat', 
              keahlian='$keahlian', 
              bio='$bio' 
              $query_foto 
              WHERE id='$id'";

    if (mysqli_query($koneksi, $query)) {
        $_SESSION['username'] = $username;
        $status_update = "success";
    } else {
        $status_update = "error";
    }
}

// AMBIL DATA TERBARU
$user = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM users WHERE id='$id'"));
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Pustani - Edit Profil</title>
    <link rel="icon" href="../../public/img/img1/logo.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=Lora:wght@400;600&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary-color: #064e3b;
            --accent-color: #16a34a;
            --bg-soft: #f0fdf4;
            --text-main: #374151;
            --nav-height: 80px;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #FAF1E6;
            color: var(--text-main);
        }

        /* Navbar & Nav Profile Styling */
        .navbar {
            height: var(--nav-height);
            background: #ffffff;
            border-bottom: 1px solid #edf2f7;
            z-index: 1000;
        }

        .nav-profile-img {
            width: 45px;
            height: 45px;
            object-fit: cover;
            border-radius: 50%;
            border: 2px solid #e2e8f0;
        }

        .custom-dropdown-btn {
            border: none;
            background: transparent;
            padding: 0;
        }

        .dropdown-menu-custom {
            border: none;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding: 10px 0;
            min-width: 220px;
            margin-top: 15px !important;
        }

        .dropdown-header-custom {
            padding: 10px 20px;
            font-weight: 700;
            color: #64748b;
            font-size: 0.95rem;
            border-bottom: 1px solid #f1f5f9;
            margin-bottom: 5px;
        }

        .dropdown-item {
            padding: 10px 20px;
            font-weight: 600;
            color: #334155;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .dropdown-item:hover {
            background-color: var(--bg-soft);
            color: var(--primary-color);
        }

        .logout-item {
            color: #ef4444 !important;
        }

        .search-btn-nav {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            color: var(--text-main);
            padding: 0.5rem 1.25rem;
            border-radius: 50px;
            text-decoration: none;
            font-size: 0.95rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        footer {
            background: #1a3e35;
            color: white;
            padding: 5rem 0 2rem;
        }

        .footer-logo {
            height: 70px;
            width: auto;
            margin-bottom: 1.5rem;
        }
    </style>
</head>

<body>

    <?php include 'layout/navbar.php'; ?>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                    <div class="card-header bg-success text-white py-3 text-center">
                        <h4 class="mb-0 fw-bold">Edit Biodata Diri</h4>
                    </div>
                    <div class="card-body p-4 p-md-5">

                        <form action="" method="POST" enctype="multipart/form-data">

                            <div class="row mb-4">
                                <div class="col-md-12 text-center mb-3">
                                    <label class="form-label fw-bold d-block">Foto Profil</label>
                                    <?php
                                    $foto = "https://ui-avatars.com/api/?name=" . urlencode($user['username']);
                                    if (!empty($user['foto_profil']) && file_exists('../../public/img/img1/profil/' . $user['foto_profil'])) {
                                        $foto = '../../public/img/img1/profil/' . $user['foto_profil'];
                                    }
                                    ?>
                                    <img src="<?= $foto; ?>" class="rounded-circle mb-3 border border-3 border-success" style="width: 100px; height: 100px; object-fit: cover;">
                                    <input type="file" name="foto" class="form-control w-75 mx-auto">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold text-success">Username</label>
                                    <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($user['username']); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold text-success">No. Handphone</label>
                                    <input type="text" name="no_hp" class="form-control" value="<?= htmlspecialchars($user['no_hp'] ?? ''); ?>" placeholder="0812...">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold text-success">Keahlian / Spesialisasi</label>
                                <input type="text" name="keahlian" class="form-control" value="<?= htmlspecialchars($user['keahlian'] ?? ''); ?>" placeholder="Contoh: Pakar Agronomi, Petani Hidroponik">
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold text-success">Alamat Lengkap</label>
                                <textarea name="alamat" class="form-control" rows="2" placeholder="Masukkan alamat lengkap..."><?= htmlspecialchars($user['alamat'] ?? ''); ?></textarea>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold text-success">Bio Singkat</label>
                                <textarea name="bio" class="form-control" rows="3" placeholder="Ceritakan sedikit tentang pengalaman bertanimu..."><?= htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="profilsaya.php" class="btn btn-secondary px-4 rounded-pill fw-bold">Batal</a>
                                <button type="submit" name="simpan" class="btn btn-success px-5 rounded-pill fw-bold">Simpan Perubahan</button>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'layout/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        <?php if ($status_update == "success") : ?>
            Swal.fire({
                title: 'Berhasil!',
                text: 'Data profil berhasil diperbarui.',
                icon: 'success',
                confirmButtonColor: '#198754'
            }).then(() => {
                window.location = 'profilsaya.php';
            });
        <?php endif; ?>
    </script>
</body>

</html>
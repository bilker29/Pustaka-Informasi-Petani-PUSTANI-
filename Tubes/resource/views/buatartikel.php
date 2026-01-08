<?php
session_start();
require '../../config/koneksi.php';

// 1. Cek Login
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

// 2. CEK ROLE
$id_user = $_SESSION['user_id'];
$cek_user = mysqli_query($koneksi, "SELECT role FROM users WHERE id = '$id_user'");
$data_user = mysqli_fetch_assoc($cek_user);

// PROTEKSI: Admin dilarang masuk ke area form user, arahkan ke dashboard admin
if ($data_user['role'] === 'admin') {
    header("Location: admin/dashboard.php");
    exit;
}

// Jika bukan Expert -> Tolak dengan Alert Konfirmasi
if ($data_user['role'] !== 'expert') {
?>
    <!DOCTYPE html>
    <html lang="id">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Akses Dibatasi</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
        <style>
            body {
                font-family: 'Plus Jakarta Sans', sans-serif;
                background-color: #FAF1E6;
            }
        </style>
    </head>

    <body>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            Swal.fire({
                title: 'Akses Dibatasi',
                text: 'Anda harus menjadi Ahli Tani untuk menulis artikel.',
                icon: 'warning',
                confirmButtonText: 'Kembali',
                confirmButtonColor: '#1a3e35'
            }).then(() => {
                window.location.href = 'profilsaya.php';
            });
        </script>
    </body>

    </html>
<?php
    exit;
}

// --- PROSES UPLOAD ---
$status_post = "";

if (isset($_POST['publish'])) {
    $title = mysqli_real_escape_string($koneksi, $_POST['title']);
    $category = $_POST['category'];
    $content = mysqli_real_escape_string($koneksi, $_POST['content']);
    $user_id = $_SESSION['user_id'];

    // PERBAIKAN LOGIKA: Semua artikel baru wajib 'draft' untuk verifikasi Admin
    $status = 'draft';

    $gambar = "";
    if ($_FILES['cover_image']['error'] === 0) {
        $namaFile = $_FILES['cover_image']['name'];
        $tmpName = $_FILES['cover_image']['tmp_name'];
        $ekstensi = strtolower(pathinfo($namaFile, PATHINFO_EXTENSION));

        if (in_array($ekstensi, ['jpg', 'jpeg', 'png'])) {
            $namaFileBaru = uniqid() . '.' . $ekstensi;
            if (move_uploaded_file($tmpName, '../../public/img/img1/' . $namaFileBaru)) {
                $gambar = $namaFileBaru;
            } else {
                $status_post = "upload_fail";
            }
        } else {
            $status_post = "invalid_type";
        }
    } else {
        $status_post = "no_image";
    }

    if ($gambar) {
        $query = "INSERT INTO articles (user_id, title, category, content, image, status) 
                  VALUES ('$user_id', '$title', '$category', '$content', '$gambar', '$status')";

        if (mysqli_query($koneksi, $query)) {
            $status_post = "success";
        } else {
            $status_post = "db_error";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Pustani - Buat Artikel</title>
    <link rel="icon" href="../../public/img/img1/Logo.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary-color: #064e3b;
            --accent-color: #16a34a;
            --bg-soft: #f0fdf4;
            --text-main: #374151;
            --nav-height: 80px;
        }

        body {
            background-color: #FAF1E6;
            font-family: 'Plus Jakarta Sans', sans-serif;
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

        .editor-container {
            background: white;
            border-radius: 1.5rem;
            padding: 2.5rem;
            margin-bottom: 5rem;
            border: 1px solid #f0f0f0;
        }

        .action-card {
            background: white;
            border-radius: 1.2rem;
            padding: 1.5rem;
            border: 1px solid #f0f0f0;
        }

        .btn-publish {
            background-color: #16a34a;
            color: white;
            width: 100%;
            border-radius: 50px;
            font-weight: bold;
        }

        .btn-publish:hover {
            background-color: #15803d;
            color: white;
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

    <div class="container mt-5">
        <h2 class="fw-bold mb-4" style="color: #064e3b;">Tulis Artikel Baru</h2>

        <form action="" method="POST" enctype="multipart/form-data">
            <div class="row">
                <div class="col-lg-8">
                    <div class="editor-container shadow-sm">
                        <div class="mb-4">
                            <label class="form-label fw-bold">Judul Artikel</label>
                            <input type="text" name="title" class="form-control form-control-lg" placeholder="Contoh: Cara Menanam Cabai Rawit..." required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Kategori</label>
                            <select name="category" class="form-select" required>
                                <option value="" selected disabled>Pilih Kategori</option>
                                <option value="Tips & Trik">Tips & Trik</option>
                                <option value="Hama & Penyakit">Hama & Penyakit</option>
                                <option value="Bisnis Tani">Bisnis Tani</option>
                                <option value="Teknologi">Teknologi</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Foto Sampul</label>
                            <input type="file" name="cover_image" class="form-control" required>
                            <small class="text-muted">Format JPG/PNG. Maks 2MB.</small>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Isi Artikel</label>
                            <textarea name="content" class="form-control" rows="12" placeholder="Tulis isi artikel yang bermanfaat di sini..." required></textarea>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="action-card shadow-sm">
                        <h6 class="fw-bold mb-3">Status Publikasi</h6>
                        <div class="mb-3">
                            <div class="alert alert-info py-2 small border-0" style="border-radius: 10px;">
                                <i class="bi bi-info-circle-fill me-2"></i> Artikel Anda akan ditinjau oleh Admin sebelum dipublikasikan secara umum.
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="visibility" value="draft" id="draft" checked disabled>
                                <label class="form-check-label fw-bold text-muted" for="draft">Menunggu Verifikasi Admin</label>
                            </div>
                        </div>
                        <button type="submit" name="publish" class="btn btn-publish py-2">
                            <i class="bi bi-send-fill me-2"></i> Ajukan Artikel
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <?php include 'layout/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        <?php if ($status_post == "success") : ?>
            Swal.fire({
                title: 'Berhasil Diajukan!',
                text: 'Artikel Anda telah dikirim ke Admin untuk proses verifikasi.',
                icon: 'success',
                timer: 3000,
                showConfirmButton: false
            }).then(() => {
                window.location = 'profilsaya.php';
            });

        <?php elseif ($status_post == "no_image") : ?>
            Swal.fire('Ups!', 'Mohon upload foto sampul artikel dulu ya.', 'warning');

        <?php elseif ($status_post == "invalid_type") : ?>
            Swal.fire('Format Salah', 'Hanya file gambar JPG atau PNG yang diperbolehkan.', 'error');

        <?php elseif ($status_post == "upload_fail") : ?>
            Swal.fire('Gagal Upload', 'Terjadi masalah saat mengupload gambar.', 'error');

        <?php elseif ($status_post == "db_error") : ?>
            Swal.fire('Database Error', 'Gagal menyimpan ke database.', 'error');
        <?php endif; ?>
    </script>

</body>

</html>
<?php
session_start();
require '../../config/koneksi.php';

// 1. Cek Login
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

// 2. Cek ID di URL
if (!isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit;
}

$id_artikel = mysqli_real_escape_string($koneksi, $_GET['id']);
$user_id = $_SESSION['user_id'];

// 3. CEK KEPEMILIKAN ARTIKEL
$query_cek = "SELECT * FROM articles WHERE id = '$id_artikel' AND user_id = '$user_id'";
$result_cek = mysqli_query($koneksi, $query_cek);
$data = mysqli_fetch_assoc($result_cek);

if (!$data) {
    echo "<script>
            alert('Akses Ditolak! Anda bukan pemilik artikel ini.');
            document.location.href = 'dashboard.php';
          </script>";
    exit;
}

$status_update = "";

// 4. PROSES UPDATE DATA
if (isset($_POST['update'])) {
    $title = mysqli_real_escape_string($koneksi, $_POST['title']);
    $category = $_POST['category'];
    $content = mysqli_real_escape_string($koneksi, $_POST['content']);

    // Perubahan Logika: Setiap kali diedit, status kembali ke 'draft' agar divalidasi Admin lagi
    $status = 'draft';

    // Cek apakah ada gambar baru yang diupload
    $query_img = "";
    if ($_FILES['image']['error'] === 0) {
        $namaFile = $_FILES['image']['name'];
        $tmpName = $_FILES['image']['tmp_name'];
        $ekstensi = strtolower(pathinfo($namaFile, PATHINFO_EXTENSION));
        $valid = ['jpg', 'jpeg', 'png'];

        if (in_array($ekstensi, $valid)) {
            $namaBaru = uniqid() . '.' . $ekstensi;
            $pathTujuan = '../../public/img/img1/' . $namaBaru;

            if (move_uploaded_file($tmpName, $pathTujuan)) {
                // Hapus gambar lama jika ada dan bukan link URL
                if (!empty($data['image']) && !filter_var($data['image'], FILTER_VALIDATE_URL)) {
                    $old_path = '../../public/img/img1/' . $data['image'];
                    if (file_exists($old_path)) unlink($old_path);
                }
                $query_img = ", image='$namaBaru'";
            }
        }
    }

    // Query Update
    $update_sql = "UPDATE articles SET 
                   title='$title', 
                   category='$category', 
                   content='$content', 
                   status='$status' 
                   $query_img 
                   WHERE id='$id_artikel'";

    if (mysqli_query($koneksi, $update_sql)) {
        $status_update = "success";
        $data = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM articles WHERE id='$id_artikel'"));
    } else {
        $status_update = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Artikel - Pustani</title>
    <link rel="icon" href="../../public/img/img1/logo.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
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

        .card {
            border-radius: 20px;
        }

        .form-control,
        .form-select {
            border-radius: 12px;
            padding: 12px;
            border: 1px solid #e2e8f0;
        }

        .form-label {
            color: var(--primary-color);
        }

        footer {
            background: #1a3e35;
            color: white;
            padding: 5rem 0 2rem;
            margin-top: 5rem;
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

    <div class="container py-5 mt-4">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom-0 pt-4 px-4">
                        <h3 class="fw-bold m-0" style="color: var(--primary-color);">Edit Artikel Saya</h3>
                        <p class="text-muted small">Setelah disimpan, artikel akan dikirim kembali ke Admin untuk divalidasi.</p>
                    </div>
                    <div class="card-body p-4">

                        <form method="POST" enctype="multipart/form-data">

                            <div class="mb-4">
                                <label class="form-label fw-bold">Judul Artikel</label>
                                <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($data['title']); ?>" required>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <label class="form-label fw-bold">Kategori</label>
                                    <select name="category" class="form-select" required>
                                        <option value="Tips & Trik" <?= $data['category'] == 'Tips & Trik' ? 'selected' : ''; ?>>Tips & Trik</option>
                                        <option value="Hama & Penyakit" <?= $data['category'] == 'Hama & Penyakit' ? 'selected' : ''; ?>>Hama & Penyakit</option>
                                        <option value="Bisnis Tani" <?= $data['category'] == 'Bisnis Tani' ? 'selected' : ''; ?>>Bisnis Tani</option>
                                        <option value="Teknologi" <?= $data['category'] == 'Teknologi' ? 'selected' : ''; ?>>Teknologi</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <label class="form-label fw-bold">Status Saat Ini</label>
                                    <div class="mt-2">
                                        <?php if ($data['status'] == 'published') : ?>
                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-2">
                                                <i class="bi bi-check-circle-fill me-1"></i> Terbit (Published)
                                            </span>
                                        <?php else : ?>
                                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-3 py-2">
                                                <i class="bi bi-clock-history me-1"></i> Menunggu Validasi (Draft)
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold">Gambar Sampul</label>
                                <div class="d-flex align-items-center gap-4 mb-3 p-3 bg-light rounded-4">
                                    <?php
                                    $imgSrc = $data['image'];
                                    if (!filter_var($imgSrc, FILTER_VALIDATE_URL)) $imgSrc = "../../public/img/img1/" . $imgSrc;
                                    ?>
                                    <img src="<?= $imgSrc ?>" class="rounded-3 shadow-sm" width="120" height="80" style="object-fit:cover;" onerror="this.src='https://placehold.co/120x80?text=No+Image'">
                                    <div class="flex-grow-1">
                                        <input type="file" name="image" class="form-control form-control-sm">
                                        <small class="text-muted mt-2 d-block">*Kosongkan jika tidak ingin mengganti gambar utama.</small>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold">Isi Konten Artikel</label>
                                <textarea name="content" class="form-control" rows="12" required style="line-height: 1.6;"><?= htmlspecialchars($data['content']); ?></textarea>
                            </div>

                            <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                                <a href="dashboard.php" class="btn btn-light rounded-pill px-4 fw-bold text-muted">
                                    <i class="bi bi-x-lg me-1"></i> Batal
                                </a>
                                <button type="submit" name="update" class="btn btn-success rounded-pill px-5 fw-bold shadow">
                                    <i class="bi bi-save me-2"></i> Simpan & Ajukan Ulang
                                </button>
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
                title: 'Berhasil Disimpan!',
                text: 'Artikel telah diperbarui dan sedang menunggu validasi ulang oleh Admin.',
                icon: 'success',
                confirmButtonColor: '#198754'
            }).then(() => {
                window.location = 'dashboard.php';
            });
        <?php elseif ($status_update == "error") : ?>
            Swal.fire({
                title: 'Gagal!',
                text: 'Terjadi kesalahan sistem saat menyimpan data.',
                icon: 'error',
                confirmButtonColor: '#d33'
            });
        <?php endif; ?>
    </script>

</body>

</html>
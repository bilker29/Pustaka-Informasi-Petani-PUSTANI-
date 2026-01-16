<?php
session_start();
require '../../config/koneksi.php';

// PROTEKSI: Jika admin mencoba masuk area user
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}
if ($_SESSION['role'] === 'admin') {
    header("Location: admin/dashboard.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$user_query = mysqli_query($koneksi, "SELECT * FROM users WHERE id = '$user_id'");
$user_data = mysqli_fetch_assoc($user_query);

$foto_profil = "https://ui-avatars.com/api/?name=" . urlencode($user_data['username']) . "&background=064e3b&color=fff";
if (!empty($user_data['foto_profil'])) {
    $src = "../../public/img/img1/profil/" . $user_data['foto_profil'];
    if (file_exists($src)) $foto_profil = $src . "?v=" . time();
}

$keahlian = !empty($user_data['keahlian']) ? $user_data['keahlian'] : "Petani TaniMaju";
$bio = !empty($user_data['bio']) ? $user_data['bio'] : "Belum ada bio. Silakan lengkapi profil Anda.";

$artikel_query = mysqli_query($koneksi, "SELECT * FROM articles WHERE user_id = '$user_id' ORDER BY created_at DESC");
$jumlah_artikel = mysqli_num_rows($artikel_query);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Profil Saya - Pustani</title>
    <link rel="icon" href="../../public/img/img1/logo.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary-color: #064e3b;
            /* Disamakan dengan file edit profil */
            --accent-color: #16a34a;
            /* Disamakan dengan file edit profil */
            --bg-soft: #f0fdf4;
            /* Disamakan dengan file edit profil */
            --text-main: #374151;
            /* Disamakan dengan file edit profil */
            --nav-height: 80px;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #FAF1E6;
            color: var(--text-main);
            margin: 0;
        }

        /* Navbar & Nav Profile Styling - DISAMAKAN PERSIS DENGAN FILE EDIT PROFIL */
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

        /* Profil Spesifik Styling */
        .profile-header {
            height: 280px;
            background: linear-gradient(135deg, #1a3e35 0%, #2d9d78 100%);
        }

        .profile-card {
            background: white;
            border-radius: 2.5rem;
            padding: 3rem 2.5rem;
            box-shadow: 0 25px 50px -12px rgba(26, 62, 53, 0.12);
            position: relative;
            margin-top: -100px;
            text-align: center;
        }

        .profile-avatar-large {
            width: 170px;
            height: 170px;
            border-radius: 50%;
            object-fit: cover;
            border: 8px solid white;
            margin-top: -130px;
            background: white;
        }

        .profile-actions-top {
            position: absolute;
            top: 1.5rem;
            right: 1.5rem;
        }

        .expertise-badge {
            background-color: var(--bg-soft);
            color: var(--primary-color);
            padding: 8px 20px;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 700;
            display: inline-block;
            margin: 1rem 0;
            border: 1px solid rgba(45, 157, 120, 0.2);
        }

        .bio-text {
            color: #64748b;
            max-width: 650px;
            margin: 0 auto 2rem;
            line-height: 1.8;
        }

        .profile-stats {
            display: flex;
            justify-content: center;
            gap: 60px;
            border-top: 1px solid #f1f5f9;
            padding-top: 2rem;
        }

        .article-card {
            border: none;
            border-radius: 1.8rem;
            background: white;
            overflow: hidden;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.04);
            height: 100%;
            transition: all 0.3s;
            position: relative;
        }

        .article-card:hover {
            transform: translateY(-5px);
        }

        .article-card img {
            height: 200px;
            width: 100%;
            object-fit: cover;
        }

        .status-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            padding: 5px 12px;
            border-radius: 50px;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            z-index: 2;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .article-body {
            padding: 1.5rem;
        }

        .category-badge {
            font-size: 0.7rem;
            font-weight: 700;
            color: var(--accent-color);
            text-transform: uppercase;
            display: block;
            margin-bottom: 0.5rem;
        }

        .article-title {
            font-weight: 700;
            color: var(--primary-color);
            text-decoration: none;
            font-size: 1.1rem;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }

        .btn-action {
            flex: 1;
            border-radius: 50px;
            padding: 6px 0;
            text-align: center;
            font-size: 0.85rem;
            font-weight: 600;
            text-decoration: none;
            transition: 0.3s;
            border: 1px solid transparent;
        }

        .btn-lihat {
            border-color: #e2e8f0;
            color: #64748b;
        }

        .btn-lihat:hover {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }

        .btn-hapus {
            border-color: #fee2e2;
            color: #ef4444;
        }

        .btn-hapus:hover {
            background: #ef4444;
            color: white;
            border-color: #ef4444;
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

    <div class="profile-header"></div>

    <main class="container pb-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="profile-card mb-5">
                    <div class="profile-actions-top">
                        <a href="editprofil.php" class="btn btn-light rounded-pill px-4 fw-bold shadow-sm border"><i class="bi bi-pencil-square me-2"></i>Edit Profil</a>
                    </div>
                    <img src="<?= $foto_profil; ?>" class="profile-avatar-large">

                    <h1 class="fw-800 mt-4 mb-1">
                        <?= htmlspecialchars($user_data['username']); ?>
                        <?php if ($user_data['role'] == 'expert'): ?>
                            <i class="bi bi-patch-check-fill text-primary ms-1" title="Ahli Tani Terverifikasi" style="font-size: 1.5rem;"></i>
                        <?php endif; ?>
                    </h1>

                    <p class="text-muted mb-1"><?= htmlspecialchars($user_data['email']); ?></p>

                    <?php if ($user_data['role'] == 'expert'): ?>
                        <span class="expertise-badge"><i class="bi bi-patch-check-fill me-2"></i><?= htmlspecialchars($keahlian); ?></span>
                    <?php endif; ?>

                    <p class="bio-text"><?= nl2br(htmlspecialchars($bio)); ?></p>

                    <div class="profile-stats">
                        <div class="text-center">
                            <h4 class="fw-bold text-success mb-0"><?= $jumlah_artikel; ?></h4>
                            <small class="text-muted fw-bold text-uppercase">Artikel</small>
                        </div>
                        <div class="text-center">
                            <h4 class="fw-bold text-success mb-0"><?= date('M Y', strtotime($user_data['created_at'])); ?></h4>
                            <small class="text-muted fw-bold text-uppercase">Bergabung</small>
                        </div>
                    </div>
                </div>

                <h4 class="fw-bold mb-4" style="color: var(--primary-color);">Daftar Artikel Saya</h4>

                <div class="row g-4">
                    <?php if (mysqli_num_rows($artikel_query) > 0) : ?>
                        <?php while ($art = mysqli_fetch_assoc($artikel_query)) :
                            $imgSrc = $art['image'];
                            if (!filter_var($imgSrc, FILTER_VALIDATE_URL)) $imgSrc = "../../public/img/img1/" . $imgSrc;

                            $status = $art['status'];
                            $badgeClass = ($status == 'published') ? 'bg-success text-white' : 'bg-warning text-white';
                            $statusText = ($status == 'published') ? '<i class="bi bi-check-circle-fill me-1"></i> Terverifikasi' : '<i class="bi bi-clock-fill me-1"></i> Menunggu';
                        ?>
                            <div class="col-md-4">
                                <div class="article-card">
                                    <span class="status-badge <?= $badgeClass; ?>">
                                        <?= $statusText; ?>
                                    </span>

                                    <img src="<?= htmlspecialchars($imgSrc); ?>" onerror="this.src='https://placehold.co/600x400?text=No+Image'">
                                    <div class="article-body">
                                        <span class="category-badge"><?= htmlspecialchars($art['category']); ?></span>
                                        <a href="artikel.php?id=<?= $art['id']; ?>" class="article-title text-truncate d-block"><?= htmlspecialchars($art['title']); ?></a>
                                        <div class="action-buttons">
                                            <a href="artikel.php?id=<?= $art['id']; ?>" class="btn-action btn-lihat">Lihat</a>
                                            <a href="#" class="btn-action btn-hapus" onclick="konfirmasiHapus(event, '<?= $art['id']; ?>')">Hapus</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else : ?>
                        <div class="col-12 text-center py-5">
                            <?php if ($user_data['role'] == 'user'): ?>
                                <div class="card p-5 border-0 shadow-sm rounded-4" style="background-color: #f0fdf4;">
                                    <i class="bi bi-pencil-square display-4 text-success mb-3"></i>
                                    <h5 class="fw-bold">Ingin membuat artikel?</h5>
                                    <p class="text-muted">Ajukan dirimu menjadi Ahli Tani untuk mulai membagikan ilmu.</p>
                                    <button class="btn btn-success rounded-pill px-4 fw-bold mx-auto" data-bs-toggle="modal" data-bs-target="#modalJadiAhli">Ajukan Sekarang</button>
                                </div>
                            <?php else: ?>
                                <p class="text-muted">Belum ada artikel.</p>
                                <a href="buatartikel.php" class="btn btn-success rounded-pill">Mulai Menulis</a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <?php include 'layout/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function konfirmasiHapus(event, id) {
            event.preventDefault();
            Swal.fire({
                title: 'Hapus Artikel?',
                text: "Artikel yang dihapus tidak bisa dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Ya, Hapus!'
            }).then((result) => {
                if (result.isConfirmed) window.location.href = 'hapusartikel.php?id=' + id;
            });
        }
    </script>
</body>

</html>
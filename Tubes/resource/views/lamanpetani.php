<?php
session_start();
require '../../config/koneksi.php';

// Cek apakah ada ID di URL (Misal: lamanpetani.php?id=1)
if (!isset($_GET['id'])) {
    header("Location: Home.php");
    exit;
}

$id_petani = mysqli_real_escape_string($koneksi, $_GET['id']);

// 1. AMBIL DATA PETANI (USER YANG DILIHAT)
$query_petani = "SELECT * FROM users WHERE id = '$id_petani'";
$result_petani = mysqli_query($koneksi, $query_petani);
$petani = mysqli_fetch_assoc($result_petani);

// Jika petani tidak ditemukan
if (!$petani) {
    echo "<script>alert('Petani tidak ditemukan!'); window.location='Home.php';</script>";
    exit;
}

// Data Tampilan Petani
$nama_petani = $petani['username'];
$email_petani = $petani['email'];
$keahlian = !empty($petani['keahlian']) ? $petani['keahlian'] : "Anggota TaniMaju";
$bio = !empty($petani['bio']) ? $petani['bio'] : "Belum ada bio.";
$tgl_gabung = date('M Y', strtotime($petani['created_at']));

// Foto Profil Petani
$foto_petani = "https://ui-avatars.com/api/?name=" . urlencode($nama_petani) . "&background=064e3b&color=fff";
if (!empty($petani['foto_profil'])) {
    $src = $petani['foto_profil'];
    if (!filter_var($src, FILTER_VALIDATE_URL)) {
        $src = "../../public/img/img1/profil/" . $src;
    }
    if (filter_var($src, FILTER_VALIDATE_URL) || file_exists($src)) {
        $foto_petani = $src;
    }
}

// 2. AMBIL ARTIKEL MILIK PETANI INI
$query_artikel = "SELECT * FROM articles WHERE user_id = '$id_petani' AND status = 'published' ORDER BY created_at DESC";
$result_artikel = mysqli_query($koneksi, $query_artikel);
$jumlah_artikel = mysqli_num_rows($result_artikel);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil <?= htmlspecialchars($nama_petani); ?> - Pustani</title>
    <link rel="icon" href="../../public/img/img1/logo.png" type="image/png">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary-color: #1a3e35;
            --accent-color: #2d9d78;
            --bg-soft: #ecfdf5;
            --text-main: #334155;
            --nav-height: 80px;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #FAF1E6;
            color: var(--text-main);
        }

        /* Navbar Styles */
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

        .navbar-brand img {
            height: 50px;
            width: auto;
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

        .logout-item:hover {
            background-color: #fef2f2 !important;
        }

        /* Profile Styles */
        .profile-header {
            height: 280px;
            background: linear-gradient(135deg, #1a3e35 0%, #2d9d78 100%);
            position: relative;
            overflow: hidden;
        }

        .profile-header::after {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            opacity: 0.1;
            background-image: url("https://www.transparenttextures.com/patterns/natural-paper.png");
        }

        .profile-content {
            margin-top: -100px;
            padding-bottom: 4rem;
        }

        .profile-card {
            background: white;
            border-radius: 2.5rem;
            padding: 3rem 2.5rem;
            box-shadow: 0 25px 50px -12px rgba(26, 62, 53, 0.12);
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.5);
        }

        .profile-avatar-large {
            width: 170px;
            height: 170px;
            border-radius: 50%;
            object-fit: cover;
            border: 8px solid white;
            margin-top: -130px;
            background: white;
            box-shadow: 0 15px 30px rgba(26, 62, 53, 0.15);
            position: relative;
            z-index: 2;
        }

        .expertise-badge {
            background-color: var(--bg-soft);
            color: var(--primary-color);
            padding: 8px 20px;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 700;
            display: inline-block;
            margin: 1.2rem 0;
            border: 1px solid rgba(45, 157, 120, 0.2);
        }

        .bio-text {
            color: #64748b;
            max-width: 650px;
            margin: 0 auto 2rem;
            line-height: 1.8;
            font-size: 1.05rem;
        }

        .profile-stats {
            display: flex;
            justify-content: center;
            gap: 60px;
            border-top: 1px solid #f1f5f9;
            padding-top: 2rem;
        }

        .stat-item h4 {
            color: var(--primary-color);
            font-weight: 800;
            margin-bottom: 4px;
            font-size: 1.6rem;
        }

        .stat-item p {
            font-size: 0.85rem;
            color: #94a3b8;
            margin-bottom: 0;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
        }

        /* Article Card Styles */
        .section-title {
            font-weight: 800;
            color: var(--primary-color);
            margin-bottom: 2.5rem;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .article-card {
            border: none;
            border-radius: 1.8rem;
            background: white;
            overflow: hidden;
            transition: all 0.4s;
            height: 100%;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.04);
        }

        .article-card:hover {
            transform: translateY(-12px);
            box-shadow: 0 30px 40px -10px rgba(26, 62, 53, 0.2);
        }

        .article-card img {
            height: 200px;
            width: 100%;
            object-fit: cover;
        }

        .article-body {
            padding: 2rem;
        }

        .category-badge {
            font-size: 0.7rem;
            text-transform: uppercase;
            font-weight: 700;
            color: var(--accent-color);
            margin-bottom: 0.5rem;
            display: block;
        }

        .article-title {
            font-size: 1.2rem;
            font-weight: 700;
            line-height: 1.4;
            color: var(--primary-color);
            margin-bottom: 1.2rem;
            text-decoration: none;
            display: block;
        }

        .btn-view-article {
            background-color: transparent;
            color: var(--primary-color);
            border: 2px solid var(--primary-color);
            font-weight: 700;
            border-radius: 50px;
            padding: 8px 24px;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }

        .btn-view-article:hover {
            background-color: var(--primary-color);
            color: white;
        }

        footer {
            background: var(--primary-color);
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

    <main class="container profile-content">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="profile-card mb-5">
                    <img src="<?= $foto_petani; ?>" alt="Foto Profil" class="profile-avatar-large">

                    <h1 class="fw-800 mt-4 mb-1">
                        <?= htmlspecialchars($nama_petani); ?>
                        <?php if ($petani['role'] == 'expert'): ?>
                            <i class="bi bi-patch-check-fill text-primary ms-1" title="Ahli Tani Terverifikasi"></i>
                        <?php endif; ?>
                    </h1>

                    <?php if ($petani['role'] == 'expert'): ?>
                        <span class="expertise-badge"><i class="bi bi-patch-check-fill me-2"></i><?= htmlspecialchars($keahlian); ?></span>
                    <?php endif; ?>

                    <p class="bio-text"><?= nl2br(htmlspecialchars($bio)); ?></p>

                    <div class="profile-stats">
                        <div class="stat-item">
                            <h4><?= $jumlah_artikel; ?></h4>
                            <p>Jumlah Artikel</p>
                        </div>
                        <div class="stat-item">
                            <h4><?= $tgl_gabung; ?></h4>
                            <p>Bergabung di Pustani</p>
                        </div>
                    </div>
                </div>

                <div class="article-list-section">
                    <div class="section-title">
                        <i class="bi bi-journals text-success fs-3" style="color: var(--accent-color) !important;"></i>
                        <h4 class="mb-0">Daftar Artikel Penulis</h4>
                    </div>

                    <div class="row g-4">
                        <?php if ($jumlah_artikel > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($result_artikel)) :
                                $imgSrc = $row['image'];
                                if (!filter_var($imgSrc, FILTER_VALIDATE_URL)) {
                                    $imgSrc = "../../public/img/img1/" . $imgSrc;
                                }
                            ?>
                                <div class="col-md-4">
                                    <div class="article-card">
                                        <img src="<?= htmlspecialchars($imgSrc); ?>" alt="Sampul Artikel" onerror="this.src='https://placehold.co/600x400?text=Pustani'">
                                        <div class="article-body">
                                            <span class="category-badge"><?= htmlspecialchars($row['category']); ?></span>
                                            <a href="artikel.php?id=<?= $row['id']; ?>" class="article-title"><?= htmlspecialchars($row['title']); ?></a>
                                            <div class="d-flex justify-content-between align-items-center mt-4">
                                                <span class="small text-muted"><i class="bi bi-calendar3 me-1"></i> <?= date('d M Y', strtotime($row['created_at'])); ?></span>
                                                <a href="artikel.php?id=<?= $row['id']; ?>" class="btn btn-view-article">Lihat</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="col-12 text-center text-muted">
                                <p>Belum ada artikel yang dipublikasikan oleh penulis ini.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include 'layout/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
<?php
session_start();
require '../../config/koneksi.php';

// PROTEKSI: Admin dilarang masuk
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    header("Location: admin/dashboard.php");
    exit;
}

// Variabel kontrol
$missing_table_message = '';
$result = false;

try {
    // Pastikan koneksi valid
    if (!isset($koneksi) || !($koneksi instanceof mysqli)) {
        throw new Exception('Koneksi database tidak ditemukan.');
    }

    // Cek apakah tabel 'articles' ada
    $check = $koneksi->query("SHOW TABLES LIKE 'articles'");
    if ($check && $check->num_rows > 0) {
        // Jika ada, jalankan SELECT dengan JOIN
        $query = "SELECT articles.*, users.username, users.role FROM articles JOIN users ON articles.user_id = users.id WHERE articles.status = 'published' ORDER BY articles.created_at DESC";
        $result = $koneksi->query($query);
        // Jika query mengembalikan false, tetap aman — $result akan bernilai false
    } else {
        $missing_table_message = "Tabel 'articles' tidak ditemukan di database. Daftar artikel tidak dapat ditampilkan.";
    }
} catch (mysqli_sql_exception $e) {
    // Tangani exception mysqli (jika dikonfigurasi melempar)
    $missing_table_message = "Kesalahan database: " . $e->getMessage();
    $result = false;
} catch (Exception $e) {
    $missing_table_message = "Kesalahan: " . $e->getMessage();
    $result = false;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pustani - Home</title>
    <link rel="icon" href="../../public/img/img1/Logo.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
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
            color: var(--primary-color);
        }

        .navbar {
            height: var(--nav-height);
            background: #ffffff;
            border-bottom: 1px solid #edf2f7;
            z-index: 1000;
            transition: all 0.3s ease;
        }

        .navbar-brand {
            padding: 0;
            display: flex;
            align-items: center;
        }

        .navbar-logo {
            height: 50px;
            width: auto;
            object-fit: contain;
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
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }

        .search-btn-nav:hover {
            border-color: var(--accent-color);
            color: var(--accent-color);
        }

        /* PERBAIKAN CSS: Style untuk foto profil bulat di navbar */
        .nav-profile-img {
            width: 45px;
            height: 45px;
            object-fit: cover;
            border-radius: 50%;
            /* Membuat jadi bulat */
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
            font-size: 0.95rem;
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

        /* Hero & Card Styles */
        .hero-section {
            padding: 4rem 0 8rem 0;
            background-color: #FFDBB0;
            position: relative;
        }

        .hero-card {
            background: white;
            border: none;
            border-radius: 1.5rem;
            overflow: hidden;
            box-shadow: 0 20px 25px -5px rgba(6, 78, 59, 0.05);
        }

        .hero-img {
            height: 420px;
            width: 100%;
            object-fit: cover;
        }

        .hero-title-link {
            text-decoration: none;
            color: var(--primary-color);
            transition: color 0.2s;
            display: block;
        }

        .hero-title-link:hover {
            color: var(--accent-color);
        }

        .carousel-indicators {
            bottom: -80px !important;
            margin-bottom: 0;
        }

        .carousel-indicators [data-bs-target] {
            background-color: var(--accent-color);
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin: 0 8px;
            border: none;
            opacity: 0.3;
            transition: all 0.3s ease;
        }

        .carousel-indicators .active {
            opacity: 1;
            transform: scale(1.3);
            background-color: var(--primary-color);
        }

        .carousel-control-prev,
        .carousel-control-next {
            width: 5%;
            filter: hue-rotate(90deg);
        }

        .card-article {
            border: none;
            border-radius: 1rem;
            transition: all 0.3s;
            height: 100%;
            background: #fff;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }

        .card-article:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }

        .card-article img {
            border-radius: 1rem 1rem 0 0;
            height: 200px;
            object-fit: cover;
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
            font-weight: 700;
            line-height: 1.4;
            margin-bottom: 0.5rem;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-decoration: none;
            color: var(--primary-color);
            font-size: 1.1rem;
        }

        .article-title:hover {
            color: var(--accent-color);
        }

        .meta-text {
            font-size: 0.8rem;
            color: #64748b;
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

        @media (max-width: 992px) {
            .navbar {
                height: auto;
                padding: 1rem 0;
            }
        }

        @media (max-width: 768px) {
            .hero-img {
                height: 280px;
            }

            .hero-section {
                padding: 2rem 0 6rem 0;
            }

            .carousel-indicators {
                bottom: -60px !important;
            }

            .carousel-control-prev,
            .carousel-control-next {
                display: none;
            }
        }
    </style>
</head>

<body>

    <?php include 'layout/navbar.php'; ?>

    <header class="hero-section">
        <div id="heroCarousel" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-indicators">
                <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="0" class="active"></button>
                <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="1"></button>
                <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="2"></button>
            </div>
            <div class="carousel-inner">
                <div class="carousel-item active">
                    <div class="container">
                        <div class="row align-items-center g-5">
                            <div class="col-lg-6"><span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill mb-3 fw-bold">TRENDING</span>
                                <h1 class="display-4 fw-800 mb-4" style="font-weight: 800;"><a href="#" class="hero-title-link">Revolusi Smart Farming Berbasis IoT</a></h1>
                                <p class="lead text-muted mb-4">Teknologi pemantauan kelembapan tanah otomatis untuk hasil panen maksimal.</p>
                                <div class="d-flex align-items-center"><img src="https://i.pravatar.cc/150?u=farmer1" class="rounded-circle me-3" width="44" height="44">
                                    <div class="small">
                                        <div class="fw-bold">Ir. Sujarwo</div>
                                        <div class="text-muted">Pakar Agronomi</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="hero-card"><img src="https://images.unsplash.com/photo-1625246333195-78d9c38ad449?q=80&w=1470&auto=format&fit=crop" class="hero-img"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="carousel-item">
                    <div class="container">
                        <div class="row align-items-center g-5">
                            <div class="col-lg-6"><span class="badge bg-warning-subtle text-warning px-3 py-2 rounded-pill mb-3 fw-bold">BUDIDAYA</span>
                                <h1 class="display-4 fw-800 mb-4" style="font-weight: 800;"><a href="#" class="hero-title-link">Sukses Hidroponik di Lahan Sempit</a></h1>
                                <p class="lead text-muted mb-4">Langkah praktis membangun sistem NFT di pekarangan rumah untuk swasembada pangan keluarga.</p>
                                <div class="d-flex align-items-center"><img src="https://i.pravatar.cc/150?u=farmer2" class="rounded-circle me-3" width="44" height="44">
                                    <div class="small">
                                        <div class="fw-bold">Dewi Lestari</div>
                                        <div class="text-muted">Praktisi Hidroponik</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="hero-card"><img src="https://images.unsplash.com/photo-1558449028-b53a39d100fc?q=80&w=1374&auto=format&fit=crop" class="hero-img"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="carousel-item">
                    <div class="container">
                        <div class="row align-items-center g-5">
                            <div class="col-lg-6"><span class="badge bg-info-subtle text-info px-3 py-2 rounded-pill mb-3 fw-bold">ORGANIK</span>
                                <h1 class="display-4 fw-800 mb-4" style="font-weight: 800;"><a href="#" class="hero-title-link">Pupuk Organik Cair dari Limbah Dapur</a></h1>
                                <p class="lead text-muted mb-4">Mengolah sisa dapur menjadi nutrisi super yang mampu memperbaiki struktur tanah alami.</p>
                                <div class="d-flex align-items-center"><img src="https://i.pravatar.cc/150?u=farmer3" class="rounded-circle me-3" width="44" height="44">
                                    <div class="small">
                                        <div class="fw-bold">Ahmad Fauzi</div>
                                        <div class="text-muted">Penyuluh Lapangan</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="hero-card"><img src="https://images.unsplash.com/photo-1622383563227-04401ab4e5ea?q=80&w=1374&auto=format&fit=crop" class="hero-img"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev"><span class="carousel-control-prev-icon"></span></button>
            <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next"><span class="carousel-control-next-icon"></span></button>
        </div>
    </header>

    <main class="container my-5 py-4">
        <h2 class="fw-800 mb-5 text-center">Info Tani Terbaru</h2>
        <div class="row g-4">
            <?php if (mysqli_num_rows($result) > 0) : ?>
                <?php while ($row = mysqli_fetch_assoc($result)) :
                    $imgSrc = $row['image'];
                    if (!filter_var($imgSrc, FILTER_VALIDATE_URL)) {
                        $path = "../../public/img/img1/" . $imgSrc;
                        if (file_exists($path)) {
                            $imgSrc = $path;
                        } else {
                            $imgSrc = "https://placehold.co/600x400?text=No+Image";
                        }
                    }
                ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card card-article">
                            <img src="<?= htmlspecialchars($imgSrc); ?>" class="card-img-top" alt="<?= htmlspecialchars($row['title']); ?>">
                            <div class="card-body">
                                <span class="category-badge"><?= htmlspecialchars($row['category']); ?></span>
                                <a href="artikel.php?id=<?= $row['id']; ?>" class="article-title"><?= htmlspecialchars($row['title']); ?></a>

                                <div class="meta-text small text-muted">
                                    Oleh: <?= htmlspecialchars($row['username']); ?>
                                    <?php if ($row['role'] == 'expert'): ?>
                                        <i class="bi bi-patch-check-fill text-primary ms-1" title="Ahli Tani"></i>
                                    <?php endif; ?>
                                </div>

                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else : ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card card-article"><img src="https://images.unsplash.com/photo-1625246333195-78d9c38ad449?q=80&w=1470&auto=format&fit=crop" class="card-img-top">
                        <div class="card-body"><span class="category-badge">TEKNOLOGI</span><a href="#" class="article-title">Smart Farming Berbasis IoT</a>
                            <div class="meta-text small text-muted">Ir. Sujarwo</div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <?php include 'layout/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
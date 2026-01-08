<?php
session_start();
require '../../config/koneksi.php';

// Logika Pencarian
$keyword = "";
$query_search = "SELECT articles.*, users.username, users.role FROM articles 
                 JOIN users ON articles.user_id = users.id 
                 WHERE status='published' ORDER BY created_at DESC LIMIT 6";

if (isset($_GET['keyword'])) {
    $keyword = mysqli_real_escape_string($koneksi, $_GET['keyword']);
    // Cari di judul atau isi konten
    $query_search = "SELECT articles.*, users.username, users.role FROM articles 
                     JOIN users ON articles.user_id = users.id 
                     WHERE status='published' 
                     AND (title LIKE '%$keyword%' OR content LIKE '%$keyword%') 
                     ORDER BY created_at DESC";
}

$result = mysqli_query($koneksi, $query_search);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pustani - Pencarian</title>
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
            background-color: #FAF1E6;
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: var(--primary-color);
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

        .search-header {
            background-color: #FFDBB0;
            padding: 4rem 0;
            text-align: center;
        }

        .search-input-large {
            width: 100%;
            padding: 1rem 1.5rem;
            border-radius: 50px;
            border: none;
            font-size: 1.1rem;
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

    <section class="search-header">
        <div class="container" style="max-width: 700px;">
            <h2 class="fw-bold mb-4" style="color: var(--primary-color);">Cari Info Pertanian</h2>

            <form action="" method="GET">
                <div class="input-group shadow-sm rounded-pill bg-white p-1">
                    <input type="text" name="keyword" class="form-control search-input-large shadow-none" placeholder="Ketik kata kunci (misal: Hidroponik)..." value="<?= htmlspecialchars($keyword); ?>">
                    <button class="btn btn-success rounded-pill px-4 fw-bold" type="submit" style="background-color: var(--accent-color); border:none;">Cari</button>
                </div>
            </form>
        </div>
    </section>

    <section class="container py-5">
        <?php if ($keyword): ?>
            <p class="text-muted mb-4 fs-5">Menampilkan hasil untuk: <strong class="text-dark">"<?= htmlspecialchars($keyword); ?>"</strong></p>
        <?php endif; ?>

        <div class="row g-4">
            <?php if (mysqli_num_rows($result) > 0) : ?>
                <?php while ($row = mysqli_fetch_assoc($result)) :
                    $imgSrc = $row['image'];
                    if (!filter_var($imgSrc, FILTER_VALIDATE_URL)) {
                        $imgSrc = "../../public/img/img1/" . $imgSrc;
                    }
                ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card card-article">
                            <img src="<?= htmlspecialchars($imgSrc); ?>" class="card-img-top" alt="<?= htmlspecialchars($row['title']); ?>" onerror="this.src='https://placehold.co/600x400?text=No+Image'">

                            <div class="card-body">
                                <span class="category-badge"><?= htmlspecialchars($row['category']); ?></span>

                                <a href="artikel.php?id=<?= $row['id']; ?>" class="article-title">
                                    <?= htmlspecialchars($row['title']); ?>
                                </a>

                                <div class="meta-text small text-muted mt-2">
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
                <div class="col-12 text-center py-5">
                    <img src="https://cdn-icons-png.flaticon.com/512/7486/7486744.png" width="120" class="mb-3 opacity-75">
                    <h4 class="fw-bold text-muted">Yah, tidak ditemukan artikel.</h4>
                    <p class="text-muted">Coba gunakan kata kunci lain seperti "Padi", "Pupuk", atau "Hama".</p>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <?php include 'layout/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
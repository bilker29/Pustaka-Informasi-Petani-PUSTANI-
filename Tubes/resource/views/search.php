<?php
session_start();
require '../../config/koneksi.php';

// Non-aktifkan reporting mysqli
mysqli_report(MYSQLI_REPORT_OFF);

// --- [SOLUSI AMAN DI SINI] ---
// Kita inisialisasi variabel supaya tidak ada error "Undefined variable"
$keyword = "";
$results = [];
// -----------------------------

$missing_table_message = '';
$query_error_message = '';

// PERBAIKAN 1: Tangkap 'keyword' (sesuai name di form), bukan 'q'
$keyword = trim($_GET['keyword'] ?? '');

// Proteksi: jika role admin dilarang
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    header("Location: admin/dashboard.php");
    exit;
}

// Pastikan koneksi valid
if (!isset($koneksi) || !($koneksi instanceof mysqli)) {
    $missing_table_message = 'Koneksi database tidak ditemukan.';
} else {
    // Cek tabel articles
    $checkArticles = $koneksi->query("SHOW TABLES LIKE 'articles'");
    if ($checkArticles && $checkArticles->num_rows > 0) {

        $checkUsers = $koneksi->query("SHOW TABLES LIKE 'users'");
        $hasUsers = ($checkUsers && $checkUsers->num_rows > 0);

        // Jika ada keyword, jalankan pencarian
        if ($keyword !== '') {
            $like = '%' . $keyword . '%';

            // Pilih query tergantung ketersediaan tabel users
            if ($hasUsers) {
                $sql = "SELECT articles.id, articles.title, articles.image, articles.category, articles.created_at, users.username, users.role
                        FROM articles
                        JOIN users ON articles.user_id = users.id
                        WHERE articles.status = 'published' AND (articles.title LIKE ? OR articles.content LIKE ?)
                        ORDER BY articles.created_at DESC";
            } else {
                $sql = "SELECT id, title, image, category, created_at, content FROM articles
                        WHERE status = 'published' AND (title LIKE ? OR content LIKE ?)
                        ORDER BY created_at DESC";
            }

            $stmt = $koneksi->prepare($sql);
            if ($stmt) {
                $stmt->bind_param('ss', $like, $like);
                if ($stmt->execute()) {
                    $res = $stmt->get_result();
                    // Simpan data ke Array $results
                    $results = $res->fetch_all(MYSQLI_ASSOC);

                    // Jika tabel users tidak ada, isi default username manual
                    if (!$hasUsers) {
                        foreach ($results as &$r) {
                            $r['username'] = 'Anonim';
                            $r['role'] = '';
                        }
                    }
                    $res->free();
                }
                $stmt->close();
            }
        }
    }
}
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
        <?php if (isset($keyword) && !empty($keyword)): ?>
            <p class="text-muted mb-4 fs-5">Menampilkan hasil untuk: <strong class="text-dark">"<?= htmlspecialchars($keyword); ?>"</strong></p>
        <?php endif; ?>

        <div class="row g-4">
            <?php if (!empty($results)) : ?>
                <?php foreach ($results as $row) :
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
                <?php endforeach; ?>
            <?php else : ?>
                <?php if (!empty($keyword)): ?>
                    <div class="col-12 text-center py-5">
                        <img src="https://cdn-icons-png.flaticon.com/512/7486/7486744.png" width="120" class="mb-3 opacity-75">
                        <h4 class="fw-bold text-muted">Yah, tidak ditemukan artikel.</h4>
                        <p class="text-muted">Coba gunakan kata kunci lain seperti "Padi", "Pupuk", atau "Hama".</p>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </section>

    <?php include 'layout/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
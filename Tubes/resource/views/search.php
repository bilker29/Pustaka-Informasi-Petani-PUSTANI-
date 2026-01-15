<?php
session_start();
require '../../config/koneksi.php';

mysqli_report(MYSQLI_REPORT_OFF);

$keyword = "";
$results = [];

$missing_table_message = '';
$query_error_message = '';

$keyword = trim($_GET['keyword'] ?? '');

if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    header("Location: admin/dashboard.php");
    exit;
}

if (!isset($koneksi) || !($koneksi instanceof mysqli)) {
    $missing_table_message = 'Koneksi database tidak ditemukan.';
} else {
    $checkArticles = $koneksi->query("SHOW TABLES LIKE 'articles'");
    if ($checkArticles && $checkArticles->num_rows > 0) {
        $checkUsers = $koneksi->query("SHOW TABLES LIKE 'users'");
        $hasUsers = ($checkUsers && $checkUsers->num_rows > 0);

        if ($keyword !== '') {
            $like = '%' . $keyword . '%';
            if ($hasUsers) {
                $sql = "SELECT articles.id, articles.title, articles.image, articles.category, articles.created_at, articles.content, users.username, users.role
                        FROM articles
                        JOIN users ON articles.user_id = users.id
                        WHERE articles.status = 'published' AND (articles.title LIKE ? OR articles.content LIKE ?)
                        ORDER BY articles.created_at DESC";
            } else {
                $sql = "SELECT id, title, image, category, created_at, content FROM articles
                        WHERE status = 'published' AND (title LIKE ? OR content LIKE ?)
                        ORDER BY   created_at DESC";
            }

            $stmt = $koneksi->prepare($sql);
            if ($stmt) {
                $stmt->bind_param('ss', $like, $like);
                if ($stmt->execute()) {
                    $res = $stmt->get_result();
                    $results = $res->fetch_all(MYSQLI_ASSOC);
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
    <title>Pustani - Pencarian Informasi</title>
    <link rel="icon" href="../../public/img/img1/Logo.png" type="image/png">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Lora:wght@600&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary-color: #064e3b;
            --accent-color: #16a34a;
            --bg-body: #FAF1E6;
            --text-muted: #64748b;
        }

        body {
            background-color: var(--bg-body);
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: #334155;
            overflow-x: hidden;
        }

        /* --- Search Header Enhancement --- */
        .search-header {
            background: linear-gradient(135deg, #FFDBB0 0%, #fde68a 100%);
            padding: 6rem 0;
            border-radius: 0 0 60px 60px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.03);
            margin-bottom: 2rem;
        }

        .search-container {
            background: #ffffff;
            padding: 8px;
            border-radius: 50px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .search-container:focus-within {
            border-color: var(--accent-color);
            transform: translateY(-2px);
        }

        .search-input-large {
            border: none !important;
            padding-left: 1.5rem;
            font-weight: 500;
            color: var(--primary-color);
        }

        /* --- Article Card Enhancement --- */
        .card-article {
            border: none;
            border-radius: 24px;
            overflow: hidden;
            background: #ffffff;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            height: 100%;
        }

        .card-article:hover {
            transform: translateY(-12px);
            box-shadow: 0 25px 50px -12px rgba(6, 78, 59, 0.15);
        }

        .img-container {
            overflow: hidden;
            height: 220px;
        }

        .card-article img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.6s ease;
        }

        .card-article:hover img {
            transform: scale(1.1);
        }

        .card-body {
            padding: 1.5rem;
        }

        .category-badge {
            background-color: #f0fdf4;
            color: var(--accent-color);
            padding: 5px 14px;
            border-radius: 10px;
            font-size: 0.7rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 12px;
            display: inline-block;
        }

        .article-title {
            font-family: 'Lora', serif;
            font-size: 1.25rem;
            font-weight: 700;
            line-height: 1.4;
            color: var(--primary-color);
            text-decoration: none;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            margin-bottom: 12px;
        }

        .article-title:hover {
            color: var(--accent-color);
        }

        .article-excerpt {
            font-size: 0.9rem;
            color: var(--text-muted);
            line-height: 1.6;
            margin-bottom: 20px;
        }

        /* --- Meta & Footer --- */
        .meta-author {
            display: flex;
            align-items: center;
            gap: 10px;
            padding-top: 15px;
            border-top: 1px solid #f1f5f9;
        }

        .avatar-circle {
            width: 35px;
            height: 35px;
            background: var(--primary-color);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 0.8rem;
        }

        .btn-search {
            background-color: var(--accent-color);
            border-radius: 50px;
            padding: 10px 25px;
            font-weight: 700;
            border: none;
        }

        .btn-search:hover {
            background-color: #15803d;
        }
    </style>
</head>

<body>

    <?php include 'layout/navbar.php'; ?>

    <section class="search-header">
        <div class="container text-center" style="max-width: 800px;">
            <span class="badge bg-white text-success px-3 py-2 rounded-pill mb-3 shadow-sm fw-bold">PUSTAKA TANI</span>
            <h1 class="fw-800 mb-4" style="color: var(--primary-color); font-size: 2.8rem;">Temukan Ilmu Pertanian</h1>

            <form action="" method="GET" class="px-2">
                <div class="input-group search-container">
                    <span class="input-group-text bg-transparent border-0 ps-4">
                        <i class="bi bi-search text-muted"></i>
                    </span>
                    <input type="text" name="keyword" class="form-control search-input-large shadow-none" 
                           placeholder="Ketik kata kunci (Padi, Hidroponik, Pupuk)..." 
                           value="<?= htmlspecialchars($keyword); ?>">
                    <button class="btn btn-success btn-search px-4" type="submit">Cari Sekarang</button>
                </div>
            </form>
        </div>
    </section>

    <section class="container py-4">
        <?php if (!empty($keyword)): ?>
            <div class="d-flex align-items-center mb-5">
                <div class="flex-grow-1 border-bottom"></div>
                <span class="px-3 text-muted">Hasil pencarian untuk: <strong class="text-dark">"<?= htmlspecialchars($keyword); ?>"</strong></span>
                <div class="flex-grow-1 border-bottom"></div>
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <?php if (!empty($results)) : ?>
                <?php foreach ($results as $row) :
                    $imgSrc = $row['image'];
                    if (!filter_var($imgSrc, FILTER_VALIDATE_URL)) {
                        $imgSrc = "../../public/img/img1/" . $imgSrc;
                    }
                    $excerpt = substr(strip_tags($row['content']), 0, 90);
                ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card card-article shadow-sm">
                            <div class="img-container">
                                <img src="<?= htmlspecialchars($imgSrc); ?>" alt="<?= htmlspecialchars($row['title']); ?>" 
                                     onerror="this.src='https://placehold.co/600x400?text=No+Image'">
                            </div>

                            <div class="card-body">
                                <span class="category-badge"><?= htmlspecialchars($row['category']); ?></span>
                                
                                <a href="artikel.php?id=<?= $row['id']; ?>" class="article-title">
                                    <?= htmlspecialchars($row['title']); ?>
                                </a>

                                <p class="article-excerpt">
                                    <?= $excerpt; ?>...
                                </p>

                                <div class="meta-author">
                                    <div class="avatar-circle">
                                        <?= strtoupper(substr($row['username'], 0, 1)); ?>
                                    </div>
                                    <div class="author-info">
                                        <div class="small fw-bold text-dark">
                                            <?= htmlspecialchars($row['username']); ?>
                                            <?php if ($row['role'] == 'expert'): ?>
                                                <i class="bi bi-patch-check-fill text-primary ms-1" title="Ahli Tani"></i>
                                            <?php endif; ?>
                                        </div>
                                        <div class="meta-text text-muted" style="font-size: 0.7rem;">
                                            <?= date('d M Y', strtotime($row['created_at'])); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else : ?>
                <?php if (!empty($keyword)): ?>
                    <div class="col-12 text-center py-5">
                        <div class="bg-white d-inline-block p-5 rounded-5 shadow-sm">
                            <img src="https://cdn-icons-png.flaticon.com/512/7486/7486744.png" width="100" class="mb-4 opacity-50">
                            <h3 class="fw-bold text-dark">Data Tidak Ditemukan</h3>
                            <p class="text-muted">Maaf, kami tidak menemukan artikel dengan kata kunci tersebut.<br>Cobalah kata kunci yang lebih umum.</p>
                            <a href="search.php" class="btn btn-outline-success rounded-pill mt-3 px-4">Lihat Semua Artikel</a>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </section>

    <?php include 'layout/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
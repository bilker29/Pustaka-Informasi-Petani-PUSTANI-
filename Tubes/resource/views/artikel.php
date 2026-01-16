<?php
session_start();
require '../../config/koneksi.php';

if (!isset($_GET['id'])) {
    header("Location: Home.php");
    exit;
}
$id_artikel = mysqli_real_escape_string($koneksi, $_GET['id']);
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

// --- PROSES SUBMIT RATING & KOMENTAR ---
if (isset($_POST['submit_review']) && $user_id) {
    $rating = (int)$_POST['rating'];
    $komentar = mysqli_real_escape_string($koneksi, $_POST['komentar']);

    // 1. Simpan Rating (Insert or Update)
    if ($rating > 0) {
        $q_rate = "INSERT INTO article_ratings (user_id, article_id, rating) VALUES ('$user_id', '$id_artikel', '$rating')
                   ON DUPLICATE KEY UPDATE rating = '$rating'";
        mysqli_query($koneksi, $q_rate);
    }

    // 2. Simpan Komentar
    if (!empty($komentar)) {
        $q_com = "INSERT INTO article_comments (user_id, article_id, comment) VALUES ('$user_id', '$id_artikel', '$komentar')";
        mysqli_query($koneksi, $q_com);
    }

    header("Location: artikel.php?id=$id_artikel"); // Refresh page
    exit;
}

// Query Data Artikel
$query = "SELECT articles.*, users.username, users.role, users.foto_profil,
          COALESCE(AVG(article_ratings.rating), 0) as avg_rating,
          COUNT(article_ratings.id) as total_raters
          FROM articles 
          JOIN users ON articles.user_id = users.id 
          LEFT JOIN article_ratings ON articles.id = article_ratings.article_id
          WHERE articles.id = '$id_artikel'";
$article = mysqli_fetch_assoc(mysqli_query($koneksi, $query));

// Jika artikel tidak ditemukan atau belum dipublish (kecuali oleh pemilik/admin)
if (!$article || ($article['status'] !== 'published' && $article['user_id'] !== $user_id)) {
    header("Location: Home.php");
    exit;
}

// Query Komentar
$q_comments = "SELECT article_comments.*, users.username, users.role, users.foto_profil 
               FROM article_comments 
               JOIN users ON article_comments.user_id = users.id 
               WHERE article_id = '$id_artikel' 
               ORDER BY created_at DESC";
$comments = mysqli_query($koneksi, $q_comments);

// Gambar Utama
$mainImg = $article['image'];
if (!filter_var($mainImg, FILTER_VALIDATE_URL)) $mainImg = "../../public/img/img1/" . $mainImg;
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($article['title']); ?> - Pustani</title>
    <link rel="icon" href="../../public/img/img1/logo.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
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
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #FAF1E6;
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

        .article-container {
            background: #fff;
            border-radius: 2rem;
            padding: 4rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        }

        .rating-stars {
            direction: rtl;
            display: inline-flex;
        }

        .rating-stars input {
            display: none;
        }

        .rating-stars label {
            font-size: 2rem;
            color: #ddd;
            cursor: pointer;
            transition: 0.2s;
        }

        .rating-stars input:checked~label,
        .rating-stars label:hover,
        .rating-stars label:hover~label {
            color: #f59e0b;
        }

        .comment-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #fff;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
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

    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-9">
                <div class="mb-4">
                    <a href="Home.php" class="text-decoration-none text-muted fw-bold">
                        <i class="bi bi-arrow-left me-2"></i> Kembali ke Beranda
                    </a>
                </div>

                <div class="article-container mb-5">
                    <span class="badge bg-success mb-3 px-3 py-2 rounded-pill"><?= $article['category'] ?></span>
                    <h1 class="fw-bold mb-4" style="color: #064e3b; font-size: 2.5rem; line-height: 1.2;"><?= $article['title'] ?></h1>

                    <div class="d-flex align-items-center justify-content-between mb-5 pb-4 border-bottom text-muted">
                        <div class="d-flex align-items-center gap-3">
                            <i class="bi bi-person-circle fs-4"></i>
                            <div>
                                <div class="fw-bold text-dark">
                                    <?= $article['username'] ?>
                                    <?php if ($article['role'] == 'expert'): ?>
                                        <i class="bi bi-patch-check-fill text-primary ms-1" title="Ahli Tani Terverifikasi"></i>
                                    <?php endif; ?>
                                </div>
                                <small><?= date('d M Y', strtotime($article['created_at'])) ?></small>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2 text-warning fw-bold bg-light px-3 py-2 rounded-pill">
                            <i class="bi bi-star-fill"></i> <?= number_format($article['avg_rating'], 1) ?>
                            <span class="text-muted fw-normal small">/ 5.0</span>
                        </div>
                    </div>

                    <img src="<?= $mainImg ?>" class="w-100 rounded-4 mb-5 shadow-lg" style="max-height: 500px; object-fit: cover;">

                    <div class="article-content fs-5 lh-lg text-secondary mb-5">
                        <?= nl2br(htmlspecialchars_decode($article['content'])); ?>
                    </div>

                    <hr class="my-5">

                    <div class="card border-0 bg-light rounded-4 p-5 mb-5 shadow-sm">
                        <h4 class="fw-bold mb-4" style="color: #064e3b;">Beri Ulasan & Diskusi</h4>

                        <?php if ($user_id): ?>
                            <form method="POST" action="">
                                <div class="mb-4">
                                    <label class="fw-bold d-block mb-2">Rating Artikel Ini:</label>
                                    <div class="rating-stars">
                                        <input type="radio" id="s5" name="rating" value="5"><label for="s5"><i class="bi bi-star-fill"></i></label>
                                        <input type="radio" id="s4" name="rating" value="4"><label for="s4"><i class="bi bi-star-fill"></i></label>
                                        <input type="radio" id="s3" name="rating" value="3"><label for="s3"><i class="bi bi-star-fill"></i></label>
                                        <input type="radio" id="s2" name="rating" value="2"><label for="s2"><i class="bi bi-star-fill"></i></label>
                                        <input type="radio" id="s1" name="rating" value="1"><label for="s1"><i class="bi bi-star-fill"></i></label>
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <textarea name="komentar" class="form-control border-0 shadow-sm" rows="4" style="border-radius: 1.5rem; padding: 1.5rem;" placeholder="Tulis pendapat atau pertanyaan Anda..." required></textarea>
                                </div>
                                <button type="submit" name="submit_review" class="btn btn-success rounded-pill fw-bold px-5 py-2 shadow">Kirim Ulasan</button>
                            </form>
                        <?php else: ?>
                            <div class="alert alert-warning rounded-4 border-0 shadow-sm">
                                <i class="bi bi-info-circle-fill me-2"></i> Silakan <a href="login.php" class="fw-bold text-decoration-none">Login</a> untuk memberi ulasan.
                            </div>
                        <?php endif; ?>
                    </div>

                    <h5 class="fw-bold mb-5" style="color: #064e3b;"><i class="bi bi-chat-dots-fill text-success me-2"></i> <?= mysqli_num_rows($comments) ?> Komentar</h5>

                    <div class="comment-section">
                        <?php while ($com = mysqli_fetch_assoc($comments)):
                            $ava = "https://ui-avatars.com/api/?name=" . urlencode($com['username']) . "&background=064e3b&color=fff";
                            if (!empty($com['foto_profil'])) $ava = "../../public/img/img1/profil/" . $com['foto_profil'];
                        ?>
                            <div class="d-flex gap-3 mb-4 animate-in">
                                <img src="<?= $ava ?>" class="comment-avatar mt-1">
                                <div class="flex-grow-1">
                                    <div class="bg-light p-4 rounded-4 shadow-sm">
                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                            <div class="d-flex align-items-center gap-2">
                                                <h6 class="fw-bold m-0 text-dark"><?= htmlspecialchars($com['username']) ?></h6>
                                                <?php if ($com['role'] == 'expert'): ?>
                                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle" style="font-size:0.6rem;">AHLI TANI</span>
                                                <?php endif; ?>
                                            </div>
                                            <small class="text-muted" style="font-size: 0.75rem;"><?= date('d M Y', strtotime($com['created_at'])) ?></small>
                                        </div>
                                        <p class="m-0 text-secondary" style="line-height: 1.6;"><?= htmlspecialchars($com['comment']) ?></p>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'layout/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
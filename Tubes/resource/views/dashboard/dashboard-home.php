<?php
// dashboard/dashboard-home.php

// Rata-rata Rating
$q_rating = mysqli_query($koneksi, "SELECT AVG(rating) as total_avg FROM article_ratings JOIN articles ON article_ratings.article_id = articles.id WHERE articles.user_id = '$user_id'");
$avg_val = mysqli_fetch_assoc($q_rating)['total_avg'] ?? 0;
$avg_global = number_format((float)$avg_val, 1);

// Get statistics
$total_articles = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM articles WHERE user_id = '$user_id'"))['total'];
$published_articles = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM articles WHERE user_id = '$user_id' AND status = 'published'"))['total'];
$draft_articles = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM articles WHERE user_id = '$user_id' AND status = 'draft'"))['total'];

// Get articles with search
$keyword = isset($_GET['keyword']) ? mysqli_real_escape_string($koneksi, $_GET['keyword']) : '';
$q_art = "SELECT a.*, COALESCE(AVG(r.rating), 0) as avg_rating 
          FROM articles a 
          LEFT JOIN article_ratings r ON a.id = r.article_id 
          WHERE a.user_id = '$user_id' AND a.title LIKE '%$keyword%' 
          GROUP BY a.id 
          ORDER BY a.created_at DESC";
$articles_query = mysqli_query($koneksi, $q_art);
$base_path_img = "../../public/img/artikelGambar/";
?>

<!-- Welcome Card -->
<div class="welcome-card animate-in">
    <div class="welcome-content">
        <h2 class="welcome-title">Selamat Datang Kembali, <?= htmlspecialchars($username); ?>! 👋</h2>
        <p class="welcome-subtitle">Mari berbagi pengetahuan dan pengalaman Anda dalam bidang pertanian kepada masyarakat luas.</p>
    </div>

    <div style="position: relative; z-index: 2; background: rgba(255,255,255,0.15); backdrop-filter: blur(10px); padding: 1.5rem; border-radius: 20px; border: 1px solid rgba(255,255,255,0.2); text-align: center; min-width: 150px;">
        <div style="color: rgba(255,255,255,0.9); font-size: 0.8rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.5rem;">Skor Rating</div>
        <div style="font-size: 2.5rem; font-weight: 800; color: #fbbf24;">
            <i class="bi bi-star-fill"></i> <?= $avg_global ?>
        </div>
    </div>

    <i class="bi bi-lightbulb-fill welcome-icon d-none d-lg-block"></i>
</div>

<!-- Statistics Cards -->
<div class="row g-4 mb-4 animate-in">
    <div class="col-md-4">
        <div class="stat-card total">
            <div>
                <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                    <i class="bi bi-journal-text"></i>
                </div>
                <div class="stat-label">Total Artikel</div>
                <div class="stat-value"><?= number_format($total_articles) ?></div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="stat-card published">
            <div>
                <div class="stat-icon bg-success bg-opacity-10 text-success">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
                <div class="stat-label">Dipublikasikan</div>
                <div class="stat-value"><?= number_format($published_articles) ?></div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="stat-card draft">
            <div>
                <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                    <i class="bi bi-clock-history"></i>
                </div>
                <div class="stat-label">Draft</div>
                <div class="stat-value"><?= number_format($draft_articles) ?></div>
            </div>
        </div>
    </div>
</div>

<!-- Search & Header -->
<div class="d-flex justify-content-between align-items-center mb-4 animate-in" style="gap: 1rem; flex-wrap: wrap;">
    <div>
        <h3 class="fw-bold mb-1" style="color: var(--pustani-green);">Artikel Saya</h3>
        <p class="text-muted small mb-0">Kelola semua artikel yang telah Anda tulis</p>
    </div>
    <div class="d-flex gap-2" style="align-items: center;">
        <div style="position: relative;">
            <i class="bi bi-search" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: #94a3b8;"></i>
            <input type="text"
                id="searchKeyword"
                class="form-control"
                placeholder="Cari artikel..."
                style="padding-left: 2.75rem; border-radius: 14px; border: 2px solid #e2e8f0; min-width: 250px;"
                value="<?= htmlspecialchars($keyword) ?>">
        </div>
        <a href="?page=tulis" class="btn-primary-action">
            <i class="bi bi-plus-circle-fill"></i>
            Tulis Baru
        </a>
    </div>
</div>

<!-- Articles Grid -->
<?php if (mysqli_num_rows($articles_query) > 0) : ?>
    <div class="row g-4 animate-in">
        <?php while ($article = mysqli_fetch_assoc($articles_query)) :
            // Handle image - URL or local file
            $image_path = '';
            $has_image = false;

            if (!empty($article['image'])) {
                if (preg_match('/^https?:\/\//i', $article['image'])) {
                    $image_path = $article['image'];
                    $has_image = true;
                } else {
                    // Cek berbagai kemungkinan path
                    $possible_paths = [
                        $base_path_img . $article['image'],
                        "../../public/img/img1/" . $article['image']
                    ];

                    foreach ($possible_paths as $path) {
                        if (file_exists($path)) {
                            $image_path = $path;
                            $has_image = true;
                            break;
                        }
                    }
                }
            }

            $status_class = $article['status'] == 'published' ? 'tag-published' : 'tag-draft';
            $status_icon = $article['status'] == 'published' ? 'check-circle-fill' : 'clock-history';
            $status_text = $article['status'] == 'published' ? 'Published' : 'Draft';

            // Rating
            $rating = number_format((float)$article['avg_rating'], 1);
        ?>
            <div class="col-md-6 col-lg-4">
                <div class="card-article">
                    <span class="status-tag <?= $status_class ?>">
                        <i class="bi bi-<?= $status_icon ?>"></i>
                        <?= $status_text ?>
                    </span>

                    <?php if ($has_image) : ?>
                        <img src="<?= htmlspecialchars($image_path) ?>"
                            alt="<?= htmlspecialchars($article['title']) ?>"
                            class="card-article-img"
                            onerror="this.parentElement.innerHTML='<div class=\'card-article-img d-flex align-items-center justify-content-center\' style=\'background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);\'><i class=\'bi bi-image\' style=\'font-size: 3rem; color: #cbd5e1;\'></i></div>'">
                    <?php else : ?>
                        <div class="card-article-img d-flex align-items-center justify-content-center" style="background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);">
                            <i class="bi bi-image" style="font-size: 3rem; color: #cbd5e1;"></i>
                        </div>
                    <?php endif; ?>

                    <div class="card-article-body">
                        <h5 class="card-article-title"><?= htmlspecialchars($article['title']) ?></h5>

                        <div class="card-article-meta">
                            <span><i class="bi bi-calendar3"></i> <?= date('d M Y', strtotime($article['created_at'])) ?></span>
                            <?php if ($rating > 0) : ?>
                                <span><i class="bi bi-star-fill text-warning"></i> <?= $rating ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="card-article-actions">
                            <a href="artikel.php?id=<?= $article['id'] ?>" class="btn btn-edit" style="flex: 0 0 auto; padding: 0.65rem 1rem;">
                                <i class="bi bi-eye-fill"></i>
                                Lihat
                            </a>
                            <button onclick="deleteArticle(<?= $article['id'] ?>)" class="btn btn-delete">
                                <i class="bi bi-trash3-fill"></i>
                                Hapus
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
<?php else : ?>
    <!-- Empty State -->
    <div class="empty-state animate-in">
        <div class="empty-state-icon">
            <i class="bi bi-journal-x"></i>
        </div>
        <h3 class="empty-state-title">
            <?php if (!empty($keyword)) : ?>
                Tidak Ada Artikel Ditemukan
            <?php else : ?>
                Belum Ada Artikel
            <?php endif; ?>
        </h3>
        <p class="empty-state-text">
            <?php if (!empty($keyword)) : ?>
                Tidak ada artikel dengan kata kunci "<?= htmlspecialchars($keyword) ?>"
            <?php else : ?>
                Mulai berbagi pengetahuan Anda dengan menulis artikel pertama.
            <?php endif; ?>
        </p>
        <a href="?page=tulis" class="btn-primary-action">
            <i class="bi bi-pencil-square"></i>
            Tulis Artikel Baru
        </a>
    </div>
<?php endif; ?>

<script>
    // Search functionality
    document.getElementById('searchKeyword')?.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            const keyword = this.value;
            window.location.href = '?page=dashboard&keyword=' + encodeURIComponent(keyword);
        }
    });

    function deleteArticle(id) {
        Swal.fire({
            title: 'Hapus Artikel?',
            html: '<p class="text-muted mb-0">Artikel akan dihapus secara permanen dan tidak dapat dikembalikan!</p>',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#64748b',
            confirmButtonText: '<i class="bi bi-trash3-fill me-2"></i>Ya, Hapus!',
            cancelButtonText: 'Batal',
            customClass: {
                popup: 'rounded-4',
                confirmButton: 'rounded-pill px-4 fw-bold',
                cancelButton: 'rounded-pill px-4 fw-bold'
            },
            buttonsStyling: false,
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                window.location = 'proses_artikel.php?hapus=' + id;
            }
        });
    }
</script>
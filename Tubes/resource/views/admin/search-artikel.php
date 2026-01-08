<?php
// search-artikel.php
require_once '../../../config/koneksi.php';

$keyword = isset($_GET['keyword']) ? mysqli_real_escape_string($koneksi, $_GET['keyword']) : '';

// Query dengan JOIN untuk mendapatkan nama penulis
$query = "SELECT a.*, u.username as author_name 
          FROM articles a 
          LEFT JOIN users u ON a.user_id = u.id 
          WHERE a.title LIKE '%$keyword%' 
          OR u.username LIKE '%$keyword%'
          ORDER BY a.created_at DESC";

$result = mysqli_query($koneksi, $query);
$base_path_img = "../../../public/img/img1/";

if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $status_class = $row['status'] == 'published' ? 'published' : 'draft';
        $status_icon = $row['status'] == 'published' ? 'check-circle-fill' : 'clock-history';
        $status_text = $row['status'] == 'published' ? 'Published' : 'Draft';

        // Get first letter for author initial
        $author_initial = strtoupper(substr($row['author_name'], 0, 1));

        // Truncate title if too long
        $title = strlen($row['title']) > 80 ? substr($row['title'], 0, 80) . '...' : $row['title'];

        // Handle image - cek apakah URL atau file lokal
        $image_path = '';
        $has_image = false;

        if (!empty($row['image'])) {
            // Cek apakah URL (http:// atau https://)
            if (preg_match('/^https?:\/\//i', $row['image'])) {
                $image_path = $row['image'];
                $has_image = true;
            }
            // Jika bukan URL, cek apakah file lokal ada
            else if (file_exists($base_path_img . $row['image'])) {
                $image_path = $base_path_img . $row['image'];
                $has_image = true;
            }
        }
?>
        <tr>
            <td>
                <div class="img-frame">
                    <?php if ($has_image) : ?>
                        <img src="<?= htmlspecialchars($image_path) ?>"
                            alt="<?= htmlspecialchars($row['title']) ?>"
                            class="img-article"
                            onerror="this.parentElement.innerHTML='<i class=\'bi bi-image-fill no-image\'></i>'">
                    <?php else : ?>
                        <i class="bi bi-image no-image"></i>
                    <?php endif; ?>
                </div>
            </td>
            <td>
                <div class="article-info">
                    <div class="article-title"><?= htmlspecialchars($title) ?></div>
                    <div class="article-author">
                        <i class="bi bi-person-circle"></i>
                        <span><?= htmlspecialchars($row['author_name']) ?></span>
                    </div>
                </div>
            </td>
            <td>
                <span class="status-badge <?= $status_class ?>">
                    <i class="bi bi-<?= $status_icon ?>"></i>
                    <?= $status_text ?>
                </span>
            </td>
            <td>
                <div class="action-buttons">
                    <button class="btn-action btn-action-view"
                        data-bs-toggle="modal"
                        data-bs-target="#detailModal<?= $row['id'] ?>"
                        title="Lihat Detail">
                        <i class="bi bi-eye-fill"></i>
                    </button>

                    <?php if ($row['status'] == 'draft') : ?>
                        <a href="validasi-artikel.php?aksi=publish&id=<?= $row['id'] ?>"
                            class="btn-action btn-action-publish"
                            title="Publikasikan">
                            <i class="bi bi-check-circle-fill"></i>
                        </a>
                    <?php else : ?>
                        <a href="validasi-artikel.php?aksi=draft&id=<?= $row['id'] ?>"
                            class="btn-action btn-action-draft"
                            title="Jadikan Draft">
                            <i class="bi bi-clock-history"></i>
                        </a>
                    <?php endif; ?>

                    <button onclick="hapusArtikel(<?= $row['id'] ?>)"
                        class="btn-action btn-action-delete"
                        title="Hapus Artikel">
                        <i class="bi bi-trash3-fill"></i>
                    </button>
                </div>
            </td>
        </tr>

        <!-- Modal Detail Artikel -->
        <div class="modal fade" id="detailModal<?= $row['id'] ?>" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content" style="border-radius: 24px;">
                    <div class="modal-header" style="border: none; padding: 2rem 2rem 1rem 2rem; background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); border-radius: 24px 24px 0 0;">
                        <h5 class="modal-title fw-bold" style="color: #0f172a; font-size: 1.35rem;">
                            <i class="bi bi-file-earmark-text me-2" style="color: var(--pustani-green);"></i>
                            Detail Artikel
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body" style="padding: 2rem;">
                        <?php if ($has_image) : ?>
                            <img src="<?= htmlspecialchars($image_path) ?>"
                                alt="<?= htmlspecialchars($row['title']) ?>"
                                class="article-detail-img"
                                onerror="this.style.display='none'">
                        <?php endif; ?>

                        <div style="background: #f8fafc; padding: 1.25rem; border-radius: 16px; margin-bottom: 1.5rem;">
                            <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
                                <div style="width: 50px; height: 50px; border-radius: 50%; background: linear-gradient(135deg, var(--pustani-green) 0%, var(--pustani-green-light) 100%); display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 1.25rem;">
                                    <?= $author_initial ?>
                                </div>
                                <div>
                                    <div style="color: #64748b; font-size: 0.8rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">Penulis</div>
                                    <div style="color: #0f172a; font-weight: 700; font-size: 1.1rem;"><?= htmlspecialchars($row['author_name']) ?></div>
                                </div>
                            </div>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                                <div>
                                    <div style="color: #64748b; font-size: 0.8rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.35rem;">Status</div>
                                    <span class="status-badge <?= $status_class ?>">
                                        <i class="bi bi-<?= $status_icon ?>"></i>
                                        <?= $status_text ?>
                                    </span>
                                </div>
                                <div>
                                    <div style="color: #64748b; font-size: 0.8rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.35rem;">Tanggal</div>
                                    <div style="color: #0f172a; font-weight: 600;">
                                        <i class="bi bi-calendar3 me-1"></i>
                                        <?= date('d M Y', strtotime($row['created_at'])) ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div style="margin-bottom: 1.5rem;">
                            <h4 style="color: #0f172a; font-weight: 800; font-size: 1.5rem; line-height: 1.4; margin-bottom: 1rem;">
                                <?= htmlspecialchars($row['title']) ?>
                            </h4>
                            <div style="color: #475569; font-size: 0.95rem; line-height: 1.8;">
                                <?= nl2br(htmlspecialchars($row['content'])) ?>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer" style="border: none; padding: 1rem 2rem 2rem 2rem; background: white; gap: 0.75rem;">
                        <button type="button" class="btn btn-cancel" data-bs-dismiss="modal" style="background: #f1f5f9; color: #475569; border: none; border-radius: 14px; padding: 0.75rem 1.75rem; font-weight: 700;">
                            Tutup
                        </button>

                        <?php if ($row['status'] == 'draft') : ?>
                            <a href="validasi-artikel.php?aksi=publish&id=<?= $row['id'] ?>"
                                class="btn"
                                style="background: linear-gradient(135deg, var(--pustani-green) 0%, #047857 100%); color: white; border: none; border-radius: 14px; padding: 0.75rem 1.75rem; font-weight: 700;">
                                <i class="bi bi-check-circle-fill me-2"></i>
                                Publikasikan
                            </a>
                        <?php else : ?>
                            <a href="validasi-artikel.php?aksi=draft&id=<?= $row['id'] ?>"
                                class="btn"
                                style="background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%); color: #78350f; border: none; border-radius: 14px; padding: 0.75rem 1.75rem; font-weight: 700;">
                                <i class="bi bi-clock-history me-2"></i>
                                Jadikan Draft
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php
    }
} else {
    // Empty state dengan styling yang lebih baik
    ?>
    <tr>
        <td colspan="4">
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i class="bi bi-inbox"></i>
                </div>
                <h3 class="empty-state-title">Tidak Ada Artikel</h3>
                <p class="empty-state-text">
                    <?php if (!empty($keyword)) : ?>
                        Tidak ditemukan artikel dengan kata kunci "<strong><?= htmlspecialchars($keyword) ?></strong>"
                    <?php else : ?>
                        Belum ada artikel yang tersedia di sistem
                    <?php endif; ?>
                </p>
            </div>
        </td>
    </tr>
<?php
}
?>
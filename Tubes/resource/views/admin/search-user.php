<?php
// search-user.php
require_once '../../../config/koneksi.php';

$keyword = isset($_GET['keyword']) ? mysqli_real_escape_string($koneksi, $_GET['keyword']) : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';

// Cek apakah kolom is_banned ada
$column_check = mysqli_query($koneksi, "SHOW COLUMNS FROM users LIKE 'is_banned'");
$has_banned_column = mysqli_num_rows($column_check) > 0;

// Build query
$query = "SELECT * FROM users WHERE role = 'user' AND (username LIKE '%$keyword%' OR email LIKE '%$keyword%')";

if ($has_banned_column && $status_filter !== 'all') {
    if ($status_filter === 'active') {
        $query .= " AND is_banned = 0";
    } elseif ($status_filter === 'banned') {
        $query .= " AND is_banned = 1";
    }
}

$query .= " ORDER BY created_at DESC";
$result = mysqli_query($koneksi, $query);

if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        // Cek is_banned dengan fallback
        $is_banned = $has_banned_column && isset($row['is_banned']) ? $row['is_banned'] : 0;
        $status_class = $is_banned == 0 ? 'active' : 'banned';
        $status_icon = $is_banned == 0 ? 'check-circle-fill' : 'slash-circle';
        $status_text = $is_banned == 0 ? 'Aktif' : 'Banned';

        // Get first letter for avatar
        $initial = strtoupper(substr($row['username'], 0, 1));

        // Handle no_hp
        $no_hp = !empty($row['no_hp']) ? htmlspecialchars($row['no_hp']) : '-';

        // Handle alamat
        $alamat = !empty($row['alamat']) ? htmlspecialchars($row['alamat']) : 'Belum diisi';
?>
        <tr>
            <td>
                <div class="user-info">
                    <div class="user-avatar"><?= $initial ?></div>
                    <div class="user-details">
                        <div class="user-name"><?= htmlspecialchars($row['username']) ?></div>
                        <div class="user-email">
                            <i class="bi bi-envelope"></i>
                            <span><?= htmlspecialchars($row['email']) ?></span>
                        </div>
                    </div>
                </div>
            </td>
            <td>
                <div style="color: #475569; font-weight: 600;">
                    <i class="bi bi-telephone me-2" style="color: #94a3b8;"></i>
                    <?= $no_hp ?>
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

                    <?php if ($is_banned == 0) : ?>
                        <button onclick="bannedUser(<?= $row['id'] ?>)"
                            class="btn-action btn-action-banned"
                            title="Banned User">
                            <i class="bi bi-slash-circle"></i>
                        </button>
                    <?php else : ?>
                        <a href="kelola-user.php?aksi=unbanned&id=<?= $row['id'] ?>"
                            class="btn-action btn-action-unbanned"
                            title="Aktifkan Kembali">
                            <i class="bi bi-check-circle-fill"></i>
                        </a>
                    <?php endif; ?>

                    <button onclick="hapusUser(<?= $row['id'] ?>)"
                        class="btn-action btn-action-delete"
                        title="Hapus User">
                        <i class="bi bi-trash3-fill"></i>
                    </button>
                </div>
            </td>
        </tr>

        <!-- Modal Detail User -->
        <div class="modal fade" id="detailModal<?= $row['id'] ?>" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="bi bi-person-circle me-2" style="color: var(--pustani-green);"></i>
                            Detail User
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <!-- User Avatar Large -->
                        <div style="text-align: center; margin-bottom: 2rem;">
                            <div style="width: 80px; height: 80px; border-radius: 50%; background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); display: inline-flex; align-items: center; justify-content: center; color: white; font-weight: 800; font-size: 2rem; box-shadow: 0 8px 24px rgba(59, 130, 246, 0.3);">
                                <?= $initial ?>
                            </div>
                        </div>

                        <!-- User Information -->
                        <div class="info-row">
                            <div style="flex: 1;">
                                <div class="info-label">Nama Lengkap</div>
                                <div class="info-value">
                                    <i class="bi bi-person-fill me-2" style="color: #3b82f6;"></i>
                                    <?= htmlspecialchars($row['username']) ?>
                                </div>
                            </div>
                        </div>

                        <div class="info-row">
                            <div style="flex: 1;">
                                <div class="info-label">Email</div>
                                <div class="info-value">
                                    <i class="bi bi-envelope-fill me-2" style="color: #3b82f6;"></i>
                                    <?= htmlspecialchars($row['email']) ?>
                                </div>
                            </div>
                        </div>

                        <div class="info-row">
                            <div style="flex: 1;">
                                <div class="info-label">No. Handphone</div>
                                <div class="info-value">
                                    <i class="bi bi-telephone-fill me-2" style="color: #3b82f6;"></i>
                                    <?= $no_hp ?>
                                </div>
                            </div>
                        </div>

                        <div class="info-row">
                            <div style="flex: 1;">
                                <div class="info-label">Alamat</div>
                                <div class="info-value" style="line-height: 1.7;">
                                    <i class="bi bi-geo-alt-fill me-2" style="color: #3b82f6;"></i>
                                    <?= $alamat ?>
                                </div>
                            </div>
                        </div>

                        <div class="info-row">
                            <div style="flex: 1;">
                                <div class="info-label">Status Akun</div>
                                <div>
                                    <span class="status-badge <?= $status_class ?>">
                                        <i class="bi bi-<?= $status_icon ?>"></i>
                                        <?= $status_text ?>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="info-row">
                            <div style="flex: 1;">
                                <div class="info-label">Bergabung Sejak</div>
                                <div class="info-value">
                                    <i class="bi bi-calendar3 me-2" style="color: #3b82f6;"></i>
                                    <?= date('d F Y', strtotime($row['created_at'])) ?>
                                </div>
                            </div>
                        </div>

                        <?php if (!empty($row['foto_profil'])) : ?>
                            <div class="info-row">
                                <div style="flex: 1;">
                                    <div class="info-label">Foto Profil</div>
                                    <img src="../../../public/img/profil/<?= htmlspecialchars($row['foto_profil']) ?>"
                                        alt="Foto Profil"
                                        style="width: 150px; height: 150px; border-radius: 12px; object-fit: cover; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-cancel" data-bs-dismiss="modal">
                            Tutup
                        </button>

                        <?php if ($is_banned == 0) : ?>
                            <button onclick="bannedUser(<?= $row['id'] ?>)"
                                class="btn"
                                style="background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%); color: white; border: none; border-radius: 14px; padding: 0.75rem 1.75rem; font-weight: 700;"
                                data-bs-dismiss="modal">
                                <i class="bi bi-slash-circle me-2"></i>
                                Banned User
                            </button>
                        <?php else : ?>
                            <a href="kelola-user.php?aksi=unbanned&id=<?= $row['id'] ?>"
                                class="btn"
                                style="background: linear-gradient(135deg, #16a34a 0%, #15803d 100%); color: white; border: none; border-radius: 14px; padding: 0.75rem 1.75rem; font-weight: 700;">
                                <i class="bi bi-check-circle-fill me-2"></i>
                                Aktifkan Kembali
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php
    }
} else {
    // Empty state
    ?>
    <tr>
        <td colspan="4">
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i class="bi bi-person-x"></i>
                </div>
                <h3 class="empty-state-title">Tidak Ada Data User</h3>
                <p class="empty-state-text">
                    <?php if (!empty($keyword)) : ?>
                        Tidak ditemukan user dengan kata kunci "<strong><?= htmlspecialchars($keyword) ?></strong>"
                    <?php elseif ($status_filter !== 'all') : ?>
                        Tidak ada user dengan status <strong><?= $status_filter === 'active' ? 'Aktif' : 'Banned' ?></strong>
                    <?php else : ?>
                        Belum ada user yang terdaftar di sistem
                    <?php endif; ?>
                </p>
            </div>
        </td>
    </tr>
<?php
}
?>
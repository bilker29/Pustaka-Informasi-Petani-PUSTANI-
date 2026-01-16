<?php
// search-ahli.php
require_once '../../../config/koneksi.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$keyword = isset($_GET['keyword']) ? mysqli_real_escape_string($koneksi, $_GET['keyword']) : '';

// Cek apakah kolom is_verified dan status_ahli ada untuk mencegah error
$column_verified = mysqli_query($koneksi, "SHOW COLUMNS FROM users LIKE 'is_verified'");
$has_verified_column = mysqli_num_rows($column_verified) > 0;

$column_status = mysqli_query($koneksi, "SHOW COLUMNS FROM users LIKE 'status_ahli'");
$has_status_column = mysqli_num_rows($column_status) > 0;

// Query: Mengambil user dengan role 'expert' ATAU user biasa yang status pengajuannya 'pending'
$query = "SELECT * FROM users 
          WHERE (role = 'expert' " . ($has_status_column ? "OR status_ahli = 'pending'" : "") . ") 
          AND (username LIKE '%$keyword%' OR email LIKE '%$keyword%')
          ORDER BY " . ($has_status_column ? "status_ahli = 'pending' DESC, " : "") . ($has_verified_column ? "is_verified ASC, " : "") . "id DESC";

$result = mysqli_query($koneksi, $query);

if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        // Logika Status Pengajuan (Pending/None)
        $is_pending = $has_status_column && isset($row['status_ahli']) && $row['status_ahli'] == 'pending';

        // Logika Verifikasi
        $is_verified = $has_verified_column && isset($row['is_verified']) ? $row['is_verified'] : 0;

        // Penentuan Badge dan UI berdasarkan status
        if ($is_pending) {
            $verified_class = 'unverified';
            $verified_icon = 'hourglass-split';
            $verified_text = 'Menunggu Persetujuan';
        } else {
            $verified_class = $is_verified == 1 ? 'verified' : 'unverified';
            $verified_icon = $is_verified == 1 ? 'shield-check' : 'shield-exclamation';
            $verified_text = $is_verified == 1 ? 'Terverifikasi' : 'Belum Diverifikasi';
        }

        // Get first letter for avatar
        $initial = strtoupper(substr($row['username'], 0, 1));

        // Handle keahlian field
        $expertise = !empty($row['keahlian']) ? htmlspecialchars($row['keahlian']) : 'Umum / Belum diisi';
?>
        <tr>
            <td>
                <div class="expert-info">
                    <div class="expert-avatar"><?= $initial ?></div>
                    <div class="expert-details">
                        <div class="expert-name">
                            <?= htmlspecialchars($row['username']) ?>
                            <?php if ($is_pending) : ?>
                                <span class="badge bg-primary ms-1" style="font-size: 0.65rem;">BARU</span>
                            <?php endif; ?>
                        </div>
                        <div class="expert-email">
                            <i class="bi bi-envelope"></i>
                            <span><?= htmlspecialchars($row['email']) ?></span>
                        </div>
                    </div>
                </div>
            </td>
            <td>
                <span class="expertise-badge">
                    <i class="bi bi-mortarboard-fill"></i>
                    <?= $expertise ?>
                </span>
            </td>
            <td>
                <span class="verification-badge <?= $verified_class ?>">
                    <i class="bi bi-<?= $verified_icon ?>"></i>
                    <?= $verified_text ?>
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

                    <?php if ($is_pending) : ?>
                        <a href="verifikasi-ahli.php?aksi=verifikasi&id=<?= $row['id'] ?>"
                            class="btn-action btn-action-verify shadow-sm"
                            title="Setujui Jadi Pakar"
                            style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white;">
                            <i class="bi bi-check-lg"></i>
                        </a>
                        <a href="verifikasi-ahli.php?aksi=tolak&id=<?= $row['id'] ?>"
                            class="btn-action btn-action-delete shadow-sm"
                            title="Tolak Pengajuan">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    <?php else : ?>
                        <?php if ($is_verified == 0) : ?>
                            <a href="verifikasi-ahli.php?aksi=verifikasi&id=<?= $row['id'] ?>"
                                class="btn-action btn-action-verify"
                                title="Verifikasi Pakar">
                                <i class="bi bi-shield-check"></i>
                            </a>
                        <?php else : ?>
                            <a href="verifikasi-ahli.php?aksi=batal&id=<?= $row['id'] ?>"
                                class="btn-action btn-action-unverify"
                                title="Batalkan Verifikasi">
                                <i class="bi bi-shield-x"></i>
                            </a>
                        <?php endif; ?>

                        <button onclick="hapusAhli(<?= $row['id'] ?>)"
                            class="btn-action btn-action-delete"
                            title="Hapus Pakar">
                            <i class="bi bi-trash3-fill"></i>
                        </button>
                    <?php endif; ?>
                </div>
            </td>
        </tr>

        <div class="modal fade" id="detailModal<?= $row['id'] ?>" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0">
                    <div class="modal-header border-bottom-0 pb-0">
                        <h5 class="modal-title">
                            <i class="bi bi-person-badge me-2" style="color: var(--pustani-green);"></i>
                            Detail Pakar
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div style="text-align: center; margin-bottom: 2rem;">
                            <div style="width: 80px; height: 80px; border-radius: 50%; background: linear-gradient(135deg, var(--pustani-green) 0%, #16a34a 100%); display: inline-flex; align-items: center; justify-content: center; color: white; font-weight: 800; font-size: 2rem; box-shadow: 0 8px 24px rgba(6, 78, 59, 0.3);">
                                <?= $initial ?>
                            </div>
                        </div>

                        <div class="info-row">
                            <div style="flex: 1;">
                                <div class="info-label">Nama Lengkap</div>
                                <div class="info-value"><?= htmlspecialchars($row['username']) ?></div>
                            </div>
                        </div>

                        <div class="info-row">
                            <div style="flex: 1;">
                                <div class="info-label">Email</div>
                                <div class="info-value"><?= htmlspecialchars($row['email']) ?></div>
                            </div>
                        </div>

                        <div class="info-row">
                            <div style="flex: 1;">
                                <div class="info-label">Bidang Keahlian</div>
                                <div class="info-value"><?= $expertise ?></div>
                            </div>
                        </div>

                        <div class="info-row">
                            <div style="flex: 1;">
                                <div class="info-label">Status</div>
                                <div>
                                    <span class="verification-badge <?= $verified_class ?>">
                                        <i class="bi bi-<?= $verified_icon ?>"></i>
                                        <?= $verified_text ?>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <?php if (isset($row['dokumen_pendukung']) && !empty($row['dokumen_pendukung'])) : ?>
                            <div class="info-row">
                                <div style="flex: 1;">
                                    <div class="info-label">Dokumen Pendukung</div>
                                    <div class="mt-2">
                                        <a href="../../../public/img/img1/dokumen/<?= $row['dokumen_pendukung'] ?>" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-bold">
                                            <i class="bi bi-file-earmark-pdf me-1"></i> Lihat CV / Sertifikat
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($row['bio'])) : ?>
                            <div class="info-row border-0">
                                <div style="flex: 1;">
                                    <div class="info-label">Biografi / Pengalaman</div>
                                    <div class="info-value" style="line-height: 1.7;">
                                        <?= nl2br(htmlspecialchars($row['bio'])) ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="modal-footer border-top-0 pt-0">
                        <button type="button" class="btn btn-cancel" data-bs-dismiss="modal">Tutup</button>

                        <?php if ($is_pending) : ?>
                            <a href="verifikasi-ahli.php?aksi=verifikasi&id=<?= $row['id'] ?>"
                                class="btn"
                                style="background: linear-gradient(135deg, var(--pustani-green) 0%, #047857 100%); color: white; border-radius: 14px; padding: 0.75rem 1.75rem; font-weight: 700;">
                                <i class="bi bi-check-circle me-2"></i>Setujui Jadi Pakar
                            </a>
                        <?php elseif ($is_verified == 0) : ?>
                            <a href="verifikasi-ahli.php?aksi=verifikasi&id=<?= $row['id'] ?>"
                                class="btn"
                                style="background: linear-gradient(135deg, var(--pustani-green) 0%, #047857 100%); color: white; border-radius: 14px; padding: 0.75rem 1.75rem; font-weight: 700;">
                                <i class="bi bi-shield-check me-2"></i>Verifikasi Sekarang
                            </a>
                        <?php else : ?>
                            <a href="verifikasi-ahli.php?aksi=batal&id=<?= $row['id'] ?>"
                                class="btn"
                                style="background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%); color: #78350f; border-radius: 14px; padding: 0.75rem 1.75rem; font-weight: 700;">
                                <i class="bi bi-shield-x me-2"></i>Batalkan Verifikasi
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php
    }
} else {
    ?>
    <tr>
        <td colspan="4">
            <div class="empty-state">
                <div class="empty-state-icon"><i class="bi bi-person-x"></i></div>
                <h3 class="empty-state-title">Tidak Ada Data Pakar</h3>
                <p class="empty-state-text">
                    <?= !empty($keyword) ? "Tidak ditemukan hasil untuk '<strong>$keyword</strong>'" : "Belum ada pengajuan ahli tani saat ini." ?>
                </p>
            </div>
        </td>
    </tr>
<?php
}
?>
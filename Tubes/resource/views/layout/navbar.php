<?php
// Cek session, jika belum ada user_id berarti mode TAMU
$nav_is_login = false;
$nav_username = "Tamu";
$nav_foto = "https://ui-avatars.com/api/?name=Tamu&background=random";
$nav_role = "";
$nav_status = "";

// 1. CEK DULU: APAKAH SUDAH LOGIN?
if (isset($_SESSION['user_id'])) {
    $u_id = $_SESSION['user_id'];

    // Ambil data user dari database
    $q_nav = mysqli_query($koneksi, "SELECT * FROM users WHERE id = '$u_id'");

    // Pastikan user benar-benar ada di database
    if (mysqli_num_rows($q_nav) > 0) {
        $d_nav = mysqli_fetch_assoc($q_nav);
        $nav_is_login = true; // Tandai sudah login

        $nav_username = $d_nav['username'];
        $nav_role = $d_nav['role'];
        $nav_status = $d_nav['status_ahli'];

        // Logika Foto Profil User
        $nav_foto = "https://ui-avatars.com/api/?name=" . urlencode($nav_username) . "&background=064e3b&color=fff";
        if (!empty($d_nav['foto_profil'])) {
            $src_nav = "../../public/img/img1/profil/" . $d_nav['foto_profil'];
            if (file_exists($src_nav)) $nav_foto = $src_nav . "?v=" . time();
        }
    }
}
?>

<nav class="navbar navbar-expand-lg sticky-top">
    <div class="container d-flex justify-content-between align-items-center">
        <a class="navbar-brand" href="Home.php"><img src="../../public/img/img1/logo navbar.png" style="height: 50px;"></a>

        <div class="d-flex align-items-center gap-3 ms-auto">
            <a href="search.php" class="search-btn-nav">
                <i class="bi bi-search"></i> <span>Cari Info</span>
            </a>

            <?php if ($nav_is_login) : ?>
                <?php if ($nav_role == 'expert') : ?>
                    <a href="dashboard.php" class="btn btn-success rounded-pill fw-bold px-3 shadow-sm">
                        <i class="bi bi-grid-fill me-1"></i> Dashboard
                    </a>
                <?php elseif ($nav_status == 'pending') : ?>
                    <button class="btn btn-warning rounded-pill fw-bold px-3 text-white shadow-sm" disabled>
                        <i class="bi bi-hourglass-split me-1"></i> Menunggu Verifikasi
                    </button>
                <?php elseif ($nav_status == 'rejected') : ?>
                    <button class="btn btn-danger rounded-pill fw-bold px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalJadiAhli">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Ajukan Ulang
                    </button>
                <?php else : ?>
                    <button class="btn btn-outline-success rounded-pill fw-bold px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalJadiAhli">
                        Jadi Ahli
                    </button>
                <?php endif; ?>

                <div class="dropdown">
                    <button class="custom-dropdown-btn" type="button" data-bs-toggle="dropdown">
                        <img src="<?= $nav_foto ?>" class="nav-profile-img shadow-sm" style="width: 45px; height: 45px; object-fit: cover; border-radius: 50%; border: 2px solid #e2e8f0;">
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-custom">
                        <li class="dropdown-header-custom">Halo, <?= htmlspecialchars($nav_username); ?></li>

                        <li><a class="dropdown-item" href="dashboard.php"><i class="bi bi-grid-fill"></i> Dashboard</a></li>

                        <?php if ($nav_role == 'expert') : ?>
                            <li><a class="dropdown-item" href="buatartikel.php"><i class="bi bi-pencil-square"></i> Buat Artikel</a></li>
                        <?php endif; ?>

                        <li><a class="dropdown-item" href="profilsaya.php"><i class="bi bi-person"></i> Profil Anda</a></li>
                        <li><a class="dropdown-item" href="editprofil.php"><i class="bi bi-gear"></i> Edit Profil</a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item logout-item" href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
                    </ul>
                </div>

            <?php else : ?>
                <a href="login.php" class="btn btn-success rounded-pill fw-bold px-4" style="background-color: var(--accent-color); border:none;">Masuk</a>
            <?php endif; ?>

        </div>
    </div>
</nav>

<div class="modal fade" id="modalJadiAhli" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 2rem;">
            <div class="modal-header border-0 pt-4 px-4">
                <h4 class="fw-bold" style="color: #1a3e35;">Pengajuan Ahli Tani</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="ajukanahli_proses.php" method="POST" enctype="multipart/form-data">
                <div class="modal-body px-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Bidang Keahlian</label>
                        <input type="text" name="keahlian" class="form-control rounded-pill px-3" placeholder="Contoh: Spesialis Hidroponik" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Bio & Pengalaman</label>
                        <textarea name="bio" class="form-control" rows="4" style="border-radius: 1rem;" placeholder="Jelaskan latar belakang Anda..." required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Upload CV/Sertifikat (PDF/JPG)</label>
                        <input type="file" name="dokumen" class="form-control rounded-pill">
                    </div>
                </div>
                <div class="modal-footer border-0 pb-4 px-4">
                    <button type="submit" name="submit_pengajuan" class="btn btn-success rounded-pill w-100 fw-bold py-2">Kirim Pengajuan</button>
                </div>
            </form>
        </div>
    </div>
</div>
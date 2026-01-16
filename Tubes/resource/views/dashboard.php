<?php
session_start();
require '../../config/koneksi.php';

// 1. PROTEKSI LOGIN
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// 2. PROTEKSI REAL-TIME & ROLE SYNC
$q_auth = mysqli_query($koneksi, "SELECT * FROM users WHERE id = '$user_id'");
$d_user = mysqli_fetch_assoc($q_auth);

if (!$d_user || $d_user['role'] !== 'expert') {
    header("Location: Home.php");
    exit;
}

$_SESSION['role'] = $d_user['role'];
$username = $d_user['username'];
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
$status_post = "";

// 3. LOGIKA SUBMIT ARTIKEL (PROSES UTAMA)
if (isset($_POST['publish_dashboard'])) {
    $title = mysqli_real_escape_string($koneksi, $_POST['title']);
    $category = mysqli_real_escape_string($koneksi, $_POST['category']);
    $content = mysqli_real_escape_string($koneksi, $_POST['content']);
    $status = 'draft';

    $gambar = "";
    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === 0) {
        $namaFile = $_FILES['cover_image']['name'];
        $tmpName = $_FILES['cover_image']['tmp_name'];
        $ekstensi = strtolower(pathinfo($namaFile, PATHINFO_EXTENSION));

        if (in_array($ekstensi, ['jpg', 'jpeg', 'png'])) {
            $namaFileBaru = uniqid() . '.' . $ekstensi;
            if (move_uploaded_file($tmpName, '../../public/img/img1/' . $namaFileBaru)) {
                $gambar = $namaFileBaru;
            } else {
                $status_post = "upload_fail";
            }
        } else {
            $status_post = "invalid_type";
        }
    } else {
        $status_post = "no_image";
    }

    if ($gambar) {
        $query = "INSERT INTO articles (user_id, title, category, content, image, status) 
                  VALUES ('$user_id', '$title', '$category', '$content', '$gambar', '$status')";
        if (mysqli_query($koneksi, $query)) {
            // Redirect instant agar data tersimpan dan halaman bersih
            header("Location: dashboard.php?status=success");
            exit;
        } else {
            $status_post = "db_error";
        }
    }
}

// 4. LOGIKA UPDATE PROFIL
if (isset($_POST['simpan_profil_dashboard'])) {
    $new_username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $no_hp = mysqli_real_escape_string($koneksi, $_POST['no_hp']);
    $alamat = mysqli_real_escape_string($koneksi, $_POST['alamat']);
    $keahlian = mysqli_real_escape_string($koneksi, $_POST['keahlian']);
    $bio = mysqli_real_escape_string($koneksi, $_POST['bio']);

    $query_foto = "";
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {
        $namaFile = $_FILES['foto']['name'];
        $tmpName = $_FILES['foto']['tmp_name'];
        $ekstensi = strtolower(pathinfo($namaFile, PATHINFO_EXTENSION));
        if (in_array($ekstensi, ['jpg', 'jpeg', 'png'])) {
            $namaFileBaru = uniqid() . '.' . $ekstensi;
            if (move_uploaded_file($tmpName, '../../public/img/img1/profil/' . $namaFileBaru)) {
                $query_foto = ", foto_profil='$namaFileBaru'";
            }
        }
    }

    $q_upd = "UPDATE users SET username='$new_username', no_hp='$no_hp', alamat='$alamat', keahlian='$keahlian', bio='$bio' $query_foto WHERE id='$user_id'";
    if (mysqli_query($koneksi, $q_upd)) {
        $_SESSION['username'] = $new_username;
        header("Location: dashboard.php?page=profil&status=profile_success");
        exit;
    }
}

// 5. STATUS HANDLING UNTUK SWEETALERT
if (isset($_GET['status'])) {
    $status_post = $_GET['status'];
}

// Logic Foto Profil Tampilan
$foto_profil = "https://ui-avatars.com/api/?name=" . urlencode($username) . "&background=064e3b&color=fff";
if (!empty($d_user['foto_profil'])) {
    $src = "../../public/img/img1/profil/" . $d_user['foto_profil'];
    if (file_exists($src)) $foto_profil = $src . "?v=" . time();
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Panel Ahli - Pustani</title>
    <link rel="icon" href="../../public/img/img1/logo.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        :root {
            --pustani-green: #064e3b;
            --pustani-green-light: #16a34a;
            --pustani-green-lighter: #22c55e;
            --sidebar-width: 280px;
            --navbar-height: 70px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: #f1f5f9;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            overflow-x: hidden;
            color: #0f172a;
        }

        /* Sidebar Styles */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background: linear-gradient(180deg, #ffffff 0%, #fafbfc 100%);
            border-right: 1px solid #e2e8f0;
            padding: 0;
            z-index: 1000;
            overflow-y: auto;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.02);
        }

        .sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: transparent;
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }

        /* Logo Section */
        .logo-section {
            padding: 1.75rem 1.5rem;
            border-bottom: 1px solid #e2e8f0;
            background: white;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .logo-container {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
        }

        .logo-container img {
            max-height: 65px;
            width: auto;
            filter: drop-shadow(0 2px 8px rgba(6, 78, 59, 0.1));
        }

        /* Expert Badge */
        .expert-badge {
            background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
            color: white;
            font-size: 0.7rem;
            font-weight: 800;
            letter-spacing: 1.8px;
            padding: 0.65rem 1rem;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 4px 20px rgba(22, 163, 74, 0.25);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            position: relative;
            overflow: hidden;
        }

        .expert-badge::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            animation: shimmer 3s infinite;
        }

        @keyframes shimmer {
            0% {
                transform: translateX(-100%) translateY(-100%) rotate(45deg);
            }

            100% {
                transform: translateX(100%) translateY(100%) rotate(45deg);
            }
        }

        .verified-icon {
            width: 8px;
            height: 8px;
            background-color: #fff;
            border-radius: 50%;
            display: inline-block;
            animation: pulse 2s infinite;
            position: relative;
            z-index: 1;
        }

        @keyframes pulse {

            0%,
            100% {
                transform: scale(1);
                box-shadow: 0 0 0 0 rgba(255, 255, 255, 0.7);
            }

            50% {
                transform: scale(1.1);
                box-shadow: 0 0 0 8px rgba(255, 255, 255, 0);
            }
        }

        /* Navigation */
        .sidebar-nav {
            padding: 1.5rem 1rem;
        }

        .nav-section-title {
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #94a3b8;
            padding: 0.75rem 1rem 0.5rem 1rem;
            margin-top: 0.5rem;
        }

        .nav-link {
            color: #475569;
            font-weight: 600;
            font-size: 0.95rem;
            padding: 0.85rem 1.25rem;
            border-radius: 14px;
            margin: 0.35rem 0;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
            gap: 0.85rem;
            position: relative;
            overflow: hidden;
            text-decoration: none;
        }

        .nav-link i {
            font-size: 1.15rem;
            transition: transform 0.3s ease;
        }

        .nav-link::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 4px;
            background: var(--pustani-green-light);
            transform: scaleY(0);
            transition: transform 0.3s ease;
        }

        .nav-link:hover {
            background: #f0fdf4;
            color: var(--pustani-green);
            transform: translateX(4px);
        }

        .nav-link:hover i {
            transform: scale(1.1);
        }

        .nav-link.active {
            background: linear-gradient(135deg, var(--pustani-green) 0%, #047857 100%);
            color: white !important;
            box-shadow: 0 4px 12px rgba(6, 78, 59, 0.25);
        }

        .nav-link.active::before {
            transform: scaleY(1);
        }

        .nav-link.active i {
            filter: drop-shadow(0 2px 4px rgba(255, 255, 255, 0.3));
        }

        .nav-divider {
            height: 1px;
            background: linear-gradient(90deg, transparent, #e2e8f0, transparent);
            margin: 1.5rem 1rem;
        }

        .nav-link.external-link {
            color: #3b82f6;
            background: #eff6ff;
        }

        .nav-link.external-link:hover {
            background: #dbeafe;
            color: #2563eb;
        }

        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Top Navbar */
        .expert-navbar {
            background: white;
            border-bottom: 1px solid #e2e8f0;
            padding: 1rem 2rem;
            height: var(--navbar-height);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 999;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
        }

        .page-title {
            color: var(--pustani-green);
            font-size: 1.75rem;
            font-weight: 800;
            margin: 0;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 1rem;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            padding: 0.65rem 1.25rem;
            border-radius: 50px;
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
        }

        .user-profile:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            transform: translateY(-2px);
        }

        .user-avatar {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--pustani-green);
            box-shadow: 0 2px 8px rgba(6, 78, 59, 0.2);
        }

        .user-info h6 {
            font-size: 0.95rem;
            font-weight: 700;
            color: #0f172a;
            margin: 0;
            line-height: 1.2;
        }

        .user-info p {
            font-size: 0.75rem;
            color: var(--pustani-green);
            margin: 0;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 0.35rem;
        }

        /* Content Wrapper */
        .content-wrapper {
            padding: 2rem;
            flex-grow: 1;
        }

        /* Welcome Card */
        .welcome-card {
            background: linear-gradient(135deg, var(--pustani-green) 0%, #059669 50%, var(--pustani-green-light) 100%);
            border-radius: 24px;
            padding: 2.5rem;
            margin-bottom: 2rem;
            color: white;
            position: relative;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(6, 78, 59, 0.3);
        }

        .welcome-card::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
            border-radius: 50%;
        }

        .welcome-card::after {
            content: '';
            position: absolute;
            bottom: -30%;
            left: -10%;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.05) 0%, transparent 70%);
            border-radius: 50%;
        }

        .welcome-content {
            position: relative;
            z-index: 2;
        }

        .welcome-title {
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 0.75rem;
        }

        .welcome-subtitle {
            font-size: 1.05rem;
            opacity: 0.9;
            margin-bottom: 0;
        }

        .welcome-icon {
            position: absolute;
            right: 2rem;
            top: 50%;
            transform: translateY(-50%);
            font-size: 120px;
            color: rgba(255, 255, 255, 0.1);
            z-index: 1;
        }

        /* Stat Cards */
        .stat-card {
            background: white;
            border-radius: 20px;
            padding: 1.75rem;
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
            height: 100%;
        }

        .stat-card:hover {
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
            transform: translateY(-4px);
            border-color: var(--card-border);
        }

        .stat-card.total {
            --card-border: #3b82f6;
        }

        .stat-card.published {
            --card-border: #16a34a;
        }

        .stat-card.draft {
            --card-border: #f59e0b;
        }

        .stat-icon {
            width: 56px;
            height: 56px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }

        .stat-label {
            color: #64748b;
            font-size: 0.85rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.5rem;
        }

        .stat-value {
            color: #0f172a;
            font-size: 2.25rem;
            font-weight: 800;
            line-height: 1;
        }

        /* Article Card */
        .card-article {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            overflow: hidden;
            transition: all 0.3s ease;
            position: relative;
            height: 100%;
        }

        .card-article:hover {
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.1);
            transform: translateY(-4px);
        }

        .card-article-img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
        }

        .status-tag {
            position: absolute;
            top: 1rem;
            left: 1rem;
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-size: 0.7rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            z-index: 10;
            display: flex;
            align-items: center;
            gap: 0.35rem;
        }

        .tag-published {
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
            color: #065f46;
            border: 1px solid #6ee7b7;
        }

        .tag-draft {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            color: #92400e;
            border: 1px solid #fcd34d;
        }

        .card-article-body {
            padding: 1.5rem;
        }

        .card-article-title {
            color: #0f172a;
            font-weight: 700;
            font-size: 1.1rem;
            margin-bottom: 0.75rem;
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .card-article-meta {
            display: flex;
            align-items: center;
            gap: 1rem;
            color: #64748b;
            font-size: 0.85rem;
            margin-bottom: 1rem;
        }

        .card-article-meta i {
            font-size: 0.9rem;
        }

        .card-article-actions {
            display: flex;
            gap: 0.5rem;
        }

        .btn-article {
            flex: 1;
            padding: 0.65rem;
            border-radius: 12px;
            font-weight: 700;
            font-size: 0.85rem;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-edit {
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
            color: #1e40af;
            border: 1px solid #bfdbfe;
        }

        .btn-edit:hover {
            background: linear-gradient(135deg, #bfdbfe 0%, #93c5fd 100%);
            transform: translateY(-2px);
        }

        .btn-delete {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            color: #991b1b;
            border: 1px solid #fca5a5;
        }

        .btn-delete:hover {
            background: linear-gradient(135deg, #fecaca 0%, #fca5a5 100%);
            transform: translateY(-2px);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            background: white;
            border-radius: 20px;
            border: 2px dashed #e2e8f0;
        }

        .empty-state-icon {
            font-size: 5rem;
            color: #cbd5e1;
            margin-bottom: 1.5rem;
        }

        .empty-state-title {
            color: #475569;
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .empty-state-text {
            color: #94a3b8;
            font-size: 0.95rem;
            margin-bottom: 2rem;
        }

        .btn-primary-action {
            background: linear-gradient(135deg, var(--pustani-green) 0%, #047857 100%);
            color: white;
            padding: 0.85rem 2rem;
            border-radius: 14px;
            font-weight: 700;
            border: none;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
        }

        .btn-primary-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(6, 78, 59, 0.3);
            color: white;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .welcome-title {
                font-size: 1.5rem;
            }

            .welcome-icon {
                font-size: 80px;
            }

            .stat-value {
                font-size: 1.75rem;
            }
        }

        /* Mobile Menu Toggle */
        .mobile-menu-toggle {
            display: none;
            background: var(--pustani-green);
            color: white;
            border: none;
            padding: 0.65rem 1rem;
            border-radius: 12px;
            font-size: 1.25rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .mobile-menu-toggle:hover {
            background: #047857;
            transform: scale(1.05);
        }

        @media (max-width: 768px) {
            .mobile-menu-toggle {
                display: block;
            }

            .page-title {
                font-size: 1.25rem;
            }
        }

        /* Sidebar Overlay */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .sidebar-overlay.show {
            display: block;
            opacity: 1;
        }

        /* Animation */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-in {
            animation: fadeInUp 0.6s ease-out;
        }
    </style>
</head>

<body>

    <!-- Sidebar Overlay for Mobile -->
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

    <!-- Sidebar -->
    <?php include 'layout/sidebar-ahli.php'; ?>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Top Navbar -->
        <div class="expert-navbar">
            <div class="d-flex align-items-center gap-3">
                <button class="mobile-menu-toggle" onclick="toggleSidebar()">
                    <i class="bi bi-list"></i>
                </button>
                <h1 class="page-title">Panel Ahli Tani</h1>
            </div>
            <div class="user-profile">
                <img src="<?= $foto_profil; ?>" alt="<?= htmlspecialchars($username); ?>" class="user-avatar">
                <div class="user-info">
                    <h6><?= htmlspecialchars($username); ?></h6>
                    <p><i class="bi bi-patch-check-fill"></i> Verified Expert</p>
                </div>
            </div>
        </div>

        <!-- Content Wrapper -->
        <div class="content-wrapper">
            <?php
            switch ($page) {
                case 'tulis':
                    include 'dashboard/dashboard-tulis.php';
                    break;
                case 'profil':
                    include 'dashboard/dashboard-profil.php';
                    break;
                default:
                    include 'dashboard/dashboard-home.php';
                    break;
            }
            ?>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // 1. TAMPILAN SIDEBAR (MOBILE)
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            sidebar.classList.toggle('show');
            overlay.classList.toggle('show');
        }

        // Tutup sidebar saat klik di luar (Mobile)
        document.addEventListener('click', function(event) {
            const sidebar = document.querySelector('.sidebar');
            const toggle = document.querySelector('.mobile-menu-toggle');
            const overlay = document.getElementById('sidebarOverlay');

            const isClickInside = sidebar.contains(event.target) || (toggle && toggle.contains(event.target));

            if (!isClickInside && window.innerWidth <= 768 && sidebar.classList.contains('show')) {
                sidebar.classList.remove('show');
                overlay.classList.remove('show');
            }
        });

        // Reset sidebar saat resize layar
        window.addEventListener('resize', function() {
            if (window.innerWidth > 768) {
                document.querySelector('.sidebar').classList.remove('show');
                const overlay = document.getElementById('sidebarOverlay');
                if (overlay) overlay.classList.remove('show');
            }
        });

        // 2. LOGIKA SWEETALERT (SETELAH REDIRECT)
        // Mengambil parameter 'status' dari URL
        const urlParams = new URLSearchParams(window.location.search);
        const status = urlParams.get('status');

        if (status === 'success') {
            Swal.fire({
                title: 'Berhasil Diajukan!',
                text: 'Artikel Anda telah disimpan dan menunggu verifikasi admin.',
                icon: 'success',
                confirmButtonColor: '#064e3b',
                timer: 3000
            }).then(() => {
                // Bersihkan URL dari parameter status tanpa refresh halaman
                window.history.replaceState({}, document.title, window.location.pathname);
            });
        } else if (status === 'profile_success') {
            Swal.fire({
                title: 'Profil Diperbarui!',
                text: 'Perubahan pada profil Anda telah berhasil disimpan.',
                icon: 'success',
                confirmButtonColor: '#064e3b',
                timer: 3000
            }).then(() => {
                window.history.replaceState({}, document.title, window.location.pathname + "?page=profil");
            });
        }

        // 3. KONFIRMASI HAPUS ARTIKEL
        function konfirmasiHapus(id) {
            Swal.fire({
                title: 'Hapus Artikel?',
                text: "Data yang dihapus tidak dapat dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'hapusartikel.php?id=' + id + '&from=dashboard';
                }
            });
        }
    </script>
</body>

</html>
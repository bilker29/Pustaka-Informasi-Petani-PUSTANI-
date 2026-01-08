<?php
session_start();
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}
require '../layout/admin-app.php';
require '../../../config/koneksi.php';

$total_user = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM users WHERE role='user'"))['total'];
$total_artikel = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM articles"))['total'];
$total_expert = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM users WHERE role='expert'"))['total'];

admin_header("Dashboard");
?>
<style>
    :root {
        --pustani-green: #064e3b;
        --pustani-green-light: #16a34a;
        --pustani-green-lighter: #22c55e;
    }

    .dashboard-container {
        background: #f8fafb;
        min-height: 100vh;
        padding: 2rem 0;
    }

    /* Header Section */
    .dashboard-header {
        background: white;
        border-radius: 24px;
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    }

    .dashboard-title {
        color: var(--pustani-green);
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
        letter-spacing: -0.5px;
    }

    .dashboard-subtitle {
        color: #64748b;
        font-size: 0.95rem;
        margin: 0;
    }

    .date-badge {
        background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
        border: 1px solid #bbf7d0;
        color: var(--pustani-green);
        padding: 0.65rem 1.25rem;
        border-radius: 50px;
        font-weight: 600;
        font-size: 0.9rem;
        transition: all 0.3s ease;
    }

    .date-badge:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(34, 197, 94, 0.15);
    }

    /* Stat Cards */
    .stat-card {
        position: relative;
        border: none;
        border-radius: 24px;
        background: white;
        padding: 2rem;
        height: 100%;
        overflow: hidden;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--card-color), transparent);
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .stat-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.08);
    }

    .stat-card:hover::before {
        opacity: 1;
    }

    .stat-card.card-primary {
        --card-color: #3b82f6;
    }

    .stat-card.card-success {
        --card-color: #16a34a;
    }

    .stat-card.card-warning {
        --card-color: #f59e0b;
    }

    .stat-label {
        color: #64748b;
        font-size: 0.8rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 0.75rem;
    }

    .stat-value {
        color: #0f172a;
        font-size: 2.5rem;
        font-weight: 800;
        line-height: 1;
        margin-bottom: 0.5rem;
    }

    .stat-change {
        font-size: 0.85rem;
        color: #16a34a;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
    }

    .icon-box {
        width: 64px;
        height: 64px;
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.75rem;
        position: relative;
        overflow: hidden;
    }

    .icon-box::before {
        content: '';
        position: absolute;
        width: 100%;
        height: 100%;
        background: inherit;
        filter: blur(20px);
        opacity: 0.5;
    }

    .icon-box i {
        position: relative;
        z-index: 1;
    }

    /* Welcome Banner */
    .welcome-banner {
        background: linear-gradient(135deg, #064e3b 0%, #059669 50%, #16a34a 100%);
        border-radius: 28px;
        border: none;
        padding: 3rem;
        position: relative;
        overflow: hidden;
        box-shadow: 0 20px 60px rgba(6, 78, 59, 0.3);
    }

    .welcome-banner::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 500px;
        height: 500px;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
        border-radius: 50%;
    }

    .welcome-banner::after {
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
        color: white;
        font-size: 2rem;
        font-weight: 800;
        margin-bottom: 1rem;
        line-height: 1.3;
    }

    .welcome-text {
        color: rgba(255, 255, 255, 0.9);
        font-size: 1.05rem;
        margin-bottom: 2rem;
        line-height: 1.6;
    }

    .btn-action {
        padding: 0.85rem 2rem;
        border-radius: 50px;
        font-weight: 700;
        font-size: 0.95rem;
        transition: all 0.3s ease;
        border: none;
        text-decoration: none;
        display: inline-block;
    }

    .btn-primary-action {
        background: white;
        color: var(--pustani-green);
        box-shadow: 0 4px 14px rgba(255, 255, 255, 0.3);
    }

    .btn-primary-action:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(255, 255, 255, 0.4);
        color: var(--pustani-green);
    }

    .btn-outline-action {
        background: rgba(255, 255, 255, 0.1);
        color: white;
        border: 2px solid rgba(255, 255, 255, 0.3);
        backdrop-filter: blur(10px);
    }

    .btn-outline-action:hover {
        background: rgba(255, 255, 255, 0.2);
        border-color: rgba(255, 255, 255, 0.5);
        transform: translateY(-2px);
        color: white;
    }

    .banner-icon {
        position: absolute;
        right: 2rem;
        top: 50%;
        transform: translateY(-50%);
        font-size: 140px;
        color: rgba(255, 255, 255, 0.1);
        z-index: 1;
    }

    /* Quick Actions Grid */
    .quick-actions {
        background: white;
        border-radius: 24px;
        padding: 2rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    }

    .quick-actions-title {
        color: var(--pustani-green);
        font-size: 1.25rem;
        font-weight: 700;
        margin-bottom: 1.5rem;
    }

    .action-item {
        padding: 1.25rem;
        border-radius: 16px;
        background: #f8fafc;
        border: 2px solid transparent;
        transition: all 0.3s ease;
        text-decoration: none;
        display: block;
        margin-bottom: 1rem;
    }

    .action-item:hover {
        background: #f0fdf4;
        border-color: #bbf7d0;
        transform: translateX(8px);
    }

    .action-item-icon {
        width: 45px;
        height: 45px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        margin-right: 1rem;
    }

    .action-item-title {
        color: #0f172a;
        font-weight: 700;
        font-size: 1rem;
        margin-bottom: 0.25rem;
    }

    .action-item-desc {
        color: #64748b;
        font-size: 0.85rem;
        margin: 0;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .dashboard-title {
            font-size: 1.5rem;
        }

        .stat-value {
            font-size: 2rem;
        }

        .welcome-title {
            font-size: 1.5rem;
        }

        .banner-icon {
            font-size: 80px;
            opacity: 0.5;
        }
    }

    /* Animations */
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
        animation: fadeInUp 0.6s ease-out forwards;
    }

    .animate-in:nth-child(1) {
        animation-delay: 0.1s;
    }

    .animate-in:nth-child(2) {
        animation-delay: 0.2s;
    }

    .animate-in:nth-child(3) {
        animation-delay: 0.3s;
    }
</style>

<div class="dashboard-container">
    <div class="container-fluid">
        <!-- Header -->
        <div class="dashboard-header animate-in">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h1 class="dashboard-title">Dashboard Admin</h1>
                    <p class="dashboard-subtitle">Selamat datang kembali! Kelola sistem Pustani dengan mudah.</p>
                </div>
                <div class="date-badge">
                    <i class="bi bi-calendar3 me-2"></i><?= date('d M Y') ?>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row g-4 mb-4">
            <div class="col-md-4 animate-in">
                <div class="stat-card card-primary">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="flex-grow-1">
                            <div class="stat-label">Total Pengguna</div>
                            <div class="stat-value"><?= number_format($total_user); ?></div>
                            <div class="stat-change">
                                <i class="bi bi-arrow-up"></i>
                                <span>Pengguna aktif</span>
                            </div>
                        </div>
                        <div class="icon-box bg-primary bg-opacity-10 text-primary">
                            <i class="bi bi-people-fill"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4 animate-in">
                <div class="stat-card card-success">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="flex-grow-1">
                            <div class="stat-label">Artikel Terbit</div>
                            <div class="stat-value"><?= number_format($total_artikel); ?></div>
                            <div class="stat-change">
                                <i class="bi bi-arrow-up"></i>
                                <span>Konten tersedia</span>
                            </div>
                        </div>
                        <div class="icon-box bg-success bg-opacity-10 text-success">
                            <i class="bi bi-journal-check"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4 animate-in">
                <div class="stat-card card-warning">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="flex-grow-1">
                            <div class="stat-label">Ahli Terverifikasi</div>
                            <div class="stat-value"><?= number_format($total_expert); ?></div>
                            <div class="stat-change">
                                <i class="bi bi-patch-check-fill"></i>
                                <span>Expert verified</span>
                            </div>
                        </div>
                        <div class="icon-box bg-warning bg-opacity-10 text-warning">
                            <i class="bi bi-patch-check-fill"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Welcome Banner -->
        <div class="row g-4 mb-4">
            <div class="col-lg-8 animate-in">
                <div class="card welcome-banner">
                    <div class="welcome-content">
                        <h2 class="welcome-title">Siap Mengelola Pustani Hari Ini?</h2>
                        <p class="welcome-text">Pastikan setiap artikel yang masuk telah divalidasi dengan benar dan sistem berjalan dengan optimal.</p>
                        <div class="d-flex gap-3 flex-wrap">
                            <a href="validasi-artikel.php" class="btn-action btn-primary-action">
                                <i class="bi bi-check-circle me-2"></i>Periksa Artikel
                            </a>
                            <a href="tambah-admin.php" class="btn-action btn-outline-action">
                                <i class="bi bi-person-plus me-2"></i>Tambah Admin
                            </a>
                        </div>
                    </div>
                    <i class="bi bi-shield-check-fill banner-icon d-none d-md-block"></i>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="col-lg-4 animate-in">
                <div class="quick-actions">
                    <h3 class="quick-actions-title">Aksi Cepat</h3>

                    <a href="validasi-artikel.php" class="action-item">
                        <div class="d-flex align-items-center">
                            <div class="action-item-icon bg-success bg-opacity-10 text-success">
                                <i class="bi bi-file-earmark-check"></i>
                            </div>
                            <div>
                                <div class="action-item-title">Validasi Artikel</div>
                                <p class="action-item-desc">Tinjau artikel baru</p>
                            </div>
                        </div>
                    </a>

                    <a href="tambah-admin.php" class="action-item">
                        <div class="d-flex align-items-center">
                            <div class="action-item-icon bg-primary bg-opacity-10 text-primary">
                                <i class="bi bi-person-plus"></i>
                            </div>
                            <div>
                                <div class="action-item-title">Tambah Admin</div>
                                <p class="action-item-desc">Kelola administrator</p>
                            </div>
                        </div>
                    </a>

                    <a href="#" class="action-item">
                        <div class="d-flex align-items-center">
                            <div class="action-item-icon bg-warning bg-opacity-10 text-warning">
                                <i class="bi bi-gear"></i>
                            </div>
                            <div>
                                <div class="action-item-title">Pengaturan</div>
                                <p class="action-item-desc">Konfigurasi sistem</p>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php admin_footer(); ?>
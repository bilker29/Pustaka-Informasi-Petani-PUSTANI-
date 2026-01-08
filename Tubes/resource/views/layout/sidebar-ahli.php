<?php
// layout/sidebar-ahli.php
$current_page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
?>

<div class="sidebar" id="sidebar">
    <div class="logo-section">
        <div class="logo-container">
            <img src="../../public/img/img1/logo navbar.png" alt="Pustani Logo">
        </div>
        <div class="expert-badge">
            <span class="verified-icon"></span>
            <span style="position: relative; z-index: 1;">EXPERT PANEL</span>
        </div>
    </div>

    <div class="sidebar-nav">
        <div class="nav-section-title">Menu Utama</div>
        <nav class="nav flex-column">
            <a href="?page=dashboard" class="nav-link <?= $current_page == 'dashboard' ? 'active' : '' ?>">
                <i class="bi bi-grid-1x2-fill"></i>
                <span>Dashboard</span>
            </a>
            <a href="?page=tulis" class="nav-link <?= $current_page == 'tulis' ? 'active' : '' ?>">
                <i class="bi bi-pencil-square"></i>
                <span>Tulis Artikel</span>
            </a>
        </nav>

        <div class="nav-section-title">Pengaturan</div>
        <nav class="nav flex-column">
            <a href="?page=profil" class="nav-link <?= $current_page == 'profil' ? 'active' : '' ?>">
                <i class="bi bi-person-circle"></i>
                <span>Profil Saya</span>
            </a>
        </nav>

        <div class="nav-divider"></div>

        <nav class="nav flex-column">
            <a href="../../resource/views/Home.php" class="nav-link external-link">
                <i class="bi bi-arrow-left-circle"></i>
                <span>Ke Halaman Web</span>
            </a>
            <a href="../../resource/views/logout.php" class="nav-link" style="color: #dc2626;">
                <i class="bi bi-box-arrow-right"></i>
                <span>Logout</span>
            </a>
        </nav>
    </div>
</div>
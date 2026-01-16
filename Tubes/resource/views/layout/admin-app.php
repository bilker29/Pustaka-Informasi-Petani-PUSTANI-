<?php
// resource/layouts/admin-app.php

function admin_header($title = "Admin Dashboard")
{
    $base_path_img = "../../../public/img/img1/";
    $current_page = basename($_SERVER['PHP_SELF']);

    if (session_status() === PHP_SESSION_NONE) session_start();
    if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
        header("Location: ../login.php");
        exit;
    }
?>
    <!DOCTYPE html>
    <html lang="id">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= $title; ?> - Pustani Admin</title>
        <link rel="icon" href="<?= $base_path_img ?>logo.png" type="image/png">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
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

            /* Admin Badge */
            .admin-badge {
                background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
                color: white;
                font-size: 0.7rem;
                font-weight: 800;
                letter-spacing: 1.8px;
                padding: 0.65rem 1rem;
                border-radius: 12px;
                text-align: center;
                box-shadow: 0 4px 20px rgba(220, 38, 38, 0.25);
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 10px;
                position: relative;
                overflow: hidden;
            }

            .admin-badge::before {
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

            .pulse-icon {
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

            /* Main Content Area */
            .main-content {
                margin-left: var(--sidebar-width);
                min-height: 100vh;
                display: flex;
                flex-direction: column;
            }

            /* Top Navbar */
            .admin-navbar {
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

            .user-greeting {
                display: flex;
                align-items: center;
                gap: 1rem;
            }

            .user-avatar {
                width: 42px;
                height: 42px;
                border-radius: 50%;
                background: linear-gradient(135deg, var(--pustani-green) 0%, var(--pustani-green-light) 100%);
                display: flex;
                align-items: center;
                justify-content: center;
                color: white;
                font-weight: 700;
                font-size: 1rem;
                box-shadow: 0 4px 12px rgba(6, 78, 59, 0.2);
            }

            .user-info h5 {
                font-size: 1rem;
                font-weight: 700;
                color: #0f172a;
                margin: 0;
                line-height: 1.2;
            }

            .user-info p {
                font-size: 0.8rem;
                color: #64748b;
                margin: 0;
            }

            /* Logout Button */
            .btn-logout {
                background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
                color: #dc2626;
                border: 2px solid #fecaca;
                padding: 0.65rem 1.75rem;
                border-radius: 50px;
                font-weight: 700;
                font-size: 0.9rem;
                transition: all 0.3s ease;
                display: flex;
                align-items: center;
                gap: 0.5rem;
                text-decoration: none;
            }

            .btn-logout:hover {
                background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
                color: #b91c1c;
                border-color: #fca5a5;
                transform: translateY(-2px);
                box-shadow: 0 6px 20px rgba(220, 38, 38, 0.2);
            }

            /* Content Wrapper */
            .content-wrapper {
                padding: 2rem;
                flex-grow: 1;
                background: #f8fafb;
            }

            /* Footer */
            .admin-footer {
                background: white;
                border-top: 1px solid #e2e8f0;
                padding: 1.5rem 2rem;
                text-align: center;
                color: #64748b;
                font-size: 0.85rem;
            }

            .admin-footer strong {
                color: var(--pustani-green);
                font-weight: 700;
            }

            /* Mobile Styles */
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

                .admin-navbar {
                    padding: 1rem;
                }

                .user-info {
                    display: none;
                }

                .content-wrapper {
                    padding: 1rem;
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
            }

            /* Overlay for mobile */
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

            /* Notification Badge */
            .notification-badge {
                position: absolute;
                top: -4px;
                right: -4px;
                background: #dc2626;
                color: white;
                font-size: 0.65rem;
                font-weight: 700;
                padding: 2px 6px;
                border-radius: 10px;
                min-width: 18px;
                text-align: center;
            }
        </style>
    </head>

    <body>
        <!-- Sidebar Overlay for Mobile -->
        <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

        <!-- Sidebar -->
        <div class="sidebar" id="sidebar">
            <div class="logo-section">
                <div class="logo-container">
                    <img src="<?= $base_path_img ?>logo navbar.png" alt="Pustani Logo">
                </div>
                <div class="admin-badge">
                    <span class="pulse-icon"></span>
                    <span style="position: relative; z-index: 1;">ADMIN PANEL</span>
                </div>
            </div>

            <div class="sidebar-nav">
                <div class="nav-section-title">Menu Utama</div>
                <nav class="nav flex-column">
                    <a href="dashboard.php" class="nav-link <?= $current_page == 'dashboard.php' ? 'active' : '' ?>">
                        <i class="bi bi-grid-1x2-fill"></i>
                        <span>Dashboard</span>
                    </a>
                    <a href="validasi-artikel.php" class="nav-link <?= $current_page == 'validasi-artikel.php' ? 'active' : '' ?>">
                        <i class="bi bi-journal-check"></i>
                        <span>Validasi Artikel</span>
                    </a>
                    <a href="verifikasi-ahli.php" class="nav-link <?= $current_page == 'verifikasi-ahli.php' ? 'active' : '' ?>">
                        <i class="bi bi-person-badge"></i>
                        <span>Verifikasi Ahli</span>
                    </a>
                    <a href="kelola-user.php" class="nav-link <?= $current_page == 'kelola-user.php' ? 'active' : '' ?>">
                        <i class="bi bi-people"></i>
                        <span>Kelola User</span>
                    </a>
                </nav>

                <div class="nav-section-title">Pengaturan</div>
                <nav class="nav flex-column">
                    <a href="tambah-admin.php" class="nav-link <?= $current_page == 'tambah-admin.php' ? 'active' : '' ?>">
                        <i class="bi bi-person-plus-fill"></i>
                        <span>Tambah Admin</span>
                    </a>
                </nav>

                <div class="nav-divider"></div>

            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Top Navbar -->
            <div class="admin-navbar">
                <div class="d-flex align-items-center gap-3">
                    <button class="mobile-menu-toggle" onclick="toggleSidebar()">
                        <i class="bi bi-list"></i>
                    </button>
                    <div class="user-greeting">
                        <div class="user-avatar">
                            <i class="bi bi-person-fill"></i>
                        </div>
                        <div class="user-info">
                            <h5>Halo, <?= htmlspecialchars($_SESSION['username']); ?>!</h5>
                            <p>Administrator</p>
                        </div>
                    </div>
                </div>
                <a href="../logout.php" class="btn-logout">
                    <i class="bi bi-box-arrow-right"></i>
                    <span>Logout</span>
                </a>
            </div>

            <!-- Content Wrapper -->
            <div class="content-wrapper">
            <?php
        }

        function admin_footer()
        {
            ?>
            </div>

            <!-- Footer -->
            <footer class="admin-footer">
                &copy; <?= date('Y'); ?> <strong>Pustani</strong>. Seluruh Hak Cipta Dilindungi.
            </footer>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <script>
            function toggleSidebar() {
                const sidebar = document.getElementById('sidebar');
                const overlay = document.getElementById('sidebarOverlay');
                sidebar.classList.toggle('show');
                overlay.classList.toggle('show');
            }

            // Close sidebar when clicking outside on mobile
            document.addEventListener('click', function(event) {
                const sidebar = document.getElementById('sidebar');
                const toggle = document.querySelector('.mobile-menu-toggle');
                const isClickInside = sidebar.contains(event.target) || toggle?.contains(event.target);

                if (!isClickInside && window.innerWidth <= 768) {
                    sidebar.classList.remove('show');
                    document.getElementById('sidebarOverlay').classList.remove('show');
                }
            });

            // Handle window resize
            window.addEventListener('resize', function() {
                if (window.innerWidth > 768) {
                    document.getElementById('sidebar').classList.remove('show');
                    document.getElementById('sidebarOverlay').classList.remove('show');
                }
            });
        </script>
    </body>

    </html>
<?php
        }
?>
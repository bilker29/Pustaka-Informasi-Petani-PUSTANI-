<?php
session_start();
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

require '../../../config/koneksi.php';
require '../layout/admin-app.php';

// --- LOGIKA AKSI ---
if (isset($_GET['aksi']) && isset($_GET['id'])) {
    $id = mysqli_real_escape_string($koneksi, $_GET['id']);
    $aksi = $_GET['aksi'];

    // Cek apakah kolom is_banned ada
    $column_check = mysqli_query($koneksi, "SHOW COLUMNS FROM users LIKE 'is_banned'");
    $has_banned_column = mysqli_num_rows($column_check) > 0;

    if (!$has_banned_column) {
        // Tambah kolom is_banned jika belum ada
        mysqli_query($koneksi, "ALTER TABLE users ADD COLUMN is_banned TINYINT(1) NOT NULL DEFAULT 0 AFTER is_verified");
    }

    if ($aksi === 'banned') {
        $query = "UPDATE users SET is_banned = 1 WHERE id = '$id' AND role = 'user'";
    } elseif ($aksi === 'unbanned') {
        $query = "UPDATE users SET is_banned = 0 WHERE id = '$id' AND role = 'user'";
    } elseif ($aksi === 'hapus') {
        $query = "DELETE FROM users WHERE id = '$id' AND role = 'user'";
    }

    if (isset($query) && mysqli_query($koneksi, $query)) {
        header("Location: kelola-user.php?status=success");
        exit;
    }
}

// Get statistics
$column_check = mysqli_query($koneksi, "SHOW COLUMNS FROM users LIKE 'is_banned'");
$has_banned_column = mysqli_num_rows($column_check) > 0;

$total_users = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM users WHERE role='user'"))['total'];

if ($has_banned_column) {
    $active_users = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM users WHERE role='user' AND is_banned=0"))['total'];
    $banned_users = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM users WHERE role='user' AND is_banned=1"))['total'];
} else {
    $active_users = $total_users;
    $banned_users = 0;
}

admin_header("Kelola User");
?>

<style>
    /* Container */
    .user-management-container {
        max-width: 1400px;
        margin: 0 auto;
    }

    /* Header Section */
    .page-header {
        background: white;
        border-radius: 24px;
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    }

    .page-title {
        color: var(--pustani-green);
        font-size: 2rem;
        font-weight: 800;
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .page-subtitle {
        color: #64748b;
        font-size: 0.95rem;
        margin: 0;
    }

    /* Stats Cards */
    .stats-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .stat-mini-card {
        background: white;
        border-radius: 18px;
        padding: 1.5rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
        border: 2px solid transparent;
    }

    .stat-mini-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
        border-color: var(--card-border);
    }

    .stat-mini-card.total {
        --card-border: #3b82f6;
    }

    .stat-mini-card.active {
        --card-border: #16a34a;
    }

    .stat-mini-card.banned {
        --card-border: #ef4444;
    }

    .stat-mini-label {
        color: #64748b;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 0.5rem;
    }

    .stat-mini-value {
        color: #0f172a;
        font-size: 1.75rem;
        font-weight: 800;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .stat-mini-icon {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
    }

    /* Search & Filter */
    .search-filter-container {
        background: white;
        border-radius: 20px;
        padding: 1.5rem;
        margin-bottom: 2rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
        align-items: center;
    }

    .search-wrapper {
        position: relative;
        flex: 1;
        min-width: 300px;
    }

    .search-icon {
        position: absolute;
        left: 1.25rem;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        font-size: 1.1rem;
        z-index: 2;
    }

    .search-input {
        width: 100%;
        border-radius: 16px;
        padding: 1rem 1rem 1rem 3.25rem;
        border: 2px solid #e2e8f0;
        font-size: 0.95rem;
        transition: all 0.3s ease;
        background: #f8fafc;
    }

    .search-input:focus {
        border-color: var(--pustani-green);
        box-shadow: 0 0 0 4px rgba(6, 78, 59, 0.1);
        outline: none;
        background: white;
    }

    .filter-select {
        padding: 0.85rem 2.5rem 0.85rem 1.25rem;
        border-radius: 14px;
        border: 2px solid #e2e8f0;
        background: #f8fafc;
        color: #475569;
        font-weight: 600;
        font-size: 0.9rem;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .filter-select:focus {
        border-color: var(--pustani-green);
        box-shadow: 0 0 0 4px rgba(6, 78, 59, 0.1);
        outline: none;
        background: white;
    }

    /* Table Card */
    .table-card {
        background: white;
        border-radius: 24px;
        padding: 2rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    }

    /* Table Styles */
    .users-table {
        border-collapse: separate;
        border-spacing: 0;
    }

    .users-table thead {
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    }

    .users-table thead th {
        color: #475569;
        font-size: 0.8rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 1rem 1.25rem;
        border: none;
        white-space: nowrap;
    }

    .users-table thead th:first-child {
        border-radius: 12px 0 0 12px;
    }

    .users-table thead th:last-child {
        border-radius: 0 12px 12px 0;
    }

    .users-table tbody tr {
        transition: all 0.2s ease;
        border-bottom: 1px solid #f1f5f9;
    }

    .users-table tbody tr:hover {
        background: #f8fafc;
        transform: scale(1.005);
    }

    .users-table tbody tr:last-child {
        border-bottom: none;
    }

    .users-table tbody td {
        padding: 1.25rem 1.25rem;
        vertical-align: middle;
    }

    /* User Info */
    .user-info {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .user-avatar {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 800;
        font-size: 1.15rem;
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.2);
        flex-shrink: 0;
    }

    .user-details {
        display: flex;
        flex-direction: column;
        gap: 0.35rem;
    }

    .user-name {
        color: #0f172a;
        font-weight: 700;
        font-size: 0.95rem;
    }

    .user-email {
        color: #64748b;
        font-size: 0.85rem;
        display: flex;
        align-items: center;
        gap: 0.4rem;
    }

    /* Status Badge */
    .status-badge {
        font-size: 0.75rem;
        font-weight: 700;
        padding: 0.55rem 1.1rem;
        border-radius: 50px;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        white-space: nowrap;
    }

    .status-badge.active {
        background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
        color: #065f46;
        border: 1px solid #6ee7b7;
    }

    .status-badge.banned {
        background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
        color: #991b1b;
        border: 1px solid #fca5a5;
    }

    /* Action Buttons */
    .action-buttons {
        display: flex;
        gap: 0.5rem;
        justify-content: center;
        flex-wrap: wrap;
    }

    .btn-action {
        width: 38px;
        height: 38px;
        border-radius: 11px;
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
        font-size: 1rem;
        cursor: pointer;
    }

    .btn-action:hover {
        transform: scale(1.1);
    }

    .btn-action-view {
        background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
        color: #1e40af;
    }

    .btn-action-banned {
        background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
        color: #991b1b;
    }

    .btn-action-unbanned {
        background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
        color: #065f46;
    }

    .btn-action-delete {
        background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
        color: #7f1d1d;
    }

    /* Modal Styles */
    .modal-content {
        border: none;
        border-radius: 24px;
        box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
    }

    .modal-header {
        border: none;
        padding: 2rem 2rem 1rem 2rem;
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        border-radius: 24px 24px 0 0;
    }

    .modal-title {
        color: #0f172a;
        font-size: 1.35rem;
        font-weight: 800;
    }

    .modal-body {
        padding: 2rem;
    }

    .modal-footer {
        border: none;
        padding: 1rem 2rem 2rem 2rem;
        background: white;
    }

    .info-row {
        display: flex;
        gap: 1rem;
        margin-bottom: 1.5rem;
        padding-bottom: 1.5rem;
        border-bottom: 1px solid #f1f5f9;
    }

    .info-row:last-child {
        border-bottom: none;
        margin-bottom: 0;
        padding-bottom: 0;
    }

    .info-label {
        color: #64748b;
        font-size: 0.8rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 0.5rem;
    }

    .info-value {
        color: #0f172a;
        font-weight: 600;
        font-size: 0.95rem;
    }

    /* Empty & Loading States */
    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
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
    }

    .loading-state {
        text-align: center;
        padding: 3rem;
    }

    .loading-spinner {
        width: 50px;
        height: 50px;
        border: 4px solid #e2e8f0;
        border-top-color: var(--pustani-green);
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin: 0 auto 1rem;
    }

    @keyframes spin {
        to {
            transform: rotate(360deg);
        }
    }

    .btn-cancel {
        background: #f1f5f9;
        color: #475569;
        border: none;
        border-radius: 14px;
        padding: 0.75rem 1.75rem;
        font-weight: 700;
        transition: all 0.3s ease;
    }

    .btn-cancel:hover {
        background: #e2e8f0;
        color: #334155;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .page-title {
            font-size: 1.5rem;
        }

        .stats-row {
            grid-template-columns: 1fr;
        }

        .search-wrapper {
            min-width: 100%;
        }

        .user-info {
            flex-direction: column;
            align-items: flex-start;
        }

        .action-buttons {
            flex-direction: column;
        }

        .btn-action {
            width: 100%;
        }
    }

    /* Animation */
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .animate-in {
        animation: fadeIn 0.4s ease-out;
    }
</style>

<div class="user-management-container">
    <!-- Page Header -->
    <div class="page-header animate-in">
        <div>
            <h1 class="page-title">
                <i class="bi bi-people-fill"></i>
                Kelola User
            </h1>
            <p class="page-subtitle">Kelola semua akun pengguna, lihat detail, dan atur status akun</p>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-row animate-in">
        <div class="stat-mini-card total">
            <div class="stat-mini-label">Total User</div>
            <div class="stat-mini-value">
                <div class="stat-mini-icon bg-primary bg-opacity-10 text-primary">
                    <i class="bi bi-people"></i>
                </div>
                <span><?= number_format($total_users) ?></span>
            </div>
        </div>

        <div class="stat-mini-card active">
            <div class="stat-mini-label">User Aktif</div>
            <div class="stat-mini-value">
                <div class="stat-mini-icon bg-success bg-opacity-10 text-success">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
                <span><?= number_format($active_users) ?></span>
            </div>
        </div>

        <div class="stat-mini-card banned">
            <div class="stat-mini-label">User Banned</div>
            <div class="stat-mini-value">
                <div class="stat-mini-icon bg-danger bg-opacity-10 text-danger">
                    <i class="bi bi-slash-circle"></i>
                </div>
                <span><?= number_format($banned_users) ?></span>
            </div>
        </div>
    </div>

    <!-- Search & Filter -->
    <div class="search-filter-container animate-in">
        <div class="search-wrapper">
            <i class="bi bi-search search-icon"></i>
            <input type="text"
                id="keyword"
                class="search-input"
                placeholder="Cari berdasarkan nama atau email user..."
                autocomplete="off">
        </div>
        <select id="statusFilter" class="filter-select">
            <option value="all">Semua Status</option>
            <option value="active">Aktif</option>
            <option value="banned">Banned</option>
        </select>
    </div>

    <!-- Users Table -->
    <div class="table-card animate-in">
        <div class="table-responsive">
            <table class="table users-table">
                <thead>
                    <tr>
                        <th>Informasi User</th>
                        <th>No. HP</th>
                        <th>Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody id="container-users">
                    <?php include 'search-user.php'; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    const keyword = document.getElementById('keyword');
    const statusFilter = document.getElementById('statusFilter');
    const container = document.getElementById('container-users');
    let searchTimeout;

    function loadUsers() {
        clearTimeout(searchTimeout);

        container.innerHTML = `
            <tr>
                <td colspan="4">
                    <div class="loading-state">
                        <div class="loading-spinner"></div>
                        <p class="text-muted mb-0">Memuat data user...</p>
                    </div>
                </td>
            </tr>
        `;

        searchTimeout = setTimeout(() => {
            const params = new URLSearchParams({
                keyword: keyword.value,
                status: statusFilter.value
            });

            fetch('search-user.php?' + params)
                .then(response => response.text())
                .then(data => {
                    container.innerHTML = data;
                })
                .catch(error => {
                    console.error('Error:', error);
                    container.innerHTML = `
                        <tr>
                            <td colspan="4">
                                <div class="empty-state">
                                    <div class="empty-state-icon">
                                        <i class="bi bi-exclamation-circle"></i>
                                    </div>
                                    <h3 class="empty-state-title">Terjadi Kesalahan</h3>
                                    <p class="empty-state-text">Gagal memuat data user. Silakan refresh halaman.</p>
                                </div>
                            </td>
                        </tr>
                    `;
                });
        }, 300);
    }

    keyword.addEventListener('keyup', loadUsers);
    statusFilter.addEventListener('change', loadUsers);

    function bannedUser(id) {
        Swal.fire({
            title: 'Banned User?',
            html: '<p class="text-muted mb-0">User tidak akan bisa login ke sistem!</p>',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#64748b',
            confirmButtonText: '<i class="bi bi-slash-circle me-2"></i>Ya, Banned!',
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
                window.location = 'kelola-user.php?aksi=banned&id=' + id;
            }
        });
    }

    function hapusUser(id) {
        Swal.fire({
            title: 'Hapus User?',
            html: '<p class="text-muted mb-0">Data user akan dihapus permanen!</p>',
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
                window.location = 'kelola-user.php?aksi=hapus&id=' + id;
            }
        });
    }

    <?php if (isset($_GET['status']) && $_GET['status'] == 'success') : ?>
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: 'Aksi berhasil dilakukan.',
            timer: 2000,
            showConfirmButton: false,
            customClass: {
                popup: 'rounded-4'
            }
        });
    <?php endif; ?>

    window.addEventListener('load', () => keyword.focus());
</script>

<?php admin_footer(); ?>
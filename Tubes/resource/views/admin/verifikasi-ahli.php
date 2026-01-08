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

    if ($aksi === 'verifikasi') {
        // Setujui: Ubah role jadi expert, status_ahli jadi none (selesai), is_verified aktif
        $query = "UPDATE users SET role = 'expert', is_verified = 1, status_ahli = 'none' WHERE id = '$id'";
    } elseif ($aksi === 'tolak') {
        // Tolak: Tetap user, status_ahli jadi rejected
        $query = "UPDATE users SET status_ahli = 'rejected', is_verified = 0 WHERE id = '$id'";
    } elseif ($aksi === 'batal') {
        // Batalkan: Kembalikan ke role user biasa
        $query = "UPDATE users SET is_verified = 0, role = 'user', status_ahli = 'none' WHERE id = '$id'";
    } elseif ($aksi === 'hapus') {
        $query = "DELETE FROM users WHERE id = '$id'";
    }

    if (isset($query) && mysqli_query($koneksi, $query)) {
        header("Location: verifikasi-ahli.php?status=success");
        exit;
    }
}

// Get statistics
$column_check = mysqli_query($koneksi, "SHOW COLUMNS FROM users LIKE 'is_verified'");
$has_verified_column = mysqli_num_rows($column_check) > 0;

// Total Ahli adalah Expert yang sudah ada + User yang sedang Pending
$total_experts = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM users WHERE role='expert' OR status_ahli='pending'"))['total'];

if ($has_verified_column) {
    $verified_experts = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM users WHERE role='expert' AND is_verified=1"))['total'];
    // Unverified di sini kita hitung sebagai yang status_ahli-nya pending
    $unverified_experts = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM users WHERE status_ahli='pending'"))['total'];
} else {
    $verified_experts = 0;
    $unverified_experts = $total_experts;
}

admin_header("Verifikasi Ahli");
?>

<style>
    /* Container */
    .verification-container {
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

    .stat-mini-card.verified {
        --card-border: #16a34a;
    }

    .stat-mini-card.unverified {
        --card-border: #f59e0b;
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

    /* Search Bar */
    .search-container {
        background: white;
        border-radius: 20px;
        padding: 1.5rem;
        margin-bottom: 2rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    }

    .search-wrapper {
        position: relative;
        max-width: 500px;
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

    .search-input::placeholder {
        color: #94a3b8;
    }

    /* Table Card */
    .table-card {
        background: white;
        border-radius: 24px;
        padding: 2rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    }

    /* Table Styles */
    .experts-table {
        border-collapse: separate;
        border-spacing: 0;
    }

    .experts-table thead {
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    }

    .experts-table thead th {
        color: #475569;
        font-size: 0.8rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 1rem 1.25rem;
        border: none;
        white-space: nowrap;
    }

    .experts-table thead th:first-child {
        border-radius: 12px 0 0 12px;
    }

    .experts-table thead th:last-child {
        border-radius: 0 12px 12px 0;
    }

    .experts-table tbody tr {
        transition: all 0.2s ease;
        border-bottom: 1px solid #f1f5f9;
    }

    .experts-table tbody tr:hover {
        background: #f8fafc;
        transform: scale(1.005);
    }

    .experts-table tbody tr:last-child {
        border-bottom: none;
    }

    .experts-table tbody td {
        padding: 1.25rem 1.25rem;
        vertical-align: middle;
    }

    /* Expert Info */
    .expert-info {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .expert-avatar {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--pustani-green) 0%, #16a34a 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 800;
        font-size: 1.15rem;
        box-shadow: 0 4px 12px rgba(6, 78, 59, 0.2);
        flex-shrink: 0;
    }

    .expert-details {
        display: flex;
        flex-direction: column;
        gap: 0.35rem;
    }

    .expert-name {
        color: #0f172a;
        font-weight: 700;
        font-size: 0.95rem;
    }

    .expert-email {
        color: #64748b;
        font-size: 0.85rem;
        display: flex;
        align-items: center;
        gap: 0.4rem;
    }

    .expert-email i {
        font-size: 0.9rem;
    }

    /* Expertise Badge */
    .expertise-badge {
        background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
        color: #1e40af;
        font-size: 0.8rem;
        font-weight: 700;
        padding: 0.5rem 1rem;
        border-radius: 50px;
        border: 1px solid #bfdbfe;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
    }

    .expertise-badge i {
        font-size: 0.85rem;
    }

    /* Verification Status Badge */
    .verification-badge {
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

    .verification-badge.verified {
        background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
        color: #065f46;
        border: 1px solid #6ee7b7;
    }

    .verification-badge.unverified {
        background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
        color: #991b1b;
        border: 1px solid #fca5a5;
    }

    .verification-badge i {
        font-size: 0.9rem;
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

    .btn-action-view:hover {
        background: linear-gradient(135deg, #bfdbfe 0%, #93c5fd 100%);
    }

    .btn-action-verify {
        background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
        color: #065f46;
    }

    .btn-action-verify:hover {
        background: linear-gradient(135deg, #a7f3d0 0%, #6ee7b7 100%);
    }

    .btn-action-unverify {
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        color: #92400e;
    }

    .btn-action-unverify:hover {
        background: linear-gradient(135deg, #fde68a 0%, #fcd34d 100%);
    }

    .btn-action-delete {
        background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
        color: #991b1b;
    }

    .btn-action-delete:hover {
        background: linear-gradient(135deg, #fecaca 0%, #fca5a5 100%);
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

    /* Empty State */
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

    /* Loading State */
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

    /* Responsive */
    @media (max-width: 768px) {
        .page-title {
            font-size: 1.5rem;
        }

        .stats-row {
            grid-template-columns: 1fr;
        }

        .search-wrapper {
            max-width: 100%;
        }

        .expert-info {
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
</style>

<div class="verification-container">
    <div class="page-header animate-in">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div>
                <h1 class="page-title">
                    <i class="bi bi-patch-check-fill"></i>
                    Verifikasi Ahli Tani
                </h1>
                <p class="page-subtitle">Validasi akun pakar untuk memberikan kredibilitas pada artikel dan kontribusi mereka</p>
            </div>
        </div>
    </div>

    <div class="stats-row animate-in">
        <div class="stat-mini-card total">
            <div class="stat-mini-label">Total Ahli</div>
            <div class="stat-mini-value">
                <div class="stat-mini-icon bg-primary bg-opacity-10 text-primary">
                    <i class="bi bi-people-fill"></i>
                </div>
                <span><?= number_format($total_experts) ?></span>
            </div>
        </div>

        <div class="stat-mini-card verified">
            <div class="stat-mini-label">Terverifikasi</div>
            <div class="stat-mini-value">
                <div class="stat-mini-icon bg-success bg-opacity-10 text-success">
                    <i class="bi bi-shield-check"></i>
                </div>
                <span><?= number_format($verified_experts) ?></span>
            </div>
        </div>

        <div class="stat-mini-card unverified">
            <div class="stat-mini-label">Menunggu Verifikasi</div>
            <div class="stat-mini-value">
                <div class="stat-mini-icon bg-warning bg-opacity-10 text-warning">
                    <i class="bi bi-shield-exclamation"></i>
                </div>
                <span><?= number_format($unverified_experts) ?></span>
            </div>
        </div>
    </div>

    <div class="search-container animate-in">
        <div class="search-wrapper">
            <i class="bi bi-search search-icon"></i>
            <input type="text"
                id="keyword"
                class="search-input"
                placeholder="Cari berdasarkan nama pakar atau email..."
                autocomplete="off">
        </div>
    </div>

    <div class="table-card animate-in">
        <div class="table-responsive">
            <table class="table experts-table">
                <thead>
                    <tr>
                        <th>Informasi Pakar</th>
                        <th>Bidang Keahlian</th>
                        <th>Status Verifikasi</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody id="container-ahli">
                    <?php include 'search-ahli.php'; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    const keyword = document.getElementById('keyword');
    const container = document.getElementById('container-ahli');
    let searchTimeout;

    // Live Search dengan debounce
    keyword.addEventListener('keyup', function() {
        clearTimeout(searchTimeout);

        // Show loading state
        container.innerHTML = `
            <tr>
                <td colspan="4">
                    <div class="loading-state">
                        <div class="loading-spinner"></div>
                        <p class="text-muted mb-0">Mencari data pakar...</p>
                    </div>
                </td>
            </tr>
        `;

        searchTimeout = setTimeout(() => {
            fetch('search-ahli.php?keyword=' + encodeURIComponent(keyword.value))
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
                                    <p class="empty-state-text">Gagal memuat data pakar. Silakan refresh halaman.</p>
                                </div>
                            </td>
                        </tr>
                    `;
                });
        }, 300);
    });

    // Function untuk hapus ahli
    function hapusAhli(id) {
        Swal.fire({
            title: 'Hapus Akun Pakar?',
            html: '<p class="text-muted mb-0">Data pakar akan dihapus secara permanen dan tidak dapat dikembalikan!</p>',
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
                window.location = 'verifikasi-ahli.php?aksi=hapus&id=' + id;
            }
        });
    }

    // Success notification
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

    // Auto focus pada search
    window.addEventListener('load', function() {
        keyword.focus();
    });
</script>

<?php admin_footer(); ?>
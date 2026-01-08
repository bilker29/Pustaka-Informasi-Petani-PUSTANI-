<?php
session_start();
// 1. PROTEKSI HALAMAN (Cek Login & Role)
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

require '../../../config/koneksi.php';
require '../layout/admin-app.php';

$status_admin = "";

// 2. LOGIKA: TAMBAH ADMIN BARU
if (isset($_POST['submit_admin'])) {
    $email = mysqli_real_escape_string($koneksi, $_POST['email']);
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = $_POST['password'];
    $password_hashed = password_hash($password, PASSWORD_DEFAULT);

    $cek_email = mysqli_query($koneksi, "SELECT email FROM users WHERE email = '$email'");
    if (mysqli_num_rows($cek_email) > 0) {
        $status_admin = "email_exists";
    } else {
        $query = "INSERT INTO users (email, username, password, role) VALUES ('$email', '$username', '$password_hashed', 'admin')";
        if (mysqli_query($koneksi, $query)) {
            $status_admin = "success";
        }
    }
}

// 3. LOGIKA: UPDATE/EDIT ADMIN
if (isset($_POST['update_admin'])) {
    $id = $_POST['id'];
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $email = mysqli_real_escape_string($koneksi, $_POST['email']);
    $password = $_POST['password'];

    if (!empty($password)) {
        // Jika password diisi, ganti password baru
        $password_hashed = password_hash($password, PASSWORD_DEFAULT);
        $query = "UPDATE users SET username='$username', email='$email', password='$password_hashed' WHERE id='$id'";
    } else {
        // Jika password kosong, update username & email saja
        $query = "UPDATE users SET username='$username', email='$email' WHERE id='$id'";
    }

    if (mysqli_query($koneksi, $query)) {
        echo "<script>window.location='tambah-admin.php?status=updated';</script>";
        exit;
    }
}

// 4. LOGIKA: HAPUS ADMIN
if (isset($_GET['hapus'])) {
    $id_hapus = $_GET['hapus'];
    // Mencegah menghapus diri sendiri
    if ($id_hapus != $_SESSION['user_id']) {
        mysqli_query($koneksi, "DELETE FROM users WHERE id = '$id_hapus' AND role = 'admin'");
        echo "<script>window.location='tambah-admin.php?status=deleted';</script>";
        exit;
    }
}

// Ambil data terbaru untuk tabel
$list_admin = mysqli_query($koneksi, "SELECT * FROM users WHERE role = 'admin' ORDER BY id DESC");
$total_admin = mysqli_num_rows($list_admin);

// Panggil Header dari Layout
admin_header("Kelola Admin");
?>

<style>
    /* Page Container */
    .admin-management-container {
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

    .admin-stats {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1.25rem;
        background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
        border: 1px solid #bbf7d0;
        border-radius: 50px;
        color: var(--pustani-green);
        font-weight: 700;
        font-size: 0.9rem;
    }

    /* Form Card */
    .form-card {
        background: white;
        border: none;
        border-radius: 24px;
        padding: 2rem;
        height: 100%;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
    }

    .form-card:hover {
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
    }

    .form-card-title {
        color: #0f172a;
        font-size: 1.35rem;
        font-weight: 800;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .form-card-title i {
        color: var(--pustani-green);
        font-size: 1.5rem;
    }

    .form-divider {
        height: 2px;
        background: linear-gradient(90deg, var(--pustani-green), transparent);
        margin-bottom: 1.5rem;
        border-radius: 2px;
    }

    /* Form Styles */
    .form-label {
        color: #334155;
        font-size: 0.85rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .form-control {
        border-radius: 14px;
        padding: 0.85rem 1.25rem;
        border: 2px solid #e2e8f0;
        font-size: 0.95rem;
        transition: all 0.3s ease;
    }

    .form-control:focus {
        border-color: var(--pustani-green);
        box-shadow: 0 0 0 4px rgba(6, 78, 59, 0.1);
        outline: none;
    }

    .form-control::placeholder {
        color: #94a3b8;
    }

    .input-icon {
        position: relative;
    }

    .input-icon i {
        position: absolute;
        left: 1.25rem;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        font-size: 1rem;
    }

    .input-icon .form-control {
        padding-left: 3rem;
    }

    /* Buttons */
    .btn-pustani {
        background: linear-gradient(135deg, var(--pustani-green) 0%, #047857 100%);
        color: white;
        border-radius: 14px;
        font-weight: 700;
        border: none;
        padding: 0.95rem 2rem;
        font-size: 0.95rem;
        transition: all 0.3s ease;
        box-shadow: 0 4px 14px rgba(6, 78, 59, 0.25);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }

    .btn-pustani:hover {
        background: linear-gradient(135deg, #047857 0%, var(--pustani-green-light) 100%);
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(6, 78, 59, 0.3);
        color: white;
    }

    .btn-pustani i {
        font-size: 1.1rem;
    }

    /* Table Card */
    .table-card {
        background: white;
        border: none;
        border-radius: 24px;
        padding: 2rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    }

    .table-card-title {
        color: #0f172a;
        font-size: 1.35rem;
        font-weight: 800;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .table-card-title-text {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .table-card-title i {
        color: var(--pustani-green);
        font-size: 1.5rem;
    }

    /* Table Styles */
    .admin-table {
        border-collapse: separate;
        border-spacing: 0;
    }

    .admin-table thead {
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    }

    .admin-table thead th {
        color: #475569;
        font-size: 0.8rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 1rem 1.25rem;
        border: none;
    }

    .admin-table thead th:first-child {
        border-radius: 12px 0 0 12px;
    }

    .admin-table thead th:last-child {
        border-radius: 0 12px 12px 0;
    }

    .admin-table tbody tr {
        transition: all 0.2s ease;
    }

    .admin-table tbody tr:hover {
        background: #f8fafc;
        transform: scale(1.01);
    }

    .admin-table tbody td {
        padding: 1.25rem 1.25rem;
        border-bottom: 1px solid #f1f5f9;
        font-size: 0.95rem;
        vertical-align: middle;
    }

    .admin-table tbody tr:last-child td {
        border-bottom: none;
    }

    .admin-username {
        color: #0f172a;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .admin-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--pustani-green) 0%, var(--pustani-green-light) 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 700;
        font-size: 0.9rem;
        box-shadow: 0 2px 8px rgba(6, 78, 59, 0.2);
    }

    .admin-email {
        color: #64748b;
        font-size: 0.9rem;
    }

    /* Action Buttons */
    .action-buttons {
        display: flex;
        gap: 0.5rem;
        justify-content: center;
    }

    .btn-action {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
        font-size: 1rem;
    }

    .btn-action-edit {
        background: #eff6ff;
        color: #3b82f6;
    }

    .btn-action-edit:hover {
        background: #dbeafe;
        transform: scale(1.1);
    }

    .btn-action-delete {
        background: #fef2f2;
        color: #dc2626;
    }

    .btn-action-delete:hover {
        background: #fee2e2;
        transform: scale(1.1);
    }

    .current-user-badge {
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        color: #92400e;
        font-size: 0.75rem;
        font-weight: 700;
        padding: 0.4rem 1rem;
        border-radius: 50px;
        border: 1px solid #fcd34d;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
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

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 3rem 2rem;
    }

    .empty-state-icon {
        font-size: 4rem;
        color: #cbd5e1;
        margin-bottom: 1rem;
    }

    .empty-state-text {
        color: #64748b;
        font-size: 1rem;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .page-title {
            font-size: 1.5rem;
        }

        .admin-stats {
            width: 100%;
            justify-content: center;
            margin-top: 1rem;
        }

        .form-card,
        .table-card {
            padding: 1.5rem;
        }

        .admin-username {
            flex-direction: column;
            align-items: flex-start;
        }

        .action-buttons {
            flex-direction: column;
        }
    }

    /* Animation */
    @keyframes slideIn {
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
        animation: slideIn 0.4s ease-out;
    }
</style>

<div class="admin-management-container">
    <!-- Page Header -->
    <div class="page-header animate-in">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div>
                <h1 class="page-title">
                    <i class="bi bi-shield-lock-fill"></i>
                    Manajemen Admin
                </h1>
                <p class="page-subtitle">Kelola akun yang memiliki akses ke Panel Administrator</p>
            </div>
            <div class="admin-stats">
                <i class="bi bi-people-fill"></i>
                <span><?= $total_admin ?> Admin Aktif</span>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Form Add Admin -->
        <div class="col-lg-4 animate-in">
            <div class="form-card">
                <h2 class="form-card-title">
                    <i class="bi bi-person-plus-fill"></i>
                    Tambah Admin Baru
                </h2>
                <div class="form-divider"></div>

                <form action="" method="POST">
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <div class="input-icon">
                            <i class="bi bi-person"></i>
                            <input type="text" name="username" class="form-control" placeholder="Masukkan username" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email Address</label>
                        <div class="input-icon">
                            <i class="bi bi-envelope"></i>
                            <input type="email" name="email" class="form-control" placeholder="admin@pustani.com" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Password</label>
                        <div class="input-icon">
                            <i class="bi bi-lock"></i>
                            <input type="password" name="password" class="form-control" placeholder="Min. 8 karakter" required>
                        </div>
                        <small class="text-muted">Password akan dienkripsi secara otomatis</small>
                    </div>

                    <button type="submit" name="submit_admin" class="btn btn-pustani w-100">
                        <i class="bi bi-check-circle-fill"></i>
                        Simpan Akun Admin
                    </button>
                </form>
            </div>
        </div>

        <!-- Admin List Table -->
        <div class="col-lg-8 animate-in">
            <div class="table-card">
                <div class="table-card-title">
                    <div class="table-card-title-text">
                        <i class="bi bi-people-fill"></i>
                        Daftar Administrator
                    </div>
                </div>

                <div class="table-responsive">
                    <?php if ($total_admin > 0) : ?>
                        <table class="table admin-table">
                            <thead>
                                <tr>
                                    <th>Administrator</th>
                                    <th>Email</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                mysqli_data_seek($list_admin, 0); // Reset pointer
                                while ($row = mysqli_fetch_assoc($list_admin)) :
                                    $initial = strtoupper(substr($row['username'], 0, 1));
                                ?>
                                    <tr>
                                        <td>
                                            <div class="admin-username">
                                                <div class="admin-avatar"><?= $initial ?></div>
                                                <span><?= htmlspecialchars($row['username']) ?></span>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="admin-email"><?= htmlspecialchars($row['email']) ?></span>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="btn btn-action btn-action-edit"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editModal<?= $row['id'] ?>"
                                                    title="Edit Admin">
                                                    <i class="bi bi-pencil-square"></i>
                                                </button>

                                                <?php if ($row['id'] != $_SESSION['user_id']) : ?>
                                                    <button onclick="konfirmasiHapus(<?= $row['id'] ?>)"
                                                        class="btn btn-action btn-action-delete"
                                                        title="Hapus Admin">
                                                        <i class="bi bi-trash3-fill"></i>
                                                    </button>
                                                <?php else : ?>
                                                    <span class="current-user-badge">
                                                        <i class="bi bi-star-fill"></i>
                                                        Anda
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- Edit Modal -->
                                    <div class="modal fade" id="editModal<?= $row['id'] ?>" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">
                                                        <i class="bi bi-pencil-square me-2"></i>
                                                        Edit Admin: <?= htmlspecialchars($row['username']) ?>
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form action="" method="POST">
                                                    <div class="modal-body">
                                                        <input type="hidden" name="id" value="<?= $row['id'] ?>">

                                                        <div class="mb-3">
                                                            <label class="form-label">Username</label>
                                                            <div class="input-icon">
                                                                <i class="bi bi-person"></i>
                                                                <input type="text" name="username" class="form-control"
                                                                    value="<?= htmlspecialchars($row['username']) ?>" required>
                                                            </div>
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="form-label">Email</label>
                                                            <div class="input-icon">
                                                                <i class="bi bi-envelope"></i>
                                                                <input type="email" name="email" class="form-control"
                                                                    value="<?= htmlspecialchars($row['email']) ?>" required>
                                                            </div>
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="form-label">Password Baru</label>
                                                            <div class="input-icon">
                                                                <i class="bi bi-lock"></i>
                                                                <input type="password" name="password" class="form-control"
                                                                    placeholder="Kosongkan jika tidak ingin mengubah">
                                                            </div>
                                                            <small class="text-muted">Biarkan kosong untuk mempertahankan password lama</small>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-cancel" data-bs-dismiss="modal">
                                                            Batal
                                                        </button>
                                                        <button type="submit" name="update_admin" class="btn btn-pustani">
                                                            <i class="bi bi-check-circle-fill"></i>
                                                            Simpan Perubahan
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    <?php else : ?>
                        <div class="empty-state">
                            <div class="empty-state-icon">
                                <i class="bi bi-people"></i>
                            </div>
                            <p class="empty-state-text">Belum ada administrator yang terdaftar</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Konfirmasi Hapus Akun
    function konfirmasiHapus(id) {
        Swal.fire({
            title: 'Hapus Akun Admin?',
            text: "Data yang dihapus tidak dapat dikembalikan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#64748b',
            confirmButtonText: '<i class="bi bi-trash3-fill me-2"></i>Ya, Hapus!',
            cancelButtonText: 'Batal',
            customClass: {
                popup: 'rounded-4',
                confirmButton: 'rounded-pill px-4',
                cancelButton: 'rounded-pill px-4'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                window.location = 'tambah-admin.php?hapus=' + id;
            }
        });
    }

    // Notifikasi SweetAlert berdasarkan Status
    <?php if ($status_admin == "success") : ?>
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: 'Admin baru telah ditambahkan ke sistem.',
            confirmButtonColor: '#064e3b',
            customClass: {
                popup: 'rounded-4',
                confirmButton: 'rounded-pill px-4'
            }
        });
    <?php elseif ($status_admin == "email_exists") : ?>
        Swal.fire({
            icon: 'error',
            title: 'Gagal!',
            text: 'Email sudah terdaftar di sistem. Gunakan email lain.',
            confirmButtonColor: '#dc2626',
            customClass: {
                popup: 'rounded-4',
                confirmButton: 'rounded-pill px-4'
            }
        });
    <?php elseif (isset($_GET['status']) && $_GET['status'] == 'updated') : ?>
        Swal.fire({
            icon: 'success',
            title: 'Diperbarui!',
            text: 'Data administrator berhasil diubah.',
            confirmButtonColor: '#064e3b',
            customClass: {
                popup: 'rounded-4',
                confirmButton: 'rounded-pill px-4'
            }
        });
    <?php elseif (isset($_GET['status']) && $_GET['status'] == 'deleted') : ?>
        Swal.fire({
            icon: 'success',
            title: 'Terhapus!',
            text: 'Akun administrator telah dihapus dari sistem.',
            confirmButtonColor: '#064e3b',
            customClass: {
                popup: 'rounded-4',
                confirmButton: 'rounded-pill px-4'
            }
        });
    <?php endif; ?>
</script>

<?php
// Panggil Footer
admin_footer();
?>
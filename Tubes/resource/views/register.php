<?php
session_start();
// Pastikan path ini benar. Jika file ini di dalam folder 'auth' atau 'views', ../../ sudah benar.
// Jika file ini di root, gunakan ./config/koneksi.php
$path_koneksi = '../../config/koneksi.php';

if (!file_exists($path_koneksi)) {
    die("Error: File koneksi.php tidak ditemukan di path: $path_koneksi. Silakan periksa struktur folder Anda.");
}

require_once $path_koneksi;

// Matikan laporan ralat otomatis agar kita bisa menanganinya secara manual dengan SweetAlert
mysqli_report(MYSQLI_REPORT_OFF);

$register_status = null;
$error_msg = '';

if (!isset($koneksi) || !($koneksi instanceof mysqli)) {
    $register_status = 'error';
    $error_msg = 'Variabel koneksi database ($koneksi) tidak ditemukan. Periksa file config/koneksi.php.';
} else {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
            $register_status = 'error';
            $error_msg = 'Semua kolom wajib diisi.';
        } elseif ($password !== $confirm_password) {
            $register_status = 'password_mismatch';
        } elseif (strlen($password) < 6) {
            $register_status = 'error';
            $error_msg = 'Password minimal harus 6 karakter.';
        } else {
            try {
                // 1. Cek apakah Username atau Email sudah ada
                $stmt = $koneksi->prepare("SELECT id FROM users WHERE email = ? OR username = ? LIMIT 1");
                if ($stmt) {
                    $stmt->bind_param("ss", $email, $username);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    
                    if ($result->num_rows > 0) {
                        $register_status = 'email_exist';
                    } else {
                        $stmt->close();
                        $hashed = password_hash($password, PASSWORD_DEFAULT);
                        $role = 'user';

                        // 2. Deteksi otomatis kolom yang tersedia di tabel users
                        // Penting: PUSTANI biasanya menggunakan skema Laravel (ada updated_at)
                        $columns_query = $koneksi->query("SHOW COLUMNS FROM users");
                        $existing_columns = [];
                        if ($columns_query) {
                            while($row = $columns_query->fetch_assoc()) {
                                $existing_columns[] = $row['Field'];
                            }
                        }

                        // Membangun query secara dinamis berdasarkan kolom yang benar-benar ada
                        $fields = ['username', 'email', 'password', 'role', 'created_at'];
                        $placeholders = ['?', '?', '?', '?', 'NOW()'];
                        $types = "ssss";
                        $params = [$username, $email, $hashed, $role];

                        // Tambahkan 'name' jika ada (biasanya wajib di Laravel/PUSTANI)
                        if (in_array('name', $existing_columns)) {
                            array_splice($fields, 1, 0, 'name');
                            array_splice($placeholders, 1, 0, '?');
                            $types = "s" . $types;
                            array_splice($params, 1, 0, $username); 
                        }

                        // Tambahkan 'updated_at' jika ada (Skema Laravel mewajibkan ini)
                        if (in_array('updated_at', $existing_columns)) {
                            $fields[] = 'updated_at';
                            $placeholders[] = 'NOW()';
                            // Tidak perlu params/types karena menggunakan fungsi SQL NOW()
                        }

                        // Tambahkan 'status' jika ada
                        if (in_array('status', $existing_columns)) {
                            $fields[] = 'status';
                            $placeholders[] = '?';
                            $types .= "s";
                            $params[] = 'active';
                        }

                        $sql = "INSERT INTO users (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ")";
                        $insert = $koneksi->prepare($sql);
                        
                        if ($insert) {
                            $insert->bind_param($types, ...$params);
                            
                            if ($insert->execute()) {
                                $register_status = 'success';
                            } else {
                                $register_status = 'error';
                                // Tampilkan ralat spesifik untuk debugging (misal: ralat duplikat atau kolom hilang)
                                $error_msg = 'Gagal menyimpan data: ' . $insert->error;
                            }
                            $insert->close();
                        } else {
                            $register_status = 'error';
                            $error_msg = 'Kesalahan sistem database (Query): ' . $koneksi->error;
                        }
                    }
                } else {
                    $register_status = 'error';
                    $error_msg = 'Gagal menyiapkan query: ' . $koneksi->error;
                }
            } catch (Exception $e) {
                $register_status = 'error';
                $error_msg = 'Ralat sistem: ' . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pustani - Daftar Akun</title>
    <link rel="icon" href="../../public/img/img1/Logo.png" type="image/png">
    
    <!-- CSS Dependencies -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary-green: #01937C;
            --bg-cream: #FAF1E6;
            --accent-yellow: #fbc02d;
        }

        body {
            background-color: var(--bg-cream);
            font-family: 'Plus Jakarta Sans', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .register-card {
            background: white;
            border-radius: 30px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0,0,0,0.1);
            max-width: 950px;
            width: 100%;
            display: flex;
        }

        .image-side {
            background: linear-gradient(rgba(1, 147, 124, 0.85), rgba(1, 147, 124, 0.85)), 
                        url('https://images.unsplash.com/photo-1523348837708-15d4a09cfac2?q=80&w=1000');
            background-size: cover;
            background-position: center;
            width: 45%;
            padding: 50px;
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            text-align: center;
        }

        .form-side {
            width: 55%;
            padding: 50px;
        }

        .form-control {
            border-radius: 12px;
            padding: 12px 18px;
            border: 1px solid #e2e8f0;
            margin-bottom: 18px;
            background-color: #f8fafc;
        }

        .form-control:focus {
            border-color: var(--primary-green);
            box-shadow: 0 0 0 3px rgba(1, 147, 124, 0.1);
            background-color: #fff;
        }

        .btn-register {
            background-color: var(--primary-green);
            color: white;
            border-radius: 12px;
            padding: 14px;
            font-weight: 700;
            width: 100%;
            border: none;
            transition: all 0.3s ease;
            margin-top: 10px;
        }

        .btn-register:hover {
            background-color: #017a66;
            transform: translateY(-2px);
            box-shadow: 0 8px 15px rgba(1, 147, 124, 0.2);
        }

        .login-text {
            text-align: center;
            margin-top: 25px;
            font-size: 0.9rem;
            color: #64748b;
        }

        .login-text a {
            color: var(--primary-green);
            text-decoration: none;
            font-weight: 700;
        }

        .login-text a:hover {
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .register-card { flex-direction: column; }
            .image-side, .form-side { width: 100%; }
            .image-side { padding: 40px 30px; }
            .form-side { padding: 40px 30px; }
        }
    </style>
</head>
<body>

    <div class="register-card shadow">
        <!-- Visual Section -->
        <div class="image-side d-none d-md-flex">
            <div class="mb-4">
                <img src="../../public/img/img1/Logo.png" alt="PUSTANI" width="80" class="mb-3 bg-white p-2 rounded-circle">
                <h2 class="fw-bold">PUSTANI</h2>
                <div class="mx-auto" style="width: 40px; height: 3px; background: var(--accent-yellow); border-radius: 10px;"></div>
            </div>
            <p class="lead">Pustaka Informasi Petani untuk memajukan sektor pertanian Indonesia melalui edukasi digital.</p>
        </div>

        <!-- Form Section -->
        <div class="form-side">
            <div class="mb-4">
                <h3 class="fw-bold text-dark mb-1">Daftar Akun Baru</h3>
                <p class="text-muted small">Lengkapi data di bawah untuk bergabung dengan komunitas kami.</p>
            </div>

            <form action="" method="POST">
                <div class="mb-1">
                    <label class="form-label small fw-bold text-secondary">Username</label>
                    <input type="text" name="username" class="form-control" placeholder="Pilih nama pengguna" required>
                </div>
                <div class="mb-1">
                    <label class="form-label small fw-bold text-secondary">Alamat Email</label>
                    <input type="email" name="email" class="form-control" placeholder="email@contoh.com" required>
                </div>
                <div class="mb-1">
                    <label class="form-label small fw-bold text-secondary">Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Minimal 6 karakter" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold text-secondary">Konfirmasi Password</label>
                    <input type="password" name="confirm_password" class="form-control" placeholder="Ulangi password" required>
                </div>
                
                <button type="submit" class="btn btn-register shadow-sm">Daftar Sekarang</button>
            </form>

            <div class="login-text">
                Sudah memiliki akun? <a href="login.php">Masuk di sini</a>
            </div>
        </div>
    </div>

    <!-- JS Dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            <?php if ($register_status == "success") : ?>
                Swal.fire({
                    title: 'Berhasil!',
                    text: 'Akun Anda telah sukses dibuat. Silakan login untuk melanjutkan.',
                    icon: 'success',
                    confirmButtonColor: '#01937C'
                }).then((result) => {
                    window.location = 'login.php';
                });
            <?php elseif ($register_status == "password_mismatch") : ?>
                Swal.fire({
                    title: 'Password Tidak Cocok',
                    text: 'Konfirmasi password yang Anda masukkan tidak sama.',
                    icon: 'error',
                    confirmButtonColor: '#d33'
                });
            <?php elseif ($register_status == "email_exist") : ?>
                Swal.fire({
                    title: 'Data Sudah Terdaftar',
                    text: 'Username atau Email ini sudah digunakan oleh pengguna lain.',
                    icon: 'warning',
                    confirmButtonColor: '#fbc02d'
                });
            <?php elseif ($register_status == "error") : ?>
                Swal.fire({
                    title: 'Terjadi Kesalahan',
                    text: '<?= addslashes($error_msg); ?>',
                    icon: 'error',
                    confirmButtonColor: '#d33'
                });
            <?php endif; ?>
        });
    </script>
</body>
</html>
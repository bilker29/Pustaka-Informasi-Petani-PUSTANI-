<?php
session_start();
// Pastikan path ini benar. Jika folder 'auth' ada di dalam 'views', gunakan ../../
// Jika register.php ada di root, gunakan ./config/koneksi.php
require_once '../../config/koneksi.php';

// Inisialisasi variabel status untuk SweetAlert
$register_status = null;
$error_msg = '';

// 1. Validasi Koneksi
if (!isset($koneksi) || !($koneksi instanceof mysqli)) {
    $register_status = 'error';
    $error_msg = 'Koneksi database tidak terinisialisasi. Periksa file koneksi.php';
} else {
    // 2. Cek apakah tabel 'users' ada
    $checkTable = $koneksi->query("SHOW TABLES LIKE 'users'");
    if (!$checkTable || $checkTable->num_rows == 0) {
        $register_status = 'error';
        $error_msg = "Sistem belum siap: Tabel 'users' tidak ditemukan di database.";
    }

    // 3. Proses Form saat POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $register_status !== 'error') {
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        // Validasi Input
        if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
            $register_status = 'error';
            $error_msg = 'Semua kolom wajib diisi.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $register_status = 'error';
            $error_msg = 'Format email tidak valid.';
        } elseif ($password !== $confirm_password) {
            $register_status = 'password_mismatch';
        } elseif (strlen($password) < 6) {
            $register_status = 'error';
            $error_msg = 'Password minimal harus 6 karakter.';
        } else {
            // Cek duplikasi Email atau Username
            $stmt = $koneksi->prepare("SELECT id FROM users WHERE email = ? OR username = ? LIMIT 1");
            $stmt->bind_param("ss", $email, $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $register_status = 'email_exist';
            } else {
                $stmt->close();

                // Hash password
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $role = 'user';
                
                // INSERT DATA - Menghapus kolom 'name' agar sesuai dengan database PUSTANI Anda
                $sql = "INSERT INTO users (username, email, password, role, created_at) VALUES (?, ?, ?, ?, NOW())";
                $insert = $koneksi->prepare($sql);

                if ($insert) {
                    $insert->bind_param("ssss", $username, $email, $hashed, $role);
                    
                    if ($insert->execute()) {
                        $register_status = 'success';
                    } else {
                        $register_status = 'error';
                        $error_msg = 'Gagal mendaftar: ' . $insert->error;
                    }
                    $insert->close();
                } else {
                    $register_status = 'error';
                    $error_msg = 'Kesalahan Query: ' . $koneksi->error;
                }
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
    <title>Pustani - Register</title>
    <link rel="icon" href="../../public/img/img1/Logo.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary-green: #01937C;
            --dark-green: #004d40;
            --bg-cream: #FAF1E6;
            --accent-yellow: #fbc02d;
        }

        body {
            background-color: var(--bg-cream);
            font-family: 'Plus Jakarta Sans', sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .navbar {
            background: white !important;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }

        .register-container {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 15px;
        }

        .register-card {
            background: white;
            border-radius: 35px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0,0,0,0.1);
            max-width: 1000px;
            width: 100%;
            border: none;
        }

        .image-section {
            background: linear-gradient(rgba(0,0,0,0.2), rgba(0,0,0,0.2)), 
                        url('https://images.unsplash.com/photo-1500382017468-9049fed747ef?q=80&w=1000');
            background-size: cover;
            background-position: center;
            min-height: 100%;
            display: flex;
            align-items: flex-end;
            padding: 40px;
            color: white;
        }

        .form-section {
            background-color: var(--primary-green);
            padding: 60px 50px;
            color: white;
        }

        .form-control {
            background-color: rgba(0, 0, 0, 0.2) !important;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            padding: 12px 20px;
            color: white !important;
            margin-bottom: 15px;
        }

        .form-control::placeholder { color: rgba(255,255,255,0.6); }

        .btn-register {
            background-color: white;
            color: var(--primary-green);
            border-radius: 15px;
            padding: 14px;
            font-weight: 800;
            font-size: 1.1rem;
            width: 100%;
            border: none;
            margin-top: 20px;
            transition: all 0.3s;
        }

        .btn-register:hover {
            background-color: var(--bg-cream);
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
        }

        .login-link {
            color: white;
            text-decoration: none;
            opacity: 0.8;
            font-size: 0.9rem;
            transition: 0.3s;
        }

        .login-link:hover { opacity: 1; text-decoration: underline; }
    </style>
</head>
<body>

    <nav class="navbar navbar-light sticky-top">
        <div class="container justify-content-center">
            <a href="../../index.php">
                <img src="../../public/img/img1/Logo.png" alt="Logo" height="50">
            </a>
        </div>
    </nav>

    <div class="register-container">
        <div class="register-card">
            <div class="row g-0">
                <div class="col-md-6 d-none d-md-flex p-3">
                    <div class="image-section rounded-4">
                        <div>
                            <h2 class="fw-800">Mari Bergabung bersama PUSTANI</h2>
                            <p class="small opacity-75">Dapatkan akses ribuan informasi pertanian untuk kemajuan tani Indonesia.</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-section">
                        <div class="text-center mb-4">
                            <h1 class="fw-800 mb-2">Daftar Akun</h1>
                            <div style="width: 50px; height: 4px; background: var(--accent-yellow); margin: 0 auto; border-radius: 10px;"></div>
                        </div>

                        <form action="" method="POST">
                            <div class="mb-3">
                                <label class="small fw-bold mb-2">Username</label>
                                <input type="text" name="username" class="form-control" placeholder="Masukkan username" required>
                            </div>
                            <div class="mb-3">
                                <label class="small fw-bold mb-2">Email</label>
                                <input type="email" name="email" class="form-control" placeholder="nama@email.com" required>
                            </div>
                            <div class="mb-3">
                                <label class="small fw-bold mb-2">Password</label>
                                <input type="password" name="password" class="form-control" placeholder="Min. 6 karakter" required>
                            </div>
                            <div class="mb-4">
                                <label class="small fw-bold mb-2">Konfirmasi Password</label>
                                <input type="password" name="confirm_password" class="form-control" placeholder="Ulangi password" required>
                            </div>

                            <button type="submit" class="btn btn-register shadow-sm">Buat Akun Sekarang</button>
                        </form>

                        <div class="text-center mt-4">
                            <a href="login.php" class="login-link">Sudah punya akun? Login di sini</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        <?php if ($register_status == "success") : ?>
            Swal.fire({
                title: 'Berhasil!',
                text: 'Akun Anda telah berhasil dibuat. Silakan login.',
                icon: 'success',
                confirmButtonColor: '#01937C'
            }).then(() => { window.location = 'login.php'; });

        <?php elseif ($register_status == "password_mismatch") : ?>
            Swal.fire({
                title: 'Password Salah',
                text: 'Konfirmasi password tidak cocok.',
                icon: 'error',
                confirmButtonColor: '#d33'
            });

        <?php elseif ($register_status == "email_exist") : ?>
            Swal.fire({
                title: 'Gagal',
                text: 'Email atau Username sudah digunakan.',
                icon: 'warning',
                confirmButtonColor: '#fbc02d'
            });

        <?php elseif ($register_status == "error") : ?>
            Swal.fire({
                title: 'Error',
                text: '<?= addslashes($error_msg); ?>',
                icon: 'error',
                confirmButtonColor: '#d33'
            });
        <?php endif; ?>
    </script>
</body>
</html>
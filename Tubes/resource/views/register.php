<?php
session_start();

/**
 * 1. DIAGNOSTIK KONEKSI
 * Mencari file koneksi secara otomatis.
 */
require_once '../../config/koneksi.php';

$path_koneksi = '';
foreach ($paths as $p) {
    if (file_exists($p)) {
        $path_koneksi = $p;
        break;
    }
}

if (!$path_koneksi) {
    die("Fatal Error: File koneksi.php tidak ditemukan.");
}

require_once $path_koneksi;

/**
 * 2. AKTIFKAN PELAPORAN ERROR
 */
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$register_status = null;
$error_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($username) || empty($email) || empty($password)) {
        $register_status = 'error';
        $error_msg = 'Harap isi semua kolom.';
    } elseif ($password !== $confirm_password) {
        $register_status = 'password_mismatch';
    } elseif (strlen($password) < 6) {
        $register_status = 'error';
        $error_msg = 'Password minimal 6 karakter.';
    } else {
        try {
            /**
             * 3. CEK DUPLIKASI (Email harus UNIK sesuai struktur DB Anda)
             */
            $stmt_check = $koneksi->prepare("SELECT id FROM users WHERE email = ? OR username = ? LIMIT 1");
            $stmt_check->bind_param("ss", $email, $username);
            $stmt_check->execute();
            if ($stmt_check->get_result()->num_rows > 0) {
                $register_status = 'email_exist';
                $stmt_check->close();
            } else {
                $stmt_check->close();

                // Hash password (BCRYPT)
                $hashed = password_hash($password, PASSWORD_DEFAULT);

                /**
                 * 4. PROSES INSERT 
                 * Berdasarkan SQL dump Anda:
                 * - Kolom: email, username, password, role
                 * - 'name' dan 'updated_at' tidak perlu karena tidak ada di skema DB users Anda.
                 */
                $sql_insert = "INSERT INTO users (email, username, password, role) VALUES (?, ?, ?, 'user')";
                $stmt_insert = $koneksi->prepare($sql_insert);

                if ($stmt_insert) {
                    $stmt_insert->bind_param("sss", $email, $username, $hashed);
                    $stmt_insert->execute();
                    $register_status = 'success';
                    $stmt_insert->close();
                }
            }
        } catch (mysqli_sql_exception $e) {
            $register_status = 'error';
            $error_msg = "Database Error: " . $e->getMessage();
        } catch (Exception $e) {
            $register_status = 'error';
            $error_msg = "Sistem Error: " . $e->getMessage();
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
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
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
        }

        .login-text {
            text-align: center;
            margin-top: 25px;
            font-size: 0.9rem;
        }

        .login-text a {
            color: var(--primary-green);
            text-decoration: none;
            font-weight: 700;
        }

        @media (max-width: 768px) {
            .register-card {
                flex-direction: column;
            }

            .image-side,
            .form-side {
                width: 100%;
            }
        }
    </style>
</head>

<body>

    <div class="register-card shadow">
        <div class="image-side d-none d-md-flex">
            <div class="mb-4">
                <img src="../../public/img/img1/Logo.png" alt="PUSTANI" width="80" class="mb-3 bg-white p-2 rounded-circle">
                <h2 class="fw-bold">PUSTANI</h2>
            </div>
            <p class="lead">Pustaka Informasi Petani untuk kemajuan sektor pertanian.</p>
        </div>

        <div class="form-side">
            <div class="mb-4 text-center text-md-start">
                <h3 class="fw-bold text-dark mb-1">Daftar Akun Baru</h3>
                <p class="text-muted small">Lengkapi data di bawah untuk bergabung.</p>
            </div>

            <form action="" method="POST">
                <div class="mb-1">
                    <label class="form-label small fw-bold text-secondary">Username</label>
                    <input type="text" name="username" class="form-control" placeholder="Pilih username" required>
                </div>
                <div class="mb-1">
                    <label class="form-label small fw-bold text-secondary">Email</label>
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

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            <?php if ($register_status == "success") : ?>
                Swal.fire({
                    title: 'Berhasil!',
                    text: 'Akun Anda telah sukses dibuat.',
                    icon: 'success',
                    confirmButtonColor: '#01937C'
                }).then(() => {
                    window.location = 'login.php';
                });
            <?php elseif ($register_status == "password_mismatch") : ?>
                Swal.fire({
                    title: 'Password Salah',
                    text: 'Konfirmasi password tidak cocok.',
                    icon: 'error',
                    confirmButtonColor: '#d33'
                });
            <?php elseif ($register_status == "email_exist") : ?>
                Swal.fire({
                    title: 'Gagal Daftar',
                    text: 'Username atau Email sudah terdaftar.',
                    icon: 'warning',
                    confirmButtonColor: '#fbc02d'
                });
            <?php elseif ($register_status == "error") : ?>
                Swal.fire({
                    title: 'Pendaftaran Gagal',
                    text: '<?= addslashes($error_msg); ?>',
                    icon: 'error',
                    confirmButtonColor: '#d33'
                });
            <?php endif; ?>
        });
    </script>
</body>

</html>
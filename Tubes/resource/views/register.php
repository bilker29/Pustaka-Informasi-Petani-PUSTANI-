<?php
session_start();
require '../../config/koneksi.php';

// Pesan untuk UI
$missing_users_message = '';
$error_message = '';
$success_message = '';

// Pastikan koneksi mysqli tersedia
if (!isset($koneksi) || !($koneksi instanceof mysqli)) {
    $missing_users_message = 'Koneksi database tidak ditemukan. Periksa konfigurasi koneksi.';
} else {
    // Periksa keberadaan tabel 'users' sebelum menjalankan query
    $check = mysqli_query($koneksi, "SHOW TABLES LIKE 'users'");
    if ($check === false) {
        // Jika gagal menjalankan SHOW TABLES, log error dan tampilkan pesan aman
        error_log('Error checking users table: ' . mysqli_error($koneksi));
        $missing_users_message = 'Terjadi kesalahan saat memeriksa tabel pengguna. Silakan hubungi admin.';
    } elseif (mysqli_num_rows($check) === 0) {
        $missing_users_message = "Tabel 'users' tidak ditemukan di database. Registrasi tidak dapat dilakukan.";
    } else {
        // Tabel ada -> proses form registrasi jika POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Ambil dan bersihkan input
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $password_confirm = $_POST['password_confirm'] ?? '';

            // Validasi dasar
            if ($name === '' || $email === '' || $password === '' || $password_confirm === '') {
                $error_message = 'Semua field harus diisi.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error_message = 'Format email tidak valid.';
            } elseif ($password !== $password_confirm) {
                $error_message = 'Konfirmasi password tidak cocok.';
            } elseif (strlen($password) < 6) {
                $error_message = 'Password minimal 6 karakter.';
            } else {
                // Cek apakah email sudah terdaftar (menggunakan prepared statement)
                $stmt = $koneksi->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
                if ($stmt) {
                    $stmt->bind_param("s", $email);
                    if ($stmt->execute()) {
                        $stmt->store_result();
                        if ($stmt->num_rows > 0) {
                            $error_message = 'Email sudah terdaftar.';
                        } else {
                            // Insert user baru
                            $stmt->close();
                            $hashed = password_hash($password, PASSWORD_DEFAULT);
                            // Sesuaikan kolom sesuai struktur tabel users Anda
                            $insert = $koneksi->prepare("INSERT INTO users (name, email, password, role, created_at) VALUES (?, ?, ?, ?, NOW())");
                            if ($insert) {
                                $role = 'user';
                                $insert->bind_param("ssss", $name, $email, $hashed, $role);
                                if ($insert->execute()) {
                                    $success_message = 'Registrasi berhasil. Silakan login.';
                                } else {
                                    error_log('Register insert error: ' . $insert->error);
                                    $error_message = 'Gagal membuat akun. Silakan coba lagi nanti.';
                                }
                                $insert->close();
                            } else {
                                error_log('Prepare insert failed: ' . $koneksi->error);
                                $error_message = 'Gagal menyiapkan pendaftaran. Silakan coba lagi nanti.';
                            }
                        }
                    } else {
                        error_log('Execute select failed: ' . $stmt->error);
                        $error_message = 'Gagal memproses pendaftaran. Silakan coba lagi.';
                    }
                    if ($stmt->errno === 0) {
                        // jika belum ditutup (safety)
                        @$stmt->close();
                    }
                } else {
                    error_log('Prepare select failed: ' . $koneksi->error);
                    $error_message = 'Terjadi kesalahan server. Silakan coba lagi nanti.';
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <style>
        :root {
            --primary-green: #00897b;
            --dark-green: #004d40;
            --input-bg: #00332c;
            --bg-cream: #fdf5e6;
            --accent-yellow: #fbc02d;
        }

        body {
            background-color: var(--bg-cream);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .navbar {
            background-color: white !important;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            padding: 6px 0;
        }

        .navbar-brand img {
            height: 70px;
            width: auto;
            max-width: 100%;
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
            border-radius: 40px;
            overflow: hidden;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
            max-width: 950px;
            width: 100%;
            border: none;
            display: flex;
        }

        .image-col {
            display: flex;
            flex-direction: column;
        }

        .image-section {
            background-image: url('../../public/img/img1/0fcd1efc658108c14fe8049b871088a1.jpg');
            background-size: cover;
            background-position: center;
            flex-grow: 1;
            margin: 20px;
            border-radius: 30px;
            min-height: 400px;
        }

        .form-section {
            background-color: #01937C;
            padding: 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            color: white;
            height: 100%;
            border-radius: 30px;
        }

        .register-title {
            font-weight: 800;
            font-size: clamp(2rem, 5vw, 3.5rem);
            margin-bottom: 5px;
            text-align: center;
        }

        .title-underline {
            height: 3px;
            background-color: var(--accent-yellow);
            width: 100%;
            margin: 20px auto 30px auto;
            border-radius: 10px;
        }

        .form-control {
            background-color: var(--input-bg) !important;
            border: none;
            border-radius: 50px;
            padding: 12px 25px;
            margin-bottom: 15px;
            color: white !important;
            transition: 0.3s;
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }

        .form-control:focus {
            box-shadow: 0 0 0 0.25rem rgba(251, 192, 45, 0.3);
            background-color: #002621 !important;
        }

        .btn-register {
            background-color: white;
            color: var(--primary-green);
            border-radius: 15px;
            padding: 12px;
            font-weight: 800;
            font-size: 1.25rem;
            width: 100%;
            border: none;
            margin-top: 10px;
            transition: all 0.3s ease;
        }

        .btn-register:hover {
            background-color: var(--bg-cream);
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            color: var(--dark-green);
        }

        .login-link {
            text-align: center;
            margin-top: 20px;
            color: white;
            text-decoration: none;
            display: block;
            font-size: 0.95rem;
            opacity: 0.9;
        }

        .login-link:hover {
            text-decoration: underline;
            opacity: 1;
        }

        @media (max-width: 768px) {
            .image-section {
                display: none;
            }

            .form-section {
                padding: 40px 25px;
            }

            .register-card {
                border-radius: 30px;
            }
        }

        @media (min-width: 1200px) {
            .register-card {
                max-width: 1000px;
            }
        }
    </style>
</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-light sticky-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="Home.php">
                <img src="../../public/img/img1/logo navbar.png" alt="Logo">
            </a>
        </div>
    </nav>

    <div class="register-container">
        <div class="container d-flex justify-content-center">
            <div class="card register-card">
                <div class="row g-0 w-100">
                    <div class="col-md-6 d-none d-md-flex image-col">
                        <div class="image-section"></div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-section">
                            <h1 class="register-title">Register</h1>
                            <div class="title-underline"></div>

                            <form action="" method="POST">
                                <div class="mb-1">
                                    <input type="email" name="email" class="form-control" placeholder="Email" required>
                                </div>
                                <div class="mb-1">
                                    <input type="text" name="username" class="form-control" placeholder="Username" required>
                                </div>
                                <div class="mb-1">
                                    <input type="password" name="password" class="form-control" placeholder="Password" required>
                                </div>
                                <div class="mb-1">
                                    <input type="password" name="confirm_password" class="form-control" placeholder="Confirm Password" required>
                                </div>

                                <button type="submit" name="register" class="btn btn-register">Register</button>
                            </form>

                            <a href="login.php" class="login-link">Sudah punya akun? Login disini</a>

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
                title: 'Registrasi Berhasil!',
                text: 'Akun Anda telah dibuat. Silakan login.',
                icon: 'success',
                confirmButtonText: 'Lanjut ke Login',
                confirmButtonColor: '#00897b'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location = 'login.php';
                }
            });

        <?php elseif ($register_status == "password_mismatch") : ?>
            Swal.fire({
                title: 'Password Tidak Cocok',
                text: 'Pastikan password dan konfirmasi password sama.',
                icon: 'error',
                confirmButtonColor: '#d33'
            });

        <?php elseif ($register_status == "email_exist") : ?>
            Swal.fire({
                title: 'Email Terdaftar',
                text: 'Email ini sudah digunakan. Silakan gunakan email lain atau login.',
                icon: 'warning',
                confirmButtonColor: '#fbc02d'
            });

        <?php elseif ($register_status == "error") : ?>
            Swal.fire({
                title: 'Terjadi Kesalahan',
                text: 'Gagal mendaftar: <?= $error_msg; ?>',
                icon: 'error',
                confirmButtonColor: '#d33'
            });
        <?php endif; ?>
    </script>
</body>

</html>
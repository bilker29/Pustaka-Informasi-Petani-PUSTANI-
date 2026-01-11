<?php
session_start();
require '../../config/koneksi.php';

// Non-aktifkan mysqli exception reporting agar kita bisa menangani error secara manual
mysqli_report(MYSQLI_REPORT_OFF);

$missing_users_message = '';
$error_message = '';
$email = '';

// Pastikan koneksi mysqli valid
if (!isset($koneksi) || !($koneksi instanceof mysqli)) {
    $missing_users_message = 'Koneksi database tidak ditemukan. Periksa konfigurasi koneksi.';
} else {
    // Cek keberadaan tabel 'users' sebelum menjalankan query apapun
    $check = mysqli_query($koneksi, "SHOW TABLES LIKE 'users'");
    if ($check === false) {
        error_log('SHOW TABLES users error: ' . mysqli_error($koneksi));
        $missing_users_message = 'Terjadi kesalahan saat memeriksa tabel pengguna. Silakan hubungi administrator.';
    } elseif (mysqli_num_rows($check) === 0) {
        $missing_users_message = "Tabel 'users' tidak ditemukan di database. Fitur login tidak dapat digunakan.";
    } else {
        // Jika tabel ada, proses form login saat POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            if ($email === '' || $password === '') {
                $error_message = 'Email dan password harus diisi.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error_message = 'Format email tidak valid.';
            } else {
                // Prepared statement untuk mengambil user berdasarkan email
                $stmt = $koneksi->prepare("SELECT id, name, email, password, role FROM users WHERE email = ? LIMIT 1");
                if ($stmt) {
                    $stmt->bind_param('s', $email);
                    if ($stmt->execute()) {
                        $res = $stmt->get_result();
                        if ($res && $res->num_rows === 1) {
                            $user = $res->fetch_assoc();
                            // Pastikan kolom password tersimpan sebagai hash (password_hash)
                            if (password_verify($password, $user['password'])) {
                                // Login sukses: set session dan redirect
                                $_SESSION['user_id'] = $user['id'];
                                $_SESSION['user_name'] = $user['name'];
                                $_SESSION['role'] = $user['role'];
                                header('Location: index.php'); // sesuaikan redirect tujuan setelah login
                                exit;
                            } else {
                                $error_message = 'Email atau password salah.';
                            }
                        } else {
                            $error_message = 'Email atau password salah.';
                        }
                        if ($res) $res->free();
                    } else {
                        error_log('Login execute error: ' . $stmt->error);
                        $error_message = 'Terjadi kesalahan saat memproses login. Silakan coba lagi.';
                    }
                    $stmt->close();
                } else {
                    error_log('Prepare login failed: ' . $koneksi->error);
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
    <title>Pustani - Login</title>
    <link rel="icon" href="../../public/img/img1/Logo.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <style>
        :root {
            --primary-green: #00897b;
            --text-green: #004d40;
            --bg-cream: #fdf5e6;
        }

        body {
            background-color: #FAF1E6;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            font-family: 'Segoe UI', sans-serif;
        }

        .navbar {
            background-color: white !important;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            padding: 5px 0;
        }

        .login-container {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 0;
        }

        .login-card {
            background: white;
            border-radius: 30px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            max-width: 850px;
            width: 100%;
            border: none;
        }

        .image-section {
            background-image: url('../../public/img/img1/coverlogin.jpg');
            background-size: cover;
            background-position: center;
            min-height: 450px;
            margin: 20px;
            border-radius: 20px;
        }

        .form-section {
            padding: 50px;
        }

        .login-title {
            color: var(--primary-green);
            font-weight: bold;
            font-size: 3rem;
            margin-bottom: 15px;
        }

        .title-underline {
            height: 3px;
            background-color: var(--primary-green);
            width: 100%;
            margin-bottom: 30px;
        }

        .form-control {
            background-color: #e9ecef;
            border: none;
            border-radius: 50px;
            padding: 12px 25px;
            margin-bottom: 15px;
            color: var(--text-green);
        }

        .btn-login {
            background-color: var(--primary-green);
            color: white;
            border-radius: 50px;
            padding: 12px;
            font-weight: bold;
            font-size: 1.2rem;
            width: 100%;
            border: none;
            margin-top: 10px;
            transition: 0.3s;
        }

        .btn-login:hover {
            background-color: var(--text-green);
            transform: translateY(-2px);
        }

        .register-link {
            text-align: center;
            margin-top: 20px;
            color: var(--primary-green);
            text-decoration: none;
            display: block;
            font-weight: 600;
        }
    </style>
</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-light sticky-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="#Home.php">
                <img src="../../public/img/img1/logo navbar.png" alt="Logo" height="70">
            </a>
        </div>
    </nav>

    <div class="login-container">
        <div class="container d-flex justify-content-center">
            <div class="card login-card">
                <div class="row g-0">
                    <div class="col-md-5 d-none d-md-block">
                        <div class="image-section"></div>
                    </div>
                    <div class="col-md-7">
                        <div class="form-section">
                            <h1 class="login-title">Login</h1>
                            <div class="title-underline"></div>
                            <form action="" method="POST">
                                <div class="mb-3">
                                    <input type="email" name="email" class="form-control" placeholder="Email" required>
                                </div>
                                <div class="mb-3">
                                    <input type="password" name="password" class="form-control" placeholder="Password" required>
                                </div>
                                <button type="submit" name="login" class="btn btn-login">Login</button>
                            </form>
                            <a href="register.php" class="register-link">Belum punya akun? Daftar disini</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        <?php if ($login_status == "success") : ?>
            Swal.fire({
                title: 'Login Berhasil!',
                text: 'Selamat datang kembali, <?= $_SESSION['username']; ?>',
                icon: 'success',
                timer: 2000,
                showConfirmButton: false
            }).then(() => {
                // Redirect sesuai role (Dashboard untuk User/Expert)
                window.location = '<?= $redirect_page ?>';
            });
        <?php elseif ($login_status == "password_wrong") : ?>
            Swal.fire({
                title: 'Gagal Masuk',
                text: 'Password salah!',
                icon: 'error',
                confirmButtonColor: '#00897b'
            });
        <?php elseif ($login_status == "email_not_found") : ?>
            Swal.fire({
                title: 'Akun Tidak Ditemukan',
                text: 'Email belum terdaftar.',
                icon: 'warning',
                confirmButtonColor: '#00897b'
            });
        <?php endif; ?>
    </script>
</body>

</html>
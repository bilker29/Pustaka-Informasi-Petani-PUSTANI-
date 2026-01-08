<?php
session_start();
require '../../config/koneksi.php';

$login_status = "";
$redirect_page = "";

if (isset($_POST['login'])) {
    $email = mysqli_real_escape_string($koneksi, $_POST['email']);
    $password = $_POST['password'];

    $result = mysqli_query($koneksi, "SELECT * FROM users WHERE email = '$email'");

    if (mysqli_num_rows($result) === 1) {
        $row = mysqli_fetch_assoc($result);

        // Memverifikasi password hash
        if (password_verify($password, $row['password'])) {
            $_SESSION['login'] = true;
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['role'] = $row['role'];

            $login_status = "success";

            // --- PERBAIKAN DI SINI ---
            // Jika Admin -> ke Dashboard Admin (jika ada)
            // Jika User/Expert -> ke Dashboard.php (Dashboard User)
            if ($row['role'] === 'admin') {
                $redirect_page = 'admin/dashboard.php';
            } else {
                $redirect_page = 'dashboard.php'; // Redirect ke Dashboard User
            }
        } else {
            $login_status = "password_wrong";
        }
    } else {
        $login_status = "email_not_found";
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
            <a class="navbar-brand d-flex align-items-center" href="#">
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
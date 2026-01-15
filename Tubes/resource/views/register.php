<?php
session_start();
// Pastikan path ini benar. Jika file ini di dalam folder 'auth' atau 'views', ../../ sudah benar.
// Jika file ini di root, gunakan ./config/koneksi.php
$path_koneksi = '../../config/koneksi.php';

if (!file_exists($path_koneksi)) {
    die("Error: File koneksi.php tidak ditemukan di path: $path_koneksi");
}

require_once $path_koneksi;

$register_status = null;
$error_msg = '';

if (!isset($koneksi) || !($koneksi instanceof mysqli)) {
    $register_status = 'error';
    $error_msg = 'Variabel $koneksi tidak ditemukan. Periksa config/koneksi.php';
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
        } else {
            // 1. Cek duplikasi
            $stmt = $koneksi->prepare("SELECT id FROM users WHERE email = ? OR username = ? LIMIT 1");
            $stmt->bind_param("ss", $email, $username);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) {
                $register_status = 'email_exist';
            } else {
                $stmt->close();
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $role = 'user';

                // 2. DETEKSI KOLOM 'name' (Agar tidak error jika kolom tidak ada)
                $res = $koneksi->query("SHOW COLUMNS FROM users LIKE 'name'");
                $hasNameColumn = ($res->num_rows > 0);

                if ($hasNameColumn) {
                    // Jika ada kolom name
                    $sql = "INSERT INTO users (username, name, email, password, role, created_at) VALUES (?, ?, ?, ?, ?, NOW())";
                    $insert = $koneksi->prepare($sql);
                    $insert->bind_param("sssss", $username, $username, $email, $hashed, $role);
                } else {
                    // Jika TIDAK ada kolom name
                    $sql = "INSERT INTO users (username, email, password, role, created_at) VALUES (?, ?, ?, ?, NOW())";
                    $insert = $koneksi->prepare($sql);
                    $insert->bind_param("ssss", $username, $email, $hashed, $role);
                }

                if ($insert && $insert->execute()) {
                    $register_status = 'success';
                } else {
                    $register_status = 'error';
                    $error_msg = 'Database Error: ' . ($insert ? $insert->error : $koneksi->error);
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
        }

        .register-card {
            background: white;
            border-radius: 30px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0,0,0,0.1);
            max-width: 900px;
            width: 100%;
            display: flex;
        }

        .image-side {
            background: linear-gradient(rgba(1, 147, 124, 0.8), rgba(1, 147, 124, 0.8)), 
                        url('https://images.unsplash.com/photo-1523348837708-15d4a09cfac2?q=80&w=1000');
            background-size: cover;
            background-position: center;
            width: 40%;
            padding: 40px;
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .form-side {
            width: 60%;
            padding: 50px;
        }

        .form-control {
            border-radius: 12px;
            padding: 12px 15px;
            border: 1px solid #dee2e6;
            margin-bottom: 15px;
        }

        .btn-register {
            background-color: var(--primary-green);
            color: white;
            border-radius: 12px;
            padding: 12px;
            font-weight: 700;
            width: 100%;
            border: none;
            transition: 0.3s;
        }

        .btn-register:hover {
            background-color: #017a66;
            transform: translateY(-2px);
        }

        @media (max-width: 768px) {
            .register-card { flex-direction: column; }
            .image-side, .form-side { width: 100%; }
            .image-side { padding: 30px; text-align: center; }
        }
    </style>
</head>
<body>

    <div class="register-card shadow">
        <div class="image-side d-none d-md-flex">
            <h2 class="fw-bold">PUSTANI</h2>
            <p>Pustaka Informasi Petani untuk kemajuan pertanian Indonesia.</p>
        </div>
        <div class="form-side">
            <div class="text-center mb-4">
                <h3 class="fw-bold text-dark">Daftar Akun</h3>
                <p class="text-muted small">Silakan isi formulir untuk mendaftar</p>
            </div>

            <form action="" method="POST">
                <input type="text" name="username" class="form-control" placeholder="Username" required>
                <input type="email" name="email" class="form-control" placeholder="Email" required>
                <input type="password" name="password" class="form-control" placeholder="Password" required>
                <input type="password" name="confirm_password" class="form-control" placeholder="Konfirmasi Password" required>
                
                <button type="submit" class="btn btn-register shadow-sm mt-2">Daftar Sekarang</button>
            </form>

            <p class="text-center mt-4 small">
                Sudah punya akun? <a href="login.php" class="text-success fw-bold text-decoration-none">Login</a>
            </p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        <?php if ($register_status == "success") : ?>
            Swal.fire('Berhasil!', 'Akun Anda telah dibuat. Silakan login.', 'success')
            .then(() => { window.location = 'login.php'; });
        <?php elseif ($register_status == "password_mismatch") : ?>
            Swal.fire('Error', 'Konfirmasi password tidak cocok.', 'error');
        <?php elseif ($register_status == "email_exist") : ?>
            Swal.fire('Gagal', 'Username atau Email sudah terdaftar.', 'warning');
        <?php elseif ($register_status == "error") : ?>
            Swal.fire('Terjadi Kesalahan', '<?= addslashes($error_msg); ?>', 'error');
        <?php endif; ?>
    </script>
</body>
</html>
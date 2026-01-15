<?php
session_start();

// 1. PENGECEKAN PATH KONEKSI SECARA DINAMIS
$paths = [
    '../../config/koneksi.php',
    '../config/koneksi.php',
    './config/koneksi.php',
    'config/koneksi.php'
];

$path_koneksi = '';
foreach ($paths as $p) {
    if (file_exists($p)) {
        $path_koneksi = $p;
        break;
    }
}

if (!$path_koneksi) {
    die("Fatal Error: File koneksi.php tidak ditemukan. Pastikan folder 'config' tersedia.");
}

require_once $path_koneksi;

// Memastikan variabel $koneksi benar-benar ada
if (!isset($koneksi) || !($koneksi instanceof mysqli)) {
    die("Fatal Error: Koneksi database tidak terdeteksi. Periksa file koneksi.php Anda (pastikan nama variabelnya adalah \$koneksi).");
}

// Matikan laporan ralat otomatis agar kita bisa menangkapnya sendiri untuk SweetAlert
mysqli_report(MYSQLI_REPORT_OFF);

$register_status = null;
$error_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validasi Input Dasar
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
            // 2. INSPEKSI TABEL (Mendeteksi kolom yang benar-benar ada di database)
            $res_cols = $koneksi->query("SHOW COLUMNS FROM users");
            if (!$res_cols) {
                throw new Exception("Gagal membaca struktur tabel 'users': " . $koneksi->error);
            }
            
            $existing_columns = [];
            while ($col = $res_cols->fetch_assoc()) {
                $existing_columns[$col['Field']] = [
                    'null'    => $col['Null'],
                    'default' => $col['Default'],
                    'extra'   => $col['Extra']
                ];
            }

            // 3. CEK DUPLIKASI DATA
            // Kita harus mengecek kolom email (pasti ada) dan username/name (jika ada)
            $check_sql = "SELECT id FROM users WHERE email = ?";
            if (isset($existing_columns['username'])) {
                $check_sql .= " OR username = ?";
            } elseif (isset($existing_columns['name'])) {
                $check_sql .= " OR name = ?";
            }
            $check_sql .= " LIMIT 1";

            $stmt_check = $koneksi->prepare($check_sql);
            if (isset($existing_columns['username']) || isset($existing_columns['name'])) {
                $stmt_check->bind_param("ss", $email, $username);
            } else {
                $stmt_check->bind_param("s", $email);
            }
            
            $stmt_check->execute();
            if ($stmt_check->get_result()->num_rows > 0) {
                $register_status = 'email_exist';
                $stmt_check->close();
            } else {
                $stmt_check->close();
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $role = 'user';

                // 4. SUSUN QUERY INSERT SECARA DINAMIS
                $fields = [];
                $placeholders = [];
                $types = "";
                $params = [];

                // Pemetaan otomatis input form ke kolom database
                if (isset($existing_columns['username'])) {
                    $fields[] = 'username'; $placeholders[] = '?'; $types .= "s"; $params[] = $username;
                }
                if (isset($existing_columns['name'])) {
                    $fields[] = 'name'; $placeholders[] = '?'; $types .= "s"; $params[] = $username;
                }
                if (isset($existing_columns['email'])) {
                    $fields[] = 'email'; $placeholders[] = '?'; $types .= "s"; $params[] = $email;
                }
                if (isset($existing_columns['password'])) {
                    $fields[] = 'password'; $placeholders[] = '?'; $types .= "s"; $params[] = $hashed;
                }
                if (isset($existing_columns['role'])) {
                    $fields[] = 'role'; $placeholders[] = '?'; $types .= "s"; $params[] = $role;
                }

                // Tambahkan Laravel Timestamps jika ada
                if (isset($existing_columns['created_at'])) {
                    $fields[] = 'created_at'; $placeholders[] = 'NOW()';
                }
                if (isset($existing_columns['updated_at'])) {
                    $fields[] = 'updated_at'; $placeholders[] = 'NOW()';
                }

                // Tambahkan token pengingat jika ada
                if (isset($existing_columns['remember_token'])) {
                    $fields[] = 'remember_token'; $placeholders[] = '?'; $types .= "s"; $params[] = substr(bin2hex(random_bytes(10)), 0, 10);
                }

                // Cek apakah ada kolom NOT NULL tanpa default yang terlewat
                foreach ($existing_columns as $colName => $info) {
                    if (!in_array($colName, $fields) && strpos($info['extra'], 'auto_increment') === false) {
                        if ($info['null'] === 'NO' && $info['default'] === NULL) {
                            $fields[] = $colName; $placeholders[] = '?'; $types .= "s"; $params[] = ""; 
                        }
                    }
                }

                // Eksekusi Insert
                $sql_insert = "INSERT INTO users (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ")";
                $stmt_insert = $koneksi->prepare($sql_insert);
                
                if ($stmt_insert) {
                    if (!empty($params)) {
                        $stmt_insert->bind_param($types, ...$params);
                    }
                    
                    if ($stmt_insert->execute()) {
                        $register_status = 'success';
                    } else {
                        $register_status = 'error';
                        $error_msg = "Database Error: " . $stmt_insert->error;
                    }
                    $stmt_insert->close();
                } else {
                    $register_status = 'error';
                    $error_msg = "Gagal memproses pendaftaran: " . $koneksi->error;
                }
            }
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

        @media (max-width: 768px) {
            .register-card { flex-direction: column; }
            .image-side, .form-side { width: 100%; }
            .image-side { padding: 40px 30px; }
        }
    </style>
</head>
<body>

    <div class="register-card shadow">
        <!-- Bagian Visual -->
        <div class="image-side d-none d-md-flex">
            <div class="mb-4">
                <img src="../../public/img/img1/Logo.png" alt="PUSTANI" width="80" class="mb-3 bg-white p-2 rounded-circle">
                <h2 class="fw-bold">PUSTANI</h2>
                <div class="mx-auto" style="width: 40px; height: 3px; background: var(--accent-yellow); border-radius: 10px;"></div>
            </div>
            <p class="lead">Pustaka Informasi Petani untuk memajukan sektor pertanian Indonesia melalui edukasi digital.</p>
        </div>

        <!-- Bagian Form -->
        <div class="form-side">
            <div class="mb-4 text-center text-md-start">
                <h3 class="fw-bold text-dark mb-1">Daftar Akun Baru</h3>
                <p class="text-muted small">Bergabunglah dengan komunitas petani modern hari ini.</p>
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

    <!-- JS Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            <?php if ($register_status == "success") : ?>
                Swal.fire({
                    title: 'Berhasil!',
                    text: 'Akun Anda telah sukses dibuat. Silakan login.',
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
                    title: 'Gagal Daftar',
                    text: 'Username atau Email sudah terdaftar di sistem.',
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
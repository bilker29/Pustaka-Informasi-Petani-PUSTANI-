<?php
session_start();
require '../../config/koneksi.php';

if (isset($_POST['submit_pengajuan'])) {
    $u_id = $_SESSION['user_id'];
    $keahlian = mysqli_real_escape_string($koneksi, $_POST['keahlian']);
    $bio = mysqli_real_escape_string($koneksi, $_POST['bio']);

    $nama_file = "";
    if (!empty($_FILES['dokumen']['name'])) {
        $ext = pathinfo($_FILES['dokumen']['name'], PATHINFO_EXTENSION);
        $nama_file = "CV_" . time() . "_" . $u_id . "." . $ext;
        if (!is_dir("../../public/img/img1/dokumen/")) mkdir("../../public/img/img1/dokumen/", 0777, true);
        move_uploaded_file($_FILES['dokumen']['tmp_name'], "../../public/img/img1/dokumen/" . $nama_file);
    }

    mysqli_query($koneksi, "UPDATE users SET keahlian='$keahlian', bio='$bio', status_ahli='pending', dokumen_pendukung='$nama_file' WHERE id='$u_id'");
    header("Location: profilsaya.php?status=pending");
    exit;
}

<?php
session_start();
include 'config.php';

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') { exit; }

// --- TAMBAH / EDIT PENGGUNA ---
if (isset($_POST['simpan_pengguna'])) {
    $nama   = mysqli_real_escape_string($conn, $_POST['nama']);
    $kontak = mysqli_real_escape_string($conn, $_POST['kontak']);
    $role   = $_POST['role'];
    $pass_input = $_POST['password']; // Tangkap input password
    $id     = isset($_POST['id_user']) ? (int)$_POST['id_user'] : null;

    if ($id) {
        // LOGIKA EDIT
        // Cek apakah admin mengisi form password untuk meresetnya
        if (!empty($pass_input)) {
            $pass_hash = password_hash($pass_input, PASSWORD_DEFAULT);
            $query = "UPDATE users SET nama='$nama', kontak='$kontak', role='$role', password='$pass_hash' WHERE id=$id";
        } else {
            // Jika kosong, update data selain password
            $query = "UPDATE users SET nama='$nama', kontak='$kontak', role='$role' WHERE id=$id";
        }
        $msg = "success_update";
    } else {
        // LOGIKA TAMBAH
        // Jika password tidak diisi saat tambah user, gunakan "password123" sebagai default
        if (empty($pass_input)) {
            $pass_input = "password123";
        }
        $pass_hash = password_hash($pass_input, PASSWORD_DEFAULT);
        $query = "INSERT INTO users (nama, kontak, password, role) VALUES ('$nama', '$kontak', '$pass_hash', '$role')";
        $msg = "success_add";
    }

    if (mysqli_query($conn, $query)) {
        header("Location: data_penyewa.php?msg=$msg");
    }
    exit;
}

// --- HAPUS PENGGUNA ---
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    
    // Proteksi: Jangan biarkan admin menghapus dirinya sendiri
    // Penyesuaian nama sesi ID agar akurat
    if(isset($_SESSION['id']) && $id == $_SESSION['id']) {
        header("Location: data_penyewa.php?msg=error_self_delete");
        exit;
    }

    if (mysqli_query($conn, "DELETE FROM users WHERE id = $id")) {
        // PERBAIKAN: Arahkan ke data_penyewa.php
        header("Location: data_penyewa.php?msg=success_delete");
    }
    exit;
}
?>
<?php
session_start();
include 'config.php';

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') { exit; }

// --- TAMBAH / EDIT PENGGUNA ---
if (isset($_POST['simpan_pengguna'])) {
    $nama   = mysqli_real_escape_string($conn, $_POST['nama']);
    $kontak = mysqli_real_escape_string($conn, $_POST['kontak']);
    $role   = $_POST['role'];
    $id     = isset($_POST['id_user']) ? (int)$_POST['id_user'] : null;

    if ($id) {
        // LOGIKA EDIT
        $query = "UPDATE users SET nama='$nama', kontak='$kontak', role='$role' WHERE id=$id";
        $msg = "success_update";
    } else {
        // LOGIKA TAMBAH (Default Password: password123)
        $pass  = password_hash("password123", PASSWORD_DEFAULT);
        $query = "INSERT INTO users (nama, kontak, password, role) VALUES ('$nama', '$kontak', '$pass', '$role')";
        $msg = "success_add";
    }

    if (mysqli_query($conn, $query)) {
        header("Location: data_pengguna.php?msg=$msg");
    }
    exit;
}

// --- HAPUS PENGGUNA ---
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    
    // Proteksi: Jangan biarkan admin menghapus dirinya sendiri
    if($id == $_SESSION['user_id']) {
        header("Location: data_pengguna.php?msg=error_self_delete");
        exit;
    }

    if (mysqli_query($conn, "DELETE FROM users WHERE id = $id")) {
        header("Location: data_pengguna.php?msg=success_delete");
    }
    exit;
}
?>
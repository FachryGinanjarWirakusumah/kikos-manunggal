<?php
session_start();
include 'config.php';

// 1. CEK LOGIN & ROLE
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

// 2. AMBIL & VALIDASI ID (Harus Angka)
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    echo "<script>alert('ID Kamar tidak valid!'); window.location='admin_dashboard.php';</script>";
    exit;
}

// 3. AMBIL DATA DARI DATABASE
$query = mysqli_query($conn, "SELECT * FROM kamar WHERE id = $id");
$data = mysqli_fetch_assoc($query);

if (!$data) {
    echo "<script>alert('Data kamar tidak ditemukan di database!'); window.location='admin_dashboard.php';</script>";
    exit;
}

// 4. LOGIKA SIMPAN PERUBAHAN
if (isset($_POST['submit_edit'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama_kamar']);
    $harga = mysqli_real_escape_string($conn, $_POST['harga']);
    $lokasi = mysqli_real_escape_string($conn, $_POST['lokasi']);
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);
    $status = $_POST['status'];

    $update = "UPDATE kamar SET 
                nama_kamar = '$nama', 
                harga = '$harga', 
                lokasi = '$lokasi', 
                deskripsi = '$deskripsi', 
                status = '$status' 
               WHERE id = $id";

    if (mysqli_query($conn, $update)) {
        // Gunakan pesan sukses yang lebih manis
        echo "<script>alert('Kamar " . $nama . " berhasil diperbarui!'); window.location='admin_dashboard.php';</script>";
    } else {
        echo "Gagal update: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Admin - Edit Kamar</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .edit-container { max-width: 700px; margin: 50px auto; background: #fff; padding: 30px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 20px; }
        label { display: block; font-weight: 700; margin-bottom: 8px; color: #333; }
        input, textarea, select { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 10px; font-family: inherit; }
        textarea { resize: vertical; min-height: 120px; }
        .btn-save { background: #1abc9c; color: white; border: none; padding: 15px 25px; border-radius: 10px; cursor: pointer; font-weight: 700; width: 100%; font-size: 16px; }
        .btn-save:hover { background: #16a085; }
    </style>
</head>
<body style="background: #f8f9fa;">
    <div class="edit-container">
        <h2>Edit Data: <?= $data['nama_kamar']; ?></h2>
        <hr style="margin-bottom: 25px; opacity: 0.1;">
        
        <form method="POST">
            <div class="form-group">
                <label>Nama Kamar</label>
                <input type="text" name="nama_kamar" value="<?= htmlspecialchars($data['nama_kamar']); ?>" required>
            </div>
            
            <div class="form-group">
                <label>Lokasi</label>
                <input type="text" name="lokasi" value="<?= htmlspecialchars($data['lokasi']); ?>" required>
            </div>

            <div class="form-group">
                <label>Harga per Bulan</label>
                <input type="number" name="harga" value="<?= $data['harga']; ?>" required>
            </div>

            <div class="form-group">
                <label>Deskripsi & Fasilitas (Admin bisa edit di sini)</label>
                <textarea name="deskripsi" placeholder="Tulis fasilitas kamar di sini..."><?= htmlspecialchars($data['deskripsi']); ?></textarea>
            </div>

            <div class="form-group">
                <label>Status</label>
                <select name="status">
                    <option value="tersedia" <?= ($data['status'] == 'tersedia') ? 'selected' : ''; ?>>Tersedia</option>
                    <option value="penuh" <?= ($data['status'] == 'penuh') ? 'selected' : ''; ?>>Penuh</option>
                </select>
            </div>

            <button type="submit" name="submit_edit" class="btn-save">Simpan Perubahan</button>
            <a href="admin_dashboard.php" style="display:block; text-align:center; margin-top:20px; color:#999; text-decoration:none;">Batal & Kembali</a>
        </form>
    </div>
</body>
</html>
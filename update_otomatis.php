<?php
session_start();
include 'config.php';

// Pastikan user sedang login agar kita bisa mengambil ID-nya
if (!isset($_SESSION['id']) && !isset($_SESSION['user_id'])) {
    die("Error: Sesi user tidak ditemukan. Silakan login kembali.");
}

// Ambil ID User dari session (Sesuaikan jika nama session loginmu 'id' atau 'user_id')
$id_user = isset($_SESSION['id']) ? $_SESSION['id'] : $_SESSION['user_id'];

// 1. Ambil data dari URL (dikirim dari bayar.php)
$order_id = isset($_GET['order_id']) ? mysqli_real_escape_string($conn, $_GET['order_id']) : '';
$id_kamar = isset($_GET['id_kamar']) ? (int)$_GET['id_kamar'] : 0;
$nominal  = isset($_GET['nominal']) ? (int)$_GET['nominal'] : 0;

// 2. Proses Insert ke Tabel Pembayaran Asli Kinara
// Berikan penanda "bukti transfer" otomatis karena ini bebas biaya atau otomatis Midtrans
$bukti_sistem = ($nominal <= 0) ? 'Promo-Gratis.png' : 'Midtrans-Otomatis.png';

// PERBAIKAN: Tambahkan kolom order_id agar sukses.php bisa membaca datanya!
$query_pembayaran = "INSERT INTO pembayaran (id_user, id_kamar, jumlah_bayar, bukti_transfer, status_pembayaran, order_id) 
                     VALUES ('$id_user', '$id_kamar', '$nominal', '$bukti_sistem', 'berhasil', '$order_id')";

if (mysqli_query($conn, $query_pembayaran)) {
    
    // 3. Update Status Kamar jadi Penuh
    mysqli_query($conn, "UPDATE kamar SET status = 'penuh' WHERE id = $id_kamar");

    // Bersihkan keranjang sementara
    if (isset($_SESSION['temp_booking'])) {
        unset($_SESSION['temp_booking']);
    }

    // Arahkan ke halaman ringkasan sukses
    header("Location: sukses.php?order_id=" . $order_id);
    exit;
} else {
    // Jika gagal, tampilkan pesan error yang rapi
    echo "<h3>Terjadi Kesalahan Sistem!</h3>";
    echo "<p>Gagal menyimpan ke database: " . mysqli_error($conn) . "</p>";
}
?>
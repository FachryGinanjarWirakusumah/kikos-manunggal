<?php
include 'config.php';
session_start();

// 1. Ambil data dari URL (dikirim dari bayar.php)
$order_id = isset($_GET['order_id']) ? mysqli_real_escape_string($conn, $_GET['order_id']) : '';
$id_kamar = isset($_GET['id_kamar']) ? (int)$_GET['id_kamar'] : 0;
$nominal  = isset($_GET['nominal']) ? (int)$_GET['nominal'] : 0;

// 2. Ambil data dari Session (Data form yang diisi user)
if (isset($_SESSION['temp_booking'])) {
    $data = $_SESSION['temp_booking'];
    $nama = mysqli_real_escape_string($conn, $data['nama']);
    $kontak = mysqli_real_escape_string($conn, $data['kontak']);

    // --- PROSES A: Masukkan ke Tabel Booking (Untuk Daftar Penyewa Terbaru) ---
    $query_booking = "INSERT INTO booking (nama, kontak, id_kamar, status, order_id) 
                      VALUES ('$nama', '$kontak', '$id_kamar', 'Berhasil', '$order_id')";
    
    // --- PROSES B: Masukkan ke Tabel Pembayaran (Untuk Laporan Keuangan) ---
    // Sesuaikan nama kolom 'penyewa', 'unit_kamar', dll dengan database kamu
    $query_pembayaran = "INSERT INTO pembayaran (penyewa, unit_kamar, nominal, bukti, status, order_id) 
                         VALUES ('$nama', '$id_kamar', '$nominal', 'Midtrans-Otomatis', 'Berhasil', '$order_id')";

    // Jalankan kedua query
    if (mysqli_query($conn, $query_booking) && mysqli_query($conn, $query_pembayaran)) {
        
        // --- PROSES C: Update Status Kamar jadi Penuh ---
        mysqli_query($conn, "UPDATE kamar SET status = 'penuh' WHERE id = $id_kamar");

        // Hapus session sementara agar tidak double input
        unset($_SESSION['temp_booking']);

        // Lempar ke halaman utama dengan pesan sukses
        header("Location: index.php?msg=success_payment");
        exit;
    } else {
        echo "Gagal update database: " . mysqli_error($conn);
    }
} else {
    // Jika tidak ada data session, kembalikan ke index
    header("Location: index.php");
    exit;
}
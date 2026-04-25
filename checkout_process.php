<?php
session_start();
include 'config.php';

// Masukkan Library Midtrans (Bisa via Composer atau manual download)
// Untuk simulasi ini, kita fokus ke logic integrasinya.

$id_kamar = $_GET['id'];
$id_user = $_SESSION['id_user']; // Pastikan user sudah login

// Ambil data kamar
$kamar = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM kamar WHERE id = $id_kamar"));
$order_id = "KINARA-" . time(); // ID Unik Transaksi
$total_harga = $kamar['harga'];

/* LOGIC DISINI:
  1. Kirim data ke API Midtrans
  2. Dapatkan SNAP TOKEN
  3. Simpan ke database dengan status 'pending'
*/

// Simulasi Simpan ke Database
mysqli_query($conn, "INSERT INTO pembayaran (id_user, id_kamar, total_bayar, status_pembayaran, order_id) 
VALUES ('$id_user', '$id_kamar', '$total_harga', 'pending', '$order_id')");

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pembayaran - Kinara Kost</title>
    <link rel="stylesheet" href="style.css">
    <script type="text/javascript" src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="SET_YOUR_CLIENT_KEY_HERE"></script>
    <style>
        .checkout-box { max-width: 500px; margin: 100px auto; padding: 40px; background: #fff; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); text-align: center; }
        .price-tag { font-size: 30px; font-weight: 800; color: #ff385c; margin: 20px 0; }
        .btn-pay { background: #1abc9c; color: white; border: none; padding: 15px 30px; border-radius: 12px; font-weight: 700; width: 100%; cursor: pointer; }
    </style>
</head>
<body style="background: #f8f9fa;">

<div class="checkout-box">
    <img src="img/qris-logo.png" width="100" alt="QRIS">
    <h3 class="fw-bold mt-3">Konfirmasi Booking</h3>
    <p class="text-muted">Unit: <strong><?= $kamar['nama_kamar']; ?></strong></p>
    
    <div class="price-tag">Rp <?= number_format($total_harga, 0, ',', '.'); ?></div>
    
    <ul class="list-group list-group-flush text-start mb-4" style="font-size: 14px;">
        <li class="list-group-item d-flex justify-content-between"><span>Biaya Sewa</span> <span>Rp <?= number_format($total_harga, 0, ',', '.'); ?></span></li>
        <li class="list-group-item d-flex justify-content-between"><span>Biaya Layanan</span> <span>Rp 0</span></li>
        <li class="list-group-item d-flex justify-content-between fw-bold"><span>Total Bayar</span> <span>Rp <?= number_format($total_harga, 0, ',', '.'); ?></span></li>
    </ul>

    <button id="pay-button" class="btn-pay">Bayar Sekarang (QRIS/Bank)</button>
    <p class="small text-muted mt-3"><i class="fas fa-lock"></i> Pembayaran Terenkripsi & Aman</p>
</div>

<script type="text/javascript">
    var payButton = document.getElementById('pay-button');
    payButton.addEventListener('click', function () {
        // Ganti 'YOUR_SNAP_TOKEN' dengan token asli dari Midtrans API
        window.snap.pay('YOUR_SNAP_TOKEN', {
            onSuccess: function(result){
                /* Kerjakan update database ke 'Berhasil' via AJAX disini */
                alert("Pembayaran Berhasil!"); window.location.href="riwayat.php";
            },
            onPending: function(result){
                alert("Menunggu pembayaran Anda!");
            },
            onError: function(result){
                alert("Pembayaran Gagal!");
            }
        });
    });
</script>
</body>
</html>
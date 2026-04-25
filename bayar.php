<?php
session_start();
require_once dirname(__FILE__) . '/midtrans-php-master/Midtrans.php';
include 'config.php';

// Proteksi Login
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

// ==========================================
// 1. MEMBACA FILE .ENV
// ==========================================
$env = parse_ini_file(__DIR__ . '/.env');

if (!$env) {
    die("Error: File .env tidak ditemukan atau tidak bisa dibaca.");
}

// Konfigurasi Midtrans (Mengambil dari .env)
\Midtrans\Config::$serverKey = $env['MIDTRANS_SERVER_KEY'];
// Ubah ini ke true jika sudah mode live/production
\Midtrans\Config::$isProduction = false; 
\Midtrans\Config::$isSanitized = true;
\Midtrans\Config::$is3ds = true;

// Ambil data kamar
$id_kamar = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$query = mysqli_query($conn, "SELECT * FROM kamar WHERE id = $id_kamar");
$k = mysqli_fetch_assoc($query);

if (!$k) {
    echo "Kamar tidak ditemukan!";
    exit;
}

$order_id = "KINARA-" . time();
$total_bayar = $k['harga'];

// Buat Transaksi
$params = [
    'transaction_details' => [
        'order_id' => $order_id,
        'gross_amount' => $total_bayar,
    ],
    'customer_details' => [
        'first_name' => $_SESSION['nama'],
        'email' => $_SESSION['email'] ?? 'user@mail.com',
    ],
    'enabled_payments' => ['qris', 'bank_transfer', 'gopay', 'shopeepay']
];

$snapToken = \Midtrans\Snap::getSnapToken($params);

// TANGKAP DATA DARI FORM ISI_DATA.PHP
$nama_form = isset($_POST['nama']) ? mysqli_real_escape_string($conn, $_POST['nama']) : '';
$kontak_form = isset($_POST['kontak']) ? mysqli_real_escape_string($conn, $_POST['kontak']) : '';

// Simpan ke session untuk dibawa ke update_otomatis.php nanti
if(!empty($nama_form)) {
    $_SESSION['temp_booking'] = [
        'nama' => $nama_form,
        'kontak' => $kontak_form,
        'id_kamar' => $id_kamar,
        'harga' => $k['harga']
    ];
}

// 2. Ambil Client Key untuk ditaruh di tag <script> HTML
$clientKey = $env['MIDTRANS_CLIENT_KEY'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout Pembayaran - Kinara Kost</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    
    <!-- MENGGUNAKAN CLIENT KEY DARI VARIABEL PHP -->
    <script type="text/javascript" src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="<?= $clientKey ?>"></script>
    
    <!-- (Optional) Tambahkan CDN SweetAlert2 karena di bawah kamu pakai Swal.fire tapi scriptnya belum di-load -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        :root { --kinara-pink: #ff385c; --bg-gray: #f7f7f7; }
        body { font-family: 'Inter', sans-serif; background-color: var(--bg-gray); color: #222; margin: 0; padding: 0; }
        
        .checkout-container { max-width: 500px; margin: 50px auto; padding: 20px; }
        .card { background: white; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); overflow: hidden; }
        
        .card-header { background: #fff; padding: 30px 30px 10px; text-align: center; }
        .brand-logo { font-size: 24px; font-weight: 800; color: var(--kinara-pink); letter-spacing: -1px; margin-bottom: 10px; display: block; text-decoration: none; }
        
        .summary-box { padding: 0 30px 30px; }
        .unit-info { background: #fff; border: 1px solid #eee; border-radius: 12px; padding: 15px; display: flex; align-items: center; gap: 15px; margin-bottom: 25px; }
        .unit-img { width: 70px; height: 70px; border-radius: 10px; object-fit: cover; }
        .unit-name { font-weight: 700; font-size: 16px; margin: 0; }
        .unit-loc { font-size: 13px; color: #666; margin: 2px 0 0; }

        .price-details { border-top: 1px dashed #ddd; padding-top: 20px; }
        .price-row { display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 14px; color: #555; }
        .price-total { display: flex; justify-content: space-between; margin-top: 15px; font-size: 18px; font-weight: 700; color: #222; border-top: 1px solid #eee; padding-top: 15px; }

        .btn-pay { width: 100%; background: var(--kinara-pink); color: white; border: none; padding: 18px; border-radius: 15px; font-size: 16px; font-weight: 700; cursor: pointer; transition: 0.3s; margin-top: 30px; display: flex; align-items: center; justify-content: center; gap: 10px; }
        .btn-pay:hover { background: #e31c5f; transform: translateY(-2px); }

        .secure-badge { text-align: center; margin-top: 20px; font-size: 12px; color: #999; display: flex; align-items: center; justify-content: center; gap: 8px; }
        .secure-badge i { color: #27ae60; }

        @media (max-width: 600px) { .checkout-container { margin: 0; width: 100%; } .card { border-radius: 0; min-height: 100vh; } }
    </style>
</head>
<body>

<div class="checkout-container">
    <div class="card">
        <div class="card-header">
            <a href="index.php" class="brand-logo">KINARA</a>
            <h2 style="font-size: 20px; margin-top: 20px;">Konfirmasi Pembayaran</h2>
            <p style="color: #666; font-size: 14px;">Silakan periksa detail pesanan Anda</p>
        </div>

        <div class="summary-box">
            <div class="unit-info">
                <img src="img/<?= $k['gambar']; ?>" class="unit-img">
                <div>
                    <p class="unit-name"><?= $k['nama_kamar']; ?></p>
                    <p class="unit-loc"><i class="fas fa-map-marker-alt"></i> <?= $k['lokasi']; ?></p>
                </div>
            </div>

            <div class="price-details">
                <div class="price-row">
                    <span>Biaya Sewa (1 Bulan)</span>
                    <span>Rp <?= number_format($k['harga'], 0, ',', '.'); ?></span>
                </div>
                <div class="price-row">
                    <span>Biaya Layanan & Admin</span>
                    <span style="color: #27ae60;">Gratis</span>
                </div>
                <div class="price-total">
                    <span>Total Pembayaran</span>
                    <span style="color: var(--kinara-pink);">Rp <?= number_format($total_bayar, 0, ',', '.'); ?></span>
                </div>
            </div>

            <button id="pay-button" class="btn-pay">
                <i class="fas fa-lock"></i> BAYAR SEKARANG
            </button>

            <div class="secure-badge">
                <i class="fas fa-shield-alt"></i> Pembayaran Terenkripsi & Aman oleh Midtrans
            </div>
            
            <p style="text-align: center; font-size: 11px; color: #ccc; margin-top: 40px;">
                ORDER ID: <?= $order_id; ?>
            </p>
        </div>
    </div>
</div>

<script type="text/javascript">
    var payButton = document.getElementById('pay-button');
    payButton.onclick = function () {
        window.snap.pay('<?= $snapToken; ?>', {
            onSuccess: function(result){
                Swal.fire('Berhasil!', 'Pembayaran telah kami terima.', 'success').then(() => {
                    window.location.href = "update_otomatis.php?order_id=" + result.order_id + 
                           "&id_kamar=<?= $id_kamar; ?>" + 
                           "&nominal=<?= $total_bayar; ?>";
                });
            },
            onPending: function(result){
                alert("Menunggu pembayaran Anda...");
            },
            onError: function(result){
                alert("Pembayaran Gagal!");
            }
        });
    };
</script>

</body>
</html>
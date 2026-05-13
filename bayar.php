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

// Konfigurasi Midtrans
\Midtrans\Config::$serverKey = $env['MIDTRANS_SERVER_KEY'];
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

// ==========================================
// 2. TANGKAP DATA DARI FORM PROSES_BOOKING.PHP
// ==========================================
$nama_penyewa   = isset($_POST['nama_penyewa']) ? mysqli_real_escape_string($conn, $_POST['nama_penyewa']) : $_SESSION['nama'];
$kontak_penyewa = isset($_POST['kontak_penyewa']) ? mysqli_real_escape_string($conn, $_POST['kontak_penyewa']) : '';
$alamat_penyewa = isset($_POST['alamat_penyewa']) ? mysqli_real_escape_string($conn, $_POST['alamat_penyewa']) : '-';
$lama_sewa      = isset($_POST['lama_sewa']) ? (int)$_POST['lama_sewa'] : 1;
$diskon_nominal = isset($_POST['diskon_nominal']) ? (int)$_POST['diskon_nominal'] : 0;
$email_penyewa  = isset($_SESSION['email']) ? $_SESSION['email'] : 'user@mail.com';

// Harga dinamis hasil perhitungan form sebelumnya
$total_bayar_post = isset($_POST['total_bayar']) ? (int)$_POST['total_bayar'] : $k['harga'];

// Jika total bayar <= 0 (karena promo FREE), atur logika khusus
$total_bayar = ($total_bayar_post <= 0) ? 0 : $total_bayar_post;
$order_id = "KINARA-" . time();

// Simpan ke session sementara untuk update database setelah bayar berhasil
$_SESSION['temp_booking'] = [
    'nama'      => $nama_penyewa,
    'kontak'    => $kontak_penyewa,
    'alamat'    => $alamat_penyewa,
    'lama_sewa' => $lama_sewa,
    'diskon'    => $diskon_nominal,
    'id_kamar'  => $id_kamar,
    'total'     => $total_bayar
];

// ==========================================
// 3. BUAT TRANSAKSI MIDTRANS
// ==========================================
$snapToken = "";
if ($total_bayar > 0) {
    $params = [
        'transaction_details' => [
            'order_id' => $order_id,
            'gross_amount' => $total_bayar,
        ],
        'customer_details' => [
            'first_name' => $nama_penyewa,
            'email' => $email_penyewa,
            'phone' => $kontak_penyewa
        ],
        'enabled_payments' => ['qris', 'bank_transfer', 'gopay', 'shopeepay']
    ];
    $snapToken = \Midtrans\Snap::getSnapToken($params);
}

$clientKey = $env['MIDTRANS_CLIENT_KEY'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout Pembayaran - Kinara Kost</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script type="text/javascript" src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="<?= $clientKey ?>"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        :root { --kinara-pink: #ff385c; --kinara-teal: #1abc9c; --bg-gray: #f4f7f6; }
        body { font-family: 'Inter', sans-serif; background-color: var(--bg-gray); color: #222; margin: 0; padding: 0; }
        
        .checkout-container { max-width: 520px; margin: 40px auto; padding: 20px; }
        .card { background: white; border-radius: 24px; box-shadow: 0 15px 35px rgba(0,0,0,0.06); border: 1px solid rgba(0,0,0,0.02); overflow: hidden; }
        
        .card-header { background: #fff; padding: 35px 30px 15px; text-align: center; }
        .brand-logo { font-size: 26px; font-weight: 800; color: var(--kinara-pink); letter-spacing: -1px; margin-bottom: 15px; display: block; text-decoration: none; }
        
        .summary-box { padding: 0 30px 30px; }
        
        /* PREMIUM UNIT INFO */
        .unit-info { background: linear-gradient(145deg, #ffffff, #f8f9fa); border: 1px solid #eee; border-radius: 16px; padding: 18px; display: flex; align-items: center; gap: 18px; margin-bottom: 25px; box-shadow: 0 4px 15px rgba(0,0,0,0.02); }
        .unit-img { width: 80px; height: 80px; border-radius: 12px; object-fit: cover; box-shadow: 0 4px 10px rgba(0,0,0,0.08); }
        .unit-name { font-weight: 800; font-size: 17px; color: #222; margin: 0 0 5px; }
        .unit-loc { font-size: 13px; color: #666; margin: 0; display: flex; align-items: center; gap: 5px; }

        /* PREMIUM USER DETAILS LIST */
        .user-details { background: #fff; border: 1px solid #eaeaea; border-radius: 16px; padding: 0; margin-bottom: 25px; overflow: hidden; }
        .user-row { display: flex; justify-content: space-between; align-items: center; padding: 15px 20px; border-bottom: 1px solid #f4f4f4; }
        .user-row:last-child { border-bottom: none; }
        .user-label { color: #777; font-size: 13px; display: flex; align-items: center; gap: 10px; width: 45%; }
        .user-label i { color: #ccc; width: 16px; text-align: center; font-size: 14px; }
        .user-value { font-weight: 700; color: #222; font-size: 14px; text-align: right; width: 55%; word-wrap: break-word; }

        /* RECEIPT STYLE PRICE DETAILS */
        .price-details { background: #fdfdfd; border-radius: 16px; padding: 20px; border: 1px dashed #ddd; margin-bottom: 25px; }
        .price-row { display: flex; justify-content: space-between; margin-bottom: 12px; font-size: 14px; color: #555; }
        .price-total { display: flex; justify-content: space-between; margin-top: 15px; font-size: 20px; font-weight: 800; color: #222; border-top: 2px dashed #eee; padding-top: 15px; }

        /* BUTTONS */
        .btn-pay { width: 100%; background: var(--kinara-pink); color: white; border: none; padding: 18px; border-radius: 16px; font-size: 16px; font-weight: 800; cursor: pointer; transition: 0.3s; margin-top: 10px; display: flex; align-items: center; justify-content: center; gap: 10px; box-shadow: 0 8px 20px rgba(255, 56, 92, 0.2); }
        .btn-pay:hover { background: #e31c5f; transform: translateY(-3px); box-shadow: 0 12px 25px rgba(255, 56, 92, 0.3); }

        .secure-badge { text-align: center; margin-top: 20px; font-size: 12px; color: #999; display: flex; align-items: center; justify-content: center; gap: 8px; font-weight: 500; }
        .secure-badge i { color: #27ae60; font-size: 14px;}

        @media (max-width: 600px) { 
            .checkout-container { margin: 0; width: 100%; padding: 0; } 
            .card { border-radius: 0; min-height: 100vh; border: none; box-shadow: none;} 
            .card-header { padding-top: 50px; }
        }
    </style>
</head>
<body>

<div class="checkout-container">
    <div class="card">
        <div class="card-header">
            <a href="index.php" class="brand-logo">KINARA</a>
            <h2 style="font-size: 22px; font-weight: 800; margin-top: 15px; color: #222;">Konfirmasi Pembayaran</h2>
            <p style="color: #777; font-size: 14px; margin-top: 5px;">Silakan periksa kembali detail pesanan Anda</p>
        </div>

        <div class="summary-box">
            
            <div class="unit-info">
                <img src="img/<?= $k['gambar']; ?>" class="unit-img">
                <div>
                    <p class="unit-name"><?= $k['nama_kamar']; ?></p>
                    <p class="unit-loc"><i class="fas fa-map-marker-alt text-danger"></i> <?= $k['lokasi']; ?></p>
                </div>
            </div>

            <div class="user-details">
                <div class="user-row">
                    <span class="user-label"><i class="fas fa-user"></i> Nama Lengkap</span>
                    <span class="user-value"><?= $nama_penyewa; ?></span>
                </div>
                <div class="user-row">
                    <span class="user-label"><i class="fas fa-envelope"></i> Email</span>
                    <span class="user-value"><?= $email_penyewa; ?></span>
                </div>
                <div class="user-row">
                    <span class="user-label"><i class="fas fa-phone-alt"></i> No. Handphone</span>
                    <span class="user-value"><?= $kontak_penyewa; ?></span>
                </div>
                <div class="user-row">
                    <span class="user-label"><i class="far fa-calendar-check"></i> Lama Sewa</span>
                    <span class="user-value"><?= $lama_sewa; ?> Bulan</span>
                </div>
                <div class="user-row">
                    <span class="user-label"><i class="fas fa-home"></i> Alamat</span>
                    <span class="user-value" style="font-weight: 500;"><?= $alamat_penyewa; ?></span>
                </div>
            </div>

            <div class="price-details">
                <div class="price-row">
                    <span>Biaya Sewa (<?= $lama_sewa; ?> Bulan)</span>
                    <span style="font-weight: 600;">Rp <?= number_format($k['harga'] * $lama_sewa, 0, ',', '.'); ?></span>
                </div>
                
                <?php if($diskon_nominal > 0): ?>
                <div class="price-row">
                    <span>Diskon Promo</span>
                    <span style="color: var(--kinara-teal); font-weight: 700;">- Rp <?= number_format($diskon_nominal, 0, ',', '.'); ?></span>
                </div>
                <?php endif; ?>

                <div class="price-row">
                    <span>Biaya Layanan & Admin</span>
                    <span style="color: #27ae60; font-weight: 600;">Gratis</span>
                </div>
                
                <div class="price-total">
                    <span>Total Pembayaran</span>
                    <span style="color: var(--kinara-pink);">
                        <?= $total_bayar <= 0 ? 'FREE' : 'Rp ' . number_format($total_bayar, 0, ',', '.'); ?>
                    </span>
                </div>
            </div>

            <?php if($total_bayar > 0): ?>
                <button id="pay-button" class="btn-pay">
                    <i class="fas fa-lock"></i> BAYAR SEKARANG
                </button>
            <?php else: ?>
                <button id="free-button" class="btn-pay" style="background: #27ae60; box-shadow: 0 8px 20px rgba(39, 174, 96, 0.2);">
                    <i class="fas fa-check-circle"></i> KONFIRMASI (GRATIS)
                </button>
            <?php endif; ?>

            <div class="secure-badge">
                <i class="fas fa-shield-check"></i> Pembayaran Terenkripsi & Aman oleh Midtrans
            </div>
            
            <p style="text-align: center; font-size: 11px; color: #bbb; margin-top: 35px; letter-spacing: 0.5px;">
                ORDER ID: <?= $order_id; ?>
            </p>
        </div>
    </div>
</div>

<script type="text/javascript">
    const totalBayar = <?= $total_bayar ?>;
    
    if (totalBayar > 0) {
        var payButton = document.getElementById('pay-button');
        payButton.onclick = function () {
            window.snap.pay('<?= $snapToken; ?>', {
                onSuccess: function(result){
                    Swal.fire({
                        title: 'Berhasil!',
                        text: 'Pembayaran telah kami terima.',
                        icon: 'success',
                        confirmButtonColor: '#ff385c'
                    }).then(() => {
                        window.location.href = "update_otomatis.php?order_id=" + result.order_id + 
                               "&id_kamar=<?= $id_kamar; ?>" + 
                               "&nominal=<?= $total_bayar; ?>";
                    });
                },
                onPending: function(result){
                    Swal.fire('Menunggu', 'Selesaikan pembayaran Anda.', 'info');
                },
                onError: function(result){
                    Swal.fire('Gagal!', 'Terjadi kesalahan saat pembayaran.', 'error');
                },
                onClose: function(){
                    // User menutup popup midtrans
                }
            });
        };
    } else {
        // Logika jika total pembayaran = FREE (Rp 0)
        var freeButton = document.getElementById('free-button');
        if(freeButton) {
            freeButton.onclick = function() {
                Swal.fire({
                    title: 'Berhasil!',
                    text: 'Pesanan gratis Anda telah dikonfirmasi.',
                    icon: 'success',
                    confirmButtonColor: '#27ae60'
                }).then(() => {
                    window.location.href = "update_otomatis.php?order_id=<?= $order_id; ?>" + 
                           "&id_kamar=<?= $id_kamar; ?>" + 
                           "&nominal=0";
                });
            }
        }
    }
</script>

</body>
</html>
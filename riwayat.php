<?php
session_start();
include 'config.php';

// Proteksi halaman, pastikan user sudah login
if (!isset($_SESSION['login'])) {
    header("Location: index.php");
    exit;
}

// Ambil ID User secara presisi
$id_user = isset($_SESSION['id']) ? $_SESSION['id'] : (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0);

// Ambil data riwayat pesanan dari database
$query_riwayat = mysqli_query($conn, "SELECT p.*, k.nama_kamar, k.gambar 
                                      FROM pembayaran p 
                                      JOIN kamar k ON p.id_kamar = k.id 
                                      WHERE p.id_user = '$id_user' 
                                      ORDER BY p.id DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pemesanan - Kinara Kost</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        /* =========================================
           UI/UX RIWAYAT PEMESANAN (HISTORY)
           ========================================= */
        :root { --kinara-pink: #ff385c; --kinara-teal: #1abc9c; --bg-gray: #f4f7f6; }
        body { font-family: 'Inter', sans-serif; background-color: var(--bg-gray); margin: 0; padding: 0; }
        
        .navbar-simple { background: #fff; padding: 15px 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 100; }
        .brand-logo { font-size: 22px; font-weight: 800; color: var(--kinara-pink); text-decoration: none; letter-spacing: -0.5px; }
        .btn-back { background: #fff; border: 1.5px solid #eaeaea; color: #333; text-decoration: none; padding: 8px 15px; border-radius: 10px; font-size: 13px; font-weight: 700; transition: 0.2s; display: flex; align-items: center; gap: 8px; }
        .btn-back:hover { background: #f9f9f9; border-color: #ddd; color: #000; }

        .history-container { max-width: 800px; margin: 40px auto; padding: 0 20px; }
        .history-header { margin-bottom: 30px; border-bottom: 2px solid #eee; padding-bottom: 15px; }
        .history-title { font-size: 24px; font-weight: 800; color: #222; margin: 0; }

        .history-card { background: #fff; border: 1px solid #eaeaea; border-radius: 16px; padding: 20px; margin-bottom: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.02); display: flex; flex-direction: column; gap: 15px; transition: transform 0.3s ease; }
        .history-card:hover { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(0,0,0,0.05); border-color: #e0e0e0; }
        
        .history-card-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px dashed #eee; padding-bottom: 15px; }
        .history-order-id { font-size: 13px; font-weight: 700; color: #555; }
        .history-date { font-size: 13px; color: #999; font-weight: 500; }

        .history-card-body { display: flex; gap: 20px; align-items: center; }
        .history-img { width: 85px; height: 85px; border-radius: 12px; object-fit: cover; border: 1px solid #eee; }
        .history-details { flex: 1; }
        .history-room-name { font-size: 18px; font-weight: 800; color: #222; margin: 0 0 5px 0; }
        .history-price { font-size: 15px; font-weight: 700; color: var(--kinara-pink); margin: 0; }

        .history-card-footer { display: flex; justify-content: space-between; align-items: center; padding-top: 15px; border-top: 1px dashed #eee; }
        
        .status-badge { padding: 6px 14px; border-radius: 8px; font-size: 12px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; }
        .status-berhasil { background: rgba(26, 188, 156, 0.1); color: var(--kinara-teal); }
        .status-pending { background: rgba(243, 156, 18, 0.1); color: #f39c12; }
        .status-gagal { background: rgba(231, 76, 60, 0.1); color: #e74c3c; }

        .btn-history-action { background: #222; color: #fff; text-decoration: none; padding: 12px 20px; border-radius: 12px; font-size: 13px; font-weight: 700; transition: 0.3s; display: inline-flex; align-items: center; gap: 8px; }
        .btn-history-action:hover { background: #000; transform: translateY(-2px); box-shadow: 0 4px 10px rgba(0,0,0,0.15); }
        .btn-wa { background: #27ae60; }
        .btn-wa:hover { background: #219653; }

        /* Responsif Mobile */
        @media (max-width: 576px) {
            .history-card-body { align-items: flex-start; }
            .history-img { width: 70px; height: 70px; }
            .history-room-name { font-size: 16px; }
            .history-card-footer { flex-direction: column; align-items: flex-start; gap: 15px; }
            .btn-history-action { width: 100%; justify-content: center; box-sizing: border-box; }
        }
    </style>
</head>
<body>

<nav class="navbar-simple">
    <a href="index.php" class="brand-logo">KINARA</a>
    <a href="index.php" class="btn-back translatable" data-en="Back to Home"><i class="fas fa-arrow-left"></i> Kembali</a>
</nav>

<div class="history-container">
    <div class="history-header">
        <h1 class="history-title translatable" data-en="Order History"><i class="fas fa-history me-2" style="color: var(--kinara-pink);"></i> Riwayat Pesanan</h1>
    </div>

    <?php if (mysqli_num_rows($query_riwayat) > 0): ?>
        <?php while($row = mysqli_fetch_assoc($query_riwayat)): ?>
            
            <?php 
                // Menentukan class warna badge berdasarkan status
                $status = strtolower($row['status_pembayaran']);
                $badge_class = 'status-pending';
                if ($status == 'berhasil') $badge_class = 'status-berhasil';
                if ($status == 'gagal') $badge_class = 'status-gagal';

                // Logika tanggal anti-error
                $waktu = isset($row['tanggal_bayar']) ? $row['tanggal_bayar'] : (isset($row['tanggal']) ? $row['tanggal'] : 'now');
            ?>

            <div class="history-card">
                <div class="history-card-header">
                    <span class="history-order-id">ORDER ID: <?= htmlspecialchars($row['order_id'] ?? '-'); ?></span>
                    <span class="history-date"><i class="far fa-calendar-alt me-1"></i> <?= date('d M Y', strtotime($waktu)); ?></span>
                </div>
                
                <div class="history-card-body">
                    <img src="img/<?= htmlspecialchars($row['gambar']); ?>" class="history-img" alt="Kamar">
                    <div class="history-details">
                        <h4 class="history-room-name translatable" data-en="<?= str_ireplace('Kamar', 'Room', htmlspecialchars($row['nama_kamar'])); ?>">
                            <?= htmlspecialchars($row['nama_kamar']); ?>
                        </h4>
                        <p class="history-price">
                            <?= $row['jumlah_bayar'] <= 0 ? 'FREE' : 'Rp ' . number_format($row['jumlah_bayar'], 0, ',', '.'); ?>
                        </p>
                    </div>
                </div>

                <div class="history-card-footer">
                    <span class="status-badge <?= $badge_class; ?> translatable" data-en="<?= ($status == 'berhasil') ? 'SUCCESS' : strtoupper($status); ?>">
                        <i class="fas fa-circle me-1" style="font-size: 8px; vertical-align: middle;"></i> <?= strtoupper($status); ?>
                    </span>
                    
                    <?php if ($status == 'berhasil'): ?>
                        <a href="sukses.php?order_id=<?= $row['order_id']; ?>" class="btn-history-action translatable" data-en="View Receipt">
                            <i class="fas fa-receipt"></i> Lihat Struk
                        </a>
                    <?php else: ?>
                        <a href="https://wa.me/6289516792463" class="btn-history-action btn-wa translatable" data-en="Contact Admin">
                            <i class="fab fa-whatsapp"></i> Hubungi Admin
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div style="text-align: center; padding: 60px 20px; background: #fff; border-radius: 20px; border: 1px dashed #ddd; box-shadow: 0 10px 30px rgba(0,0,0,0.02);">
            <i class="fas fa-box-open" style="font-size: 60px; color: #eee; margin-bottom: 20px;"></i>
            <h3 class="translatable" data-en="No orders yet" style="margin: 0 0 10px; color: #222; font-weight: 800;">Belum ada pesanan</h3>
            <p class="translatable" data-en="You haven't booked any rooms yet. Let's find your dream room!" style="color: #777; font-size: 15px; margin-bottom: 25px;">Anda belum melakukan pemesanan kamar apa pun. Yuk cari kamar impianmu!</p>
            <a href="index.php" class="btn-history-action translatable" data-en="Explore Rooms"><i class="fas fa-search"></i> Mulai Cari Kamar</a>
        </div>
    <?php endif; ?>
</div>

<script>
// Logika Bilingual (Bahasa)
document.addEventListener('DOMContentLoaded', () => {
    const savedLang = localStorage.getItem('kinara_lang') || 'id';
    if (savedLang === 'en') {
        document.querySelectorAll('.translatable').forEach(el => {
            if(el.getAttribute('data-en')) {
                // Jangan hilangkan icon jika ada di dalam elemen
                const iconMatch = el.innerHTML.match(/<i.*?<\/i>/);
                if (iconMatch) {
                    el.innerHTML = iconMatch[0] + ' ' + el.getAttribute('data-en');
                } else {
                    el.innerText = el.getAttribute('data-en');
                }
            }
        });
    }
});
</script>

</body>
</html>
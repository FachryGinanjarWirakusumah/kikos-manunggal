<?php
session_start();
include 'config.php';

$id_kamar = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$query = mysqli_query($conn, "SELECT * FROM kamar WHERE id = $id_kamar");
$k = mysqli_fetch_assoc($query);

if (!$k) {
    echo "<script>alert('Kamar tidak ditemukan!'); window.location='cek_kamar.php';</script>";
    exit;
}

$query_galeri = mysqli_query($conn, "SELECT * FROM galeri_kamar WHERE id_kamar = $id_kamar ORDER BY id ASC");

$all_media = [];
$all_media[] = ['src' => 'img/'.$k['gambar'], 'type' => 'foto'];

while($g = mysqli_fetch_assoc($query_galeri)) {
    $all_media[] = [
        'src' => 'img/galeri/'.$g['file_name'],
        'type' => $g['tipe_file']
    ];
}

$lokasi_sekarang = mysqli_real_escape_string($conn, $k['lokasi']);
$query_rekomendasi = mysqli_query($conn, "SELECT * FROM kamar WHERE lokasi = '$lokasi_sekarang' AND id != $id_kamar LIMIT 4");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail <?= $k['nama_kamar']; ?> - Kinara Kost</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --kinara-pink: #ff385c; --kinara-teal: #1abc9c; }
        body { font-family: 'Inter', sans-serif; background-color: #fff; color: #222; }
        .container { max-width: 1100px; margin: 0 auto; padding: 20px; }
        
        .gallery-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 10px; margin-top: 100px; border-radius: 15px; overflow: hidden; height: 450px; position: relative; }
        .main-img-wrapper { width: 100%; height: 100%; overflow: hidden; cursor: pointer; }
        .main-img { width: 100%; height: 100%; object-fit: cover; transition: 0.3s; }
        .side-gallery { display: grid; grid-template-rows: 1fr 1fr; gap: 10px; }
        .side-img-wrapper { width: 100%; height: 100%; overflow: hidden; cursor: pointer; position: relative; }
        .side-img { width: 100%; height: 100%; object-fit: cover; transition: 0.3s; }
        .more-photos-overlay { position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); color: white; display: flex; align-items: center; justify-content: center; font-size: 20px; font-weight: 700; }
        .btn-view-all { position: absolute; bottom: 20px; right: 20px; background: white; padding: 8px 16px; border-radius: 8px; font-size: 14px; font-weight: 600; border: 1px solid #222; cursor: pointer; z-index: 10; display: flex; align-items: center; gap: 8px; }

        .content-wrapper { display: grid; grid-template-columns: 1.8fr 1.2fr; gap: 50px; margin-top: 30px; }
        .kamar-title { font-size: 32px; font-weight: 700; margin-bottom: 5px; }
        .location-text { color: #666; display: flex; align-items: center; gap: 5px; margin-bottom: 20px; }
        .section-title { font-size: 20px; font-weight: 700; margin: 30px 0 15px; border-bottom: 1px solid #eee; padding-bottom: 10px; }
        
        .fasilitas-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; }
        .fasilitas-item { display: flex; align-items: center; gap: 12px; font-size: 15px; color: #444; }
        .fasilitas-item i { width: 20px; color: var(--kinara-teal); font-size: 18px; }

        .booking-card { border: 1px solid #ddd; padding: 25px; border-radius: 15px; box-shadow: 0 10px 25px rgba(0,0,0,0.07); position: sticky; top: 120px; background: white; }
        .price-big { font-size: 24px; font-weight: 700; color: var(--kinara-pink); }
        .btn-confirm { width: 100%; background: var(--kinara-pink); color: white; border: none; padding: 15px; border-radius: 10px; font-weight: 700; cursor: pointer; margin-top: 15px; }

        .promo-section { margin-top: 60px; padding-top: 30px; border-top: 1px solid #eee; }
        .rekomendasi-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 20px; margin-top: 20px; }
        .reko-card { border-radius: 12px; overflow: hidden; border: 1px solid #eee; text-decoration: none; color: inherit; transition: 0.3s; background: #fff; }
        .reko-card:hover { transform: translateY(-5px); box-shadow: 0 8px 20px rgba(0,0,0,0.1); }
        .reko-img { width: 100%; height: 150px; object-fit: cover; }
        .reko-info { padding: 15px; }

        .lightbox { display: none; position: fixed; z-index: 10000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.95); }
        .lightbox-content { position: relative; width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; flex-direction: column; }
        .lightbox-media { max-width: 90%; max-height: 75vh; border-radius: 8px; }
        .lightbox-close { position: absolute; top: 30px; right: 30px; color: white; font-size: 40px; cursor: pointer; }
        .lightbox-nav { position: absolute; width: 100%; display: flex; justify-content: space-between; padding: 0 30px; pointer-events: none; }
        .nav-btn { background: rgba(255,255,255,0.1); color: white; border: none; width: 60px; height: 60px; border-radius: 50%; cursor: pointer; font-size: 24px; pointer-events: auto; }
        .media-counter { color: white; margin-top: 20px; font-size: 14px; font-weight: 600; background: rgba(255,255,255,0.1); padding: 5px 15px; border-radius: 20px; }
    </style>
</head>
<body>

<nav class="navbar">
    <div class="nav-container">
        <div class="logo"><a href="index.php" style="text-decoration:none; color:inherit;">KINARA</a></div>
        <ul class="nav-links">
            <li><a href="index.php">Home</a></li>
            <li><a href="cek_kamar.php">Cek Unit Kamar</a></li>
        </ul>
    </div>
</nav>

<div class="container">
    <div class="gallery-grid">
        <div class="main-img-wrapper" onclick="openLightbox(0)">
            <img src="img/<?= $k['gambar']; ?>" class="main-img">
        </div>
        <div class="side-gallery">
            <?php 
            $media_count = count($all_media);
            for($i = 1; $i < 3; $i++):
                if(isset($all_media[$i])):
            ?>
                <div class="side-img-wrapper" onclick="openLightbox(<?= $i; ?>)">
                    <img src="<?= $all_media[$i]['src']; ?>" class="side-img">
                    <?php if($i == 2 && $media_count > 3): ?>
                        <div class="more-photos-overlay">+<?= $media_count - 3; ?> Media Lainnya</div>
                    <?php endif; ?>
                </div>
            <?php endif; endfor; ?>
        </div>
        <button class="btn-view-all" onclick="openLightbox(0)"><i class="fas fa-th"></i> Lihat semua media</button>
    </div>

    <div class="content-wrapper">
        <div class="main-info">
            <h1 class="kamar-title"><?= $k['nama_kamar']; ?></h1>
            <div class="location-text">
                <i class="fas fa-map-marker-alt text-danger"></i> <?= $k['lokasi']; ?> • 
                <span class="badge-rukita" style="background:var(--kinara-teal); margin-bottom:0;">Kost <?= ucfirst($k['tipe']); ?></span>
            </div>

            <h2 class="section-title">Fasilitas & Deskripsi</h2>
            <div class="fasilitas-grid mb-4">
                <?php 
                if(!empty($k['deskripsi'])):
                    $lines = explode("\n", str_replace("\r", "", $k['deskripsi']));
                    foreach($lines as $line):
                        $line = trim($line); if(empty($line)) continue;
                        $icon = "fa-check-circle";
                        $text = ltrim($line, '- ');
                        if(stripos($line, 'wifi') !== false) $icon = "fa-wifi";
                        elseif(stripos($line, 'kasur') !== false || stripos($line, 'bed') !== false) $icon = "fa-bed";
                        elseif(stripos($line, 'mandi') !== false) $icon = "fa-bath";
                        elseif(stripos($line, 'ac') !== false || stripos($line, 'kipas') !== false) $icon = "fa-fan";
                        elseif(stripos($line, 'listrik') !== false) $icon = "fa-bolt";
                        elseif(stripos($line, 'lemari') !== false) $icon = "fa-tshirt";
                ?>
                    <div class="fasilitas-item">
                        <i class="fas <?= $icon; ?> text-teal"></i> <?= htmlspecialchars($text); ?>
                    </div>
                <?php endforeach; else: ?>
                    <p class="text-muted">Kamar bersih dan nyaman di lokasi strategis.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="side-booking">
            <div class="booking-card">
                <div class="price-big">Rp <?= number_format($k['harga'], 0, ',', '.'); ?> <span style="font-size:14px; font-weight:400; color:#666;">/ bulan</span></div>
                <hr style="margin: 20px 0; opacity: 0.1;">
                <div style="font-size: 14px; margin-bottom: 15px;">Status Kamar: <strong class="text-success">Tersedia</strong></div>
                <button class="btn-confirm" onclick="window.location.href='bayar.php?id=<?= $id_kamar; ?>'">Booking Sekarang</button>
            </div>
        </div>
    </div>

    <div class="promo-section">
        <h2 class="section-title">Unit Lain di <?= $k['lokasi']; ?></h2>
        <div class="rekomendasi-grid">
            <?php while($rk = mysqli_fetch_assoc($query_rekomendasi)): ?>
            <a href="proses_booking.php?id=<?= $rk['id']; ?>" class="reko-card">
                <img src="img/<?= $rk['gambar']; ?>" class="reko-img">
                <div class="reko-info">
                    <div style="font-weight: 700; font-size: 15px; margin-bottom: 5px;"><?= $rk['nama_kamar']; ?></div>
                    <div style="font-size: 13px; color: var(--kinara-pink); font-weight: 600;">Rp <?= number_format($rk['harga'], 0, ',', '.'); ?></div>
                </div>
            </a>
            <?php endwhile; ?>
        </div>
    </div>
</div>

<div id="lightbox" class="lightbox">
    <span class="lightbox-close" onclick="closeLightbox()">&times;</span>
    <div class="lightbox-content">
        <div id="media-container"></div>
        <div class="media-counter" id="media-counter"></div>
        <div class="lightbox-nav">
            <button class="nav-btn" onclick="changeImage(-1, event)"><i class="fas fa-chevron-left"></i></button>
            <button class="nav-btn" onclick="changeImage(1, event)"><i class="fas fa-chevron-right"></i></button>
        </div>
    </div>
</div>

<script>
    const mediaItems = <?= json_encode($all_media); ?>;
    let currentIndex = 0;
    const lightbox = document.getElementById('lightbox');
    const mediaContainer = document.getElementById('media-container');

    function openLightbox(index) {
        currentIndex = index;
        lightbox.style.display = 'block';
        document.body.style.overflow = 'hidden';
        renderMedia();
    }

    function closeLightbox() {
        lightbox.style.display = 'none';
        document.body.style.overflow = 'auto';
        mediaContainer.innerHTML = '';
    }

    function changeImage(step, event) {
        if(event) event.stopPropagation();
        currentIndex += step;
        if (currentIndex >= mediaItems.length) currentIndex = 0;
        if (currentIndex < 0) currentIndex = mediaItems.length - 1;
        renderMedia();
    }

    function renderMedia() {
        const item = mediaItems[currentIndex];
        mediaContainer.innerHTML = '';
        if(item.type === 'video') {
            const video = document.createElement('video');
            video.src = item.src; video.controls = true; video.autoplay = true; video.className = 'lightbox-media';
            mediaContainer.appendChild(video);
        } else {
            const img = document.createElement('img');
            img.src = item.src; img.className = 'lightbox-media';
            mediaContainer.appendChild(img);
        }
        document.getElementById('media-counter').innerText = (currentIndex + 1) + ' / ' + mediaItems.length;
    }

    lightbox.addEventListener('click', function(e) {
        if (e.target === lightbox || e.target.classList.contains('lightbox-content')) closeLightbox();
    });
</script>

</body>
</html>
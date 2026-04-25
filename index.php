<?php
session_start();
include 'config.php';

// Atur judul spesifik untuk halaman ini
$page_title = "Beranda - Kinara Kost Manunggal";

// Panggil Komponen Utama
include 'components/header.php';
include 'components/navbar.php';
include 'components/modals.php';

// Ambil Data Hero Section
$h = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM hero_section WHERE id = 1"));
?>

<!-- === 1. HERO SECTION === -->
<main class="hero-section" style="background-image: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('img/<?= $h['gambar'] ?>'); background-size: cover; background-position: center;">
    <div class="hero-content text-center">
        <h1 class="fw-bold"><?= $h['judul'] ?></h1>
        <p><?= $h['sub_judul'] ?></p>
    </div>
</main>
    
<!-- === 2. PROPERTY LISTING SECTION === -->
<section class="content-container">
    <div class="filter-section">
        <div class="category-tabs">
            <button class="tab-btn active" data-target="ikhwan"><i class="fas fa-male"></i> Kost Ikhwan</button>
            <button class="tab-btn" data-target="akhwat"><i class="fas fa-female"></i> Kost Akhwat</button>
        </div>
    </div>
        
    <div class="icon-filters">
        <div class="filter-item"><i class="fas fa-wifi"></i><span>High-Speed Wi-Fi</span></div>
        <div class="filter-item"><i class="fas fa-snowflake"></i><span>AC</span></div>
        <div class="filter-item"><i class="fas fa-bath"></i><span>K. Mandi Dalam</span></div>
        <div class="filter-item"><i class="fas fa-temperature-high"></i><span>Water Heater</span></div>
        <div class="filter-item"><i class="fas fa-door-closed"></i><span>Lemari Pakaian</span></div>
        <div class="filter-item"><i class="fas fa-couch"></i><span>Meja & Kursi</span></div>
        <div class="filter-item"><i class="fas fa-tv"></i><span>TV Area Umum</span></div>
        <div class="filter-item"><i class="fas fa-sink"></i><span>Dapur Bersama</span></div>
        <div class="filter-item"><i class="fas fa-icicles"></i><span>Kulkas</span></div>
        <div class="filter-item"><i class="fas fa-tshirt"></i><span>Mesin Cuci</span></div>
        <div class="filter-item"><i class="fas fa-utensils"></i><span>Peralatan Makan</span></div>
        <div class="filter-item"><i class="fas fa-fire"></i><span>Free Gas Refill</span></div> 
        <div class="filter-item"><i class="fas fa-faucet"></i><span>Free Mineral Water</span></div>
        <div class="filter-item"><i class="fas fa-hands-bubbles"></i><span>Layanan Kebersihan</span></div>
        <div class="filter-item"><i class="fas fa-tools"></i><span>Free Maintenance</span></div>
        <div class="filter-item"><i class="fas fa-broom"></i><span>Alat Kebersihan</span></div>
    </div>

    <div class="slider-wrapper">
        <button class="slide-btn prev" id="prevBtn"><i class="fas fa-chevron-left"></i></button>
        
        <div class="property-slider" id="propertySlider">
            <?php
            $query_kamar = mysqli_query($conn, "SELECT * FROM kamar ORDER BY is_featured DESC, id DESC");
            while($kamar = mysqli_fetch_assoc($query_kamar)): 
            ?>
            <div class="property-card" data-type="<?= $kamar['tipe']; ?>" style="<?= ($kamar['tipe'] == 'akhwat') ? 'display:none;' : ''; ?>">
                <div class="card-image">
                    <img src="img/<?= $kamar['gambar']; ?>" alt="<?= $kamar['nama_kamar']; ?>" onerror="this.src='https://via.placeholder.com/500x300'">
                    
                    <?php if($kamar['is_featured']): ?>
                        <div class="badge-rukita" style="background: #ffc107; color: #000;">FEATURED</div>
                    <?php else: ?>
                        <div class="badge-rukita">KINARA</div>
                    <?php endif; ?>
                    
                    <div class="sisa-kamar">Status: <?= ucfirst($kamar['status']); ?></div>
                </div>
                <div class="card-info">
                    <div class="property-type"><i class="fas fa-<?= $kamar['tipe'] == 'ikhwan' ? 'male' : 'female'; ?>"></i> Kost <?= ucfirst($kamar['tipe']); ?></div>
                    <h3 class="property-name"><?= $kamar['nama_kamar']; ?></h3>
                    <p class="property-location"><?= $kamar['lokasi']; ?></p>
                    <div class="property-price">mulai dari <span>Rp <?= number_format($kamar['harga'], 0, ',', '.'); ?></span></div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>

        <button class="slide-btn next" id="nextBtn"><i class="fas fa-chevron-right"></i></button>
    </div>
</section>

<!-- === 3. PROMO SECTION === -->
<section class="content-container promo-section">
    <div class="section-header">
        <h2>Promo berlangsung</h2>
    </div>
    <div class="slider-wrapper">
        <button class="slide-btn prev" id="promoPrev"><i class="fas fa-chevron-left"></i></button>
        
        <div class="promo-container d-flex gap-3 overflow-auto pb-3" id="promoSlider" style="scroll-behavior: smooth; scrollbar-width: none; -ms-overflow-style: none;">
            <?php
            $q_promo = mysqli_query($conn, "SELECT * FROM promo ORDER BY id DESC");
            while($promo = mysqli_fetch_assoc($q_promo)):
            ?>
            <div class="promo-card">
                <img src="img/promo/<?= $promo['gambar']; ?>" alt="<?= $promo['judul']; ?>">
            </div>
            <?php endwhile; ?>
        </div>

        <button class="slide-btn next" id="promoNext"><i class="fas fa-chevron-right"></i></button>
    </div>
</section>

<!-- === 4. FEATURES SECTION === -->
<section class="features-section">
    <div class="container-slider"> 
        <h2 class="section-title">Keuntungan Tinggal di Kinara</h2>

        <div class="carousel-wrapper">
            <div class="features-grid" id="slider">
                <?php
                $q_fitur = mysqli_query($conn, "SELECT * FROM keuntungan ORDER BY id ASC");
                while($f = mysqli_fetch_assoc($q_fitur)):
                ?>
                <div class="feature-card">
                    <div class="image-wrapper">
                        <img src="img/fitur/<?= $f['gambar']; ?>" alt="<?= $f['judul']; ?>">
                    </div>
                    <h3><?= $f['judul']; ?></h3>
                    <p><?= $f['deskripsi']; ?></p>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
</section>

<!-- Panggil Footer (Berisi Penutup Body/HTML & Script JS) -->
<?php include 'components/footer.php'; ?>
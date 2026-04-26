<?php
session_start();
include 'config.php';
$current_page = basename($_SERVER['PHP_SELF']); 
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>kinara Kost Manunggal</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="path/ke/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>

<style>
/* MODAL BACKGROUND */
.login-modal {
    display: none;
    position: fixed;
    z-index: 9999;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.7); /* Lebih gelap agar fokus */
    backdrop-filter: blur(4px); /* Efek blur kekinian */
}

/* BOX MODAL - Update bagian ini */
.login-box {
    background: #fff;
    width: 90%; /* Gunakan persentase agar aman di mobile */
    max-width: 400px;
    margin: 5% auto; /* Kurangi margin atas agar tidak terlalu turun */
    padding: 30px; /* Sedikit dikurangi agar lebih compact */
    border-radius: 16px;
    position: relative;
    box-shadow: 0 10px 25px rgba(0,0,0,0.2);
    animation: modalSlideUp 0.4s ease-out;
    max-height: 90vh; /* Maksimal tinggi 90% layar */
    overflow-y: auto; /* Jika konten kepanjangan, bisa di-scroll di dalam box */
}

@keyframes modalSlideUp {
    from { opacity: 0; transform: translateY(30px); }
    to { opacity: 1; transform: translateY(0); }
}

/* FORM STYLING */
.login-box h3 {
    font-size: 24px;
    font-weight: 700;
    margin-bottom: 8px;
    color: #333;
}

.login-box p.subtitle {
    font-size: 14px;
    color: #666;
    margin-bottom: 25px;
}

.form-group {
    margin-bottom: 20px;
    text-align: left;
}

.form-group label {
    display: block;
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 8px;
    color: #444;
}

.form-input {
    width: 100%;
    padding: 12px 16px;
    border: 1.5px solid #e0e0e0;
    border-radius: 8px;
    font-size: 15px;
    transition: all 0.3s;
    box-sizing: border-box;
}

.form-input:focus {
    border-color: #ff385c; /* Warna aksen Kinara */
    outline: none;
    box-shadow: 0 0 0 3px rgba(255, 56, 92, 0.1);
}

/* BUTTON */
.btn-submit {
    width: 100%;
    padding: 14px;
    background: #ff385c;
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 700;
    cursor: pointer;
    transition: background 0.3s;
    margin-top: 10px;
}

.btn-submit:hover {
    background: #e31c5f;
}

/* CLOSE BUTTON */
.close-btn {
    position: absolute;
    right: 20px;
    top: 20px;
    font-size: 24px;
    color: #999;
    cursor: pointer;
    transition: 0.2s;
}

.close-btn:hover {
    color: #333;
}

/* LINK DAFTAR */
.signup-text {
    margin-top: 25px;
    font-size: 14px;
    color: #666; /* Warna abu-abu gelap agar terbaca */
    text-align: center; /* Supaya posisinya di tengah */
    display: block; /* Memastikan elemen mengambil ruang */
}

.signup-text a {
    color: #ff385c; /* Warna pink/merah Kinara */
    text-decoration: none;
    font-weight: 600;
    cursor: pointer;
}

.signup-text a:hover {
    text-decoration: underline;
}

/* Container utama promo */
.promo-container {
    display: flex;
    gap: 20px;
    padding: 10px 5px;
    /* Sembunyikan scrollbar tapi tetap bisa scroll */
    -webkit-overflow-scrolling: touch; 
}

.promo-container::-webkit-scrollbar {
    display: none; /* Sembunyikan scrollbar di Chrome/Safari */
}

/* Kotak pembungkus gambar promo */
.promo-card {
    flex: 0 0 80%; /* Menampilkan 80% lebar layar agar gambar berikutnya sedikit terlihat */
    max-width: 400px; /* Batas maksimal biar tidak terlalu raksasa di desktop */
    border-radius: 15px;
    overflow: hidden;
    transition: transform 0.3s ease;
}

/* Gambar di dalam promo card */
.promo-card img {
    width: 100%;
    /* Gunakan aspect-ratio agar tinggi foto proporsional dengan lebarnya */
    aspect-ratio: 16 / 9; /* Rasio landscape standar, sesuaikan jika banner-mu berbeda */
    
    display: block;
    border-radius: 15px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    
    /* object-fit: cover;  <-- HAPUS INI jika fotonya jadi penyok */
    /* Gunakan object-fit: contain; jika ingin seluruh isi foto terlihat tanpa terpotong, 
       tapi akan ada ruang kosong (hitam/putih) di pinggir jika rasionya beda. */
       
    /* ATAU, jika ingin tetap cover (memenuhi kotak) tapi rapi di tengah: */
    object-fit: cover; 
    object-position: center; /* Memastikan bagian tengah foto yang jadi fokus */
    
    transition: filter 0.3s ease, transform 0.3s ease;
}

.promo-card:hover img {
    filter: brightness(90%); /* Sedikit menggelap saat dihover */
}

/* Efek hover dikit biar cakep */
.promo-card:hover {
    transform: scale(1.02);
}

/* Styling Modal Zoom */
.img-zoom-modal {
    display: none; /* TETAPKAN display: none sebagai default */
    position: fixed; 
    z-index: 10000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.85);
    backdrop-filter: blur(8px);
    
    /* Gunakan flexbox untuk posisi tengah */
    justify-content: center;
    align-items: center;
    
    opacity: 0;
    transition: opacity 0.3s ease;
}

.img-zoom-modal.show {
    display: flex !important; /* Paksa muncul sebagai flex */
    opacity: 1;
}

/* 2. Konten Gambar (Fotonya) */
.img-modal-content {
    /* Ukuran responsif yang rapi */
    width: auto;
    height: auto;
    max-width: 90%;  /* Beri margin 5% kiri-kanan */
    max-height: 85vh; /* Beri margin 7.5vh atas-bawah, agar caption muat */
    
    display: block;
    border-radius: 16px; /* Samakan dengan style modal login */
    box-shadow: 0 20px 50px rgba(0,0,0,0.5); /* Bayangan tegas agar "melayang" */
    
    /* Animasi muncul melayang dari bawah */
    transform: scale(0.9) translateY(20px);
    transition: transform 0.3s ease;
}

/* State gambar saat modal aktif */
.img-zoom-modal.show .img-modal-content {
    transform: scale(1) translateY(0);
}

/* 3. Keterangan Gambar (Caption) */
#imgCaption {
    position: absolute;
    bottom: 20px; /* Letakkan di bawah foto */
    left: 50%;
    transform: translateX(-50%);
    width: 80%;
    max-width: 700px;
    text-align: center;
    color: #fff;
    background: rgba(0,0,0,0.5); /* Background gelap transparan agar teks terbaca */
    padding: 10px 20px;
    border-radius: 50px;
    font-size: 14px;
    font-weight: 600;
    letter-spacing: 0.5px;
}

/* 4. Tombol Tutup (X) */
.img-close {
    position: absolute;
    top: 20px;
    right: 25px;
    color: rgba(255,255,255,0.6);
    font-size: 35px;
    font-weight: 300; /* Lebih tipis lebih elegan */
    cursor: pointer;
    transition: 0.2s;
    z-index: 10001; /* Di atas gambar */
    
    /* Lingkaran background tipis agar mudah diklik */
    background: rgba(255,255,255,0.1);
    width: 50px;
    height: 50px;
    display: flex;
    justify-content: center;
    align-items: center;
    border-radius: 50%;
}

.img-close:hover {
    color: #fff;
    background: rgba(255,255,255,0.2);
    transform: rotate(90deg); /* Animasi putar dikit */
}

/* Penyesuaian Mobile */
@media (max-width: 576px) {
    .img-modal-content {
        max-width: 95%;
        max-height: 75vh;
    }
    #imgCaption {
        font-size: 12px;
        width: 90%;
        bottom: 10px;
    }
    .img-close {
        top: 10px;
        right: 10px;
        width: 40px;
        height: 40px;
        font-size: 28px;
    }
}

@keyframes zoomIn {
    from {transform:scale(0.5); opacity:0} 
    to {transform:scale(1); opacity:1}
}

/* Kursor pointer saat hover di promo agar user tau bisa diklik */
.promo-card { cursor: pointer; }

/* Responsif untuk layar Desktop */
@media (min-width: 768px) {
    .promo-card {
        flex: 0 0 45%; /* Di desktop tampil 2-3 gambar sekaligus */
    }
}

/* ANIMASI */
@keyframes fadeIn {
    from {opacity: 0; transform: translateY(-20px);}
    to {opacity: 1; transform: translateY(0);}
}

.hero-section {
    height: 80vh;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    text-align: center;
    /* Kita hilangkan background-image static karena sudah dipanggil lewat inline style PHP di atas */
}

.hero-section h1 {
    font-size: 3.5rem;
    margin-bottom: 1rem;
    text-shadow: 0 2px 10px rgba(0,0,0,0.3);
}

.hero-section p {
    font-size: 1.2rem;
    opacity: 0.9;
}

/* Wrapper Navbar untuk mengatur letak di Desktop */
.nav-menu-wrapper {
    display: flex;
    flex: 1;
    justify-content: space-between;
    align-items: center;
}

/* =========================================
   UI/UX RESPONSIVE LAYOUT (MOBILE FIRST)
   ========================================= */

/* 1. Global Box Sizing */
*, *::before, *::after { box-sizing: border-box; }

/* 2. Hamburger Menu (Hidden di Desktop) */
.menu-toggle {
    display: none;
    font-size: 24px;
    cursor: pointer;
    color: var(--kinara-pink, #ff385c);
}

/* 3. Filter Icon Horizontal Scroll (UX Swipe) */
.icon-filters {
    display: flex;
    gap: 20px;
    overflow-x: auto;
    padding-bottom: 10px;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: none; /* Sembunyikan scrollbar di Firefox */
}
.icon-filters::-webkit-scrollbar { display: none; } /* Sembunyikan di Chrome/Safari */
.filter-item { flex: 0 0 auto; } /* Mencegah icon menyusut di layar kecil */

/* 4. Native Swipe UX untuk Slider Kamar & Promo */
.property-slider, .promo-container {
    display: flex;
    overflow-x: auto;
    scroll-snap-type: x mandatory; /* Efek magnet saat di swipe */
    scroll-behavior: smooth;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: none;
}
.property-slider::-webkit-scrollbar, .promo-container::-webkit-scrollbar { display: none; }

.property-card, .promo-card {
    scroll-snap-align: center; /* Biar posisi berhenti di tengah layar HP */
}

/* 5. Features Grid (Auto menyesuaikan kolom) */
.features-grid {
    display: grid;
    gap: 20px;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
}

/* =========================================
   MEDIA QUERIES (BREAKPOINTS)
   ========================================= */

/* --- TABLET & MOBILE (Maksimal 1024px) --- */
@media (max-width: 1024px) {
    .property-card { flex: 0 0 45%; max-width: 45%; }
}

/* --- MOBILE (Maksimal 768px) --- */
@media (max-width: 768px) {
    /* Navbar Dropdown Mobile */
    .menu-toggle { display: block; }
    
    .nav-menu-wrapper {
        display: none;
        position: absolute;
        top: 70px;
        left: 0;
        width: 100%;
        
        /* FIX UX 1: Efek Kaca (Glassmorphism) - Semi transparan + Blur */
        background: rgba(255, 255, 255) !important; /* Diturunkan jadi 0.65 agar lebih tembus pandang */
        backdrop-filter: blur(12px) !important; /* Tambahkan !important agar tidak tertimpa CSS lain */
        -webkit-backdrop-filter: blur(12px) !important; /* Khusus untuk pengguna iPhone/Safari */
        border-bottom: 1px solid rgb(255, 255, 255) !important; /* Border putih tipis ciri khas efek kaca */

        flex-direction: column;
        padding: 20px;
        box-shadow: 0 15px 25px rgba(0,0,0,0.1);
        z-index: 999;
        gap: 20px;
        align-items: flex-start;
    }
    
    .nav-menu-wrapper.active { display: flex; }

    /* Lanjutan CSS nav-links dan slider dibiarkan sama... */
    .nav-links, .nav-actions { flex-direction: column; width: 100%; gap: 15px; align-items: flex-start; }
    .dropdown-content { position: static !important; box-shadow: none !important; padding-left: 15px; margin-top: 10px; display: none; border-left: 2px solid var(--kinara-pink, #ff385c); }
    .dropdown-wrapper.active .dropdown-content { display: block !important; }
    .property-card { flex: 0 0 85%; max-width: 85%; } 
    .promo-card { flex: 0 0 90%; max-width: 90%; }
    .slide-btn { display: none !important; }
    .footer-container { display: flex; flex-direction: column; gap: 30px; padding: 20px; }
    .footer-bottom { flex-direction: column; text-align: center; gap: 15px; }
}

/* FIX UX 2: Overlay Background Blur saat menu terbuka */
.mobile-overlay {
    display: none;
    position: fixed;
    top: 70px; /* Mulai dari bawah navbar */
    left: 0;
    width: 100%;
    height: calc(100vh - 70px);
    background: rgba(0, 0, 0, 0.5); /* Gelap 50% */
    backdrop-filter: blur(4px); /* Blur konten di belakangnya */
    z-index: 998; /* Di bawah menu, di atas konten */
}
.mobile-overlay.active {
    display: block;
}

/* --- SMALL MOBILE (Maksimal 576px) --- */
@media (max-width: 576px) {
    /* Hero Text Penyesuaian */
    .hero-section h1 { font-size: 2.2rem; }
    .hero-section p { font-size: 1rem; }
    
    /* Tab Ikhwan/Akhwat */
    .category-tabs { flex-direction: column; width: 100%; gap: 10px; }
    .tab-btn { width: 100%; text-align: center; }
}
</style>

<body>

    <div class="overlay" id="overlay"></div>

<nav class="navbar">
    <div class="nav-container">
        <div class="logo">KINARA</div>

        <!-- TAMBAHAN BARU: Tombol Hamburger Menu untuk Mobile -->
        <div class="menu-toggle" id="mobile-menu">
            <i class="fas fa-bars"></i>
        </div>

        <div class="nav-menu-wrapper" id="navMenu">
        <ul class="nav-links">
            <li>
                <a href="index.php" class="<?= ($current_page == 'index.php') ? 'active' : ''; ?>">Home</a>
            </li>

            <li class="dropdown-wrapper">
                <div class="nav-item <?= ($current_page == 'tentang_kami.php') ? 'active' : ''; ?>">
                    <span class="translatable" data-en="About Kinara">Tentang Kinara</span> <i class="fas fa-chevron-down"></i>
                </div>
                
                <div class="dropdown-content menu-tentang">
                    <a href="tentang_kami.php" class="dropdown-link <?= ($current_page == 'tentang_kami.php') ? 'active' : ''; ?>">
                        <div class="dropdown-item">
                            <div class="icon-box"><i class="fas fa-users"></i></div>
                            <div class="text">
                                <strong class="translatable" data-en="About Us">Tentang Kami</strong>
                                <p class="translatable" data-en="Building more accessible housing solutions.">Membangun solusi hunian yang lebih mudah diakses.</p>
                            </div>
                        </div>
                    </a>

                    <!-- <a href="komitmen.php" class="dropdown-link <?= ($current_page == 'komitmen.php') ? 'active' : ''; ?>">
                        <div class="dropdown-item">
                            <div class="icon-box"><i class="fas fa-leaf"></i></div>
                            <div class="text">
                                <strong>Komitmen Kami</strong>
                                <p>Keberlanjutan dan tanggung jawab sosial kami.</p>
                            </div>
                        </div>
                    </a> -->
                </div>
            </li>

            <li class="dropdown-wrapper">
                <div class="nav-item <?= ($current_page == 'aturan.php' || $current_page == 'cek_kamar.php') ? 'active' : ''; ?>">
                    <span class="translatable" data-en="Check Room Units">Cek Unit Kamar</span> <i class="fas fa-chevron-down"></i>
                </div>
                
                <div class="dropdown-content menu-tentang">
                    <a href="aturan.php" class="dropdown-link <?= ($current_page == 'aturan.php') ? 'active' : ''; ?>">
                        <div class="dropdown-item">
                            <div class="icon-box"><i class="fas fa-clipboard-list"></i></div>
                            <div class="text">
                                <strong>Aturan Kinara Kost</strong>
                                <p>Penting untuk kalian ketahui ya</p>
                            </div>
                        </div>
                    </a>

                    <a href="cek_kamar.php" class="dropdown-link <?= ($current_page == 'cek_kamar.php') ? 'active' : ''; ?>">
                        <div class="dropdown-item">
                            <div class="icon-box"><i class="fas fa-bed"></i></div>
                            <div class="text">
                                <strong>Cek Kamar</strong>
                                <p>Ayo cek kamar yang masih tersedia.</p>
                            </div>
                        </div>
                    </a>
                </div>
            </li>
        </ul>

        <div class="nav-actions">
            <!-- === DROPDOWN BAHASA (SUDAH DIPERBAIKI) === -->
            <div class="dropdown-wrapper">
                <!-- Tampilan bahasa yang sedang aktif -->
                <div class="nav-item" id="current-lang-display">
                    <img src="https://flagcdn.com/w40/id.png" class="flag-circle" alt="ID" id="current-flag"> 
                    <span id="current-lang-code">ID</span> <i class="fas fa-chevron-down"></i>
                </div>

                <div class="dropdown-content lang-dropdown">
                    <h4 class="dropdown-title translatable" data-en="Select Language">Pilih Bahasa</h4>
                    
                    <!-- Tombol Indonesia -->
                    <div class="dropdown-item lang-option active-lang" id="btn-lang-id" data-lang="id">
                        <img src="https://flagcdn.com/w40/id.png" class="flag-circle">
                        <span class="lang-text">Bahasa Indonesia</span>
                        <i class="fas fa-check check-icon" id="check-id"></i>
                    </div>

                    <!-- Tombol Inggris -->
                    <div class="dropdown-item lang-option" id="btn-lang-en" data-lang="en">
                        <img src="https://flagcdn.com/w40/us.png" class="flag-circle">
                        <span class="lang-text">English</span>
                        <i class="fas fa-check check-icon" id="check-en" style="display:none;"></i>
                    </div>
                </div>
            </div>

            <!-- === LOGIN BUTTON === -->
            <?php if(isset($_SESSION['login'])): ?>
                <div class="dropdown-wrapper">
                    <button class="login-btn">
                        <i class="far fa-user"></i> Halo, <?= $_SESSION['nama']; ?>
                    </button>
                    <div class="dropdown-content">
                        <a href="logout.php" style="color: red; padding: 10px; display: block; text-decoration: none;">
                            <i class="fas fa-sign-out-alt"></i> <span class="translatable" data-en="Logout">Keluar</span>
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <button class="login-btn" id="openLogin">
                    <i class="far fa-user"></i> <span class="translatable" data-en="Login / Register">Masuk / Daftar</span>
                </button>
            <?php endif; ?>

        </div>
    </div>
</nav>

<!-- TAMBAHAN BARU: Latar belakang blur saat menu HP dibuka -->
<div class="mobile-overlay" id="mobileOverlay"></div>

<!-- LOGIN MODAL -->
<div class="login-modal" id="loginModal">
    <div class="login-box">
        <span class="close-btn" id="closeLogin">&times;</span>

        <div id="loginFormContainer">
            <h3 class="translatable" data-en="Welcome">Selamat Datang</h3>
            <p class="subtitle translatable" data-en="Log in to start finding your dream home.">Masuk untuk mulai mencari hunian impianmu.</p>

            <form action="login_process.php" method="POST">
                <div class="form-group">
                    <label class="translatable" data-en="Email or Phone Number">Email atau Nomor HP</label>
                    <input type="text" name="username" class="form-input translatable" data-en="e.g., email@yours.com" placeholder="contoh: email@anda.com" required>
                </div>
                <div class="form-group">
                    <label class="translatable" data-en="Password">Password</label>
                    <input type="password" name="password" class="form-input translatable" data-en="Enter password" placeholder="Masukkan password" required>
                </div>
                <button type="submit" class="btn-submit translatable" data-en="Login">Masuk</button>
            </form>
            <p class="signup-text">
                <span class="translatable" data-en="Don't have an account?">Belum punya akun?</span> 
                <a href="javascript:void(0)" id="toRegister" class="translatable" data-en="Register Now">Daftar Sekarang</a>
            </p>
        </div>

        <div id="registerFormContainer" style="display: none;">
            <h3 class="translatable" data-en="Create New Account">Daftar Akun Baru</h3>
            <p class="subtitle translatable" data-en="Complete your details to join Kinara.">Lengkapi data diri untuk bergabung dengan Kinara.</p>

            <form action="register_process.php" method="POST">
                <div class="form-group">
                    <label class="translatable" data-en="Full Name">Nama Lengkap</label>
                    <input type="text" name="nama" class="form-input translatable" data-en="Name exactly as on ID" placeholder="Nama sesuai KTP" required>
                </div>
                <div class="form-group">
                    <label class="translatable" data-en="Email / Phone No.">Email / No. HP</label>
                    <input type="text" name="kontak" class="form-input translatable" data-en="Active Email or WhatsApp" placeholder="Email atau WhatsApp aktif" required>
                </div>
                <div class="form-group">
                    <label class="translatable" data-en="Create Password">Buat Password</label>
                    <input type="password" name="password" class="form-input translatable" data-en="Minimum 8 characters" placeholder="Minimal 8 karakter" required>
                </div>
                <button type="submit" class="btn-submit translatable" data-en="Register User">Daftar User</button>
            </form>
            <p class="signup-text">
                <span class="translatable" data-en="Already have an account?">Sudah punya akun?</span> 
                <a href="javascript:void(0)" id="toLogin" class="translatable" data-en="Log in here">Masuk di sini</a>
            </p>
        </div>
    </div>
</div>
    
<?php
// Letakkan ini di bagian atas index.php setelah include config
$h = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM hero_section WHERE id = 1"));
?>

<main class="hero-section" style="background-image: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('img/<?= $h['gambar'] ?>'); background-size: cover; background-position: center;">
    <div class="hero-content text-center">
        <h1 class="fw-bold translatable" data-en="Comfortable Living, Better Life"><?= $h['judul'] ?></h1>
        
        <p class="translatable" data-en="Modern boarding and apartment solutions at your fingertips."><?= $h['sub_judul'] ?></p>
    </div>
</main>
    
<section class="content-container">
        <div class="filter-section">
            <div class="category-tabs">
                <button class="tab-btn active" data-target="ikhwan">
                    <i class="fas fa-male"></i> <span class="translatable" data-en="Men's Boarding">Kost Ikhwan</span>
                </button>
                <button class="tab-btn" data-target="akhwat">
                    <i class="fas fa-female"></i> <span class="translatable" data-en="Women's Boarding">Kost Akhwat</span>
                </button>
            </div>
        </div>
        
<div class="icon-filters">
        <div class="filter-item"><i class="fas fa-wifi"></i><span class="translatable" data-en="High-Speed Wi-Fi">High-Speed Wi-Fi</span></div>
        <div class="filter-item"><i class="fas fa-snowflake"></i><span class="translatable" data-en="AC">AC</span></div>
        <div class="filter-item"><i class="fas fa-bath"></i><span class="translatable" data-en="Ensuite Bathroom">K. Mandi Dalam</span></div>
        <div class="filter-item"><i class="fas fa-temperature-high"></i><span class="translatable" data-en="Water Heater">Water Heater</span></div>
        <div class="filter-item"><i class="fas fa-door-closed"></i><span class="translatable" data-en="Wardrobe">Lemari Pakaian</span></div>
        <div class="filter-item"><i class="fas fa-couch"></i><span class="translatable" data-en="Desk & Chair">Meja & Kursi</span></div>
        
        <div class="filter-item"><i class="fas fa-tv"></i><span class="translatable" data-en="Shared TV">TV Area Umum</span></div>
        <div class="filter-item"><i class="fas fa-sink"></i><span class="translatable" data-en="Shared Kitchen">Dapur Bersama</span></div>
        <div class="filter-item"><i class="fas fa-icicles"></i><span class="translatable" data-en="Refrigerator">Kulkas</span></div>
        <div class="filter-item"><i class="fas fa-tshirt"></i><span class="translatable" data-en="Washing Machine">Mesin Cuci</span></div>
        <div class="filter-item"><i class="fas fa-utensils"></i><span class="translatable" data-en="Cutlery">Peralatan Makan</span></div>
        <div class="filter-item"><i class="fas fa-fire"></i><span class="translatable" data-en="Free Gas Refill">Free Gas Refill</span></div> 
        
        <div class="filter-item"><i class="fas fa-faucet"></i><span class="translatable" data-en="Free Mineral Water">Free Mineral Water</span></div>
        <div class="filter-item"><i class="fas fa-hands-bubbles"></i><span class="translatable" data-en="Cleaning Service">Layanan Kebersihan</span></div>
        <div class="filter-item"><i class="fas fa-tools"></i><span class="translatable" data-en="Free Maintenance">Free Maintenance</span></div>
        <div class="filter-item"><i class="fas fa-broom"></i><span class="translatable" data-en="Cleaning Tools">Alat Kebersihan</span></div>
    </div>
</div>

<div class="slider-wrapper">
            <button class="slide-btn prev" id="prevBtn"><i class="fas fa-chevron-left"></i></button>
            
        <div class="property-slider" id="propertySlider">
            <?php
            // AMBIL DATA KAMAR DARI DATABASE
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
                    
                    <?php 
                        $status_id = ucfirst($kamar['status']);
                        $status_en = strtolower($status_id) == 'tersedia' ? 'Available' : 'Full';
                    ?>
                    <div class="sisa-kamar"><span class="translatable" data-en="Status:">Status:</span> <span class="translatable" data-en="<?= $status_en ?>"><?= $status_id ?></span></div>
                </div>
                <div class="card-info">
                    <?php $tipe_en = $kamar['tipe'] == 'ikhwan' ? "Men's Boarding" : "Women's Boarding"; ?>
                    <div class="property-type"><i class="fas fa-<?= $kamar['tipe'] == 'ikhwan' ? 'male' : 'female'; ?>"></i> <span class="translatable" data-en="<?= $tipe_en ?>">Kost <?= ucfirst($kamar['tipe']); ?></span></div>
                    <h3 class="property-name"><?= $kamar['nama_kamar']; ?></h3>
                    <p class="property-location"><?= $kamar['lokasi']; ?></p>
                    <div class="property-price"><span class="translatable" data-en="Starts from">mulai dari</span> <span>Rp <?= number_format($kamar['harga'], 0, ',', '.'); ?></span></div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>

            <button class="slide-btn next" id="nextBtn"><i class="fas fa-chevron-right"></i></button>
        </div>
    </section>

    <section class="content-container promo-section">
        <div class="section-header">
            <h2 class="translatable" data-en="Ongoing Promos">Promo berlangsung</h2>
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

<section class="features-section">
    <div class="container-slider"> 
        <h2 class="section-title translatable" data-en="Benefits of Living at Kinara">Keuntungan Tinggal di Kinara</h2>

        <div class="carousel-wrapper">
            <div class="features-grid" id="slider">
                <?php
                // CATATAN: Idealnya data dinamis dari DB (Keuntungan) juga diterjemahkan di panel admin.
                // Untuk saat ini, kita biarkan data DB tetap, tapi judul sectionnya sudah diterjemahkan.
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

<footer class="main-footer">
    <div class="footer-container">
        <!-- <div class="footer-column">
            <div class="footer-group">
                <h3 class="translatable" data-en="Tenant">Tenant</h3>
                <ul>
                    <li><a href="#" class="translatable" data-en="Boarding House">Kost</a></li>
                    <li><a href="#" class="translatable" data-en="Apartment">Apartemen</a></li>
                    <li><a href="#" class="translatable" data-en="Community">Community</a></li>
                </ul>
            </div>
            <div class="footer-group">
                <h3 class="translatable" data-en="Kinara Partnership">Kerjasama kinara</h3>
                <ul>
                    <li><a href="#" class="translatable" data-en="Coliving">Coliving</a></li>
                    <li><a href="#" class="translatable" data-en="Apartment">Apartemen</a></li>
                    <li><a href="#" class="translatable" data-en="Build to Rent">Build to Rent</a></li>
                    <li><a href="#" class="translatable" data-en="RuFinance">RuFinance</a></li>
                </ul>
            </div>
        </div> -->

        <!-- <div class="footer-column">
            <div class="footer-group">
                <h3 class="translatable" data-en="Kinara For Business">kinara For Business</h3>
                <ul>
                    <li><a href="#" class="translatable" data-en="Kinara For Business">kinara For Business</a></li>
                    <li><a href="#" class="translatable" data-en="Corporate Subscription">Corporate Subscription</a></li>
                    <li><a href="#" class="translatable" data-en="RuCollab">RuCollab</a></li>
                </ul>
            </div>
            <div class="footer-group">
                <h3 class="translatable" data-en="About Kinara">Tentang kinara</h3>
                <ul>
                    <li><a href="#" class="translatable" data-en="About Us">Tentang Kami</a></li>
                    <li><a href="#" class="translatable" data-en="Our ESG Commitment">Komitmen ESG Kami</a></li>
                </ul>
            </div>
        </div> -->

        <div class="footer-column">
            <div class="footer-group">
                <h3 class="translatable" data-en="Resource">Resource</h3>
                <ul>
                    <li><a href="#" class="translatable" data-en="FAQ">FAQ</a></li>
                    <li><a href="#" class="translatable" data-en="Careers">Karir</a></li>
                    <li><a href="#" class="translatable" data-en="Stories">Stories</a></li>
                    <li><a href="#" class="translatable" data-en="Help Center">Pusat Bantuan</a></li>
                </ul>
            </div>
            <div class="footer-group">
                <h3 class="translatable" data-en="Brand Partner">Brand Partner</h3>
                <ul>
                    <li><a href="#" class="translatable" data-en="Uma Living">KIKOST BOGOR</a></li>
                    <li><a href="#" class="translatable" data-en="Infokost Pro">Kinara Land</a></li>
                </ul>
            </div>
        </div>

        <div class="footer-column">
            <h3 class="translatable" data-en="Support">Support</h3>
            <ul class="support-list">
                <li><i class="fab fa-whatsapp"></i> +62 895-1679-2463</li>
                <li><i class="far fa-envelope"></i> info@kinara.com</li>
            </ul>
            <div class="operational-hours">
                <strong class="translatable" data-en="Operational Hours">Jam Operasional</strong>
                <p class="translatable" data-en="Monday - Friday: 8.00 AM - 5.00 PM">Senin - Jumat: 8.00 - 17.00</p>
                <p class="translatable" data-en="Saturday - Sunday: 8.30 AM - 4.30 PM">Sabtu - Minggu: 8.30 - 16.30</p>
            </div>
        </div>
    </div>

    <div class="footer-bottom">
        <div class="bottom-left">
            <span class="translatable" data-en="© 2026 kinara. All rights reserved.">© 2026 kinara. All rights reserved.</span>
            <a href="#" class="translatable" data-en="Terms & Conditions">Syarat & Ketentuan</a>
            <a href="#" class="translatable" data-en="Privacy Policy">Kebijakan Privasi</a>
        </div>
        <div class="social-icons">
            <a href="#"><i class="fab fa-facebook"></i></a>
            <a href="#"><i class="fab fa-instagram"></i></a>
            <a href="#"><i class="fab fa-twitter"></i></a>
            <a href="#"><i class="fab fa-tiktok"></i></a>
        </div>
    </div>
</footer>

<a href="https://wa.me/6289516792463" class="fab-whatsapp" target="_blank">
    <img src="https://upload.wikimedia.org/wikipedia/commons/6/6b/WhatsApp.svg" alt="WhatsApp Help">
</a>


<div id="imageModal" class="img-zoom-modal">
    <span class="img-close">&times;</span>
    <img class="img-modal-content" id="imgFull">
    <div id="imgCaption"></div>
</div>

<script>
// === 0. LOGIKA HAMBURGER MENU MOBILE ===
const mobileMenuBtn = document.getElementById('mobile-menu');
const navMenu = document.getElementById('navMenu');
const mobileOverlay = document.getElementById('mobileOverlay');

if(mobileMenuBtn) {
    mobileMenuBtn.addEventListener('click', (e) => {
        e.stopPropagation(); // Mencegah klik menyebar
        
        navMenu.classList.toggle('active');
        mobileOverlay.classList.toggle('active');
        
        // Animasi icon Hamburger berubah jadi (X)
        const icon = mobileMenuBtn.querySelector('i');
        icon.classList.toggle('fa-bars');
        icon.classList.toggle('fa-times');
    });
}

// FIX UX 3: Tutup menu saat klik sembarang area (di luar menu)
document.addEventListener('click', (e) => {
    // Jika menu sedang terbuka, dan yang diklik BUKAN bagian dari menu
    if (navMenu && navMenu.classList.contains('active')) {
        if (!navMenu.contains(e.target) && !mobileMenuBtn.contains(e.target)) {
            // Tutup Menu & Overlay
            navMenu.classList.remove('active');
            mobileOverlay.classList.remove('active');
            
            // Kembalikan icon X jadi Hamburger
            const icon = mobileMenuBtn.querySelector('i');
            icon.classList.remove('fa-times');
            icon.classList.add('fa-bars');
        }
    }
});

document.addEventListener('DOMContentLoaded', () => {
    const isLogin = <?= isset($_SESSION['login']) ? 'true' : 'false'; ?>;
    const loginModal = document.getElementById("loginModal");
    const overlay = document.getElementById('overlay');

    // === 1. LOGIKA DROPDOWN NAVBAR (KLIK LUAR OTOMATIS TUTUP) ===
    document.addEventListener('click', (e) => {
        const wrappers = document.querySelectorAll('.dropdown-wrapper');
        
        // Cek apakah yang diklik adalah bagian dari dropdown
        let targetWrapper = e.target.closest('.dropdown-wrapper');

        if (targetWrapper) {
            // Jika yang diklik adalah Trigger (Tombol/Menu)
            const trigger = e.target.closest('.nav-item, .login-btn');
            if (trigger) {
                e.preventDefault();
                e.stopPropagation();
                
                const isActive = targetWrapper.classList.contains('active');
                
                // Tutup semua dropdown lain dulu
                wrappers.forEach(w => w.classList.remove('active'));
                if(overlay) overlay.classList.remove('show');

                // Toggle yang sedang diklik
                if (!isActive) {
                    targetWrapper.classList.add('active');
                    if(overlay) overlay.classList.add('show');
                }
            }
        } else {
            // JIKA KLIK DI LUAR, PASTI TUTUP SEMUA
            wrappers.forEach(w => w.classList.remove('active'));
            if(overlay) overlay.classList.remove('show');
        }
    });

    // === 2. LOGIKA MODAL LOGIN & REGISTER ===
    const openLogin = document.getElementById("openLogin");
    const closeLogin = document.getElementById("closeLogin");
    const loginFormContainer = document.getElementById("loginFormContainer");
    const registerFormContainer = document.getElementById("registerFormContainer");
    const toRegister = document.getElementById("toRegister");
    const toLogin = document.getElementById("toLogin");

    if(openLogin) openLogin.onclick = () => loginModal.style.display = "block";
    
    if(closeLogin) {
        closeLogin.onclick = () => {
            loginModal.style.display = "none";
            setTimeout(() => {
                loginFormContainer.style.display = "block";
                registerFormContainer.style.display = "none";
            }, 400);
        };
    }

    if(toRegister) toRegister.onclick = (e) => {
        e.preventDefault();
        loginFormContainer.style.display = "none";
        registerFormContainer.style.display = "block";
    };

    if(toLogin) toLogin.onclick = (e) => {
        e.preventDefault();
        registerFormContainer.style.display = "none";
        loginFormContainer.style.display = "block";
    };

    // Klik luar modal tutup
    window.addEventListener("click", (e) => {
        if (e.target == loginModal) {
            loginModal.style.display = "none";
            loginFormContainer.style.display = "block";
            registerFormContainer.style.display = "none";
        }
    });

    // === 3. LOGIKA ZOOM GAMBAR PROMO ===
    const imgModal = document.getElementById("imageModal");
    const fullImg = document.getElementById("imgFull");
    const captionText = document.getElementById("imgCaption");
    const closeImg = document.querySelector(".img-close");

    document.querySelectorAll('.promo-card').forEach(card => {
        card.addEventListener('click', function() {
            const img = this.querySelector('img');
            imgModal.classList.add('show'); 
            fullImg.src = img.src;
            captionText.innerHTML = img.alt;
        });
    });

    if(closeImg) closeImg.onclick = () => imgModal.classList.remove('show');
    if(imgModal) imgModal.onclick = (e) => { if(e.target === imgModal) imgModal.classList.remove('show'); };

    // === 4. LOGIKA SLIDER (PROPERTY & PROMO) ===
    function initSlider(sliderId, prevBtnId, nextBtnId) {
        const slider = document.getElementById(sliderId);
        const prev = document.getElementById(prevBtnId);
        const next = document.getElementById(nextBtnId);
        if (slider && prev && next) {
            next.onclick = () => slider.scrollLeft += slider.offsetWidth;
            prev.onclick = () => slider.scrollLeft -= slider.offsetWidth;
        }
    }
    initSlider('propertySlider', 'prevBtn', 'nextBtn');
    initSlider('promoSlider', 'promoPrev', 'promoNext');

    // === 5. FILTER TAB (IKHWAN / AKHWAT) ===
    const tabBtns = document.querySelectorAll('.tab-btn');
    const cards = document.querySelectorAll('.property-card');
    tabBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const target = btn.getAttribute('data-target');
            tabBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            cards.forEach(card => {
                card.style.display = (card.getAttribute('data-type') === target) ? 'block' : 'none';
            });
        });
    });

// === 6. LOGIKA MULTI-BAHASA (BILINGUAL) ===
    const btnLangId = document.getElementById('btn-lang-id');
    const btnLangEn = document.getElementById('btn-lang-en');
    const currentFlag = document.getElementById('current-flag');
    const currentLangCode = document.getElementById('current-lang-code');
    const checkId = document.getElementById('check-id');
    const checkEn = document.getElementById('check-en');

    function setLanguage(lang) {
        // 1. Simpan pilihan user di browser (agar tidak hilang saat refresh)
        localStorage.setItem('kinara_lang', lang);

        // 2. Ubah Tampilan Dropdown
        if (lang === 'en') {
            currentFlag.src = 'https://flagcdn.com/w40/us.png';
            currentLangCode.innerText = 'EN';
            checkId.style.display = 'none';
            checkEn.style.display = 'inline-block';
            btnLangEn.classList.add('active-lang');
            btnLangId.classList.remove('active-lang');
        } else {
            currentFlag.src = 'https://flagcdn.com/w40/id.png';
            currentLangCode.innerText = 'ID';
            checkEn.style.display = 'none';
            checkId.style.display = 'inline-block';
            btnLangId.classList.add('active-lang');
            btnLangEn.classList.remove('active-lang');
        }

        // 3. Eksekusi Terjemahan pada semua elemen yang memiliki class 'translatable'
        document.querySelectorAll('.translatable').forEach(el => {
            // Jika elemen adalah input (placeholder)
            if(el.tagName === 'INPUT' && el.hasAttribute('placeholder')) {
                if(!el.getAttribute('data-id-text')) {
                    el.setAttribute('data-id-text', el.getAttribute('placeholder'));
                }
                el.setAttribute('placeholder', lang === 'en' ? el.getAttribute('data-en') : el.getAttribute('data-id-text'));
            } 
            // Jika elemen adalah teks biasa
            else {
                // FIX: Gunakan textContent, BUKAN innerText agar bisa membaca menu yang tersembunyi
                if(!el.getAttribute('data-id-text')) {
                    el.setAttribute('data-id-text', el.textContent.trim()); 
                }
                el.textContent = lang === 'en' ? el.getAttribute('data-en') : el.getAttribute('data-id-text');
            }
        });
    }

    // Event Listener ketika tombol bahasa diklik
    if(btnLangId) btnLangId.addEventListener('click', () => setLanguage('id'));
    if(btnLangEn) btnLangEn.addEventListener('click', () => setLanguage('en'));

    // Otomatis cek bahasa saat web pertama kali dibuka
    const savedLang = localStorage.getItem('kinara_lang') || 'id';
    setLanguage(savedLang);

});
</script>
</body>
</html>
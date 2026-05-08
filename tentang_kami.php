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
    <title>Tentang Kami - Kinara Kost</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css"> <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
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

        /* Styling Khusus Halaman Tentang Kami */
        .about-header {
            padding: 100px 0 60px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            text-align: center;
        }

        .about-container {
            max-width: 1000px;
            margin: -50px auto 80px;
            padding: 0 20px;
        }

        .about-card {
            background: white;
            border-radius: 30px; /* Rounded besar sesuai gaya Kinara */
            padding: 50px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.05);
            line-height: 1.8;
            color: #444;
        }

        .about-card h2 {
            color: #333;
            font-weight: 700;
            margin-bottom: 25px;
            position: relative;
            display: inline-block;
        }

        .about-card h2::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 50px;
            height: 4px;
            background: #1abc9c; /* Warna hijau toska Kinara */
            border-radius: 2px;
        }

        .mission-vision {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-top: 40px;
        }

        .mv-item {
            background: #fdfdfd;
            padding: 30px;
            border-radius: 20px;
            border: 1px solid #f0f0f0;
        }

        .mv-item i {
            font-size: 2rem;
            color: #1abc9c;
            margin-bottom: 15px;
        }

        .highlight-text {
            font-size: 1.2rem;
            color: #1abc9c;
            font-weight: 600;
            margin-bottom: 20px;
        }

        @media (max-width: 768px) {
            .mission-vision { grid-template-columns: 1fr; }
            .about-card { padding: 30px; }
        }

        .dropdown-wrapper.active-dropdown .dropdown-content {
            display: block;
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        /* =========================================
   UI/UX RESPONSIVE LAYOUT (MOBILE FIRST)
   ========================================= */

*, *::before, *::after { box-sizing: border-box; }

/* Wrapper Navbar Desktop */
.nav-menu-wrapper {
    display: flex;
    flex: 1;
    justify-content: space-between;
    align-items: center;
}

/* Hamburger Menu & Overlay */
.menu-toggle {
    display: none;
    font-size: 24px;
    cursor: pointer;
    color: #ff385c;
}
.mobile-overlay {
    display: none;
    position: fixed;
    top: 70px; left: 0;
    width: 100%; height: calc(100vh - 70px);
    background: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(4px);
    z-index: 998;
}
.mobile-overlay.active { display: block; }

/* =========================================
   MEDIA QUERIES (BREAKPOINTS)
   ========================================= */

/* --- TABLET & MOBILE (Maksimal 768px) --- */
@media (max-width: 768px) {
    /* 1. Navbar Dropdown Mobile & Glassmorphism */
    .menu-toggle { display: block; }
    .nav-menu-wrapper {
        display: none; position: absolute; top: 70px; left: 0; width: 100%;
        background: rgba(255, 255, 255, 0.90) !important;
        backdrop-filter: blur(15px); -webkit-backdrop-filter: blur(15px);
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        flex-direction: column; padding: 20px; box-shadow: 0 15px 25px rgba(0,0,0,0.1);
        z-index: 999; gap: 20px; align-items: flex-start;
    }
    .nav-menu-wrapper.active { display: flex; }
    .nav-links, .nav-actions { flex-direction: column; width: 100%; gap: 15px; align-items: flex-start; }
    
    /* 2. Akordion Menu */
    .dropdown-content { position: static !important; box-shadow: none !important; padding-left: 15px; margin-top: 10px; display: none; border-left: 2px solid #ff385c; }
    .dropdown-wrapper.active .dropdown-content { display: block !important; }

    /* 3. Penyesuaian Halaman Tentang Kami */
    .about-header { padding: 80px 20px 40px; }
    .about-header h1 { font-size: 28px !important; }
    .about-container { margin-top: -30px; }
    .about-card { padding: 30px 20px; border-radius: 20px; }
    .mission-vision { grid-template-columns: 1fr; gap: 20px; margin-top: 30px; }

    /* 4. Footer Stacking */
    .footer-container { display: flex; flex-direction: column; gap: 30px; padding: 20px; }
    .footer-bottom { flex-direction: column; text-align: center; gap: 15px; }
}

/* --- SMALL MOBILE (Maksimal 576px) --- */
@media (max-width: 576px) {
    .about-header h1 { font-size: 24px !important; }
    .mv-item { padding: 20px; }
    .mv-item h4 { font-size: 18px; }
    .highlight-text { font-size: 1.1rem; }
}
    </style>
</head>
<body>
<div class="overlay" id="overlay"></div>

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


    <section class="about-header">
        <h1 class="fw-bold translatable" data-en="About Kinara" style="font-size: 36px;">Tentang Kinara</h1>
        <p class="text-muted translatable" data-en="Get to know our modern housing solutions closer.">Mengenal lebih dekat solusi hunian modern kami.</p>
    </section>

    <div class="about-container">
        <div class="about-card">
            <div class="highlight-text translatable" data-en='"Building more accessible housing solutions."'>"Membangun solusi hunian yang lebih mudah diakses."</div>
            <p class="translatable" data-en="Kinara Kost was born from a deep understanding of housing needs that support productivity and comfort. We ensure every unit is maintained to high standards to provide the best living experience.">Kinara Kost lahir dari pemahaman mendalam akan kebutuhan hunian yang mendukung produktivitas dan kenyamanan. Kami memastikan setiap unit terpelihara dengan standar tinggi untuk memberikan pengalaman hidup terbaik.</p>
            <div class="mission-vision">
                <div class="mv-item">
                    <i class="fas fa-eye"></i>
                    <h4 class="translatable" data-en="Our Vision">Visi Kami</h4>
                    <p class="translatable" data-en="To become the trusted housing standard for the younger generation.">Menjadi standar hunian terpercaya bagi generasi muda.</p>
                </div>
                <div class="mv-item">
                    <i class="fas fa-bullseye"></i>
                    <h4 class="translatable" data-en="Our Mission">Misi Kami</h4>
                    <p class="translatable" data-en="Providing easy digital booking and consistent facility quality.">Memberikan kemudahan booking digital dan kualitas fasilitas konsisten.</p>
                </div>
            </div>
        </div>
    </div>
    
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

<script>
document.addEventListener('DOMContentLoaded', () => {

    // === 0. LOGIKA HAMBURGER MENU MOBILE ===
    const mobileMenuBtn = document.getElementById('mobile-menu');
    const navMenu = document.getElementById('navMenu');
    const mobileOverlay = document.getElementById('mobileOverlay');

    if(mobileMenuBtn) {
        mobileMenuBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            navMenu.classList.toggle('active');
            mobileOverlay.classList.toggle('active');
            
            const icon = mobileMenuBtn.querySelector('i');
            icon.classList.toggle('fa-bars');
            icon.classList.toggle('fa-times');
        });
    }

    // Tutup menu saat klik sembarang area
    document.addEventListener('click', (e) => {
        if (navMenu && navMenu.classList.contains('active')) {
            if (!navMenu.contains(e.target) && !mobileMenuBtn.contains(e.target)) {
                navMenu.classList.remove('active');
                mobileOverlay.classList.remove('active');
                
                const icon = mobileMenuBtn.querySelector('i');
                icon.classList.remove('fa-times');
                icon.classList.add('fa-bars');
            }
        }
    });

    const isLogin = <?= isset($_SESSION['login']) ? 'true' : 'false'; ?>;
    const loginModal = document.getElementById("loginModal");
    const overlay = document.getElementById('overlay');

    // === 1. LOGIKA DROPDOWN (KLIK LUAR OTOMATIS TUTUP) ===
    document.addEventListener('click', (e) => {
        const wrappers = document.querySelectorAll('.dropdown-wrapper');
        let targetWrapper = e.target.closest('.dropdown-wrapper');

        if (targetWrapper) {
            const trigger = e.target.closest('.nav-item, .login-btn');
            if (trigger) {
                e.preventDefault();
                e.stopPropagation();
                
                const isActive = targetWrapper.classList.contains('active');
                
                wrappers.forEach(w => w.classList.remove('active'));
                if(overlay) overlay.classList.remove('show');

                if (!isActive) {
                    targetWrapper.classList.add('active');
                    if(overlay) overlay.classList.add('show');
                }
            }
        } else {
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

    window.addEventListener("click", (e) => {
        if (e.target == loginModal) {
            loginModal.style.display = "none";
            loginFormContainer.style.display = "block";
            registerFormContainer.style.display = "none";
        }
    });

    // === 3. LOGIKA MULTI-BAHASA (BILINGUAL) ===
    const btnLangId = document.getElementById('btn-lang-id');
    const btnLangEn = document.getElementById('btn-lang-en');
    const currentFlag = document.getElementById('current-flag');
    const currentLangCode = document.getElementById('current-lang-code');
    const checkId = document.getElementById('check-id');
    const checkEn = document.getElementById('check-en');

    function setLanguage(lang) {
        localStorage.setItem('kinara_lang', lang);

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

        document.querySelectorAll('.translatable').forEach(el => {
            if(el.tagName === 'INPUT' && el.hasAttribute('placeholder')) {
                if(!el.getAttribute('data-id-text')) {
                    el.setAttribute('data-id-text', el.getAttribute('placeholder'));
                }
                el.setAttribute('placeholder', lang === 'en' ? el.getAttribute('data-en') : el.getAttribute('data-id-text'));
            } 
            else {
                if(!el.getAttribute('data-id-text')) {
                    el.setAttribute('data-id-text', el.textContent.trim()); 
                }
                el.textContent = lang === 'en' ? el.getAttribute('data-en') : el.getAttribute('data-id-text');
            }
        });
    }

    if(btnLangId) btnLangId.addEventListener('click', () => setLanguage('id'));
    if(btnLangEn) btnLangEn.addEventListener('click', () => setLanguage('en'));

    // Otomatis terapkan bahasa saat halaman dimuat
    const savedLang = localStorage.getItem('kinara_lang') || 'id';
    setLanguage(savedLang);

});

</script>
</body>
</html>
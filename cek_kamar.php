<?php
session_start();
include 'config.php';
$current_page = basename($_SERVER['PHP_SELF']); 

// Ambil data filter jika ada
$filter_tipe = isset($_GET['tipe']) ? $_GET['tipe'] : 'semua';

// Query dasar
$sql = "SELECT * FROM kamar";
if ($filter_tipe !== 'semua') {
    $sql .= " WHERE tipe = '$filter_tipe'";
}
$sql .= " ORDER BY status ASC, id DESC";
$query_kamar = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cek Unit Kamar - Kinara Kost</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root { --kinara-teal: #1abc9c; --kinara-pink: #ff385c; }
        body { font-family: 'Inter', sans-serif; background-color: #f8f9fa; margin: 0; }

        /* Header */
        .page-header { padding: 120px 20px 40px; text-align: center; background: white; }

        /* MODAL BACKGROUND */
        .login-modal { display: none; position: fixed; z-index: 10000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.7); backdrop-filter: blur(4px); }
        .login-box { background: #fff; width: 90%; max-width: 400px; margin: 5vh auto; padding: 30px; border-radius: 16px; position: relative; box-shadow: 0 10px 25px rgba(0,0,0,0.2); animation: modalSlideUp 0.4s ease-out; max-height: 90vh; overflow-y: auto; }
        @keyframes modalSlideUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
        .login-box h3 { font-size: 24px; font-weight: 700; margin-bottom: 8px; color: #333; }
        .login-box p.subtitle { font-size: 14px; color: #666; margin-bottom: 25px; }
        .form-group { margin-bottom: 20px; text-align: left; }
        .form-group label { display: block; font-size: 14px; font-weight: 600; margin-bottom: 8px; color: #444; }
        .form-input { width: 100%; padding: 12px 16px; border: 1.5px solid #e0e0e0; border-radius: 8px; font-size: 15px; transition: all 0.3s; box-sizing: border-box; }
        .form-input:focus { border-color: #ff385c; outline: none; box-shadow: 0 0 0 3px rgba(255, 56, 92, 0.1); }
        .btn-submit { width: 100%; padding: 14px; background: #ff385c; color: white; border: none; border-radius: 8px; font-size: 16px; font-weight: 700; cursor: pointer; transition: background 0.3s; margin-top: 10px; }
        .btn-submit:hover { background: #e31c5f; }
        .close-btn { position: absolute; right: 20px; top: 20px; font-size: 24px; color: #999; cursor: pointer; transition: 0.2s; }
        .close-btn:hover { color: #333; }
        .signup-text { margin-top: 25px; font-size: 14px; color: #666; text-align: center; display: block; }
        .signup-text a { color: #ff385c; text-decoration: none; font-weight: 600; cursor: pointer; }
        .signup-text a:hover { text-decoration: underline; }
        
        /* Filter Tabs */
        .filter-nav { display: flex; justify-content: center; gap: 15px; margin-bottom: 40px; }
        .btn-filter { padding: 10px 25px; border-radius: 50px; border: 1px solid #ddd; background: white; cursor: pointer; transition: 0.3s; font-weight: 600; text-decoration: none; color: #666; }
        .btn-filter.active { background: var(--kinara-teal); color: white; border-color: var(--kinara-teal); }

        /* Grid Kamar */
        .kamar-container { max-width: 1200px; margin: 0 auto 80px; padding: 0 20px; }
        .kamar-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 30px; }
        
        .kamar-card { background: white; border-radius: 20px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.05); transition: 0.3s; position: relative; }
        .kamar-card:hover { transform: translateY(-10px); }
        .kamar-img { width: 100%; height: 220px; object-fit: cover; }
        .kamar-info { padding: 20px; }
        .badge-tipe { font-size: 11px; font-weight: 700; padding: 5px 12px; border-radius: 5px; text-transform: uppercase; margin-bottom: 10px; display: inline-block; }
        .tipe-ikhwan { background: #e3f2fd; color: #1976d2; }
        .tipe-akhwat { background: #fce4ec; color: #c2185b; }
        .harga { font-size: 20px; font-weight: 700; color: var(--kinara-pink); margin: 10px 0; }
        .status-tag { position: absolute; top: 15px; right: 15px; padding: 5px 15px; border-radius: 50px; font-size: 12px; font-weight: 700; }
        .status-tersedia { background: #2ecc71; color: white; }
        .status-penuh { background: #e74c3c; color: white; }
        .btn-booking { width: 100%; padding: 12px; border: none; border-radius: 12px; background: var(--kinara-pink); color: white; font-weight: 700; cursor: pointer; transition: 0.3s; }
        .btn-booking:hover { background: #e31c5f; }
        .btn-booking:disabled { background: #ccc; cursor: not-allowed; }

        /* =========================================
           UI/UX RESPONSIVE LAYOUT (MOBILE FIRST)
           ========================================= */
        *, *::before, *::after { box-sizing: border-box; }

        .nav-menu-wrapper { display: flex; flex: 1; justify-content: space-between; align-items: center; }
        .menu-toggle { display: none; font-size: 24px; cursor: pointer; color: #ff385c; }
        .mobile-overlay { display: none; position: fixed; top: 70px; left: 0; width: 100%; height: calc(100vh - 70px); background: rgba(0, 0, 0, 0.5); backdrop-filter: blur(4px); z-index: 998; }
        .mobile-overlay.active { display: block; }

        @media (max-width: 768px) {
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
            .dropdown-content { position: static !important; box-shadow: none !important; padding-left: 15px; margin-top: 10px; display: none; border-left: 2px solid #ff385c; }
            .dropdown-wrapper.active .dropdown-content { display: block !important; }

            /* Penyesuaian Header Kamar Mobile */
            .page-header { padding: 100px 20px 30px; }
            .page-header h1 { font-size: 28px !important; }
            .filter-nav { flex-wrap: wrap; gap: 10px; }
            .btn-filter { flex: 1; text-align: center; padding: 10px 15px; font-size: 14px; }
            
            /* Penyesuaian Grid Mobile (Dipaksa 1 Kolom Full) */
            .kamar-grid { grid-template-columns: 1fr; gap: 20px; }
            
            /* Footer Stacking */
            .footer-container { display: flex; flex-direction: column; gap: 30px; padding: 20px; }
            .footer-bottom { flex-direction: column; text-align: center; gap: 15px; }
        }
    </style>
</head>
<body>

<nav class="navbar">
    <div class="nav-container">
        <div class="logo">KINARA</div>

        <div class="menu-toggle" id="mobile-menu">
            <i class="fas fa-bars"></i>
        </div>

        <div class="nav-menu-wrapper" id="navMenu">
            <ul class="nav-links">
                <li><a href="index.php" class="<?= ($current_page == 'index.php') ? 'active' : ''; ?>">Home</a></li>
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
                                    <strong class="translatable" data-en="Kinara Boarding Rules">Aturan Kinara Kost</strong>
                                    <p class="translatable" data-en="Important things you need to know.">Penting untuk kalian ketahui ya</p>
                                </div>
                            </div>
                        </a>
                        <a href="cek_kamar.php" class="dropdown-link <?= ($current_page == 'cek_kamar.php') ? 'active' : ''; ?>">
                            <div class="dropdown-item">
                                <div class="icon-box"><i class="fas fa-bed"></i></div>
                                <div class="text">
                                    <strong class="translatable" data-en="Check Rooms">Cek Kamar</strong>
                                    <p class="translatable" data-en="Let's check available rooms.">Ayo cek kamar yang masih tersedia.</p>
                                </div>
                            </div>
                        </a>
                    </div>
                </li>
            </ul>

            <div class="nav-actions">
                <div class="dropdown-wrapper">
                    <div class="nav-item" id="current-lang-display">
                        <img src="https://flagcdn.com/w40/id.png" class="flag-circle" alt="ID" id="current-flag"> 
                        <span id="current-lang-code">ID</span> <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="dropdown-content lang-dropdown">
                        <h4 class="dropdown-title translatable" data-en="Select Language">Pilih Bahasa</h4>
                        <div class="dropdown-item lang-option active-lang" id="btn-lang-id" data-lang="id">
                            <img src="https://flagcdn.com/w40/id.png" class="flag-circle">
                            <span class="lang-text">Bahasa Indonesia</span>
                            <i class="fas fa-check check-icon" id="check-id"></i>
                        </div>
                        <div class="dropdown-item lang-option" id="btn-lang-en" data-lang="en">
                            <img src="https://flagcdn.com/w40/us.png" class="flag-circle">
                            <span class="lang-text">English</span>
                            <i class="fas fa-check check-icon" id="check-en" style="display:none;"></i>
                        </div>
                    </div>
                </div>

                <?php if(isset($_SESSION['login'])): ?>
                    <div class="dropdown-wrapper">
                        <button class="login-btn"><i class="far fa-user"></i> Halo, <?= $_SESSION['nama']; ?></button>
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
    </div>
</nav>

<div class="mobile-overlay" id="mobileOverlay"></div>

    <header class="page-header">
        <h1 class="fw-bold translatable" data-en="Choose Your Dream Room">Pilih Kamar Impianmu</h1>
        <p class="text-muted translatable" data-en="Find the best comfort in Kinara Kost units.">Temukan kenyamanan terbaik di unit-unit Kinara Kost.</p>
    </header>

    <div class="kamar-container">
        <div class="filter-nav">
            <a href="cek_kamar.php?tipe=semua" class="btn-filter <?= ($filter_tipe == 'semua') ? 'active' : ''; ?> translatable" data-en="All">Semua</a>
            <a href="cek_kamar.php?tipe=ikhwan" class="btn-filter <?= ($filter_tipe == 'ikhwan') ? 'active' : ''; ?> translatable" data-en="Men's">Ikhwan</a>
            <a href="cek_kamar.php?tipe=akhwat" class="btn-filter <?= ($filter_tipe == 'akhwat') ? 'active' : ''; ?> translatable" data-en="Women's">Akhwat</a>
        </div>

        <div class="kamar-grid">
            <?php while($k = mysqli_fetch_assoc($query_kamar)): ?>
            <div class="kamar-card">
                <?php 
                    $status_id = ucfirst($k['status']);
                    $status_en = strtolower($status_id) == 'tersedia' ? 'Available' : 'Full';
                ?>
                <span class="status-tag <?= ($k['status'] == 'tersedia') ? 'status-tersedia' : 'status-penuh'; ?>">
                    <span class="translatable" data-en="<?= $status_en ?>"><?= $status_id ?></span>
                </span>
                
                <img src="img/<?= $k['gambar']; ?>" class="kamar-img" alt="<?= $k['nama_kamar']; ?>" onerror="this.src='https://via.placeholder.com/400x250'">
                
                <div class="kamar-info">
                    <?php $tipe_en = $k['tipe'] == 'ikhwan' ? "Men's Boarding" : "Women's Boarding"; ?>
                    <span class="badge-tipe <?= ($k['tipe'] == 'ikhwan') ? 'tipe-ikhwan' : 'tipe-akhwat'; ?> translatable" data-en="<?= $tipe_en ?>">
                        Kost <?= ucfirst($k['tipe']); ?>
                    </span>
                    
                    <?php $nama_kamar_en = str_ireplace('Kamar', 'Room', $k['nama_kamar']); ?>
                    <h3 class="fw-bold m-0 translatable" data-en="<?= $nama_kamar_en ?>"><?= $k['nama_kamar']; ?></h3>
                    
                    <p class="text-muted small"><i class="fas fa-map-marker-alt me-1"></i> <?= $k['lokasi']; ?></p>
                    <div class="harga"><span class="translatable" data-en="Starts from ">mulai dari </span>Rp <?= number_format($k['harga'], 0, ',', '.'); ?><span style="font-size: 12px; color: #999;" class="translatable" data-en=" / month"> / bulan</span></div>
                    
                    <?php if($k['status'] == 'tersedia'): ?>
                        <button class="btn-booking translatable" data-en="Book Now" onclick="prosesBooking(<?= $k['id']; ?>)">Booking Sekarang</button>
                    <?php else: ?>
                        <button class="btn-booking translatable" data-en="Room Full" disabled>Kamar Penuh</button>
                    <?php endif; ?>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>

<footer class="main-footer">
    <div class="footer-container">
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
                    <li><a href="#" class="translatable" data-en="KIKOST BOGOR">KIKOST BOGOR</a></li>
                    <li><a href="#" class="translatable" data-en="Kinara Land">Kinara Land</a></li>
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

<script>
// Pindahkan inisialisasi variabel isLogin ke luar agar dikenali fungsi prosesBooking
const isLogin = <?= isset($_SESSION['login']) ? 'true' : 'false'; ?>;

function prosesBooking(id) {
    const loginModal = document.getElementById("loginModal");
    if (!isLogin) {
        const loginForm = document.querySelector('#loginModal form[action="login_process.php"]');
        let redirectInput = loginForm.querySelector('input[name="redirect_to"]');
        if (!redirectInput) {
            redirectInput = document.createElement('input');
            redirectInput.type = 'hidden'; 
            redirectInput.name = 'redirect_to';
            loginForm.appendChild(redirectInput);
        }
        redirectInput.value = 'proses_booking.php?id=' + id;
        
        // Translasi SweetAlert
        const isEn = localStorage.getItem('kinara_lang') === 'en';
        Swal.fire({
            title: isEn ? 'Not Logged In' : 'Belum Login',
            text: isEn ? 'Please log in to continue booking.' : "Silakan login untuk melanjutkan booking.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ff385c',
            cancelButtonText: isEn ? 'Cancel' : 'Batal',
            confirmButtonText: isEn ? 'Log in Now' : 'Login Sekarang'
        }).then((result) => {
            if (result.isConfirmed) loginModal.style.display = "block";
        });
    } else {
        window.location.href = 'proses_booking.php?id=' + id;
    }
}

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

    const loginModal = document.getElementById("loginModal");
    const overlay = document.getElementById('overlay');

    // === 1. LOGIKA DROPDOWN ===
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
                if (!isActive) targetWrapper.classList.add('active');
            }
        } else {
            wrappers.forEach(w => w.classList.remove('active'));
        }
    });

    // === 2. LOGIKA MODAL LOGIN ===
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
    if(toRegister) {
        toRegister.onclick = (e) => {
            e.preventDefault();
            loginFormContainer.style.display = "none";
            registerFormContainer.style.display = "block";
        };
    }
    if(toLogin) {
        toLogin.onclick = (e) => {
            e.preventDefault();
            registerFormContainer.style.display = "none";
            loginFormContainer.style.display = "block";
        };
    }
    window.addEventListener("click", (e) => {
        if (e.target == loginModal) {
            loginModal.style.display = "none";
            loginFormContainer.style.display = "block";
            registerFormContainer.style.display = "none";
        }
    });

    // === 3. LOGIKA BILINGUAL ===
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
            if(btnLangEn) btnLangEn.classList.add('active-lang');
            if(btnLangId) btnLangId.classList.remove('active-lang');
        } else {
            currentFlag.src = 'https://flagcdn.com/w40/id.png';
            currentLangCode.innerText = 'ID';
            checkEn.style.display = 'none';
            checkId.style.display = 'inline-block';
            if(btnLangId) btnLangId.classList.add('active-lang');
            if(btnLangEn) btnLangEn.classList.remove('active-lang');
        }

        document.querySelectorAll('.translatable').forEach(el => {
            if(el.tagName === 'INPUT' && el.hasAttribute('placeholder')) {
                if(!el.getAttribute('data-id-text')) el.setAttribute('data-id-text', el.getAttribute('placeholder'));
                el.setAttribute('placeholder', lang === 'en' ? el.getAttribute('data-en') : el.getAttribute('data-id-text'));
            } else {
                if(!el.getAttribute('data-id-text')) el.setAttribute('data-id-text', el.textContent.trim()); 
                el.textContent = lang === 'en' ? el.getAttribute('data-en') : el.getAttribute('data-id-text');
            }
        });
    }

    if(btnLangId) btnLangId.addEventListener('click', () => setLanguage('id'));
    if(btnLangEn) btnLangEn.addEventListener('click', () => setLanguage('en'));
    
    const savedLang = localStorage.getItem('kinara_lang') || 'id';
    setLanguage(savedLang);
});
</script>
</body>
</html>
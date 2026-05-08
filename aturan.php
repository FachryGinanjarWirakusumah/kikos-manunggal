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
    <title>Aturan Hunian - Kinara Kost</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --kinara-primary: #1abc9c;
            --kinara-dark: #2c3e50;
            --bg-light: #f8f9fa;
        }

        body { font-family: 'Inter', sans-serif; background-color: var(--bg-light); color: var(--kinara-dark); }

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

        /* Header Section */
        .rules-header {
            padding: 120px 20px 60px;
            background: linear-gradient(135deg, #ffffff 0%, #eef2f3 100%);
            text-align: center;
        }

        .rules-header h1 { font-size: 36px; font-weight: 700; margin-bottom: 10px; }

        /* Container Aturan */
        .rules-container { max-width: 1000px; margin: -40px auto 80px; padding: 0 20px; }
        
        .rules-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
        }

        .rule-card {
            background: white;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            border-top: 5px solid var(--kinara-primary);
            transition: transform 0.3s ease;
        }

        .rule-card:hover { transform: translateY(-5px); }

        .rule-card i {
            font-size: 24px;
            color: var(--kinara-primary);
            margin-bottom: 15px;
            display: block;
        }

        .rule-card h3 { margin-bottom: 15px; font-size: 18px; font-weight: 700; }

        .rule-card ul { padding-left: 18px; margin: 0; }
        .rule-card ul li { margin-bottom: 10px; font-size: 14.5px; line-height: 1.6; color: #555; }

        /* Modal Login Styling (Proteksi agar tidak bocor) */
        .login-modal {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0; top: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.6);
            backdrop-filter: blur(5px);
        }
        .form-input { width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #eee; border-radius: 10px; }
        .btn-submit { width: 100%; padding: 12px; background: var(--kinara-primary); color: white; border: none; border-radius: 10px; font-weight: 600; cursor: pointer; }
        .close-btn { position: absolute; right: 25px; top: 20px; font-size: 20px; cursor: pointer; }

        @media (max-width: 768px) {
            .rules-header { padding: 100px 20px 40px; }
        }

/* =========================================
           UI/UX RESPONSIVE LAYOUT (MOBILE FIRST)
           ========================================= */
        *, *::before, *::after { box-sizing: border-box; }

        /* Wrapper Navbar Desktop */
        .nav-menu-wrapper { display: flex; flex: 1; justify-content: space-between; align-items: center; }

        /* Hamburger Menu & Overlay */
        .menu-toggle { display: none; font-size: 24px; cursor: pointer; color: #ff385c; }
        .mobile-overlay {
            display: none; position: fixed; top: 70px; left: 0;
            width: 100%; height: calc(100vh - 70px);
            background: rgba(0, 0, 0, 0.5); backdrop-filter: blur(4px); z-index: 998;
        }
        .mobile-overlay.active { display: block; }

        @media (max-width: 768px) {
            /* 1. Navbar Mobile */
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

            /* 2. Rules Section Mobile */
            .rules-header { padding: 90px 20px 40px; }
            .rules-header h1 { font-size: 28px !important; }
            .rules-container { margin-top: -30px; }
            .rule-card { padding: 20px; border-radius: 15px; }
            .rule-card h3 { font-size: 16px; }

            /* 3. Footer Stacking */
            .footer-container { display: flex; flex-direction: column; gap: 30px; padding: 20px; }
            .footer-bottom { flex-direction: column; text-align: center; gap: 15px; }
        }
    </style>
</head>
<body>
    <div class="overlay" id="overlay"></div>

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

<section class="rules-header">
    <h1 class="translatable" data-en="Kinara Boarding Rules">Aturan Hunian Kinara</h1>
    <p class="text-muted translatable" data-en="For our mutual comfort and peace at Kinara Kost.">Demi kenyamanan dan ketenangan bersama di Kinara Kost.</p>
</section>

<div class="rules-container">
    <div class="rules-grid">
        <?php
        $q_aturan = mysqli_query($conn, "SELECT * FROM aturan_kost ORDER BY urutan ASC");
        while($row = mysqli_fetch_assoc($q_aturan)):
            $list_aturan = explode("\n", str_replace("\r", "", $row['deskripsi']));
            
            // --- TRIK TERJEMAHAN MANUAL KATEGORI ---
            $kategori_id = $row['kategori'];
            $kategori_en = $kategori_id; 

            if (stripos($kategori_id, 'Waktu') !== false) $kategori_en = "Time & Order";
            if (stripos($kategori_id, 'Kebersihan') !== false) $kategori_en = "Cleanliness";
            if (stripos($kategori_id, 'Keamanan') !== false) $kategori_en = "Security & Electricity";
            if (stripos($kategori_id, 'Administrasi') !== false) $kategori_en = "Administration"; // Tambahan Baru
        ?>
        <div class="rule-card">
            <i class="<?= $row['ikon']; ?>"></i>
            <h3 class="translatable" data-en="<?= $kategori_en ?>"><?= htmlspecialchars($kategori_id); ?></h3>
            <ul>
                <?php 
                foreach($list_aturan as $item): 
                    if(trim($item) != ""): 
                        $teks_id = trim($item);
                        $teks_en = $teks_id; 

                        // --- KAMUS HARDCODE FRONTEND ---
                        if ($teks_id == "Jam bertamu maksimal hingga pukul 22.00 WIB.") $teks_en = "Visiting hours are allowed up to 22.00 WIB.";
                        if ($teks_id == "Wajib menjaga ketenangan setelah pukul 21.00 WIB.") $teks_en = "Please keep noise to a minimum after 21.00 WIB.";
                        if ($teks_id == "Lawan jenis dilarang masuk ke dalam kamar.") $teks_en = "Opposite sex is strictly prohibited from entering the room.";
                        
                        if ($teks_id == "Dilarang membuang sampah/benda apapun ke dalam kloset.") $teks_en = "Do not dispose of any garbage/objects into the toilet.";
                        if ($teks_id == "Sampah kamar wajib dibungkus plastik dan ditaruh di tempat yang disediakan.") $teks_en = "Room garbage must be wrapped in plastic and placed in the designated area.";
                        if ($teks_id == "Menjaga kebersihan area bersama (dapur/jemuran).") $teks_en = "Keep common areas clean (kitchen/drying area).";

                        if ($teks_id == "Matikan lampu dan AC saat meninggalkan kamar.") $teks_en = "Turn off lights and AC when leaving the room.";
                        if ($teks_id == "Dilarang membawa perangkat elektronik berdaya tinggi tanpa izin pengelola.") $teks_en = "High-power electronic devices are not allowed without management permission.";
                        if ($teks_id == "Dilarang membawa senjata tajam, miras, atau obat-obatan terlarang.") $teks_en = "Sharp weapons, alcohol, or illegal drugs are strictly prohibited.";
                        
                        // --- TAMBAHAN ATURAN ADMINISTRASI ---
                        if (stripos($teks_id, "Pembayaran kost dilakukan") !== false) $teks_en = "Boarding payment must be made no later than the 5th of every month.";
                        if (stripos($teks_id, "Kehilangan kunci kamar") !== false) $teks_en = "Loss of room keys is the full responsibility of the resident.";
                        if (stripos($teks_id, "Pindah atau keluar") !== false) $teks_en = "Moving out must be reported 2 weeks in advance.";
                ?>
                    <li class="translatable" data-en="<?= htmlspecialchars($teks_en) ?>"><?= htmlspecialchars($teks_id); ?></li>
                <?php 
                    endif; 
                endforeach; 
                ?>
            </ul>
        </div>
        <?php endwhile; ?>
    </div>
</div>

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

    // Terapkan bahasa saat dimuat
    const savedLang = localStorage.getItem('kinara_lang') || 'id';
    setLanguage(savedLang);

});
</script>
</body>
</html>
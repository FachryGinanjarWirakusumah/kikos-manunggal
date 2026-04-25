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
        body { font-family: 'Inter', sans-serif; background-color: #f8f9fa; }

        /* Header */
        .page-header { padding: 120px 20px 40px; text-align: center; background: white; }

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
        
        /* Filter Tabs */
        .filter-nav { display: flex; justify-content: center; gap: 15px; margin-bottom: 40px; }
        .btn-filter { 
            padding: 10px 25px; border-radius: 50px; border: 1px solid #ddd; 
            background: white; cursor: pointer; transition: 0.3s; font-weight: 600; text-decoration: none; color: #666;
        }
        .btn-filter.active { background: var(--kinara-teal); color: white; border-color: var(--kinara-teal); }

        /* Grid Kamar */
        .kamar-container { max-width: 1200px; margin: 0 auto 80px; padding: 0 20px; }
        .kamar-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 30px; }
        
        .kamar-card { 
            background: white; border-radius: 20px; overflow: hidden; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.05); transition: 0.3s; position: relative;
        }
        .kamar-card:hover { transform: translateY(-10px); }
        
        .kamar-img { width: 100%; height: 220px; object-fit: cover; }
        
        .kamar-info { padding: 20px; }
        .badge-tipe { 
            font-size: 11px; font-weight: 700; padding: 5px 12px; border-radius: 5px; 
            text-transform: uppercase; margin-bottom: 10px; display: inline-block;
        }
        .tipe-ikhwan { background: #e3f2fd; color: #1976d2; }
        .tipe-akhwat { background: #fce4ec; color: #c2185b; }
        
        .harga { font-size: 20px; font-weight: 700; color: var(--kinara-pink); margin: 10px 0; }
        .status-tag { 
            position: absolute; top: 15px; right: 15px; padding: 5px 15px; 
            border-radius: 50px; font-size: 12px; font-weight: 700; 
        }
        .status-tersedia { background: #2ecc71; color: white; }
        .status-penuh { background: #e74c3c; color: white; }

        .btn-booking { 
            width: 100%; padding: 12px; border: none; border-radius: 12px; 
            background: var(--kinara-pink); color: white; font-weight: 700; cursor: pointer;
        }
        .btn-booking:disabled { background: #ccc; cursor: not-allowed; }

        /* Modal Login */
        .login-modal { display: none; position: fixed; z-index: 10000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); backdrop-filter: blur(4px); }
        .login-box { background: #fff; width: 90%; max-width: 400px; margin: 10vh auto; padding: 30px; border-radius: 16px; position: relative; }
        .form-input { width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #ddd; border-radius: 8px; box-sizing: border-box; }
        .close-btn { position: absolute; right: 20px; top: 20px; font-size: 24px; cursor: pointer; color: #999; }
    </style>
</head>
<body>
<div class="overlay" id="overlay"></div>

    <div class="overlay" id="overlay"></div>

<nav class="navbar" >
    <div class="nav-container">
        <div class="logo">KINARA</div>

        <ul class="nav-links">
            <li>
                <a href="index.php" class="<?= ($current_page == 'index.php') ? 'active' : ''; ?>">Home</a>
            </li>

            <li class="dropdown-wrapper">
                <div class="nav-item <?= ($current_page == 'tentang_kami.php') ? 'active' : ''; ?>">
                    Tentang Kinara <i class="fas fa-chevron-down"></i>
                </div>
                
                <div class="dropdown-content menu-tentang">
                    <a href="tentang_kami.php" class="dropdown-link <?= ($current_page == 'tentang_kami.php') ? 'active' : ''; ?>">
                        <div class="dropdown-item">
                            <div class="icon-box"><i class="fas fa-users"></i></div>
                            <div class="text">
                                <strong>Tentang Kami</strong>
                                <p>Membangun solusi hunian yang lebih mudah diakses.</p>
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
                    Cek Unit Kamar <i class="fas fa-chevron-down"></i>
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
            <div class="dropdown-wrapper">
                <div class="nav-item">
                    <img src="https://flagcdn.com/w40/id.png" class="flag-circle" alt="ID"> 
                    <span>ID</span> <i class="fas fa-chevron-down"></i>
                </div>

                <div class="dropdown-content lang-dropdown">
                    <h4 class="dropdown-title">Pilih Bahasa</h4>
                    
                    <div class="dropdown-item lang-option active-lang">
                        <img src="https://flagcdn.com/w40/id.png" class="flag-circle">
                        <span class="lang-text">Bahasa Indonesia</span>
                        <i class="fas fa-check check-icon"></i>
                    </div>

                    <div class="dropdown-item lang-option">
                        <img src="https://flagcdn.com/w40/us.png" class="flag-circle">
                        <span class="lang-text">Bahasa Inggris</span>
                    </div>
                </div>
            </div>

            <!-- LOGIN BUTTON -->
<?php if(isset($_SESSION['login'])): ?>
    <div class="dropdown-wrapper">
        <button class="login-btn">
            <i class="far fa-user"></i> Halo, <?= $_SESSION['nama']; ?>
        </button>
        <div class="dropdown-content">
            <a href="logout.php" style="color: red; padding: 10px; display: block; text-decoration: none;">
                <i class="fas fa-sign-out-alt"></i> Keluar
            </a>
        </div>
    </div>
<?php else: ?>
    <button class="login-btn" id="openLogin">
        <i class="far fa-user"></i> Masuk / Daftar
    </button>
<?php endif; ?>

        </div>
    </div>
</nav>

<!-- LOGIN MODAL -->
<div class="login-modal" id="loginModal">
    <div class="login-box">
        <span class="close-btn" id="closeLogin">&times;</span>

        <div id="loginFormContainer">
            <h3>Selamat Datang</h3>
            <p class="subtitle">Masuk untuk mulai mencari hunian impianmu.</p>

            <form action="login_process.php" method="POST">
                <div class="form-group">
                    <label>Email atau Nomor HP</label>
                    <input type="text" name="username" class="form-input" placeholder="contoh: email@anda.com" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" class="form-input" placeholder="Masukkan password" required>
                </div>
                <button type="submit" class="btn-submit">Masuk</button>
            </form>
            <p class="signup-text">
                Belum punya akun? <a href="javascript:void(0)" id="toRegister">Daftar Sekarang</a>
            </p>
        </div>

        <div id="registerFormContainer" style="display: none;">
            <h3>Daftar Akun Baru</h3>
            <p class="subtitle">Lengkapi data diri untuk bergabung dengan Kinara.</p>

            <form action="register_process.php" method="POST">
                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" name="nama" class="form-input" placeholder="Nama sesuai KTP" required>
                </div>
                <div class="form-group">
                    <label>Email / No. HP</label>
                    <input type="text" name="kontak" class="form-input" placeholder="Email atau WhatsApp aktif" required>
                </div>
                <div class="form-group">
                    <label>Buat Password</label>
                    <input type="password" name="password" class="form-input" placeholder="Minimal 8 karakter" required>
                </div>
                <button type="submit" class="btn-submit">Daftar User</button>
            </form>
            <p class="signup-text">
                Sudah punya akun? <a href="javascript:void(0)" id="toLogin">Masuk di sini</a>
            </p>
        </div>
    </div>
</div>

    <header class="page-header">
        <h1 class="fw-bold">Pilih Kamar Impianmu</h1>
        <p class="text-muted">Temukan kenyamanan terbaik di unit-unit Kinara Kost.</p>
    </header>

    <div class="kamar-container">
        <div class="filter-nav">
            <a href="cek_kamar.php?tipe=semua" class="btn-filter <?= ($filter_tipe == 'semua') ? 'active' : ''; ?>">Semua</a>
            <a href="cek_kamar.php?tipe=ikhwan" class="btn-filter <?= ($filter_tipe == 'ikhwan') ? 'active' : ''; ?>">Ikhwan</a>
            <a href="cek_kamar.php?tipe=akhwat" class="btn-filter <?= ($filter_tipe == 'akhwat') ? 'active' : ''; ?>">Akhwat</a>
        </div>

        <div class="kamar-grid">
            <?php while($k = mysqli_fetch_assoc($query_kamar)): ?>
            <div class="kamar-card">
                <span class="status-tag <?= ($k['status'] == 'tersedia') ? 'status-tersedia' : 'status-penuh'; ?>">
                    <?= ucfirst($k['status']); ?>
                </span>
                <img src="img/<?= $k['gambar']; ?>" class="kamar-img" alt="<?= $k['nama_kamar']; ?>" onerror="this.src='https://via.placeholder.com/400x250'">
                
                <div class="kamar-info">
                    <span class="badge-tipe <?= ($k['tipe'] == 'ikhwan') ? 'tipe-ikhwan' : 'tipe-akhwat'; ?>">
                        Kost <?= ucfirst($k['tipe']); ?>
                    </span>
                    <h3 class="fw-bold m-0"><?= $k['nama_kamar']; ?></h3>
                    <p class="text-muted small"><i class="fas fa-map-marker-alt me-1"></i> <?= $k['lokasi']; ?></p>
                    <div class="harga">Rp <?= number_format($k['harga'], 0, ',', '.'); ?><span style="font-size: 12px; color: #999;"> / bulan</span></div>
                    
                    <?php if($k['status'] == 'tersedia'): ?>
                        <button class="btn-booking" onclick="prosesBooking(<?= $k['id']; ?>)">Booking Sekarang</button>
                    <?php else: ?>
                        <button class="btn-booking" disabled>Kamar Penuh</button>
                    <?php endif; ?>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>

<footer class="main-footer">
    <div class="footer-container">
        <!-- <div class="footer-column brand-section">
            <img src="logo-rukita.png" alt="Rukita Logo" class="footer-logo">
            <div class="app-download-area">
                <img src="qr-code.png" alt="QR Code" class="qr-code">
                <div class="download-buttons">
                    <a href="#" class="btn-download">
                        <img src="apple-icon.png" alt="App Store"> Download Aplikasi
                    </a>
                    <a href="#" class="btn-login">Masuk / Daftar</a>
                </div>
            </div>
        </div> -->

        <div class="footer-column">
            <div class="footer-group">
                <h3>Tenant</h3>
                <ul>
                    <li><a href="#">Kost</a></li>
                    <li><a href="#">Apartemen</a></li>
                    <li><a href="#">Community</a></li>
                </ul>
            </div>
            <div class="footer-group">
                <h3>Kerjasama kinara</h3>
                <ul>
                    <li><a href="#">Coliving</a></li>
                    <li><a href="#">Apartemen</a></li>
                    <li><a href="#">Build to Rent</a></li>
                    <li><a href="#">RuFinance</a></li>
                </ul>
            </div>
        </div>

        <div class="footer-column">
            <div class="footer-group">
                <h3>kinara For Business</h3>
                <ul>
                    <li><a href="#">kinara For Business</a></li>
                    <li><a href="#">Corporate Subscription</a></li>
                    <li><a href="#">RuCollab</a></li>
                </ul>
            </div>
            <div class="footer-group">
                <h3>Tentang kinara</h3>
                <ul>
                    <li><a href="#">Tentang Kami</a></li>
                    <li><a href="#">Komitmen ESG Kami</a></li>
                </ul>
            </div>
        </div>

        <div class="footer-column">
            <div class="footer-group">
                <h3>Resource</h3>
                <ul>
                    <li><a href="#">FAQ</a></li>
                    <li><a href="#">Karir</a></li>
                    <li><a href="#">Stories</a></li>
                    <li><a href="#">Pusat Bantuan</a></li>
                </ul>
            </div>
            <div class="footer-group">
                <h3>Brand Partner</h3>
                <ul>
                    <li><a href="#">Uma Living</a></li>
                    <li><a href="#">Infokost Pro</a></li>
                </ul>
            </div>
        </div>

        <div class="footer-column">
            <h3>Support</h3>
            <ul class="support-list">
                <li><i class="fab fa-whatsapp"></i> +62 811-900-87829</li>
                <li><i class="far fa-envelope"></i> info@kinara.com</li>
            </ul>
            <div class="operational-hours">
                <strong>Jam Operasional</strong>
                <p>Senin - Jumat: 8.00 - 17.00</p>
                <p>Sabtu - Minggu: 8.30 - 16.30</p>
            </div>
        </div>
    </div>

    <div class="footer-bottom">
        <div class="bottom-left">
            <span>© 2026 kinara. All rights reserved.</span>
            <a href="#">Syarat & Ketentuan</a>
            <a href="#">Kebijakan Privasi</a>
        </div>
        <div class="social-icons">
            <a href="#"><i class="fab fa-facebook"></i></a>
            <a href="#"><i class="fab fa-instagram"></i></a>
            <a href="#"><i class="fab fa-twitter"></i></a>
            <a href="#"><i class="fab fa-tiktok"></i></a>
        </div>
    </div>
</footer>

<a href="https://wa.me/6281190087829" class="fab-whatsapp" target="_blank">
    <img src="https://upload.wikimedia.org/wikipedia/commons/6/6b/WhatsApp.svg" alt="WhatsApp Help">
</a>

<script>
const isLogin = <?= isset($_SESSION['login']) ? 'true' : 'false'; ?>;
const loginModal = document.getElementById("loginModal");
const overlay = document.getElementById('overlay');

// 1. FUNGSI BOOKING & LOGIN REDIRECT
function prosesBooking(id) {
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
        
        Swal.fire({
            title: 'Belum Login',
            text: "Silakan login untuk melanjutkan booking.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ff385c',
            confirmButtonText: 'Login Sekarang'
        }).then((result) => {
            if (result.isConfirmed) loginModal.style.display = "block";
        });
    } else {
        window.location.href = 'proses_booking.php?id=' + id;
    }
}

// 2. LOGIKA DROPDOWN NAVBAR (VERSI FIX: KLIK LUAR OTOMATIS TUTUP)
document.addEventListener('click', function(e) {
    const wrappers = document.querySelectorAll('.dropdown-wrapper');
    
    // Cek apakah yang diklik berada di dalam dropdown-wrapper mana pun
    let targetWrapper = e.target.closest('.dropdown-wrapper');

    if (targetWrapper) {
        // Jika yang diklik adalah Trigger (Tombol/Menu)
        const trigger = e.target.closest('.nav-item, .login-btn');
        if (trigger) {
            e.preventDefault();
            e.stopPropagation(); // Stop agar tidak dianggap klik di luar
            
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
        // JIKA KLIK DI MANA SAJA YANG BUKAN DROPDOWN, TUTUP SEMUA
        wrappers.forEach(w => w.classList.remove('active'));
        if(overlay) overlay.classList.remove('show');
    }
});

// 3. LOGIKA MODAL LOGIN & REGISTER (SWITCHING)
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
        // Reset tampilan ke form login saat ditutup
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

// Tutup modal jika klik di area hitam (overlay modal)
window.addEventListener("click", (e) => {
    if (e.target == loginModal) {
        loginModal.style.display = "none";
        // Reset form
        loginFormContainer.style.display = "block";
        registerFormContainer.style.display = "none";
    }
});
</script>
</body>
</html>

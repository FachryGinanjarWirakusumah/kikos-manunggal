<?php
session_start();
include 'config.php';
$current_page = basename($_SERVER['PHP_SELF']); 

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

// Ambil data user jika sudah login untuk auto-fill form
$user_nama = isset($_SESSION['nama']) ? $_SESSION['nama'] : '';
$user_kontak = isset($_SESSION['kontak']) ? $_SESSION['kontak'] : '';

// --- TAMBAHAN: AMBIL DATA PROMO AKTIF DARI DATABASE ---
$tgl_sekarang = date('Y-m-d');
// Pastikan query mengambil kolom 'diskon' juga
$query_promo_aktif = mysqli_query($conn, "SELECT judul, diskon FROM promo WHERE tanggal_akhir >= '$tgl_sekarang'");
$promo_aktif_array = [];
while($p_data = mysqli_fetch_assoc($query_promo_aktif)){
    // PERUBAHAN: Hapus strtoupper() agar huruf besar & kecil sesuai aslinya di database
    $kode_promo = trim($p_data['judul']);
    $promo_aktif_array[$kode_promo] = (int)$p_data['diskon']; 
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail <?= $k['nama_kamar']; ?> - Kinara Kost</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root { --kinara-pink: #ff385c; --kinara-teal: #1abc9c; }
        body { font-family: 'Inter', sans-serif; background-color: #fff; color: #222; margin: 0; }
        .container { max-width: 1100px; margin: 0 auto; padding: 20px; }
        
        /* Modal Login */
        .login-modal { display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.7); backdrop-filter: blur(4px); }
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

        /* Galeri */
        .gallery-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 10px; margin-top: 100px; border-radius: 15px; overflow: hidden; height: 450px; position: relative; }
        .main-img-wrapper { width: 100%; height: 100%; overflow: hidden; cursor: pointer; }
        .main-img { width: 100%; height: 100%; object-fit: cover; transition: 0.3s; }
        .side-gallery { display: grid; grid-template-rows: 1fr 1fr; gap: 10px; }
        .side-img-wrapper { width: 100%; height: 100%; overflow: hidden; cursor: pointer; position: relative; }
        .side-img { width: 100%; height: 100%; object-fit: cover; transition: 0.3s; }
        .more-photos-overlay { position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); color: white; display: flex; align-items: center; justify-content: center; font-size: 20px; font-weight: 700; }
        .btn-view-all { position: absolute; bottom: 20px; right: 20px; background: white; padding: 8px 16px; border-radius: 8px; font-size: 14px; font-weight: 600; border: 1px solid #222; cursor: pointer; z-index: 10; display: flex; align-items: center; gap: 8px; }

        /* Content & Sidebar */
        .content-wrapper { display: grid; grid-template-columns: 1.8fr 1.2fr; gap: 50px; margin-top: 30px; align-items: start;}
        .kamar-title { font-size: 32px; font-weight: 700; margin-bottom: 5px; }
        .location-text { color: #666; display: flex; align-items: center; gap: 5px; margin-bottom: 20px; flex-wrap: wrap;}
        .section-title { font-size: 20px; font-weight: 700; margin: 30px 0 15px; border-bottom: 1px solid #eee; padding-bottom: 10px; }
        
        .fasilitas-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; }
        .fasilitas-item { display: flex; align-items: center; gap: 12px; font-size: 15px; color: #444; }
        .fasilitas-item i { width: 20px; color: var(--kinara-teal); font-size: 18px; }

        /* Form Booking Box */
        .booking-card { border: 1px solid #ddd; padding: 25px; border-radius: 15px; box-shadow: 0 10px 25px rgba(0,0,0,0.07); position: sticky; top: 100px; background: white; }
        .price-big { font-size: 24px; font-weight: 700; color: var(--kinara-pink); display: flex; justify-content: space-between; align-items: center;}
        
        /* Input Form Spesifik Booking */
        .book-input-group { margin-bottom: 15px; }
        .book-input-group label { display: block; font-size: 13px; font-weight: 600; color: #555; margin-bottom: 6px; }
        .book-input { width: 100%; padding: 10px 14px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px; font-family: inherit; transition: 0.3s;}
        .book-input:focus { border-color: var(--kinara-teal); outline: none; box-shadow: 0 0 0 3px rgba(26, 188, 156, 0.1); }
        textarea.book-input { resize: vertical; min-height: 60px; }
        
        .promo-box { display: flex; gap: 10px; }
        .btn-apply-promo { background: #222; color: #fff; border: none; padding: 0 15px; border-radius: 8px; font-weight: 600; cursor: pointer; transition: 0.2s;}
        .btn-apply-promo:hover { background: #000; }
        .promo-msg { font-size: 12px; margin-top: 5px; display: block; }
        .text-success-promo { color: var(--kinara-teal); font-weight: 600; }
        .text-danger-promo { color: #e74c3c; }

        .rincian-harga { background: #f8f9fa; padding: 15px; border-radius: 10px; margin: 20px 0; font-size: 14px;}
        .rincian-flex { display: flex; justify-content: space-between; margin-bottom: 8px; color: #555;}
        .rincian-total { display: flex; justify-content: space-between; font-weight: 700; font-size: 16px; margin-top: 10px; padding-top: 10px; border-top: 1px dashed #ccc; color: #222;}

        .btn-confirm { width: 100%; background: var(--kinara-pink); color: white; border: none; padding: 15px; border-radius: 10px; font-weight: 700; cursor: pointer; transition: 0.3s; font-size: 16px;}
        .btn-confirm:hover { background: #e31c5f; }

        /* Promo & Rekomendasi Section */
        .promo-section { margin-top: 60px; padding-top: 30px; border-top: 1px solid #eee; }
        .rekomendasi-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 20px; margin-top: 20px; }
        .reko-card { border-radius: 12px; overflow: hidden; border: 1px solid #eee; text-decoration: none; color: inherit; transition: 0.3s; background: #fff; }
        .reko-card:hover { transform: translateY(-5px); box-shadow: 0 8px 20px rgba(0,0,0,0.1); }
        .reko-img { width: 100%; height: 150px; object-fit: cover; }
        .reko-info { padding: 15px; }

        /* Lightbox */
        .lightbox { display: none; position: fixed; z-index: 10000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.95); }
        .lightbox-content { position: relative; width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; flex-direction: column; }
        .lightbox-media { max-width: 90%; max-height: 75vh; border-radius: 8px; }
        .lightbox-close { position: absolute; top: 30px; right: 30px; color: white; font-size: 40px; cursor: pointer; }
        /* Perbaikan Posisi Tombol Navigasi Lightbox */
        .lightbox-nav { 
            position: absolute; 
            top: 50%; /* Kunci di tengah vertikal */
            transform: translateY(-50%); 
            width: 100%; 
            display: flex; 
            justify-content: space-between; 
            padding: 0 30px; 
            pointer-events: none; 
            z-index: 1050; /* Pastikan selalu berada di atas gambar */
        }
        .nav-btn { 
            background: rgba(255, 255, 255, 0.15); 
            color: white; 
            border: none; 
            width: 55px; 
            height: 55px; 
            border-radius: 50%; 
            cursor: pointer; 
            font-size: 20px; 
            pointer-events: auto; 
            transition: all 0.3s ease; 
            backdrop-filter: blur(4px); /* Efek kaca modern */
        }
        .nav-btn:hover {
            background: var(--kinara-pink);
            transform: scale(1.1);
        }
        .media-counter { color: white; margin-top: 20px; font-size: 14px; font-weight: 600; background: rgba(255,255,255,0.1); padding: 5px 15px; border-radius: 20px; }

        /* =========================================
           ALUR PEMESANAN (BOOKING STEPS) - PREMIUM UI
           ========================================= */
        .booking-steps-section {
            margin-top: 60px;
            padding: 40px 0;
            border-top: 1px solid #eee;
        }
        .steps-header-wrap {
            text-align: center;
            margin-bottom: 40px;
        }
        .steps-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(26, 188, 156, 0.1); /* Soft Kinara Teal */
            color: var(--kinara-teal);
            padding: 6px 18px;
            border-radius: 50px;
            font-size: 13px;
            font-weight: 700;
            margin-bottom: 15px;
        }
        .steps-title {
            font-size: 28px;
            font-weight: 800;
            color: #222;
            margin-bottom: 10px;
        }
        .steps-subtitle {
            font-size: 15px;
            color: #666;
            max-width: 600px;
            margin: 0 auto;
            line-height: 1.6;
        }
        
        .steps-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 25px;
        }
        
        /* Premium Card Styling */
        .step-card {
            background: #fff;
            padding: 30px;
            border-radius: 20px;
            position: relative;
            box-shadow: 0 10px 30px rgba(0,0,0,0.04);
            border: 1px solid #f0f0f0;
            transition: 0.4s ease;
            overflow: hidden;
            z-index: 1;
        }
        
        /* Floating Hover Effect */
        .step-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 35px rgba(255, 56, 92, 0.12); /* Soft Pink Shadow */
            border-color: rgba(255, 56, 92, 0.2);
        }
        
        /* Giant Watermark Number (01, 02, 03) */
        .step-card::after {
            content: attr(data-step);
            position: absolute;
            top: -10px;
            right: -10px;
            font-size: 100px;
            font-weight: 900;
            color: rgba(0,0,0,0.03);
            z-index: -1;
            transition: 0.4s ease;
        }
        .step-card:hover::after {
            color: rgba(255, 56, 92, 0.05);
            transform: scale(1.1);
        }

        /* Gradient Icon Box */
        .step-icon-wrap {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--kinara-pink) 0%, #ff6b8b 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 16px;
            font-size: 24px;
            margin-bottom: 20px;
            box-shadow: 0 8px 20px rgba(255, 56, 92, 0.3);
        }

        /* Variasi Warna Teal untuk Kartu Nomor 2 */
        .step-card:nth-child(2) .step-icon-wrap {
            background: linear-gradient(135deg, var(--kinara-teal) 0%, #48c9b0 100%);
            box-shadow: 0 8px 20px rgba(26, 188, 156, 0.3);
        }
        .step-card:nth-child(2):hover {
            box-shadow: 0 15px 35px rgba(26, 188, 156, 0.15);
            border-color: rgba(26, 188, 156, 0.3);
        }
        .step-card:nth-child(2):hover::after {
            color: rgba(26, 188, 156, 0.06);
        }

        .step-card h4 { font-size: 18px; font-weight: 700; color: #222; margin-bottom: 12px; }
        .step-card p { font-size: 14px; color: #666; line-height: 1.6; margin: 0; }

        /* Responsif Mobile */
        @media (max-width: 768px) {
            .steps-grid { grid-template-columns: 1fr; gap: 20px; }
            .booking-steps-section { padding: 30px 0; }
            .steps-title { font-size: 24px; }
            .step-card { padding: 25px; }
        }

        /* =========================================
           CSS KHUSUS KOLOM & INPUT TANGGAL (MULAI HUNI)
           ========================================= */
        .form-row-custom {
            display: flex;
            gap: 15px;
            margin-bottom: 18px;
        }
        .form-col-custom {
            flex: 1;
        }
        .form-col-custom .book-input-group {
            margin-bottom: 0; /* Menghindari margin ganda */
        }
        
        /* Modifikasi bentuk kalender agar seragam & premium */
        input[type="date"].book-input {
            appearance: none;
            -webkit-appearance: none;
            color: #444;
            font-family: 'Inter', sans-serif;
            background-color: #fdfdfd;
            cursor: text;
        }
        
        /* Ikon kalender bawaan browser agar lebih rapi */
        input[type="date"].book-input::-webkit-calendar-picker-indicator {
            cursor: pointer;
            opacity: 0.6;
            transition: 0.2s;
        }
        input[type="date"].book-input::-webkit-calendar-picker-indicator:hover {
            opacity: 1;
        }

        /* Saat di HP, kolom Lama Sewa & Mulai Huni ditumpuk ke bawah */
        @media (max-width: 576px) {
            .form-row-custom {
                flex-direction: column;
                gap: 18px;
                margin-bottom: 18px;
            }
        }

        /* =========================================
           SYARAT & KETENTUAN CHECKBOX (PREMIUM UI)
           ========================================= */
        .terms-box { 
            background: #fdfdfd; 
            border: 1.5px solid #eaeaea; 
            padding: 16px 20px; 
            border-radius: 12px; 
            margin-bottom: 25px; 
            display: flex; 
            gap: 15px; 
            align-items: center; 
            cursor: pointer;
            transition: all 0.3s ease;
        }

        /* Efek nyala saat box diarahkan kursor */
        .terms-box:hover {
            border-color: var(--kinara-pink);
            background: #fffafa; /* Warna pink sangat pudar */
            box-shadow: 0 4px 15px rgba(255, 56, 92, 0.05);
        }

        .checkbox-wrapper {
            display: flex;
            align-items: center;
        }

        .terms-box input[type="checkbox"] { 
            width: 20px; 
            height: 20px; 
            accent-color: var(--kinara-pink); 
            cursor: pointer;
            margin: 0;
        }

        .terms-text { 
            font-size: 13px; 
            color: #555; 
            line-height: 1.6;
        }

        .terms-text a {
            color: var(--kinara-pink); 
            text-decoration: none; 
            font-weight: 700;
            transition: 0.2s;
        }

        .terms-text a:hover {
            color: #e31c5f;
            text-decoration: underline;
        }

        /* =========================================
           UI/UX RESPONSIVE LAYOUT (MOBILE FIRST)
           ========================================= */
        *, *::before, *::after { box-sizing: border-box; }

        .nav-menu-wrapper { display: flex; flex: 1; justify-content: space-between; align-items: center; }
        .menu-toggle { display: none; font-size: 24px; cursor: pointer; color: #ff385c; }
        .mobile-overlay { display: none; position: fixed; top: 70px; left: 0; width: 100%; height: calc(100vh - 70px); background: rgba(0, 0, 0, 0.5); backdrop-filter: blur(4px); z-index: 998; }
        .mobile-overlay.active { display: block; }

        /* MEDIA QUERIES MOBILE */
        @media (max-width: 768px) {
            /* Navbar */
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

            /* Gallery Grid Mobile */
            .gallery-grid { grid-template-columns: 1fr; grid-template-rows: 250px 100px; height: auto; margin-top: 80px; }
            .side-gallery { grid-template-columns: 1fr 1fr; grid-template-rows: 1fr; }
            
            /* Content Layout Mobile */
            .content-wrapper { grid-template-columns: 1fr; gap: 30px; margin-top: 20px; }
            .kamar-title { font-size: 24px; }
            
            /* Booking Card Mobile Fix */
            .booking-card { position: static; box-shadow: 0 -5px 20px rgba(0,0,0,0.05); border-radius: 15px; padding: 20px; }
            
            /* Footer Stacking */
            .footer-container { display: flex; flex-direction: column; gap: 30px; padding: 20px; }
            .footer-bottom { flex-direction: column; text-align: center; gap: 15px; }
        }

        /* SMALL MOBILE */
        @media (max-width: 576px) {
            .fasilitas-grid { grid-template-columns: 1fr; } 
            .rekomendasi-grid { grid-template-columns: 1fr; }
            
            /* PERUBAHAN: Penyesuaian form booking di layar HP kecil */
            .promo-box { flex-direction: column; gap: 5px; }
            .btn-apply-promo { width: 100%; padding: 12px; }
            .price-big { font-size: 20px; flex-direction: column; align-items: flex-start; gap: 5px; }
        }
    </style>
</head>
<body>

<nav class="navbar">
    <div class="nav-container">
        <div class="logo"><a href="index.php" style="text-decoration:none; color:inherit;">KINARA</a></div>

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
                    <div class="nav-item <?= ($current_page == 'aturan.php' || $current_page == 'cek_kamar.php' || $current_page == 'proses_booking.php') ? 'active' : ''; ?>">
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
                        <div class="more-photos-overlay">+<?= $media_count - 3; ?> <span class="translatable" data-en=" More Media"> Media Lainnya</span></div>
                    <?php endif; ?>
                </div>
            <?php endif; endfor; ?>
        </div>
        <button class="btn-view-all translatable" data-en="View all media" onclick="openLightbox(0)"><i class="fas fa-th"></i> Lihat semua media</button>
    </div>

    <div class="content-wrapper">
        <div class="main-info">
            <?php $nama_kamar_en = str_ireplace('Kamar', 'Room', $k['nama_kamar']); ?>
            <h1 class="kamar-title translatable" data-en="<?= $nama_kamar_en ?>"><?= $k['nama_kamar']; ?></h1>
            <div class="location-text">
                <i class="fas fa-map-marker-alt text-danger"></i> <?= $k['lokasi']; ?> • 
                <?php $tipe_en = $k['tipe'] == 'ikhwan' ? "Men's Boarding" : "Women's Boarding"; ?>
                <span class="badge-rukita translatable" data-en="<?= $tipe_en ?>" style="background:var(--kinara-teal); margin-bottom:0; margin-left: 5px;">Kost <?= ucfirst($k['tipe']); ?></span>
            </div>

            <h2 class="section-title translatable" data-en="Facilities & Description">Fasilitas & Deskripsi</h2>
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
                        
                        $text_en = $text;
                        if(stripos($text, 'Kamar Mandi Dalam') !== false) $text_en = "Ensuite Bathroom";
                        if(stripos($text, 'Kasur Springbed') !== false) $text_en = "Springbed Mattress";
                        if(stripos($text, 'Listrik Token') !== false) $text_en = "Token Electricity";
                        if(stripos($text, 'Lemari Pakaian') !== false) $text_en = "Wardrobe";
                        if(stripos($text, 'Kipas Angin') !== false) $text_en = "Fan";
                        if(stripos($text, 'Meja Belajar') !== false) $text_en = "Study Desk";
                ?>
                    <div class="fasilitas-item">
                        <i class="fas <?= $icon; ?> text-teal"></i> <span class="translatable" data-en="<?= $text_en ?>"><?= htmlspecialchars($text); ?></span>
                    </div>
                <?php endforeach; else: ?>
                    <p class="text-muted translatable" data-en="Clean and comfortable room in a strategic location.">Kamar bersih dan nyaman di lokasi strategis.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="side-booking">
            <form action="bayar.php?id=<?= $id_kamar; ?>" method="POST" id="formBooking" class="booking-card">
                <input type="hidden" name="id_kamar" value="<?= $id_kamar; ?>">
                <input type="hidden" id="harga_dasar" value="<?= $k['harga']; ?>">
                <input type="hidden" name="total_bayar" id="total_bayar_input" value="<?= $k['harga']; ?>">

                <div class="price-big" id="tampil_harga">
                    Rp <?= number_format($k['harga'], 0, ',', '.'); ?> 
                    <span class="translatable" data-en="/ month" style="font-size:14px; font-weight:400; color:#666;">/ bulan</span>
                </div>
                
                <?php 
                    $status_id = ucfirst($k['status']);
                    $status_en = strtolower($status_id) == 'tersedia' ? 'Available' : 'Full';
                    $status_color = strtolower($status_id) == 'tersedia' ? 'text-success' : 'text-danger';
                ?>
                <div style="font-size: 14px; margin: 10px 0 20px;"><span class="translatable" data-en="Room Status: ">Status Kamar: </span> <strong class="<?= $status_color ?> translatable" data-en="<?= $status_en ?>"><?= $status_id ?></strong></div>
                
                <hr style="margin: 20px 0; opacity: 0.1;">

                <h4 style="font-size: 16px; margin-bottom: 15px;" class="translatable" data-en="Booking Details">Data Pemesanan</h4>

                <div class="book-input-group">
                    <label class="translatable" data-en="Full Name (as per ID)">Nama Sesuai KTP <span class="text-danger">*</span></label>
                    <input type="text" name="nama_penyewa" class="book-input translatable" data-en="Enter full name" placeholder="Masukkan nama lengkap" value="<?= $user_nama; ?>" required>
                </div>

                <div class="book-input-group">
                    <label class="translatable" data-en="Active Phone/WhatsApp Number">Nomor HP/WhatsApp Aktif <span class="text-danger">*</span></label>
                    <input type="text" name="kontak_penyewa" class="book-input translatable" data-en="e.g. 0812xxxx" placeholder="Contoh: 0812xxxx" value="<?= $user_kontak; ?>" required>
                </div>

                <div class="book-input-group">
                    <label class="translatable" data-en="Origin Address">Alamat Asal <span class="text-danger">*</span></label>
                    <textarea name="alamat_penyewa" class="book-input translatable" data-en="Enter complete address" placeholder="Masukkan alamat lengkap" required></textarea>
                </div>

                <div class="form-row-custom">
                    <div class="form-col-custom">
                        <div class="book-input-group">
                            <label class="translatable" data-en="Duration of Stay">Lama Sewa <span class="text-danger">*</span></label>
                            <select name="lama_sewa" id="lama_sewa" class="book-input" onchange="hitungTotal()" required>
                                <option value="1">1 Bulan</option>
                                <option value="3">3 Bulan</option>
                                <option value="6">6 Bulan</option>
                                <option value="12">1 Tahun</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-col-custom">
                        <div class="book-input-group">
                            <label class="translatable" data-en="Move-in Date">Mulai Huni <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_mulai" class="book-input" required min="<?= date('Y-m-d'); ?>">
                        </div>
                    </div>
                </div>

                <div class="form-row-custom">
                    <div class="form-col-custom">
                        <div class="book-input-group">
                            <label class="translatable" data-en="Payment Option">Opsi Pembayaran <span class="text-danger">*</span></label>
                            <select name="tipe_pembayaran" id="tipe_pembayaran" class="book-input" onchange="hitungTotal()" required>
                                <option value="lunas" class="translatable" data-en="Pay in Full">Bayar Penuh (Lunas)</option>
                                <option value="dp" class="translatable" data-en="Booking (Down Payment)">Booking (Down Payment)</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-col-custom">
                        <div class="book-input-group">
                            <label class="translatable" data-en="Promo Code (Optional)">Kode Promo (Opsional)</label>
                            <div class="promo-box">
                                <input type="text" id="kode_promo" name="kode_promo" class="book-input translatable" data-en="Enter code here" placeholder="Masukkan kode">
                                <button type="button" class="btn-apply-promo translatable" data-en="Apply" onclick="cekPromo()">Terapkan</button>
                            </div>
                            <span id="promo_msg" class="promo-msg"></span>
                            <input type="hidden" id="diskon_nominal" name="diskon_nominal" value="0">
                        </div>
                    </div>
                </div>

                <div class="rincian-harga">
                    <div class="rincian-flex">
                        <span class="translatable" data-en="Rent Price">Harga Sewa</span>
                        <span id="rincian_harga_sewa" style="font-weight: 600;">Rp <?= number_format($k['harga'], 0, ',', '.'); ?></span>
                    </div>
                    <div class="rincian-flex" id="baris_diskon" style="display:none; color: var(--kinara-teal); font-weight: 600;">
                        <span class="translatable" data-en="Promo Discount">Diskon Promo</span>
                        <span id="rincian_diskon">- Rp 0</span>
                    </div>
                    <div class="rincian-total">
                        <span id="label_total_bayar" class="translatable" data-en="Total Payment">Total Bayar</span>
                        <span id="rincian_total_bayar" style="color: var(--kinara-pink);">Rp <?= number_format($k['harga'], 0, ',', '.'); ?></span>
                    </div>
                </div>

                <div id="info_sisa_bayar" style="display: none; font-size: 13px; color: #e74c3c; margin-bottom: 20px; text-align: right; background: rgba(231, 76, 60, 0.05); padding: 10px; border-radius: 8px;">
                    *Sisa pelunasan dibayar saat kedatangan.
                </div>

                <label class="terms-box" for="syaratKetentuan">
                    <div class="checkbox-wrapper">
                        <input type="checkbox" id="syaratKetentuan" name="syarat_ketentuan" required>
                    </div>
                    <div class="terms-text">
                        Saya menyetujui <a href="aturan.php" target="_blank" onclick="event.stopPropagation()">Syarat & Ketentuan</a> serta Aturan Kost Kinara.
                    </div>
                </label>             

                <?php if($k['status'] == 'tersedia'): ?>
                    <button type="button" class="btn-confirm translatable" data-en="Proceed to Payment" onclick="validasiSubmit()">
                        <i class="fas fa-lock me-2"></i> Lanjutkan Pembayaran
                    </button>
                <?php else: ?>
                    <button type="button" class="btn-confirm translatable" data-en="Room Full" style="background:#ccc; cursor:not-allowed; box-shadow:none;" disabled>
                        <i class="fas fa-times-circle me-2"></i> Kamar Penuh
                    </button>
                <?php endif; ?>
            </form>
        </div>
    </div>
</div>

<div class="booking-steps-section">
        <div class="steps-header-wrap">
            <div class="steps-badge">
                <i class="far fa-calendar-check"></i> <span class="translatable" data-en="Booking Process">Alur Pemesanan</span>
            </div>
            <h2 class="steps-title translatable" data-en="How to Book in 3 Steps">Cara Memesan Dalam 3 Langkah</h2>
            <p class="steps-subtitle translatable" data-en="The rental process is designed to be simple so you can quickly choose a unit, visit, and proceed to book without hassle.">Proses sewa dirancang sederhana supaya kamu bisa cepat memilih unit, berkunjung, dan lanjut memesan tanpa repot.</p>
        </div>

        <div class="steps-grid">
            <div class="step-card" data-step="01">
                <div class="step-icon-wrap"><i class="fas fa-search-location"></i></div>
                <h4 class="translatable" data-en="Choose Area and Unit">Pilih Area dan Unit</h4>
                <p class="translatable" data-en="Filter by location, housing type, and move-in date.">Saring berdasarkan lokasi, tipe hunian, dan tanggal masuk.</p>
            </div>
            
            <div class="step-card" data-step="02">
                <div class="step-icon-wrap"><i class="far fa-eye"></i></div>
                <h4 class="translatable" data-en="Schedule a Visit">Jadwalkan Kunjungan</h4>
                <p class="translatable" data-en="Determine the visit time directly from the housing list page.">Tentukan waktu kunjungan langsung dari halaman daftar hunian.</p>
            </div>
            
            <div class="step-card" data-step="03">
                <div class="step-icon-wrap"><i class="fas fa-home"></i></div>
                <h4 class="translatable" data-en="Online Booking">Pemesanan Daring</h4>
                <p class="translatable" data-en="Proceed with booking and payment through a transparent process.">Lanjutkan pemesanan dan pembayaran dengan proses yang transparan.</p>
            </div>
        </div>
    </div>

    <div class="promo-section">
        <h2 class="section-title"><span class="translatable" data-en="Other Units in ">Unit Lain di </span> <?= $k['lokasi']; ?></h2>
        <div class="rekomendasi-grid">
            <?php while($rk = mysqli_fetch_assoc($query_rekomendasi)): ?>
            <a href="proses_booking.php?id=<?= $rk['id']; ?>" class="reko-card">
                <img src="img/<?= $rk['gambar']; ?>" class="reko-img">
                <div class="reko-info">
                    <?php $rk_nama_en = str_ireplace('Kamar', 'Room', $rk['nama_kamar']); ?>
                    <div class="translatable" data-en="<?= $rk_nama_en ?>" style="font-weight: 700; font-size: 15px; margin-bottom: 5px;"><?= $rk['nama_kamar']; ?></div>
                    <div style="font-size: 13px; color: var(--kinara-pink); font-weight: 600;">Rp <?= number_format($rk['harga'], 0, ',', '.'); ?></div>
                </div>
            </a>
            <?php endwhile; ?>
        </div>
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
                <input type="hidden" name="redirect_to" value="proses_booking.php?id=<?= $id_kamar ?>">
                
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
// === LOGIKA HITUNG HARGA, PROMO & DP ===
    const hargaDasar = parseInt(document.getElementById('harga_dasar').value);
    
    function formatRupiah(angka) {
        return new Intl.NumberFormat('id-ID').format(angka);
    }

    const activePromos = <?= json_encode($promo_aktif_array); ?>;
    const manualPromos = { 'MAHASISWA': 100000 };
    const validPromos = { ...activePromos, ...manualPromos };

    function cekPromo() {
        const inputKode = document.getElementById('kode_promo').value.trim();
        const msgBox = document.getElementById('promo_msg');
        const inputDiskon = document.getElementById('diskon_nominal');
        const lang = localStorage.getItem('kinara_lang') || 'id';
        
        if (inputKode === '') {
            msgBox.className = 'promo-msg text-danger-promo';
            msgBox.innerText = lang === 'en' ? 'Promo code cannot be empty!' : 'Kode promo tidak boleh kosong!';
            inputDiskon.value = 0;
            hitungTotal();
            return;
        }

        if (validPromos.hasOwnProperty(inputKode)) {
            const diskon = validPromos[inputKode];
            inputDiskon.value = diskon;
            msgBox.className = 'promo-msg text-success-promo';
            msgBox.innerText = lang === 'en' ? 'Promo applied! Discount Rp ' + formatRupiah(diskon) : 'Promo berhasil diterapkan! Diskon Rp ' + formatRupiah(diskon);
        } else {
            inputDiskon.value = 0;
            msgBox.className = 'promo-msg text-danger-promo';
            msgBox.innerText = lang === 'en' ? 'Promo code invalid or expired.' : 'Kode promo tidak valid atau kadaluarsa.';
        }
        hitungTotal();
    }

    // FUNGSI SUPER PINTAR: HITUNG TOTAL, DISKON & DP
    function hitungTotal() {
        const hargaPerBulan = <?= $k['harga']; ?>; 
        const lamaSewa = parseInt(document.getElementById('lama_sewa').value) || 1;
        const diskon = parseInt(document.getElementById('diskon_nominal').value) || 0;
        const tipePembayaran = document.getElementById('tipe_pembayaran').value;
        
        // 1. Hitung Subtotal Harga Sewa Asli
        const totalHargaAsli = hargaPerBulan * lamaSewa;
        document.getElementById('rincian_harga_sewa').innerText = 'Rp ' + formatRupiah(totalHargaAsli);

        // 2. Hitung Potongan Diskon
        let subTotal = totalHargaAsli - diskon;
        if (subTotal < 0) subTotal = 0; // Cegah minus

        if (diskon > 0) {
            document.getElementById('baris_diskon').style.display = 'flex';
            document.getElementById('rincian_diskon').innerText = '- Rp ' + formatRupiah(diskon);
        } else {
            document.getElementById('baris_diskon').style.display = 'none';
        }

        // 3. Logika Pembayaran (Lunas / DP)
        let totalBayar = subTotal;
        let sisaBayar = 0;

        const infoSisaBox = document.getElementById('info_sisa_bayar');
        const labelTotal = document.getElementById('label_total_bayar');

        if (tipePembayaran === 'dp') {
            // Aturan DP Kinara Kost
            let dpAmount = 500000; // Default 1 & 3 Bulan
            if (lamaSewa === 6) dpAmount = 1000000;
            else if (lamaSewa === 12) dpAmount = 1500000;

            // Mencegah harga DP lebih mahal dari Total Tagihan (Misal: User pakai Promo 100%)
            if (subTotal > dpAmount) {
                totalBayar = dpAmount; 
                sisaBayar = subTotal - dpAmount;
                
                infoSisaBox.innerHTML = `*Sisa pelunasan <strong>Rp ${formatRupiah(sisaBayar)}</strong> dibayar saat kedatangan.`;
                infoSisaBox.style.display = 'block';
                labelTotal.innerText = 'Total DP (Booking)';
            } else {
                // Jika tagihan lebih murah dari DP, otomatis tertagih lunas
                totalBayar = subTotal;
                infoSisaBox.style.display = 'none';
                labelTotal.innerText = 'Total Bayar';
            }
        } else {
            // Jika pilih LUNAS
            infoSisaBox.style.display = 'none';
            labelTotal.innerText = 'Total Bayar';
        }

        // 4. Cetak ke Layar
        const totalElem = document.getElementById('rincian_total_bayar'); 
        const inputTotal = document.getElementById('total_bayar_input'); 
        
        if (totalBayar === 0) {
            totalElem.innerHTML = 'FREE';
            totalElem.style.color = 'var(--kinara-pink)';
            inputTotal.value = 0;
        } else {
            totalElem.innerHTML = 'Rp ' + formatRupiah(totalBayar);
            totalElem.style.color = 'var(--kinara-pink)';
            inputTotal.value = totalBayar;
        }
    }

    const isLogin = <?= isset($_SESSION['login']) ? 'true' : 'false'; ?>;
    
    function validasiSubmit() {
        const lang = localStorage.getItem('kinara_lang') || 'id';
        if (!isLogin) {
            const loginModal = document.getElementById("loginModal");
            Swal.fire({
                title: lang === 'en' ? 'Not Logged In' : 'Belum Login',
                text: lang === 'en' ? 'Please log in first to continue booking.' : "Silakan login terlebih dahulu untuk melakukan pemesanan.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ff385c',
                cancelButtonText: lang === 'en' ? 'Cancel' : 'Batal',
                confirmButtonText: lang === 'en' ? 'Log in Now' : 'Login Sekarang'
            }).then((result) => {
                if (result.isConfirmed) loginModal.style.display = "block";
            });
            return;
        }

        const form = document.getElementById('formBooking');
        // Fitur CheckValidity bawaan HTML5 akan memastikan Checkbox sudah dicentang
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }
        form.submit();
    }

    // === LIGHTBOX GALLERY LOGIC ===
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

document.addEventListener('DOMContentLoaded', () => {
    hitungTotal();

    // === LOGIKA HAMBURGER MENU MOBILE ===
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

    // === LOGIKA DROPDOWN ===
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

    // --- FITUR GANTI FOTO PAKAI KEYBOARD ---
    document.addEventListener('keydown', function(e) {
        if (lightbox.style.display === 'block') {
            if (e.key === 'ArrowLeft') changeImage(-1);
            if (e.key === 'ArrowRight') changeImage(1);
            if (e.key === 'Escape') closeLightbox(); // Tekan ESC untuk keluar
        }
    });

    // === LOGIKA MODAL LOGIN ===
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

    // === LOGIKA MULTI-BAHASA ===
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

    const savedLang = localStorage.getItem('kinara_lang') || 'id';
    setLanguage(savedLang);
});
</script>
</body>
</html>
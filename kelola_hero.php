<?php
session_start();
include 'config.php';

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') { header("Location: index.php"); exit; }

// Ambil data hero saat ini
$hero = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM hero_section WHERE id = 1"));

if (isset($_POST['update_hero'])) {
    $judul = mysqli_real_escape_string($conn, $_POST['judul']);
    $sub_judul = mysqli_real_escape_string($conn, $_POST['sub_judul']);
    
    if ($_FILES['gambar']['name'] != "") {
        $file_name = time() . '_' . $_FILES['gambar']['name'];
        if (move_uploaded_file($_FILES['gambar']['tmp_name'], "img/" . $file_name)) {
            // Hapus gambar lama jika bukan default
            if ($hero['gambar'] != 'hero-bg.jpg' && file_exists("img/" . $hero['gambar'])) {
                unlink("img/" . $hero['gambar']);
            }
            mysqli_query($conn, "UPDATE hero_section SET judul='$judul', sub_judul='$sub_judul', gambar='$file_name' WHERE id=1");
        }
    } else {
        mysqli_query($conn, "UPDATE hero_section SET judul='$judul', sub_judul='$sub_judul' WHERE id=1");
    }
    header("Location: kelola_hero.php?msg=success");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Edit Hero Section - Kinara Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root { --primary-color: #ff385c; --sidebar-bg: #212529; }
        body { font-family: 'Inter', sans-serif; background-color: #f8f9fa; }
/* UPDATE SIDEBAR AGAR BISA DI-SCROLL */
.sidebar { 
    height: 100vh; 
    width: 250px; 
    position: fixed; 
    top: 0; 
    left: 0; 
    background-color: var(--sidebar-bg); 
    padding-top: 20px; 
    padding-bottom: 20px; /* Tambah padding bawah agar menu terakhir tidak mepet */
    color: white; 
    
    /* INI KUNCINYA */
    overflow-y: auto; 
    scrollbar-width: thin; /* Untuk Firefox */
    scrollbar-color: rgba(255,255,255,0.1) transparent;
}

/* CUSTOM SCROLLBAR UNTUK CHROME, SAFARI, & EDGE (Agar terlihat modern) */
.sidebar::-webkit-scrollbar {
    width: 5px;
}

.sidebar::-webkit-scrollbar-track {
    background: transparent;
}

.sidebar::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 10px;
}

.sidebar::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.2);
}

        .sidebar .nav-link {
            color: rgba(255,255,255,0.7);
            padding: 12px 20px;
            margin: 4px 15px;
            border-radius: 8px;
        }

        /* Update Sidebar Styling */
        .sidebar .nav-group-label {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: #6c757d;
            font-weight: 700;
            margin: 20px 25px 10px;
            display: block;
        }

        .sidebar .nav-link {
            color: #adb5bd; /* Warna default abu-abu terang */
            padding: 12px 20px;
            margin: 2px 15px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s;
        }

        .sidebar .nav-link:hover {
            background: rgba(255, 255, 255, 0.05);
            color: #fff;
        }

        .sidebar .nav-link.active {
            background: var(--primary-color);
            color: white;
            box-shadow: 0 4px 15px rgba(255, 56, 92, 0.3);
        }

        .sidebar hr {
            border-color: rgba(255,255,255,0.1);
            margin: 20px 15px;
        }
        
        /* Main Content Area */
        .main-content {
            margin-left: 250px;
            padding: 30px;
        }
        
        .stat-card {
            border: none;
            border-radius: 15px;
            transition: transform 0.3s;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        .stat-card:hover { transform: translateY(-5px); }
        
        .navbar-admin {
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            margin-bottom: 30px;
            padding: 15px 25px;
            border-radius: 12px;
        }
        .preview-hero { width: 100%; max-height: 300px; object-fit: cover; border-radius: 15px; margin-bottom: 20px; border: 4px solid #fff; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }

        /* =========================================
           UI/UX RESPONSIVE ADMIN (MOBILE FIRST)
           ========================================= */
        
        .sidebar-overlay {
            display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0, 0, 0, 0.5); backdrop-filter: blur(3px); z-index: 998;
        }

        .btn-toggle-sidebar {
            display: none; background: none; border: none; font-size: 22px; color: #212529; cursor: pointer; padding: 0;
        }

        .sidebar { z-index: 999; transition: transform 0.3s ease-in-out; }

        @media (max-width: 768px) {
            /* Sembunyikan Sidebar ke Kiri */
            .sidebar { transform: translateX(-100%); }
            .sidebar.show { transform: translateX(0); box-shadow: 5px 0 15px rgba(0,0,0,0.1); }
            .sidebar-overlay.show { display: block; }

            /* Konten Utama Penuhi Layar */
            .main-content { margin-left: 0 !important; padding: 15px; }
            
            /* Tampilkan Tombol Hamburger & Rapikan Header */
            .btn-toggle-sidebar { display: block; }
            .header-admin-mobile { display: flex; align-items: center; gap: 15px; margin-bottom: 25px; }
            .header-admin-mobile h3 { font-size: 22px; margin-bottom: 0 !important; }

            /* Sesuaikan tinggi preview gambar di HP */
            .preview-hero { max-height: 200px; }
        }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="px-4 mb-4 mt-2">
        <h4 class="fw-bold text-white" style="letter-spacing: -1px;">
            KINARA <span style="color: var(--primary-color);">ADMIN</span>
        </h4>
    </div>

    <span class="nav-group-label translatable" data-en="Main Menu">Main Menu</span>
    <ul class="nav flex-column">
        <li class="nav-item">
            <a href="admin_dashboard.php" class="nav-link">
                <i class="fas fa-th-large me-2"></i> <span class="translatable" data-en="Dashboard">Dashboard</span>
            </a>
        </li>
    </ul>

    <span class="nav-group-label translatable" data-en="Boarding Management">Manajemen Kost</span>
    <ul class="nav flex-column">
        <li class="nav-item">
            <a href="kelola_kamar.php" class="nav-link">
                <i class="fas fa-bed me-2"></i> <span class="translatable" data-en="Manage Rooms">Kelola Kamar</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="data_penyewa.php" class="nav-link">
                <i class="fas fa-users me-2"></i> <span class="translatable" data-en="User Data">Data Pengguna</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="pembayaran.php" class="nav-link">
                <i class="fas fa-wallet me-2"></i> <span class="translatable" data-en="Payments">Pembayaran</span>
            </a>
        </li>
        <li class="nav-item"><a href="data_penghuni.php" class="nav-link"><i class="fas fa-user-check me-2"></i> Data Penghuni</a></li>
    </ul>

<span class="nav-group-label translatable" data-en="User Interface">Tampilan User</span>
    <ul class="nav flex-column">
        <li class="nav-item">
            <a href="kelola_promo.php" class="nav-link">
                <i class="fas fa-percentage me-2"></i> <span class="translatable" data-en="Promos">Promo</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="kelola_keuntungan.php" class="nav-link">
                <i class="fas fa-star me-2"></i> <span class="translatable" data-en="Benefits">Keuntungan</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="kelola_hero.php" class="nav-link active">
                <i class="fas fa-image me-2"></i> <span class="translatable" data-en="Banners">Banner</span>
            </a>
        </li>
    </ul>

    <hr>

    <ul class="nav flex-column">
        <li class="nav-item">
            <a href="javascript:void(0)" class="nav-link text-danger" id="btnLogout">
                <i class="fas fa-power-off me-2"></i> <span class="translatable" data-en="Logout">Logout</span>
            </a>
        </li>
    </ul>
</div>

<div class="sidebar-overlay" id="sidebarOverlay"></div>

<div class="main-content">
    
    <div class="header-admin-mobile">
        <button class="btn-toggle-sidebar" id="btnToggleSidebar">
            <i class="fas fa-bars"></i>
        </button>
        <h3 class="fw-bold mb-0 translatable" data-en="Hero Banner Settings">Pengaturan Hero Banner</h3>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm rounded-4 p-4">
                <form action="" method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="fw-bold small mb-2 translatable" data-en="Main Title (Heading)">Judul Utama (Heading)</label>
                        <input type="text" name="judul" class="form-control" value="<?= $hero['judul'] ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold small mb-2 translatable" data-en="Sub-Title (Description)">Sub-Judul (Deskripsi)</label>
                        <textarea name="sub_judul" class="form-control" rows="3" required><?= $hero['sub_judul'] ?></textarea>
                    </div>
                    <div class="mb-4">
                        <label class="fw-bold small mb-2 translatable" data-en="Change Background Image">Ganti Background Gambar</label>
                        <input type="file" name="gambar" class="form-control" accept="image/*">
                        <div class="form-text text-muted mt-2 translatable" data-en="Recommended size: 1920 x 1080 px to avoid pixelation.">Rekomendasi ukuran: 1920 x 1080 px agar tidak pecah.</div>
                    </div>
                    <button type="submit" name="update_hero" class="btn btn-danger px-4 fw-bold">
                        <i class="fas fa-save me-2"></i> <span class="translatable" data-en="Save Changes">Simpan Perubahan</span>
                    </button>
                </form>
            </div>
        </div>
        <div class="col-md-4">
            <h6 class="fw-bold mb-3 translatable" data-en="Current Preview:">Preview Saat Ini:</h6>
            <img src="img/<?= $hero['gambar'] ?>" class="preview-hero">
            <div class="alert alert-info border-0 small">
                <i class="fas fa-info-circle me-2"></i> <span class="translatable" data-en="This is the large background image that appears on the front page.">Gambar ini adalah latar belakang besar yang muncul di halaman depan.</span>
            </div>
        </div>
    </div>
</div>

<?php if(isset($_GET['msg']) && $_GET['msg'] == 'success'): ?>
<script>
    // Translasi alert SweetAlert
    const isEn = localStorage.getItem('kinara_lang') === 'en';
    const titleText = isEn ? 'Success!' : 'Berhasil!';
    const msgText = isEn ? 'Hero banner has been updated.' : 'Hero banner telah diperbarui.';
    Swal.fire(titleText, msgText, 'success');
</script>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', () => {
    
    // === 1. Logika Terjemahan Bahasa (Membaca dari localStorage) ===
    const savedLang = localStorage.getItem('kinara_lang') || 'id';
    
    if (savedLang === 'en') {
        document.querySelectorAll('.translatable').forEach(el => {
            // Cek jika elemen itu input placeholder
            if (el.tagName === 'INPUT' && el.hasAttribute('placeholder')) {
                if (!el.getAttribute('data-id-text')) el.setAttribute('data-id-text', el.getAttribute('placeholder'));
                el.setAttribute('placeholder', el.getAttribute('data-en'));
            } 
            // Cek jika elemen itu teks HTML biasa
            else {
                if (!el.getAttribute('data-id-text')) el.setAttribute('data-id-text', el.innerText);
                el.innerText = el.getAttribute('data-en');
            }
        });
    }

    // === 2. Logika Konfirmasi Logout Admin (Bilingual) ===
    document.getElementById('btnLogout')?.addEventListener('click', function() {
        const isEnglish = localStorage.getItem('kinara_lang') === 'en';
        
        Swal.fire({
            title: isEnglish ? 'Log out of the system?' : 'Keluar dari Sistem?',
            text: isEnglish ? 'You must log in again to manage data.' : 'Anda harus login kembali untuk mengelola data.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#ff385c',
            cancelButtonColor: '#6c757d',
            confirmButtonText: isEnglish ? 'Yes, Log out!' : 'Ya, Keluar!',
            cancelButtonText: isEnglish ? 'Cancel' : 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'logout.php';
            }
        });
    });

});

// === 0. LOGIKA HAMBURGER MENU ADMIN (MOBILE) ===
    const sidebar = document.querySelector('.sidebar');
    const btnToggleSidebar = document.getElementById('btnToggleSidebar');
    const sidebarOverlay = document.getElementById('sidebarOverlay');

    if (btnToggleSidebar) {
        btnToggleSidebar.addEventListener('click', () => {
            sidebar.classList.add('show');
            sidebarOverlay.classList.add('show');
            document.body.style.overflow = 'hidden'; // Kunci scroll halaman saat menu buka
        });
    }

    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', () => {
            sidebar.classList.remove('show');
            sidebarOverlay.classList.remove('show');
            document.body.style.overflow = 'auto'; // Lepas kunci scroll
        });
    }
</script>
</body>
</html>
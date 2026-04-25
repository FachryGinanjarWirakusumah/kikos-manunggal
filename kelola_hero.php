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
    </style>
</head>
<body>

<div class="sidebar">
    <div class="px-4 mb-4 mt-2">
        <h4 class="fw-bold text-white" style="letter-spacing: -1px;">
            KINARA <span style="color: var(--primary-color);">ADMIN</span>
        </h4>
    </div>

    <span class="nav-group-label">Main Menu</span>
    <ul class="nav flex-column">
        <li class="nav-item">
            <a href="admin_dashboard.php" class="nav-link">
                <i class="fas fa-th-large me-2"></i> Dashboard
            </a>
        </li>
    </ul>

    <span class="nav-group-label">Manajemen Kost</span>
    <ul class="nav flex-column">
        <li class="nav-item">
            <a href="kelola_kamar.php" class="nav-link">
                <i class="fas fa-bed me-2"></i> Kelola Kamar
            </a>
        </li>
        <li class="nav-item">
            <a href="data_penyewa.php" class="nav-link">
                <i class="fas fa-users me-2"></i> Data Pengguna
            </a>
        </li>
        <li class="nav-item">
            <a href="pembayaran.php" class="nav-link">
                <i class="fas fa-wallet me-2"></i> Pembayaran
            </a>
        </li>
    </ul>

<span class="nav-group-label">Tampilan User</span>
    <ul class="nav flex-column">
        <li class="nav-item">
            <a href="kelola_promo.php" class="nav-link">
                <i class="fas fa-percentage me-2"></i> Promo
            </a>
        </li>
        <li class="nav-item">
            <a href="kelola_keuntungan.php" class="nav-link">
                <i class="fas fa-star me-2"></i> Keuntungan
            </a>
        </li>
        <li class="nav-item">
            <a href="kelola_hero.php" class="nav-link active">
                <i class="fas fa-star me-2"></i> Banner
            </a>
        </li>
    </ul>

    <span class="nav-group-label">Laporan</span>
    <ul class="nav flex-column">
        <li class="nav-item">
            <a href="laporan.php" class="nav-link">
                <i class="fas fa-chart-line me-2"></i> Statistik Booking
            </a>
        </li>
    </ul>

    <hr>

    <ul class="nav flex-column">
        <li class="nav-item">
            <a href="javascript:void(0)" class="nav-link text-danger" id="btnLogout">
                <i class="fas fa-power-off me-2"></i> Logout
            </a>
        </li>
    </ul>
</div>
<div class="main-content">
    <h3 class="fw-bold mb-4">Pengaturan Hero Banner</h3>

    <div class="row">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm rounded-4 p-4">
                <form action="" method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="fw-bold small mb-2">Judul Utama (Heading)</label>
                        <input type="text" name="judul" class="form-control" value="<?= $hero['judul'] ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold small mb-2">Sub-Judul (Deskripsi)</label>
                        <textarea name="sub_judul" class="form-control" rows="3" required><?= $hero['sub_judul'] ?></textarea>
                    </div>
                    <div class="mb-4">
                        <label class="fw-bold small mb-2">Ganti Background Gambar</label>
                        <input type="file" name="gambar" class="form-control" accept="image/*">
                        <div class="form-text text-muted mt-2">Rekomendasi ukuran: 1920 x 1080 px agar tidak pecah.</div>
                    </div>
                    <button type="submit" name="update_hero" class="btn btn-danger px-4 fw-bold">
                        <i class="fas fa-save me-2"></i> Simpan Perubahan
                    </button>
                </form>
            </div>
        </div>
        <div class="col-md-4">
            <h6 class="fw-bold mb-3">Preview Saat Ini:</h6>
            <img src="img/<?= $hero['gambar'] ?>" class="preview-hero">
            <div class="alert alert-info border-0 small">
                <i class="fas fa-info-circle me-2"></i> Gambar ini adalah latar belakang besar yang muncul di halaman depan.
            </div>
        </div>
    </div>
</div>

<?php if(isset($_GET['msg']) && $_GET['msg'] == 'success'): ?>
<script>Swal.fire('Berhasil!', 'Hero banner telah diperbarui.', 'success');</script>
<?php endif; ?>

<script>
    document.getElementById('btnLogout')?.addEventListener('click', function() {
        Swal.fire({
            title: 'Keluar dari Sistem?',
            text: "Anda harus login kembali untuk mengelola data.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#ff385c',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Keluar!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'logout.php';
            }
        });
    });
</script>
</body>
</html>
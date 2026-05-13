<?php
session_start();
include 'config.php';

// Proteksi Halaman
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') { 
    header("Location: index.php"); 
    exit; 
}

// Helper untuk cek kadaluarsa
function isExpired($date) {
    return (strtotime($date) < strtotime(date('Y-m-d')));
}

// --- LOGIKA TAMBAH PROMO ---
if (isset($_POST['upload_promo'])) {
    $judul = mysqli_real_escape_string($conn, $_POST['judul']);
    $diskon = (int)$_POST['diskon']; // Tangkap nominal diskon
    $tgl_akhir = mysqli_real_escape_string($conn, $_POST['tanggal_akhir']);
    $file_name = time() . '_' . $_FILES['gambar']['name'];
    
    if (move_uploaded_file($_FILES['gambar']['tmp_name'], "img/promo/" . $file_name)) {
        mysqli_query($conn, "INSERT INTO promo (judul, diskon, gambar, tanggal_akhir) VALUES ('$judul', '$diskon', '$file_name', '$tgl_akhir')");
        header("Location: kelola_promo.php?msg=success");
        exit;
    }
}

// --- LOGIKA UPDATE PROMO ---
if (isset($_POST['update_promo'])) {
    $id = (int)$_POST['id_promo'];
    $judul = mysqli_real_escape_string($conn, $_POST['judul']);
    $diskon = (int)$_POST['diskon']; // Tangkap nominal diskon
    $tgl_akhir = mysqli_real_escape_string($conn, $_POST['tanggal_akhir']);
    
    $sql = "UPDATE promo SET judul='$judul', diskon='$diskon', tanggal_akhir='$tgl_akhir'";

    if ($_FILES['gambar']['name'] != "") {
        $file_name = time() . '_' . $_FILES['gambar']['name'];
        if (move_uploaded_file($_FILES['gambar']['tmp_name'], "img/promo/" . $file_name)) {
            // Hapus gambar lama
            $res = mysqli_query($conn, "SELECT gambar FROM promo WHERE id = $id");
            $lama = mysqli_fetch_assoc($res);
            if (file_exists("img/promo/" . $lama['gambar'])) { unlink("img/promo/" . $lama['gambar']); }
            
            $sql .= ", gambar='$file_name'";
        }
    }
    
    $sql .= " WHERE id=$id";
    mysqli_query($conn, $sql);
    header("Location: kelola_promo.php?msg=success_edit");
    exit;
}

// --- LOGIKA HAPUS PROMO --- (Tetap sama)
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    $res = mysqli_query($conn, "SELECT gambar FROM promo WHERE id = $id");
    $data = mysqli_fetch_assoc($res);
    if ($data) {
        if (file_exists("img/promo/" . $data['gambar'])) { unlink("img/promo/" . $data['gambar']); }
        mysqli_query($conn, "DELETE FROM promo WHERE id = $id");
    }
    header("Location: kelola_promo.php?msg=success_delete");
    exit;
}

$promos = mysqli_query($conn, "SELECT * FROM promo ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Kelola Promo - Kinara Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>

        :root {
            --primary-color: #ff385c;
            --sidebar-bg: #212529;
        }
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

        /* Pastikan tinggi preview seragam */
.promo-preview { 
    width: 100%; 
    aspect-ratio: 16 / 9; /* Ini menjaga rasio gambar tetap landscape yang rapi */
    object-fit: cover;
    border-bottom: 1px solid #eee;
}

.row {
    display: flex;
    flex-wrap: wrap;
}

/* Tambahkan efek hover agar keren */
.card:hover {
    transform: translateY(-5px);
    transition: 0.3s;
}

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
            .header-admin-mobile { display: flex; align-items: center; gap: 15px; margin-bottom: 20px; }
            .header-admin-mobile h3 { font-size: 22px; margin-bottom: 0 !important; }
        }

</style>
</head>
<body class="bg-light">

<?php if(isset($_GET['msg']) && $_GET['msg'] == 'success'): ?>
    <script>
        Swal.fire({
            title: 'Berhasil!',
            text: 'Banner promo baru telah ditambahkan.',
            icon: 'success',
            confirmButtonColor: '#ff385c'
        });
    </script>
<?php endif; ?>

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
        <li class="nav-item"><a href="data_penghuni.php" class="nav-link"><i class="fas fa-user-check me-2"></i> Data Penghuni</a></li>
    </ul>

    <span class="nav-group-label">Tampilan User</span>
    <ul class="nav flex-column">
        <li class="nav-item">
            <a href="kelola_promo.php" class="nav-link active">
                <i class="fas fa-percentage me-2"></i> Promo
            </a>
        </li>
        <li class="nav-item">
            <a href="kelola_keuntungan.php" class="nav-link">
                <i class="fas fa-star me-2"></i> Keuntungan
            </a>
        </li>
        <li class="nav-item"><a href="kelola_hero.php" class="nav-link"><i class="fas fa-image me-2"></i> Banner</a></li>            
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

<div class="sidebar-overlay" id="sidebarOverlay"></div>

<div class="main-content">
    
    <div class="header-admin-mobile">
        <button class="btn-toggle-sidebar" id="btnToggleSidebar">
            <i class="fas fa-bars"></i>
        </button>
        <h3 class="fw-bold mb-4">Edit Promo Berlangsung</h3>
    </div>

<div class="card border-0 shadow-sm p-4 mb-4 rounded-4">
        <h6 class="fw-bold mb-3"><i class="fas fa-ticket-alt text-danger me-2"></i>Tambah Banner & Kode Promo Baru</h6>
        <form action="" method="POST" enctype="multipart/form-data" class="row g-3">
            <div class="col-md-3">
                <label class="small fw-bold text-muted mb-1">Kode Promo</label>
                <input type="text" name="judul" class="form-control fw-bold" placeholder="Contoh: KINARA10" style="text-transform: uppercase;" required>
            </div>
            <div class="col-md-3">
                <label class="small fw-bold text-muted mb-1">Nominal Diskon (Rp)</label>
                <input type="number" name="diskon" class="form-control" placeholder="Contoh: 50000" required>
            </div>
            <div class="col-md-2">
                <label class="small fw-bold text-muted mb-1">Berlaku Sampai</label>
                <input type="date" name="tanggal_akhir" class="form-control" required>
            </div>
            <div class="col-md-3">
                <label class="small fw-bold text-muted mb-1">Upload Banner</label>
                <input type="file" name="gambar" class="form-control" accept="image/*" required>
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button type="submit" name="upload_promo" class="btn btn-danger w-100 fw-bold"><i class="fas fa-save"></i></button>
            </div>
        </form>
    </div>

    <div class="row">
        <?php if(mysqli_num_rows($promos) > 0): ?>
            <?php while($p = mysqli_fetch_assoc($promos)): ?>
            <div class="col-md-4 mb-4"> <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100">
                    <img src="img/promo/<?= $p['gambar']; ?>" class="promo-preview" onerror="this.src='https://via.placeholder.com/400x200?text=Gambar+Rusak'">
                    
                    <div class="p-3 d-flex justify-content-between align-items-center">
                        <div class="text-truncate me-2">
                            <span class="badge bg-danger mb-1"><?= $p['judul']; ?></span><br>
                            <small class="text-muted" style="font-size: 11px;">s/d <?= date('d M Y', strtotime($p['tanggal_akhir'])); ?></small>
                        </div>
                        
                        <div class="btn-group">
                            <button type="button" class="btn btn-sm btn-light border text-primary btn-edit-promo" 
                                    data-id="<?= $p['id']; ?>" 
                                    data-judul="<?= $p['judul']; ?>"
                                    data-diskon="<?= $p['diskon']; ?>"
                                    data-tanggal="<?= $p['tanggal_akhir']; ?>">
                                <i class="fas fa-edit"></i>
                            </button>

                            <button type="button" class="btn btn-sm btn-light border text-danger btn-hapus" 
                                    data-id="<?= $p['id']; ?>" 
                                    data-judul="<?= $p['judul']; ?>">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div> </div> <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <img src="https://illustrations.popsy.co/gray/not-found.svg" style="width: 200px;" class="mb-3">
                <p class="text-muted">Belum ada promo yang dipasang.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="modal fade" id="modalEditPromo" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id_promo" id="edit_id">
            <div class="modal-content rounded-4 border-0">
                <div class="modal-header border-0">
                    <h5 class="fw-bold">Edit Banner Promo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="small fw-bold">Kode Promo</label>
                        <input type="text" name="judul" id="edit_judul" class="form-control fw-bold" style="text-transform: uppercase;" required>
                    </div>
                    <div class="mb-3">
                        <label class="small fw-bold">Nominal Diskon (Rp)</label>
                        <input type="number" name="diskon" id="edit_diskon" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="small fw-bold">Berlaku Sampai</label>
                        <input type="date" name="tanggal_akhir" id="edit_tanggal" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="small fw-bold">Ganti Banner (Opsional)</label>
                        <input type="file" name="gambar" class="form-control" accept="image/*">
                    </div>
                </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.querySelectorAll('.btn-edit-promo').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('edit_id').value = this.dataset.id;
            document.getElementById('edit_judul').value = this.dataset.judul;
            document.getElementById('edit_diskon').value = this.dataset.diskon; // Parsing diskon
            document.getElementById('edit_tanggal').value = this.dataset.tanggal;
            var myModal = new bootstrap.Modal(document.getElementById('modalEditPromo'));
            myModal.show();
        });
    });

// LOGIKA KONFIRMASI HAPUS DENGAN SWEETALERT2
document.querySelectorAll('.btn-hapus').forEach(btn => {
    btn.addEventListener('click', function() {
        const id = this.dataset.id;
        const judul = this.dataset.judul;

        Swal.fire({
            title: 'Hapus Promo?',
            html: `Anda akan menghapus promo: <br><strong>${judul}</strong>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ff385c',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                // Arahkan ke URL hapus jika dikonfirmasi
                window.location.href = `kelola_promo.php?hapus=${id}`;
            }
        });
    });
});

</script>

<?php if(isset($_GET['msg'])): ?>
<script>
    const msg = "<?= $_GET['msg'] ?>";
    const config = {
        confirmButtonColor: '#ff385c',
        timer: 3000
    };

    if (msg === 'success') {
        Swal.fire({...config, title: 'Berhasil!', text: 'Promo baru telah ditambahkan.', icon: 'success'});
    } else if (msg === 'success_edit') {
        Swal.fire({...config, title: 'Diperbarui!', text: 'Data promo berhasil diubah.', icon: 'success'});
    } else if (msg === 'success_delete') {
        Swal.fire({...config, title: 'Terhapus!', text: 'Banner promo telah dihapus.', icon: 'success'});
    }
</script>
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

// === LOGIKA HAMBURGER MENU ADMIN (MOBILE) ===
const sidebar = document.querySelector('.sidebar');
const btnToggleSidebar = document.getElementById('btnToggleSidebar');
const sidebarOverlay = document.getElementById('sidebarOverlay');

if (btnToggleSidebar) {
    btnToggleSidebar.addEventListener('click', () => {
        sidebar.classList.add('show');
        sidebarOverlay.classList.add('show');
        document.body.style.overflow = 'hidden'; // Kunci scroll halaman utama
    });
}

if (sidebarOverlay) {
    sidebarOverlay.addEventListener('click', () => {
        sidebar.classList.remove('show');
        sidebarOverlay.classList.remove('show');
        document.body.style.overflow = 'auto'; // Buka kembali scroll
    });
}
</script>
</body>
</html>
<?php
session_start();
include 'config.php';

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') { header("Location: index.php"); exit; }

// --- LOGIKA TAMBAH ---
if (isset($_POST['tambah_aturan'])) {
    $ikon = mysqli_real_escape_string($conn, $_POST['ikon']);
    $kategori = mysqli_real_escape_string($conn, $_POST['kategori']);
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);
    $urutan = (int)$_POST['urutan'];
    
    mysqli_query($conn, "INSERT INTO aturan_kost (ikon, kategori, deskripsi, urutan) VALUES ('$ikon', '$kategori', '$deskripsi', '$urutan')");
    header("Location: kelola_aturan.php?msg=success");
    exit;
}

// --- LOGIKA UPDATE ---
if (isset($_POST['update_aturan'])) {
    $id = (int)$_POST['id_aturan'];
    $ikon = mysqli_real_escape_string($conn, $_POST['ikon']);
    $kategori = mysqli_real_escape_string($conn, $_POST['kategori']);
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);
    $urutan = (int)$_POST['urutan'];

    mysqli_query($conn, "UPDATE aturan_kost SET ikon='$ikon', kategori='$kategori', deskripsi='$deskripsi', urutan='$urutan' WHERE id=$id");
    header("Location: kelola_aturan.php?msg=updated");
    exit;
}

// --- LOGIKA HAPUS ---
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    mysqli_query($conn, "DELETE FROM aturan_kost WHERE id=$id");
    header("Location: kelola_aturan.php?msg=deleted");
    exit;
}

$semua_aturan = mysqli_query($conn, "SELECT * FROM aturan_kost ORDER BY urutan ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Aturan - Admin Kinara</title>
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
        .main-content { margin-left: 250px; padding: 30px; }
        .card-aturan { border: none; border-radius: 15px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); transition: 0.3s; }
        .icon-preview { font-size: 24px; color: var(--primary-color); }
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
            <a href="kelola_hero.php" class="nav-link">
                <i class="fas fa-star me-2"></i> Banner
            </a>
        </li>
        <li class="nav-item">
            <a href="kelola_aturan.php" class="nav-link active">
                <i class="fas fa-star me-2"></i> Aturan Kost
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
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold m-0">Manajemen Aturan Kost</h3>
        <button class="btn btn-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#modalTambah">
            <i class="fas fa-plus me-2"></i> Tambah Aturan
        </button>
    </div>

    <div class="row">
        <?php while($row = mysqli_fetch_assoc($semua_aturan)): ?>
        <div class="col-md-4 mb-4">
            <div class="card card-aturan h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-3">
                        <i class="<?= $row['ikon']; ?> icon-preview"></i>
                        <span class="badge bg-light text-dark border">Urutan: <?= $row['urutan']; ?></span>
                    </div>
                    <h5 class="fw-bold"><?= $row['kategori']; ?></h5>
                    <p class="text-muted small"><?= nl2br($row['deskripsi']); ?></p>
                </div>
                <div class="card-footer bg-transparent border-0 d-flex gap-2 pb-3">
                    <button class="btn btn-sm btn-outline-primary w-100 btn-edit" 
                            data-id="<?= $row['id']; ?>" 
                            data-ikon="<?= $row['ikon']; ?>"
                            data-kategori="<?= $row['kategori']; ?>"
                            data-deskripsi="<?= $row['deskripsi']; ?>"
                            data-urutan="<?= $row['urutan']; ?>">Edit</button>
                    <button class="btn btn-sm btn-outline-danger btn-hapus" data-id="<?= $row['id']; ?>"><i class="fas fa-trash"></i></button>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
</div>

<div class="modal fade" id="modalTambah" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="" method="POST" class="modal-content rounded-4">
            <div class="modal-header border-0">
                <h5 class="fw-bold">Tambah Kategori Aturan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="small fw-bold">Ikon FontAwesome</label>
                    <input type="text" name="ikon" class="form-control" placeholder="Contoh: fas fa-clock" required>
                </div>
                <div class="mb-3">
                    <label class="small fw-bold">Nama Kategori</label>
                    <input type="text" name="kategori" class="form-control" placeholder="Contoh: Waktu & Ketertiban" required>
                </div>
                <div class="mb-3">
                    <label class="small fw-bold">Daftar Aturan (Pisahkan dengan baris baru)</label>
                    <textarea name="deskripsi" class="form-control" rows="5" required></textarea>
                </div>
                <div class="mb-3">
                    <label class="small fw-bold">No. Urut Tampil</label>
                    <input type="number" name="urutan" class="form-control" value="0">
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="submit" name="tambah_aturan" class="btn btn-primary w-100 py-2 rounded-3">Simpan Aturan</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="modalEdit" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="" method="POST" class="modal-content rounded-4">
            <input type="hidden" name="id_aturan" id="edit_id">
            <div class="modal-header border-0">
                <h5 class="fw-bold">Edit Aturan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="small fw-bold">Ikon</label>
                    <input type="text" name="ikon" id="edit_ikon" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="small fw-bold">Kategori</label>
                    <input type="text" name="kategori" id="edit_kategori" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="small fw-bold">Deskripsi</label>
                    <textarea name="deskripsi" id="edit_deskripsi" class="form-control" rows="5" required></textarea>
                </div>
                <div class="mb-3">
                    <label class="small fw-bold">Urutan</label>
                    <input type="number" name="urutan" id="edit_urutan" class="form-control">
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="submit" name="update_aturan" class="btn btn-primary w-100 py-2 rounded-3">Update Aturan</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Handle Edit
    document.querySelectorAll('.btn-edit').forEach(btn => {
        btn.onclick = function() {
            document.getElementById('edit_id').value = this.dataset.id;
            document.getElementById('edit_ikon').value = this.dataset.ikon;
            document.getElementById('edit_kategori').value = this.dataset.kategori;
            document.getElementById('edit_deskripsi').value = this.dataset.deskripsi;
            document.getElementById('edit_urutan').value = this.dataset.urutan;
            new bootstrap.Modal(document.getElementById('modalEdit')).show();
        }
    });

    // Handle Hapus
    document.querySelectorAll('.btn-hapus').forEach(btn => {
        btn.onclick = function() {
            const id = this.dataset.id;
            Swal.fire({
                title: 'Hapus aturan ini?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Ya, Hapus!'
            }).then((result) => {
                if (result.isConfirmed) window.location.href = `kelola_aturan.php?hapus=${id}`;
            });
        }
    });
</script>

<?php if(isset($_GET['msg'])): ?>
<script>
    const msg = "<?= $_GET['msg'] ?>";
    if(msg === 'success') Swal.fire('Berhasil!', 'Aturan baru ditambahkan.', 'success');
    if(msg === 'updated') Swal.fire('Berhasil!', 'Aturan diperbarui.', 'success');
    if(msg === 'deleted') Swal.fire('Dihapus!', 'Aturan telah dibuang.', 'success');
</script>
<?php endif; ?>

</body>
</html>
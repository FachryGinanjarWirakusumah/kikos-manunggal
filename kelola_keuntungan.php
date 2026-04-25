<?php
session_start();
include 'config.php';

// Proteksi Halaman
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') { 
    header("Location: index.php"); 
    exit; 
}

// --- LOGIKA TAMBAH ---
if (isset($_POST['tambah_fitur'])) {
    $judul = mysqli_real_escape_string($conn, $_POST['judul']);
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);
    if (!is_dir("img/fitur")) {
        mkdir("img/fitur", 0777, true);
    }
    $file_name = time() . '_' . $_FILES['gambar']['name'];
    
    if (move_uploaded_file($_FILES['gambar']['tmp_name'], "img/fitur/" . $file_name)) {
        mysqli_query($conn, "INSERT INTO keuntungan (judul, deskripsi, gambar) VALUES ('$judul', '$deskripsi', '$file_name')");
        header("Location: kelola_keuntungan.php?msg=success");
        exit;
    }
}

// --- LOGIKA UPDATE ---
if (isset($_POST['update_fitur'])) {
    $id = (int)$_POST['id_fitur'];
    $judul = mysqli_real_escape_string($conn, $_POST['judul']);
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);
    $urutan = (int)$_POST['urutan']; // TAMBAHKAN INI

    $sql = "UPDATE keuntungan SET judul='$judul', deskripsi='$deskripsi', urutan='$urutan'"; // TAMBAHKAN URUTAN DI QUERY

    if ($_FILES['gambar']['name'] != "") {
        $file_name = time() . '_' . $_FILES['gambar']['name'];
        if (move_uploaded_file($_FILES['gambar']['tmp_name'], "img/fitur/" . $file_name)) {
            $res = mysqli_query($conn, "SELECT gambar FROM keuntungan WHERE id = $id");
            $lama = mysqli_fetch_assoc($res);
            if ($lama && file_exists("img/fitur/" . $lama['gambar'])) { 
                unlink("img/fitur/" . $lama['gambar']); 
            }
            $sql .= ", gambar='$file_name'";
        }
    }
    
    $sql .= " WHERE id=$id";
    mysqli_query($conn, $sql);
    header("Location: kelola_keuntungan.php?msg=success_edit");
    exit;
}

// --- LOGIKA HAPUS ---
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    $res = mysqli_query($conn, "SELECT gambar FROM keuntungan WHERE id = $id");
    $data = mysqli_fetch_assoc($res);
    if ($data) {
        if (file_exists("img/fitur/" . $data['gambar'])) { unlink("img/fitur/" . $data['gambar']); }
        mysqli_query($conn, "DELETE FROM keuntungan WHERE id = $id");
    }
    header("Location: kelola_keuntungan.php?msg=success_delete");
    exit;
}

$keuntungan = mysqli_query($conn, "SELECT * FROM keuntungan ORDER BY urutan ASC, id DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Keuntungan - Admin Kinara</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root { --primary-color: #ff385c; --sidebar-bg: #212529; }
        body { font-family: 'Inter', sans-serif; background-color: #f8f9fa; }
        .sidebar { height: 100vh; width: 250px; position: fixed; top: 0; left: 0; background-color: var(--sidebar-bg); padding-top: 20px; overflow-y: auto; z-index: 999;}
        .sidebar .nav-link { color: #adb5bd; padding: 12px 20px; margin: 2px 15px; border-radius: 10px; font-size: 14px; transition: 0.3s; text-decoration:none; display:block;}
        .sidebar .nav-link:hover { background: rgba(255,255,255,0.05); color: #fff; }
        .sidebar .nav-link.active { background: var(--primary-color); color: white; }
        .sidebar .nav-group-label { font-size: 11px; text-transform: uppercase; color: #6c757d; margin: 20px 25px 10px; display: block; font-weight:700;}
        .main-content { margin-left: 250px; padding: 30px; }
        .fitur-img { width: 100%; height: 150px; object-fit: cover; border-radius: 12px; }
        .card { border: none; border-radius: 15px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
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
            <a href="kelola_keuntungan.php" class="nav-link active">
                <i class="fas fa-star me-2"></i> Keuntungan
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
    <h3 class="fw-bold mb-4">Kelola Keuntungan Tinggal</h3>

    <div class="card p-4 mb-4">
        <h6 class="fw-bold mb-3">Tambah Keuntungan Baru</h6>
        <form action="" method="POST" enctype="multipart/form-data" class="row g-3">
            <div class="col-md-4">
                <label class="small fw-bold text-muted mb-1">No. Urut</label>
                <input type="number" name="urutan" class="form-control" placeholder="No. Urut (1, 2, 3..)" required>
            </div>
            <div class="col-md-4">
                <label class="small fw-bold text-muted mb-1">Judul Informasi</label>
                <input type="text" name="judul" class="form-control" placeholder="Judul (Contoh: Fully Furnished)" required>
            </div>
            <div class="col-md-4">
                <label class="small fw-bold text-muted mb-1">Deskripsi Singkat</label>
                <input type="text" name="deskripsi" class="form-control" placeholder="Deskripsi Singkat" required>
            </div>
<div class="col-md-12">
    <label class="small fw-bold text-muted mb-1">Gambar / Foto</label>
    <input type="file" name="gambar" class="form-control bg-light" accept="image/*" required>
</div>
            <div class="col-md-1">
                <button type="submit" name="tambah_fitur" class="btn btn-danger w-100"><i class="fas fa-plus"></i></button>
            </div>
        </form>
    </div>

    <div class="row">
        <?php while($row = mysqli_fetch_assoc($keuntungan)): ?>
        <div class="col-md-3 mb-4">
            <div class="card h-100">
                <div class="p-2">
                    <img src="img/fitur/<?= $row['gambar']; ?>" class="fitur-img">
                </div>
                <div class="card-body pt-0">
                    <h6 class="fw-bold mb-1"><?= $row['judul']; ?></h6>
                    <p class="text-muted small mb-3"><?= $row['deskripsi']; ?></p>
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-outline-primary w-100 btn-edit" 
                                data-urutan="<?= $row['urutan']; ?>"                        
                                data-id="<?= $row['id']; ?>" 
                                data-judul="<?= $row['judul']; ?>"
                                data-desc="<?= $row['deskripsi']; ?>">Edit</button>
                        <button class="btn btn-sm btn-outline-danger btn-hapus" data-id="<?= $row['id']; ?>"><i class="fas fa-trash"></i></button>
                    </div>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
</div>

<div class="modal fade" id="modalEdit" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id_fitur" id="edit_id">
            <div class="modal-content rounded-4">
                <div class="modal-header border-0">
                    <h5 class="fw-bold">Edit Keuntungan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="small fw-bold">No. Urut</label>
                        <input type="number" name="urutan" id="edit_urutan" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="small fw-bold">Judul</label>
                        <input type="text" name="judul" id="edit_judul" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="small fw-bold">Deskripsi</label>
                        <textarea name="deskripsi" id="edit_desc" class="form-control" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="small fw-bold">Ganti Gambar (Opsional)</label>
                        <input type="file" name="gambar" class="form-control" accept="image/*">
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="submit" name="update_fitur" class="btn btn-danger w-100">Simpan Perubahan</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Logic Edit
    document.querySelectorAll('.btn-edit').forEach(btn => {
        btn.onclick = function() {
            document.getElementById('edit_id').value = this.dataset.id;
            document.getElementById('edit_judul').value = this.dataset.judul;
            document.getElementById('edit_desc').value = this.dataset.desc;
            document.getElementById('edit_urutan').value = this.dataset.urutan; // TAMBAHKAN INI
            new bootstrap.Modal(document.getElementById('modalEdit')).show();
        }
    });

    // Logic Hapus
    document.querySelectorAll('.btn-hapus').forEach(btn => {
        btn.onclick = function() {
            const id = this.dataset.id;
            Swal.fire({
                title: 'Hapus data ini?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ff385c',
                confirmButtonText: 'Ya, Hapus!'
            }).then((result) => {
                if (result.isConfirmed) window.location.href = `kelola_keuntungan.php?hapus=${id}`;
            });
        }
    });
</script>

<?php if(isset($_GET['msg'])): ?>
<script>
    const msg = "<?= $_GET['msg'] ?>";
    const config = { confirmButtonColor: '#ff385c', timer: 2000 };
    if (msg === 'success') Swal.fire({...config, title: 'Berhasil!', icon: 'success'});
    if (msg === 'success_edit') Swal.fire({...config, title: 'Diperbarui!', icon: 'success'});
    if (msg === 'success_delete') Swal.fire({...config, title: 'Dihapus!', icon: 'success'});
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
</script>

</body>
</html>
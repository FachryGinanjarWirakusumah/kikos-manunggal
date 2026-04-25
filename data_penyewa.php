<?php
session_start();
include 'config.php';

// Proteksi Admin
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

// Ambil Data Berdasarkan Role
$query_user = mysqli_query($conn, "SELECT * FROM users WHERE role = 'user' ORDER BY id DESC");
$query_admin = mysqli_query($conn, "SELECT * FROM users WHERE role = 'admin' ORDER BY id DESC");
$query_owner = mysqli_query($conn, "SELECT * FROM users WHERE role = 'owner' ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Pengguna - Kinara Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
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
        .sidebar .nav-group-label { font-size: 11px; text-transform: uppercase; letter-spacing: 1.5px; color: #6c757d; font-weight: 700; margin: 20px 25px 10px; display: block; }
        .sidebar .nav-link { color: #adb5bd; padding: 12px 20px; margin: 2px 15px; border-radius: 10px; font-size: 14px; transition: all 0.3s; }
        .sidebar .nav-link:hover { background: rgba(255, 255, 255, 0.05); color: #fff; }
        .sidebar .nav-link.active { background: var(--primary-color); color: white; box-shadow: 0 4px 15px rgba(255, 56, 92, 0.3); }
        .main-content { margin-left: 250px; padding: 30px; }
        .navbar-admin { background: white; box-shadow: 0 2px 4px rgba(0,0,0,0.05); margin-bottom: 30px; padding: 15px 25px; border-radius: 12px; }
        .avatar-user, .avatar-circle { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; }
    </style>
</head>
<body>

<?php if(isset($_GET['msg'])): ?>
    <script>
        const msg = "<?= $_GET['msg']; ?>";
        if(msg === 'success_add') Swal.fire('Berhasil!', 'Pengguna ditambahkan.', 'success');
        if(msg === 'success_update') Swal.fire('Berhasil!', 'Data diperbarui.', 'success');
        if(msg === 'success_delete') Swal.fire('Terhapus!', 'Pengguna telah dihapus.', 'success');
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
            <a href="data_penyewa.php" class="nav-link active">
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
    <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 rounded-4 shadow-sm">
        <h5 class="fw-bold mb-0">Manajemen Pengguna</h5>
        <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#modalUser"><i class="fas fa-plus me-1"></i> Tambah Pengguna</button>
    </div>

    <ul class="nav nav-tabs mb-4" id="userTab" role="tablist">
        <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#user-list">User (Penyewa)</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#admin-list">Admin (Staff)</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#owner-list">Owner (Pemilik)</button></li>
    </ul>

    <div class="tab-content">
        <div class="tab-pane fade show active" id="user-list">
            <div class="card border-0 shadow-sm rounded-4 p-3">
                <table class="table table-hover align-middle">
                    <thead><tr><th>No</th><th>Profil</th><th>Kontak</th><th>Status</th><th class="text-center">Aksi</th></tr></thead>
                    <tbody>
                        <?php $n=1; while($u = mysqli_fetch_assoc($query_user)): ?>
                        <tr>
                            <td><?= $n++; ?></td>
                            <td><img src="https://ui-avatars.com/api/?name=<?= urlencode($u['nama']); ?>&background=random" class="avatar-circle me-2"> <strong><?= $u['nama']; ?></strong></td>
                            <td><?= $u['kontak']; ?></td>
                            <td><span class="badge bg-success opacity-75">Penyewa</span></td>
                            <td class="text-center">
                                <div class="btn-group">
                                    <button class="btn btn-sm btn-light border btn-edit-user" data-id="<?= $u['id']; ?>" data-nama="<?= $u['nama']; ?>" data-kontak="<?= $u['kontak']; ?>" data-role="<?= $u['role']; ?>"><i class="fas fa-edit text-primary"></i></button>
                                    <button class="btn btn-sm btn-light border btn-hapus-user" data-id="<?= $u['id']; ?>" data-nama="<?= $u['nama']; ?>"><i class="fas fa-trash text-danger"></i></button>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="tab-pane fade" id="admin-list">
            <div class="card border-0 shadow-sm rounded-4 p-3 border-start border-danger border-4">
                <table class="table table-hover align-middle">
                    <thead><tr><th>No</th><th>Admin</th><th>Kontak</th><th>Role</th><th class="text-center">Aksi</th></tr></thead>
                    <tbody>
                        <?php $na=1; while($a = mysqli_fetch_assoc($query_admin)): ?>
                        <tr>
                            <td><?= $na++; ?></td>
                            <td><img src="https://ui-avatars.com/api/?name=<?= urlencode($a['nama']); ?>&background=ff385c&color=fff" class="avatar-circle me-2"> <strong><?= $a['nama']; ?></strong></td>
                            <td><?= $a['kontak']; ?></td>
                            <td><span class="badge bg-dark">Admin</span></td>
                            <td class="text-center">
                                <div class="btn-group">
                                    <button class="btn btn-sm btn-light border btn-edit-user" data-id="<?= $a['id']; ?>" data-nama="<?= $a['nama']; ?>" data-kontak="<?= $a['kontak']; ?>" data-role="<?= $a['role']; ?>"><i class="fas fa-edit text-primary"></i></button>
                                    <button class="btn btn-sm btn-light border btn-hapus-user" data-id="<?= $a['id']; ?>" data-nama="<?= $a['nama']; ?>"><i class="fas fa-trash text-danger"></i></button>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="tab-pane fade" id="owner-list">
            <div class="card border-0 shadow-sm rounded-4 p-4 border-start border-dark border-4">
                <table class="table align-middle">
                    <thead><tr><th>Owner</th><th>Kontak</th><th>Akses</th><th class="text-center">Aksi</th></tr></thead>
                    <tbody>
                        <?php while($o = mysqli_fetch_assoc($query_owner)): ?>
                        <tr>
                            <td><img src="https://ui-avatars.com/api/?name=<?= urlencode($o['nama']); ?>&background=000&color=fff" class="avatar-circle me-2"> <strong><?= $o['nama']; ?></strong></td>
                            <td><?= $o['kontak']; ?></td>
                            <td><span class="badge bg-warning text-dark">Owner</span></td>
                            <td class="text-center">
                                <div class="btn-group">
                                    <button class="btn btn-sm btn-light border btn-edit-user" data-id="<?= $o['id']; ?>" data-nama="<?= $o['nama']; ?>" data-kontak="<?= $o['kontak']; ?>" data-role="<?= $o['role']; ?>"><i class="fas fa-edit text-primary"></i></button>
                                    <button class="btn btn-sm btn-light border btn-hapus-user" data-id="<?= $o['id']; ?>" data-nama="<?= $o['nama']; ?>"><i class="fas fa-trash text-danger"></i></button>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalUser" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="proses_pengguna.php" method="POST">
            <input type="hidden" name="id_user" id="user_id">
            <div class="modal-content rounded-4 border-0">
                <div class="modal-header border-0"><h5 class="fw-bold" id="modalTitle">Tambah Pengguna</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-3"><label class="small fw-bold">Nama Lengkap</label><input type="text" name="nama" id="user_nama" class="form-control" required></div>
                    <div class="mb-3"><label class="small fw-bold">Kontak</label><input type="text" name="kontak" id="user_kontak" class="form-control" required></div>
                    <div class="mb-3"><label class="small fw-bold">Role</label>
                        <select name="role" id="user_role" class="form-select">
                            <option value="user">Penyewa</option>
                            <option value="admin">Admin</option>
                            <option value="owner">Owner</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0"><button type="submit" name="simpan_pengguna" class="btn btn-danger w-100 rounded-3">Simpan Data</button></div>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// EDIT JS - MENGISI MODAL OTOMATIS
document.querySelectorAll('.btn-edit-user').forEach(btn => {
    btn.addEventListener('click', function() {
        document.getElementById('modalTitle').innerText = 'Edit Data Pengguna';
        document.getElementById('user_id').value = this.dataset.id;
        document.getElementById('user_nama').value = this.dataset.nama;
        document.getElementById('user_kontak').value = this.dataset.kontak;
        document.getElementById('user_role').value = this.dataset.role;
        new bootstrap.Modal(document.getElementById('modalUser')).show();
    });
});

// HAPUS JS - SWEETALERT
document.querySelectorAll('.btn-hapus-user').forEach(btn => {
    btn.addEventListener('click', function() {
        const id = this.dataset.id;
        const nama = this.dataset.nama;
        Swal.fire({
            title: 'Hapus Akun?',
            text: "Akun " + nama + " akan dihapus selamanya!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ff385c',
            confirmButtonText: 'Ya, Hapus!'
        }).then((result) => {
            if (result.isConfirmed) window.location.href = 'proses_pengguna.php?hapus=' + id;
        });
    });
});
</script>

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
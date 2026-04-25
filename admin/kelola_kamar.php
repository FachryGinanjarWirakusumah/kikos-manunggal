<?php
session_start();
include 'config.php';

// Proteksi Admin
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

// Logika Toggle Unggulan
if (isset($_GET['toggle_featured'])) {
    $id = (int)$_GET['toggle_featured'];
    $val = $_GET['val'] == 1 ? 0 : 1;
    mysqli_query($conn, "UPDATE kamar SET is_featured = $val WHERE id = $id");
    header("Location: kelola_kamar.php");
    exit;
}

// Ambil lokasi untuk pengelompokan
$query_lokasi = mysqli_query($conn, "SELECT DISTINCT lokasi FROM kamar ORDER BY lokasi ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Kamar - Kinara Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
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
        
        .main-content { margin-left: 250px; padding: 30px; }
        .navbar-admin { background: white; box-shadow: 0 2px 4px rgba(0,0,0,0.05); margin-bottom: 30px; padding: 15px 25px; border-radius: 12px; }
        
        /* TABLE STYLING */
        .img-kamar { width: 80px; height: 60px; object-fit: cover; border-radius: 8px; }
        .btn-featured { transition: all 0.3s; }
        .btn-featured:hover { transform: scale(1.1); }
    </style>
</head>
<body>

<?php if(isset($_GET['msg'])): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const msg = "<?= $_GET['msg']; ?>";
            if(msg === 'success_add') Swal.fire('Berhasil!', 'Unit kamar ditambahkan.', 'success');
            if(msg === 'success_update') Swal.fire('Berhasil!', 'Data kamar diperbarui.', 'success');
            if(msg === 'success_delete') Swal.fire('Terhapus!', 'Unit kamar telah dihapus.', 'success');
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
            <a href="kelola_kamar.php" class="nav-link active">
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
        <h4 class="fw-bold mb-0">Daftar Unit Kost</h4>
        <button id="btnTambahBaru" class="btn btn-danger px-4 rounded-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambah">
            <i class="fas fa-plus me-2"></i> Tambah Kamar
        </button>
    </div>

    <?php while ($loc = mysqli_fetch_assoc($query_lokasi)) : $nama_lokasi = $loc['lokasi']; ?>
        <div class="location-header shadow-sm mb-3">
            <i class="fas fa-map-marker-alt text-danger me-2"></i> LOKASI: <strong><?= strtoupper($nama_lokasi); ?></strong>
        </div>

        <div class="card border-0 shadow-sm rounded-4 p-3 mb-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Unit</th>
                            <th>Info Detail</th>
                            <th>Harga</th>
                            <th class="text-center">Unggulan</th>
                            <th class="text-center">Status</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query_kamar = mysqli_query($conn, "SELECT * FROM kamar WHERE lokasi = '$nama_lokasi' ORDER BY LENGTH(nama_kamar) ASC, nama_kamar ASC");
                        while ($row = mysqli_fetch_assoc($query_kamar)) :
                        ?>
                        <tr>
                            <td class="ps-3"><img src="img/<?= $row['gambar']; ?>" class="img-kamar shadow-sm" onerror="this.src='https://via.placeholder.com/150'"></td>
                            <td>
                                <div class="fw-bold"><?= $row['nama_kamar']; ?></div>
                                <span class="badge bg-light text-dark border fw-normal"><i class="fas fa-<?= $row['tipe'] == 'ikhwan' ? 'male text-primary' : 'female text-danger'; ?> me-1"></i> <?= ucfirst($row['tipe']); ?></span>
                            </td>
                            <td class="fw-bold">Rp <?= number_format($row['harga'], 0, ',', '.'); ?></td>
                            <td class="text-center">
                                <a href="?toggle_featured=<?= $row['id']; ?>&val=<?= $row['is_featured']; ?>" class="btn btn-sm <?= $row['is_featured'] ? 'btn-warning' : 'btn-outline-secondary'; ?>">
                                    <i class="fas fa-star"></i>
                                </a>
                            </td>
                            <td class="text-center"><span class="badge <?= $row['status'] == 'tersedia' ? 'bg-success' : 'bg-danger'; ?> rounded-pill"><?= ucfirst($row['status']); ?></span></td>
                            <td class="text-end pe-3">
                                <div class="btn-group">
                                    <button class="btn btn-sm btn-light border btn-edit" 
                                            data-id="<?= $row['id']; ?>" 
                                            data-nama="<?= $row['nama_kamar']; ?>"
                                            data-lokasi="<?= $row['lokasi']; ?>"
                                            data-harga="<?= $row['harga']; ?>"
                                            data-tipe="<?= $row['tipe']; ?>"
                                            data-status="<?= $row['status']; ?>"
                                            data-deskripsi="<?= htmlspecialchars($row['deskripsi']); ?>"> <i class="fas fa-edit text-primary"></i>
                                    </button>
                                    <button class="btn btn-sm btn-light border btn-hapus" 
                                            data-id="<?= $row['id']; ?>" 
                                            data-nama="<?= $row['nama_kamar']; ?>">
                                        <i class="fas fa-trash text-danger"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endwhile; ?>
</div>

<div class="modal fade" id="modalTambah" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="proses_kamar.php" method="POST" enctype="multipart/form-data">
            <div class="modal-content rounded-4 border-0">
                <div class="modal-header border-0"><h5 class="fw-bold modal-title">Tambah Unit Baru</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-3"><label class="small fw-bold">Nama Kamar</label><input type="text" name="nama_kamar" class="form-control" required></div>
                    <div class="mb-3"><label class="small fw-bold">Lokasi</label><input type="text" name="lokasi" class="form-control" required></div>
                    <div class="row">
                        <div class="col-6 mb-3"><label class="small fw-bold">Harga</label><input type="number" name="harga" class="form-control" required></div>
                        <div class="col-6 mb-3"><label class="small fw-bold">Tipe</label><select name="tipe" class="form-select"><option value="ikhwan">Ikhwan</option><option value="akhwat">Akhwat</option></select></div>
                    </div>
                    <div class="mb-3">
                        <label class="small fw-bold">Deskripsi & Fasilitas Kamar</label>
                        <textarea name="deskripsi" class="form-control" rows="5" placeholder="Contoh: 
                    - Kasur & Bantal
                    - Kamar Mandi Dalam
                    - WiFi Kencang
                    - AC / Kipas Angin"></textarea>
                        <small class="text-muted">Gunakan baris baru (Enter) untuk setiap fasilitas agar tampil rapi.</small>
                    </div>                        
                    <div class="mb-3"><label class="small fw-bold">Status</label><select name="status" class="form-select"><option value="tersedia">Tersedia</option><option value="penuh">Penuh</option></select></div>
                    <div class="mb-3"><label class="small fw-bold">Upload Galeri</label><input type="file" name="galeri[]" class="form-control" multiple></div>
                    <div id="container-galeri-edit" class="mt-3 p-3 border rounded bg-light" style="display:none;">
                        <label class="small fw-bold d-block mb-2 text-danger">Galeri Saat Ini</label>
                        <div id="list-galeri" class="d-flex flex-wrap gap-2"></div>
                    </div>
                </div>
                <div class="modal-footer border-0"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button><button type="submit" name="tambah_kamar" class="btn btn-danger px-4">Simpan</button></div>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
const modalElemen = document.getElementById('modalTambah');
const modalInstance = new bootstrap.Modal(modalElemen);

document.querySelectorAll('.btn-edit').forEach(btn => {
    btn.addEventListener('click', function() {
        const idKamar = this.dataset.id;
        document.getElementById('container-galeri-edit').style.display = 'block';
        const listGaleri = document.getElementById('list-galeri');
        listGaleri.innerHTML = 'Memuat galeri...';

        fetch(`ambil_galeri.php?id_kamar=${idKamar}`)
            .then(res => res.json())
            .then(data => {
                listGaleri.innerHTML = '';
                data.forEach(item => {
                    const div = document.createElement('div');
                    div.className = 'position-relative border rounded p-1 bg-white';
                    div.style.width = '100px';
                    div.innerHTML = `<img src="img/galeri/${item.file_name}" style="width:100%; height:60px; object-fit:cover;">
                        <div class="d-flex justify-content-between mt-1">
                            <button type="button" onclick="setSampul(${idKamar}, '${item.file_name}')" class="btn btn-sm btn-warning p-1 text-white"><i class="fas fa-star"></i></button>
                            <button type="button" onclick="hapusSatuFile(${item.id}, this)" class="btn btn-sm btn-danger p-1"><i class="fas fa-trash"></i></button>
                        </div>`;
                    listGaleri.appendChild(div);
                });
            });

        modalElemen.querySelector('.modal-title').innerText = 'Edit Unit Kamar';
        const subBtn = modalElemen.querySelector('button[type="submit"]');
        subBtn.innerText = 'Update Unit'; subBtn.setAttribute('name', 'edit_kamar');

        modalElemen.querySelector('input[name="nama_kamar"]').value = this.dataset.nama;
        modalElemen.querySelector('input[name="lokasi"]').value = this.dataset.lokasi;
        modalElemen.querySelector('input[name="harga"]').value = this.dataset.harga;
        modalElemen.querySelector('select[name="tipe"]').value = this.dataset.tipe;
        modalElemen.querySelector('select[name="status"]').value = this.dataset.status;
        modalElemen.querySelector('textarea[name="deskripsi"]').value = this.dataset.deskripsi;

        if(!document.getElementById('edit_id')){
            let h = document.createElement('input'); h.type='hidden'; h.name='id_kamar'; h.id='edit_id';
            modalElemen.querySelector('form').appendChild(h);
        }
        document.getElementById('edit_id').value = idKamar;
        modalInstance.show();
    });
});

function hapusSatuFile(id, el) {
    fetch(`proses_kamar.php?hapus_galeri=${id}`).then(res => res.json()).then(res => {
        if(res.status === 'success') el.closest('.position-relative').remove();
    });
}

function setSampul(id, file) {
    fetch(`proses_kamar.php?set_sampul=1&id_kamar=${id}&file=${file}`).then(res => res.json()).then(res => {
        if(res.status === 'success') location.reload();
    });
}

document.querySelectorAll('.btn-hapus').forEach(btn => {
    btn.onclick = function() {
        const id = this.dataset.id;
        Swal.fire({ title: 'Hapus unit?', icon: 'warning', showCancelButton: true }).then(r => {
            if(r.isConfirmed) window.location.href = `proses_kamar.php?hapus=${id}`;
        });
    };
});

document.getElementById('btnTambahBaru').onclick = () => {
    modalElemen.querySelector('form').reset();
    document.getElementById('container-galeri-edit').style.display = 'none';
    if(document.getElementById('edit_id')) document.getElementById('edit_id').remove();
};
</script>
</body>
</html>
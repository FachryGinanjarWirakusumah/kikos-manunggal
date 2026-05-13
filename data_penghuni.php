<?php
session_start();
include 'config.php';

// Proteksi Admin
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

// LOGIKA CHECKOUT (MENGOSONGKAN KAMAR)
if (isset($_GET['kosongkan'])) {
    $id_kamar = (int)$_GET['kosongkan'];
    // Ubah status kamar kembali menjadi tersedia
    mysqli_query($conn, "UPDATE kamar SET status = 'tersedia' WHERE id = $id_kamar");
    header("Location: data_penghuni.php?msg=success_checkout");
    exit;
}

// AMBIL DATA PENGHUNI (HANYA KAMAR YANG PENUH)
$query_penghuni = mysqli_query($conn, "
    SELECT k.id as kamar_id, k.nama_kamar, k.lokasi, k.harga, k.tipe, k.gambar,
           p.*, 
           u.nama as nama_penghuni, u.kontak
    FROM kamar k
    JOIN pembayaran p ON k.id = p.id_kamar
    JOIN users u ON p.id_user = u.id
    WHERE k.status = 'penuh' AND p.status_pembayaran = 'berhasil'
    ORDER BY p.id DESC
");

// 1. Filter agar 1 kamar hanya menampilkan 1 penghuni terakhir (Mencegah duplikat)
$data_okupansi = [];
while($row = mysqli_fetch_assoc($query_penghuni)){
    if(!isset($data_okupansi[$row['kamar_id']])){
        $data_okupansi[$row['kamar_id']] = $row;
    }
}

// 2. Kelompokkan data berdasarkan LOKASI KAMAR
$grouped_data = [];
foreach($data_okupansi as $p) {
    $lokasi = strtoupper($p['lokasi']);
    if(!isset($grouped_data[$lokasi])) {
        $grouped_data[$lokasi] = [];
    }
    $grouped_data[$lokasi][] = $p;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Penghuni - Kinara Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root { --primary-color: #ff385c; --sidebar-bg: #212529; }
        body { font-family: 'Inter', sans-serif; background-color: #f4f7f6; }
        
        /* SIDEBAR STYLING */
        .sidebar { height: 100vh; width: 250px; position: fixed; top: 0; left: 0; background-color: var(--sidebar-bg); padding: 20px 0; color: white; overflow-y: auto; z-index: 999; }
        .sidebar::-webkit-scrollbar { width: 5px; }
        .sidebar::-webkit-scrollbar-track { background: transparent; }
        .sidebar::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.1); border-radius: 10px; }
        .sidebar .nav-group-label { font-size: 11px; text-transform: uppercase; letter-spacing: 1.5px; color: #6c757d; font-weight: 700; margin: 20px 25px 10px; display: block; }
        .sidebar .nav-link { color: #adb5bd; padding: 12px 20px; margin: 2px 15px; border-radius: 10px; font-size: 14px; font-weight: 500; transition: all 0.3s; }
        .sidebar .nav-link:hover { background: rgba(255, 255, 255, 0.05); color: #fff; }
        .sidebar .nav-link.active { background: var(--primary-color); color: white; box-shadow: 0 4px 15px rgba(255, 56, 92, 0.3); }
        .sidebar hr { border-color: rgba(255,255,255,0.1); margin: 20px 15px; }
        
        /* MAIN CONTENT */
        .main-content { margin-left: 250px; padding: 30px; }
        
        /* UI TABLE MODERN */
        .location-header { background: white; padding: 14px 20px; border-radius: 12px; border-left: 4px solid var(--primary-color); font-size: 15px; letter-spacing: 0.5px; }
        .table-custom td { padding: 16px 12px; vertical-align: middle; border-bottom: 1px dashed #eee; }
        .table-custom th { padding: 15px 12px; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; color: #888; border-bottom: 2px solid #eee; }
        
        .img-kamar { width: 65px; height: 65px; object-fit: cover; border-radius: 12px; border: 1px solid #eaeaea; }
        .user-avatar { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 2px solid #fff; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }

        .badge-status { padding: 6px 12px; border-radius: 30px; font-size: 12px; font-weight: 700; letter-spacing: 0.5px; }
        
        .btn-action-outline { border: 1.5px solid #eee; background: white; color: #e74c3c; font-weight: 600; font-size: 13px; padding: 8px 16px; border-radius: 10px; transition: 0.2s; }
        .btn-action-outline:hover { background: #e74c3c; color: white; border-color: #e74c3c; }

        /* Mobile Responsif */
        .sidebar-overlay { display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0, 0, 0, 0.5); backdrop-filter: blur(3px); z-index: 998; }
        .btn-toggle-sidebar { display: none; background: none; border: none; font-size: 22px; color: #212529; cursor: pointer; padding: 0; }
        
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); transition: transform 0.3s; }
            .sidebar.show { transform: translateX(0); }
            .sidebar-overlay.show { display: block; }
            .main-content { margin-left: 0 !important; padding: 15px; }
            .btn-toggle-sidebar { display: block; }
            .table-responsive { border-radius: 12px; border: 1px solid #eee; }
            .table td, .table th { white-space: nowrap; } 
        }
    </style>
</head>
<body>

<?php if(isset($_GET['msg']) && $_GET['msg'] == 'success_checkout'): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                title: 'Kamar Dikosongkan!',
                text: 'Kamar telah direset dan siap disewa kembali.',
                icon: 'success',
                confirmButtonColor: '#ff385c'
            });
        });
    </script>
<?php endif; ?>

<div class="sidebar-overlay" id="sidebarOverlay"></div>

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
        <li class="nav-item"><a href="data_penghuni.php" class="nav-link active"><i class="fas fa-user-check me-2"></i> Data Penghuni</a></li>
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
        <li class="nav-item"><a href="kelola_hero.php" class="nav-link"><i class="fas fa-image me-2"></i> Banner</a></li>            
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
        <div class="d-flex align-items-center gap-3">
            <button class="btn-toggle-sidebar" id="btnToggleSidebar"><i class="fas fa-bars"></i></button>
            <div>
                <h4 class="fw-bold mb-0" style="color: #222; letter-spacing: -0.5px;">Status Okupansi & Penghuni</h4>
                <p class="text-muted mb-0" style="font-size: 14px;">Pantau kamar yang sedang disewa per lokasi.</p>
            </div>
        </div>
    </div>

    <?php if(empty($grouped_data)): ?>
        <div class="text-center py-5 bg-white rounded-4 border" style="border-style: dashed !important;">
            <i class="fas fa-door-open mb-3 text-muted" style="font-size: 50px;"></i>
            <h5 class="fw-bold text-dark">Semua Kamar Kosong</h5>
            <p class="text-muted">Belum ada kamar yang terisi saat ini.</p>
        </div>
    <?php else: ?>
        
        <?php foreach($grouped_data as $lokasi => $penghuni_list): ?>
            
            <div class="location-header shadow-sm mb-3 mt-4 d-flex align-items-center justify-content-between">
                <div><i class="fas fa-map-marker-alt text-danger me-2"></i> LOKASI: <strong><?= $lokasi; ?></strong></div>
                <span class="badge bg-dark rounded-pill"><?= count($penghuni_list); ?> Terisi</span>
            </div>

            <div class="card border-0 shadow-sm rounded-4 p-2 mb-4">
                <div class="table-responsive">
                    <table class="table table-custom table-hover align-middle mb-0 border-0">
                        <thead class="table-light border-0">
                            <tr>
                                <th class="ps-4 border-0 rounded-start">Unit Kamar</th>
                                <th class="border-0">Data Penghuni</th>
                                <th class="border-0">Durasi & Bayar</th>
                                <th class="text-center border-0">Status</th>
                                <th class="text-end pe-4 border-0 rounded-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="border-top-0">
                            <?php foreach($penghuni_list as $p): ?>
                            
                            <?php 
                                // Trik Pintar Estimasi Durasi
                                $lama_bulan = 1;
                                if($p['harga'] > 0 && $p['jumlah_bayar'] > 0){
                                    $lama_bulan = floor($p['jumlah_bayar'] / $p['harga']);
                                    if($lama_bulan < 1) $lama_bulan = 1; 
                                }
                                // Atasi error tanggal
                                $waktu = isset($p['tanggal_bayar']) ? $p['tanggal_bayar'] : (isset($p['tanggal']) ? $p['tanggal'] : 'now');
                            ?>

                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center gap-3">
                                        <img src="img/<?= $p['gambar']; ?>" class="img-kamar" onerror="this.src='https://via.placeholder.com/150'">
                                        <div>
                                            <div class="fw-bold text-dark" style="font-size: 15px;"><?= $p['nama_kamar']; ?></div>
                                            <div class="text-muted mt-1" style="font-size: 12px;">
                                                <span class="badge bg-light text-dark border"><i class="fas fa-<?= $p['tipe'] == 'ikhwan' ? 'male text-primary' : 'female text-danger'; ?>"></i> <?= ucfirst($p['tipe']); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <img src="https://ui-avatars.com/api/?name=<?= urlencode($p['nama_penghuni']); ?>&background=f4f7f6&color=222" class="user-avatar">
                                        <div>
                                            <div class="fw-bold text-dark" style="font-size: 14px;"><?= $p['nama_penghuni']; ?></div>
                                            <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $p['kontak']); ?>" target="_blank" class="text-decoration-none text-success mt-1 d-inline-block" style="font-size: 12px; font-weight: 600;">
                                                <i class="fab fa-whatsapp"></i> <?= $p['kontak']; ?>
                                            </a>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    <div class="fw-bold text-dark" style="font-size: 14px;">
                                        <?= $p['jumlah_bayar'] <= 0 ? '<span class="text-danger">FREE / Promo</span>' : 'Rp ' . number_format($p['jumlah_bayar'], 0, ',', '.'); ?>
                                    </div>
                                    <div style="font-size: 12px; color: #777; margin-top: 4px;">
                                        <i class="far fa-clock me-1"></i> Sewa: <strong><?= $p['jumlah_bayar'] <= 0 ? 'Promo' : $lama_bulan . ' Bln'; ?></strong>
                                    </div>
                                    <div style="font-size: 11px; color: #aaa; margin-top: 2px;">
                                        Mulai: <?= date('d M Y', strtotime($waktu)); ?>
                                    </div>
                                </td>

                                <td class="text-center">
                                    <span class="badge-status bg-danger bg-opacity-10 text-danger border border-danger">TERISI</span>
                                </td>

                                <td class="text-end pe-4">
                                    <button class="btn btn-action-outline btn-kosongkan" data-id="<?= $p['kamar_id']; ?>" data-nama="<?= $p['nama_kamar']; ?>" data-penghuni="<?= $p['nama_penghuni']; ?>">
                                        <i class="fas fa-sign-out-alt"></i> Kosongkan
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
        <?php endforeach; ?>
    <?php endif; ?>

</div>

<script>
// Sidebar Toggle Logic
const sidebar = document.querySelector('.sidebar');
const btnToggleSidebar = document.getElementById('btnToggleSidebar');
const sidebarOverlay = document.getElementById('sidebarOverlay');

if (btnToggleSidebar) {
    btnToggleSidebar.addEventListener('click', () => {
        sidebar.classList.add('show');
        sidebarOverlay.classList.add('show');
        document.body.style.overflow = 'hidden';
    });
}
if (sidebarOverlay) {
    sidebarOverlay.addEventListener('click', () => {
        sidebar.classList.remove('show');
        sidebarOverlay.classList.remove('show');
        document.body.style.overflow = 'auto';
    });
}

// Logika Kosongkan Kamar (Checkout)
document.querySelectorAll('.btn-kosongkan').forEach(btn => {
    btn.onclick = function() {
        const id = this.dataset.id;
        const nama = this.dataset.nama;
        const penghuni = this.dataset.penghuni;
        
        Swal.fire({ 
            title: 'Kosongkan ' + nama + '?', 
            html: `Penghuni <b>${penghuni}</b> akan check-out.<br><br>Status kamar akan dikembalikan menjadi <b>Tersedia</b>.`,
            icon: 'warning', 
            showCancelButton: true,
            confirmButtonColor: '#ff385c',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Kosongkan',
            cancelButtonText: 'Batal'
        }).then(r => {
            if(r.isConfirmed) window.location.href = `data_penghuni.php?kosongkan=${id}`;
        });
    };
});
</script>

</body>
</html>
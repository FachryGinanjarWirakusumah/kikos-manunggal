<?php
session_start();
include 'config.php';

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

// Update Status Pembayaran via AJAX/Link
if (isset($_GET['update_status'])) {
    $id = (int)$_GET['update_status'];
    $status = $_GET['status'];
    mysqli_query($conn, "UPDATE pembayaran SET status_pembayaran = '$status' WHERE id = $id");
    header("Location: pembayaran.php?msg=status_updated");
    exit;
}

// Ambil data pembayaran dengan JOIN tabel users dan kamar
$query = mysqli_query($conn, "SELECT p.*, u.nama, k.nama_kamar 
                              FROM pembayaran p 
                              JOIN users u ON p.id_user = u.id 
                              JOIN kamar k ON p.id_kamar = k.id 
                              ORDER BY p.tgl_bayar DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Manajemen Pembayaran - Kinara Admin</title>
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

        .main-content { margin-left: 250px; padding: 30px; }
        .status-badge { font-size: 12px; padding: 5px 12px; border-radius: 20px; }
        .bukti-img { width: 50px; height: 50px; object-fit: cover; border-radius: 5px; cursor: pointer; transition: 0.3s; }
        .bukti-img:hover { transform: scale(1.1); }
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
            <a href="pembayaran.php" class="nav-link active">
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
        <h5 class="fw-bold mb-0">Konfirmasi Pembayaran</h5>
        <div class="small text-muted">Admin: <?= $_SESSION['nama']; ?></div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 p-3">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Penyewa</th>
                        <th>Unit Kamar</th>
                        <th>Nominal</th>
                        <th>Bukti</th>
                        <th>Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($p = mysqli_fetch_assoc($query)): ?>
                    <tr>
                        <td><strong><?= $p['nama']; ?></strong><br><small class="text-muted"><?= date('d/m/Y', strtotime($p['tgl_bayar'])); ?></small></td>
                        <td><?= $p['nama_kamar']; ?></td>
                        <td class="fw-bold">Rp <?= number_format($p['jumlah_bayar'], 0, ',', '.'); ?></td>
                        <td>
                            <img src="img/bukti/<?= $p['bukti_transfer']; ?>" class="bukti-img" 
                                 onclick="Swal.fire({imageUrl: 'img/bukti/<?= $p['bukti_transfer']; ?>', imageWidth: 400, title: 'Bukti Transfer'})">
                        </td>
                        <td>
                            <?php if($p['status_pembayaran'] == 'pending'): ?>
                                <span class="badge bg-warning text-dark status-badge">Pending</span>
                            <?php elseif($p['status_pembayaran'] == 'berhasil'): ?>
                                <span class="badge bg-success status-badge">Berhasil</span>
                            <?php else: ?>
                                <span class="badge bg-danger status-badge">Ditolak</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <?php if($p['status_pembayaran'] == 'pending'): ?>
                                <div class="btn-group">
                                    <a href="?update_status=<?= $p['id']; ?>&status=berhasil" class="btn btn-sm btn-success" title="Terima"><i class="fas fa-check"></i></a>
                                    <a href="?update_status=<?= $p['id']; ?>&status=ditolak" class="btn btn-sm btn-danger" title="Tolak"><i class="fas fa-times"></i></a>
                                </div>
                            <?php else: ?>
                                <span class="text-muted small">Sudah Diproses</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    
                    <?php if(mysqli_num_rows($query) == 0): ?>
                        <tr><td colspan="6" class="text-center py-4 text-muted">Belum ada riwayat pembayaran.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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
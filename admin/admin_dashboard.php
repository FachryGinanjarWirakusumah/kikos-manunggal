<?php
session_start();
include 'config.php';

// Proteksi halaman
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

// --- BAGIAN INI YANG BIKIN ERROR KALAU ILANG ---

// 1. Hitung Total User
$q_u = mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role = 'user'");
$total_user = mysqli_fetch_assoc($q_u)['total'];

// 2. Hitung Kamar Tersedia
$q_k = mysqli_query($conn, "SELECT COUNT(*) as total FROM kamar WHERE status = 'tersedia'");
$total_kamar = mysqli_fetch_assoc($q_k)['total'];

// 3. Hitung Booking Baru (Pending)
$q_b = mysqli_query($conn, "SELECT COUNT(*) as total FROM pembayaran WHERE status_pembayaran = 'pending'");
$total_booking = mysqli_fetch_assoc($q_b)['total'];

// 4. Ambil Data Penyewa Terbaru (Ini variabel $q_terbaru yang tadi error)
$q_terbaru = mysqli_query($conn, "SELECT u.nama, u.kontak, k.nama_kamar, p.status_pembayaran 
                                  FROM pembayaran p 
                                  JOIN users u ON p.id_user = u.id 
                                  JOIN kamar k ON p.id_kamar = k.id 
                                  ORDER BY p.tgl_bayar DESC LIMIT 5");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Kinara Kost</title>
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
            <a href="admin_dashboard.php" class="nav-link active">
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
    <div class="navbar-admin d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-bold">Ringkasan Panel</h5>
        <div class="user-profile">
            <span class="me-2 text-muted">Halo, Admin <strong><?php echo $_SESSION['nama']; ?></strong></span>
            <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['nama']); ?>&background=ff385c&color=fff" class="rounded-circle" width="35">
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card stat-card p-3 bg-white border-0">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-0 small fw-bold text-uppercase">Total User</p>
                        <h2 class="fw-bold mb-0"><?php echo $total_user; ?></h2>
                    </div>
                    <div class="bg-primary bg-opacity-10 p-3 rounded-circle text-primary">
                        <i class="fas fa-users fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card stat-card p-3 bg-white border-0">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-0 small fw-bold text-uppercase">Kamar Tersedia</p>
                        <h2 class="fw-bold mb-0"><?php echo $total_kamar; ?></h2>
                    </div>
                    <div class="bg-success bg-opacity-10 p-3 rounded-circle text-success">
                        <i class="fas fa-door-open fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card stat-card p-3 bg-white border-0">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-0 small fw-bold text-uppercase">Booking Baru</p>
                        <h2 class="fw-bold mb-0"><?php echo $total_booking; ?></h2>
                    </div>
                    <div class="bg-warning bg-opacity-10 p-3 rounded-circle text-warning">
                        <i class="fas fa-bell fa-2x <?php echo ($total_booking > 0) ? 'fa-beat' : ''; ?>"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="fw-bold mb-0">Penyewa Terbaru</h5>
            <a href="pembayaran.php" class="btn btn-sm btn-outline-danger">Lihat Semua</a>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Nama</th>
                        <th>Kontak</th>
                        <th>Kamar</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(mysqli_num_rows($q_terbaru) > 0): ?>
                        <?php while($row = mysqli_fetch_assoc($q_terbaru)): ?>
                        <tr>
                            <td class="fw-bold"><?php echo $row['nama']; ?></td>
                            <td><?php echo $row['kontak']; ?></td>
                            <td><?php echo $row['nama_kamar']; ?></td>
                            <td>
                                <?php if($row['status_pembayaran'] == 'berhasil'): ?>
                                    <span class="badge bg-success rounded-pill">Aktif</span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark rounded-pill">Pending</span>
                                <?php endif; ?>
                            </td>
                            <td><a href="pembayaran.php" class="btn btn-sm btn-light border">Detail</a></td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted small">Belum ada transaksi booking.</td>
                        </tr>
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
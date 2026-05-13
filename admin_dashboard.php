<?php
session_start();
include 'config.php';

// Proteksi halaman
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

// ==========================================
// 1. DATA RINGKASAN PANEL ATAS
// ==========================================
$total_penyewa = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM kamar WHERE status = 'penuh'"))['total'];
$total_kamar = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM kamar WHERE status = 'tersedia'"))['total'];
$total_booking = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM pembayaran WHERE status_pembayaran = 'pending'"))['total'];
$q_pendapatan = mysqli_query($conn, "SELECT SUM(jumlah_bayar) as total FROM pembayaran WHERE status_pembayaran = 'berhasil'");
$total_pendapatan = mysqli_fetch_assoc($q_pendapatan)['total'] ?? 0;

// ==========================================
// 2. DATA UNTUK GRAFIK (CHART.JS)
// ==========================================

// TAHAP 1: Ambil SEMUA nama lokasi dari database sebagai MASTER CETAKAN
$query_semua_lokasi = mysqli_query($conn, "SELECT DISTINCT lokasi FROM kamar ORDER BY lokasi ASC");
$lokasi_list = [];
while($row = mysqli_fetch_assoc($query_semua_lokasi)) {
    $lokasi_list[] = strtoupper($row['lokasi']);
}

if(empty($lokasi_list)) {
    $lokasi_list = ['A1 MANUNGGAL', 'A2 MANUNGGAL', 'A3 MANUNGGAL', 'A4 MANUNGGAL'];
}

// TAHAP 2: Grafik Bulanan (Diubah agar mengambil Total Uang & Total Orang sekaligus)
$query_chart_rev = mysqli_query($conn, "
    SELECT 
        DATE_FORMAT(p.tgl_bayar, '%b %Y') as bulan, 
        MONTH(p.tgl_bayar) as bulan_angka,
        k.lokasi,
        SUM(p.jumlah_bayar) as total_bulanan,
        COUNT(p.id) as total_penghuni
    FROM pembayaran p
    JOIN kamar k ON p.id_kamar = k.id
    WHERE p.status_pembayaran = 'berhasil' 
    GROUP BY DATE_FORMAT(p.tgl_bayar, '%b %Y'), MONTH(p.tgl_bayar), k.lokasi
    ORDER BY bulan_angka ASC 
    LIMIT 30
");

$label_bulan = [];
$raw_data_pendapatan = [];
$raw_data_penghuni = [];

while($row = mysqli_fetch_assoc($query_chart_rev)) {
    $bln = $row['bulan'];
    $lok = strtoupper($row['lokasi']);
    if(!in_array($bln, $label_bulan)) $label_bulan[] = $bln;
    
    // Simpan kedua datanya
    $raw_data_pendapatan[$bln][$lok] = (int)$row['total_bulanan'];
    $raw_data_penghuni[$bln][$lok] = (int)$row['total_penghuni'];
}

if(empty($label_bulan)) { $label_bulan = [date('M Y')]; }

$datasets_pendapatan = [];
$datasets_penghuni = [];
$warna_master = ['#ff385c', '#1abc9c', '#f1c40f', '#3498db', '#9b59b6', '#e67e22', '#34495e'];
$i = 0;

foreach($lokasi_list as $lok) {
    $data_per_lokasi_uang = [];
    $data_per_lokasi_orang = [];
    
    foreach($label_bulan as $bln) {
        $data_per_lokasi_uang[] = isset($raw_data_pendapatan[$bln][$lok]) ? $raw_data_pendapatan[$bln][$lok] : 0;
        $data_per_lokasi_orang[] = isset($raw_data_penghuni[$bln][$lok]) ? $raw_data_penghuni[$bln][$lok] : 0;
    }
    
    // Dataset untuk mode UANG
    $datasets_pendapatan[] = [
        'label' => $lok,
        'data' => $data_per_lokasi_uang,
        'backgroundColor' => $warna_master[$i % count($warna_master)],
        'borderRadius' => 4,
        'barThickness' => 12
    ];
    
    // Dataset untuk mode ORANG
    $datasets_penghuni[] = [
        'label' => $lok,
        'data' => $data_per_lokasi_orang,
        'backgroundColor' => $warna_master[$i % count($warna_master)],
        'borderRadius' => 4,
        'barThickness' => 12
    ];
    $i++;
}

// TAHAP 3: Grafik Doughnut (Orang vs Pendapatan)
// A. Query untuk Jumlah Orang (Kamar Terisi)
$query_occ_orang = mysqli_query($conn, "SELECT lokasi, COUNT(*) as total_terisi FROM kamar WHERE status = 'penuh' GROUP BY lokasi");
$raw_data_occ_orang = [];
while($row = mysqli_fetch_assoc($query_occ_orang)) {
    $raw_data_occ_orang[strtoupper($row['lokasi'])] = (int)$row['total_terisi'];
}

// B. Query untuk Total Pendapatan per Lokasi (Doughnut Uang)
$query_occ_uang = mysqli_query($conn, "
    SELECT k.lokasi, SUM(p.jumlah_bayar) as total_uang 
    FROM pembayaran p 
    JOIN kamar k ON p.id_kamar = k.id 
    WHERE p.status_pembayaran = 'berhasil' 
    GROUP BY k.lokasi
");
$raw_data_occ_uang = [];
while($row = mysqli_fetch_assoc($query_occ_uang)) {
    $raw_data_occ_uang[strtoupper($row['lokasi'])] = (int)$row['total_uang'];
}

$label_okupansi = $lokasi_list; 
$data_okupansi_orang = [];
$data_okupansi_uang = [];

foreach($lokasi_list as $lok) {
    $data_okupansi_orang[] = isset($raw_data_occ_orang[$lok]) ? $raw_data_occ_orang[$lok] : 0;
    $data_okupansi_uang[]  = isset($raw_data_occ_uang[$lok]) ? $raw_data_occ_uang[$lok] : 0;
}
$warna_okupansi = array_slice($warna_master, 0, count($lokasi_list));

// ==========================================
// 3. STATISTIK LAMA SEWA (Harian, 1, 3, 6, 12 Bulan)
// ==========================================
$query_durasi = mysqli_query($conn, "
    SELECT k.id as kamar_id, k.lokasi, k.harga, p.jumlah_bayar 
    FROM kamar k
    JOIN pembayaran p ON k.id = p.id_kamar
    WHERE k.status = 'penuh' AND p.status_pembayaran = 'berhasil'
    ORDER BY p.id DESC
");

$stats_durasi = [];
foreach($lokasi_list as $lok) {
    $stats_durasi[$lok] = ['harian' => 0, '1_bln' => 0, '3_bln' => 0, '6_bln' => 0, '12_bln' => 0];
}

$kamar_terhitung = [];
while($row = mysqli_fetch_assoc($query_durasi)) {
    if(!in_array($row['kamar_id'], $kamar_terhitung)) {
        $kamar_terhitung[] = $row['kamar_id'];
        
        $bayar = (int)$row['jumlah_bayar'];
        $harga = (int)$row['harga'];
        $lok = strtoupper($row['lokasi']);
        
        $durasi = 1;
        if ($bayar == 1500000) $durasi = 12;
        elseif ($bayar == 1000000) $durasi = 6;
        elseif ($bayar == 500000) $durasi = 3; 
        elseif ($harga > 0 && $bayar >= $harga) {
            $durasi = floor($bayar / $harga);
        } else {
            $durasi = 0; 
        }

        if ($durasi >= 12) $stats_durasi[$lok]['12_bln']++;
        elseif ($durasi >= 6) $stats_durasi[$lok]['6_bln']++;
        elseif ($durasi >= 3) $stats_durasi[$lok]['3_bln']++;
        elseif ($durasi >= 1) $stats_durasi[$lok]['1_bln']++;
        else $stats_durasi[$lok]['harian']++;
    }
}

// ==========================================
// 4. HISTORY SEWA (FILTER BULANAN, LOKASI, UNIT)
// ==========================================
$filter_bulan = isset($_GET['filter_bulan']) ? $_GET['filter_bulan'] : date('Y-m');
$filter_y = date('Y', strtotime($filter_bulan));
$filter_m = date('m', strtotime($filter_bulan));

$filter_lokasi = isset($_GET['filter_lokasi']) ? $_GET['filter_lokasi'] : 'semua';
$filter_lokasi_safe = mysqli_real_escape_string($conn, $filter_lokasi);

$filter_unit = isset($_GET['filter_unit']) ? $_GET['filter_unit'] : 'semua';
$filter_unit_safe = mysqli_real_escape_string($conn, $filter_unit);

$sql_lokasi = ($filter_lokasi !== 'semua') ? " AND k.lokasi = '$filter_lokasi_safe'" : "";
$sql_unit = ($filter_unit !== 'semua') ? " AND k.id = '$filter_unit_safe'" : "";

$q_history = mysqli_query($conn, "
    SELECT u.nama, u.kontak, k.nama_kamar, k.lokasi, p.jumlah_bayar, p.status_pembayaran, p.tgl_bayar 
    FROM pembayaran p 
    JOIN users u ON p.id_user = u.id 
    JOIN kamar k ON p.id_kamar = k.id 
    WHERE MONTH(p.tgl_bayar) = '$filter_m' AND YEAR(p.tgl_bayar) = '$filter_y' $sql_lokasi $sql_unit
    ORDER BY p.tgl_bayar DESC
");

$query_semua_kamar = mysqli_query($conn, "SELECT id, nama_kamar, lokasi FROM kamar ORDER BY lokasi ASC, nama_kamar ASC");
$kamar_list = [];
while($r = mysqli_fetch_assoc($query_semua_kamar)){
    $kamar_list[] = $r;
}

// ==========================================
// 5. DATA PENYEWA TERBARU
// ==========================================
$q_terbaru = mysqli_query($conn, "SELECT u.nama, u.kontak, k.nama_kamar, k.lokasi, p.status_pembayaran 
                                  FROM pembayaran p 
                                  JOIN users u ON p.id_user = u.id 
                                  JOIN kamar k ON p.id_kamar = k.id 
                                  ORDER BY p.id DESC LIMIT 5");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Kinara Kost</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="admin_style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<div class="sidebar">
    <div class="px-4 mb-4 mt-2">
        <h4 class="fw-bold text-white" style="letter-spacing: -1px;">KINARA <span style="color: var(--primary-color);">ADMIN</span></h4>
    </div>
    <span class="nav-group-label">Main Menu</span>
    <ul class="nav flex-column"><li class="nav-item"><a href="admin_dashboard.php" class="nav-link active"><i class="fas fa-th-large me-2"></i> Dashboard</a></li></ul>
    <span class="nav-group-label">Manajemen Kost</span>
    <ul class="nav flex-column">
        <li class="nav-item"><a href="kelola_kamar.php" class="nav-link"><i class="fas fa-bed me-2"></i> Kelola Kamar</a></li>
        <li class="nav-item"><a href="data_penyewa.php" class="nav-link"><i class="fas fa-users me-2"></i> Data Pengguna</a></li>
        <li class="nav-item"><a href="pembayaran.php" class="nav-link"><i class="fas fa-wallet me-2"></i> Pembayaran</a></li>
        <li class="nav-item"><a href="data_penghuni.php" class="nav-link"><i class="fas fa-user-check me-2"></i> Data Penghuni</a></li>
    </ul>
    <span class="nav-group-label">Tampilan User</span>
    <ul class="nav flex-column">
        <li class="nav-item"><a href="kelola_promo.php" class="nav-link"><i class="fas fa-percentage me-2"></i> Promo</a></li>
        <li class="nav-item"><a href="kelola_keuntungan.php" class="nav-link"><i class="fas fa-star me-2"></i> Keuntungan</a></li>
        <li class="nav-item"><a href="kelola_hero.php" class="nav-link"><i class="fas fa-image me-2"></i> Banner</a></li>
    </ul>
    <span class="nav-group-label">Laporan</span>
    <ul class="nav flex-column">
        <li class="nav-item"><a href="laporan.php" class="nav-link"><i class="fas fa-chart-line me-2"></i> Statistik Booking</a></li>
    </ul>
    <hr>
    <ul class="nav flex-column"><li class="nav-item"><a href="javascript:void(0)" class="nav-link text-danger" id="btnLogout"><i class="fas fa-power-off me-2"></i> Logout</a></li></ul>
</div>

<div class="sidebar-overlay" id="sidebarOverlay"></div>

<div class="main-content">
    <div class="navbar-admin d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-3">
            <button class="btn-toggle-sidebar" id="btnToggleSidebar"><i class="fas fa-bars"></i></button>
            <h5 class="mb-0 fw-bold">Ringkasan Panel</h5>
        </div>
        <div class="user-profile d-flex align-items-center">
            <span class="me-2 text-muted fw-bold">Halo, <?php echo $_SESSION['nama']; ?></span>
            <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['nama']); ?>&background=ff385c&color=fff" class="rounded-circle shadow-sm" width="38">
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-3 col-sm-6">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-0 small fw-bold text-uppercase">Total Pendapatan</p>
                        <h2 class="mb-0 text-success">Rp <?= number_format($total_pendapatan, 0, ',', '.'); ?></h2>
                    </div>
                    <div class="bg-success bg-opacity-10 p-3 rounded-circle text-success"><i class="fas fa-money-bill-wave fa-lg"></i></div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-0 small fw-bold text-uppercase">Penghuni Aktif</p>
                        <h2 class="mb-0 text-primary"><?= $total_penyewa; ?></h2>
                    </div>
                    <div class="bg-primary bg-opacity-10 p-3 rounded-circle text-primary">
                        <i class="fas fa-user-check fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-0 small fw-bold text-uppercase">Kamar Tersedia</p>
                        <h2 class="mb-0" style="color: var(--kinara-teal);"><?= $total_kamar; ?></h2>
                    </div>
                    <div class="bg-info bg-opacity-10 p-3 rounded-circle" style="color: var(--kinara-teal);"><i class="fas fa-door-open fa-lg"></i></div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-0 small fw-bold text-uppercase">Booking Baru</p>
                        <h2 class="mb-0 text-warning"><?= $total_booking; ?></h2>
                    </div>
                    <div class="bg-warning bg-opacity-10 p-3 rounded-circle text-warning">
                        <i class="fas fa-bell fa-lg <?= ($total_booking > 0) ? 'fa-shake' : ''; ?>"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-8">
            <div class="chart-card">
                <div class="chart-header d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-3 pb-2 border-bottom">
                    <h5 class="chart-title mb-2 mb-sm-0"><i class="fas fa-chart-bar text-primary me-2"></i> Grafik Aktivitas Bulanan</h5>
                    <select id="filterChartMode" class="form-select form-select-sm border-secondary shadow-none w-auto" onchange="updateChartMode()" style="cursor: pointer; font-weight: 600; font-size: 13px;">
                        <option value="pendapatan">💰 Pendapatan per Lokasi (Rp)</option>
                        <option value="penghuni">👥 Penghuni per Lokasi (Orang)</option>
                    </select>
                </div>
                <div class="chart-container">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="chart-card">
                <div class="chart-header pb-2 border-bottom">
                    <h5 class="chart-title mb-0" id="doughnutTitle"><i class="fas fa-chart-pie text-danger me-2"></i> Persentase Pendapatan</h5>
                </div>
                <div class="chart-container d-flex justify-content-center align-items-center mt-3">
                    <canvas id="roomChart" style="max-height: 250px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
        <div class="d-flex align-items-center mb-3 border-bottom pb-2">
            <i class="fas fa-chart-line text-primary fs-5 me-2"></i>
            <h5 class="fw-bold mb-0">Statistik Durasi Sewa Penghuni Aktif</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered text-center align-middle mb-0" style="border-radius: 10px; overflow: hidden;">
                <thead class="table-light">
                    <tr>
                        <th class="text-start text-uppercase" style="font-size: 13px; color: #666;">Lokasi Unit</th>
                        <th class="text-uppercase" style="font-size: 13px; color: #666;">Harian</th>
                        <th class="text-uppercase" style="font-size: 13px; color: #666;">1 Bulan</th>
                        <th class="text-uppercase" style="font-size: 13px; color: #666;">3 Bulan</th>
                        <th class="text-uppercase" style="font-size: 13px; color: #666;">6 Bulan</th>
                        <th class="text-uppercase" style="font-size: 13px; color: #666;">1 Tahun</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($lokasi_list as $lok): ?>
                    <tr>
                        <td class="text-start fw-bold text-dark" style="font-size: 14px;"><?= $lok; ?></td>
                        <td>
                            <span class="badge bg-<?= $stats_durasi[$lok]['harian'] > 0 ? 'info text-dark' : 'light text-muted' ?> px-3 py-2 rounded-pill">
                                <?= $stats_durasi[$lok]['harian']; ?> Orang
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-<?= $stats_durasi[$lok]['1_bln'] > 0 ? 'primary' : 'light text-muted' ?> px-3 py-2 rounded-pill">
                                <?= $stats_durasi[$lok]['1_bln']; ?> Orang
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-<?= $stats_durasi[$lok]['3_bln'] > 0 ? 'success' : 'light text-muted' ?> px-3 py-2 rounded-pill">
                                <?= $stats_durasi[$lok]['3_bln']; ?> Orang
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-<?= $stats_durasi[$lok]['6_bln'] > 0 ? 'warning text-dark' : 'light text-muted' ?> px-3 py-2 rounded-pill">
                                <?= $stats_durasi[$lok]['6_bln']; ?> Orang
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-<?= $stats_durasi[$lok]['12_bln'] > 0 ? 'danger' : 'light text-muted' ?> px-3 py-2 rounded-pill">
                                <?= $stats_durasi[$lok]['12_bln']; ?> Orang
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="table-card mb-4">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center mb-4 border-bottom pb-3 gap-3">
            <h5 class="fw-bold mb-0"><i class="fas fa-history text-muted me-2"></i> History Sewa</h5>
            <form action="" method="GET" class="d-flex align-items-center gap-2 m-0 flex-wrap">
                <label class="small fw-bold text-muted mb-0 d-none d-sm-block"><i class="fas fa-filter"></i> Filter:</label>
                <select name="filter_lokasi" class="form-select form-select-sm border-secondary shadow-none" onchange="this.form.submit()" style="cursor: pointer; width: auto; min-width: 140px;">
                    <option value="semua">Semua Lokasi</option>
                    <?php foreach($lokasi_list as $lok): ?>
                        <option value="<?= $lok; ?>" <?= ($filter_lokasi == $lok) ? 'selected' : ''; ?>><?= $lok; ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="filter_unit" class="form-select form-select-sm border-secondary shadow-none" onchange="this.form.submit()" style="cursor: pointer; width: auto; max-width: 180px;">
                    <option value="semua">Semua Unit</option>
                    <?php foreach($kamar_list as $kmr): ?>
                        <option value="<?= $kmr['id']; ?>" <?= ($filter_unit == $kmr['id']) ? 'selected' : ''; ?>>
                            <?= $kmr['nama_kamar']; ?> (<?= $kmr['lokasi']; ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <input type="month" name="filter_bulan" id="filter_bulan" class="form-control form-control-sm border-secondary shadow-none" value="<?= $filter_bulan; ?>" onchange="this.form.submit()" style="cursor: pointer; width: auto;">
            </form>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="text-uppercase" style="font-size:12px; color:#888;">Tgl Transaksi</th>
                        <th class="text-uppercase" style="font-size:12px; color:#888;">Nama Penyewa</th>
                        <th class="text-uppercase" style="font-size:12px; color:#888;">Unit Kamar</th>
                        <th class="text-uppercase" style="font-size:12px; color:#888;">Nominal</th>
                        <th class="text-uppercase" style="font-size:12px; color:#888;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(mysqli_num_rows($q_history) > 0): ?>
                        <?php while($row = mysqli_fetch_assoc($q_history)): ?>
                        <tr>
                            <td class="text-muted" style="font-size: 13px;">
                                <i class="far fa-calendar-alt text-primary me-1"></i>
                                <?= date('d M Y', strtotime($row['tgl_bayar'] ?? 'now')); ?>
                            </td>
                            <td class="fw-bold text-dark">
                                <?= $row['nama']; ?>
                                <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $row['kontak']); ?>" target="_blank" class="text-success ms-1"><i class="fab fa-whatsapp"></i></a>
                            </td>
                            <td>
                                <span class="fw-bold text-muted d-block"><?= $row['nama_kamar']; ?></span>
                                <small class="text-danger"><i class="fas fa-map-marker-alt"></i> <?= $row['lokasi']; ?></small>
                            </td>
                            <td class="fw-bold">Rp <?= number_format($row['jumlah_bayar'], 0, ',', '.'); ?></td>
                            <td>
                                <?php if($row['status_pembayaran'] == 'berhasil'): ?>
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success rounded-pill px-3">Berhasil</span>
                                <?php else: ?>
                                    <span class="badge bg-warning bg-opacity-10 text-warning border border-warning rounded-pill px-3">Pending</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center py-4 text-muted fw-bold">Tidak ada riwayat sewa untuk filter ini.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="table-card">
        <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
            <h5 class="fw-bold mb-0"><i class="fas fa-bolt text-warning me-2"></i> Transaksi Terbaru (Global)</h5>
            <a href="pembayaran.php" class="btn btn-sm btn-outline-danger fw-bold rounded-pill px-3">Lihat Semua</a>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="text-uppercase" style="font-size:12px; color:#888;">Nama Penyewa</th>
                        <th class="text-uppercase" style="font-size:12px; color:#888;">Kontak WA</th>
                        <th class="text-uppercase" style="font-size:12px; color:#888;">Unit Kamar</th>
                        <th class="text-uppercase" style="font-size:12px; color:#888;">Lokasi</th>
                        <th class="text-uppercase" style="font-size:12px; color:#888;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(mysqli_num_rows($q_terbaru) > 0): ?>
                        <?php while($row = mysqli_fetch_assoc($q_terbaru)): ?>
                        <tr>
                            <td class="fw-bold text-dark"><?= $row['nama']; ?></td>
                            <td><a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $row['kontak']); ?>" target="_blank" class="text-success text-decoration-none fw-bold"><i class="fab fa-whatsapp"></i> <?= $row['kontak']; ?></a></td>
                            <td class="text-muted fw-bold"><?= $row['nama_kamar']; ?></td>
                            <td class="text-muted" style="font-size: 13px;"><i class="fas fa-map-marker-alt text-danger"></i> <?= $row['lokasi']; ?></td>
                            <td>
                                <?php if($row['status_pembayaran'] == 'berhasil'): ?>
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success rounded-pill px-3">Lunas / Aktif</span>
                                <?php else: ?>
                                    <span class="badge bg-warning bg-opacity-10 text-warning border border-warning rounded-pill px-3">Pending</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center py-5 text-muted fw-bold">Belum ada transaksi booking masuk.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// ==========================================
// RENDER GRAFIK DENGAN CHART.JS (PINTAR & DINAMIS)
// ==========================================

const datasetsPendapatan = <?= json_encode($datasets_pendapatan); ?>;
const datasetsPenghuni = <?= json_encode($datasets_penghuni); ?>;

const dataOccPendapatan = <?= json_encode($data_okupansi_uang); ?>;
const dataOccPenghuni = <?= json_encode($data_okupansi_orang); ?>;
const labelOkupansiAsli = <?= json_encode($label_okupansi); ?>;
const warnaOkupansiAsli = <?= json_encode($warna_okupansi); ?>;

const sumPendapatan = dataOccPendapatan.reduce((a, b) => a + b, 0);
const sumPenghuni = dataOccPenghuni.reduce((a, b) => a + b, 0);

const ctxRev = document.getElementById('revenueChart').getContext('2d');
let mainChart = new Chart(ctxRev, {
    type: 'bar',
    data: {
        labels: <?= json_encode($label_bulan); ?>,
        datasets: datasetsPendapatan
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: true, position: 'top', labels: { usePointStyle: true, boxWidth: 8, font: {family: 'Inter', weight: 'bold'} } },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        const mode = document.getElementById('filterChartMode').value;
                        if(mode === 'pendapatan') {
                            return context.dataset.label + ': Rp ' + new Intl.NumberFormat('id-ID').format(context.raw);
                        } else {
                            return context.dataset.label + ': ' + context.raw + ' Orang';
                        }
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true, 
                grid: { borderDash: [5, 5], color: '#eee' }, 
                ticks: {
                    callback: function(value) {
                        const mode = document.getElementById('filterChartMode').value;
                        if(mode === 'pendapatan') return 'Rp ' + (value/1000000) + ' Jt';
                        return value + ' Orang';
                    },
                    font: {family: 'Inter'}
                }
            },
            x: { grid: { display: false }, ticks: { font: {family: 'Inter', weight: 'bold'} } }
        }
    }
});

const ctxRoom = document.getElementById('roomChart').getContext('2d');
let occChart = new Chart(ctxRoom, {
    type: 'doughnut',
    data: {
        labels: sumPendapatan === 0 ? ['Belum Ada Pendapatan'] : labelOkupansiAsli,
        datasets: [{ 
            data: sumPendapatan === 0 ? [1] : dataOccPendapatan, 
            backgroundColor: sumPendapatan === 0 ? ['#ecf0f1'] : warnaOkupansiAsli, 
            borderWidth: 2, 
            borderColor: '#fff', 
            hoverOffset: 6 
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false, cutout: '65%', 
        plugins: {
            legend: { position: 'bottom', labels: { usePointStyle: true, padding: 15, font: {family: 'Inter', weight: '600'} } },
            tooltip: { 
                callbacks: { 
                    label: function(context) { 
                        if (context.label === 'Belum Ada Pendapatan' || context.label === 'Kamar Kosong Semua') return ' Belum ada data';
                        const mode = document.getElementById('filterChartMode').value;
                        if(mode === 'pendapatan'){
                            return ' ' + context.label + ': Rp ' + new Intl.NumberFormat('id-ID').format(context.raw);
                        } else {
                            return ' ' + context.label + ': ' + context.raw + ' Kamar Terisi'; 
                        }
                    } 
                } 
            }
        }
    }
});

function updateChartMode() {
    const mode = document.getElementById('filterChartMode').value;
    
    if(mode === 'pendapatan') {
        mainChart.data.datasets = datasetsPendapatan;
        mainChart.options.scales.y.ticks.stepSize = undefined; 
        
        if(sumPendapatan === 0) {
            occChart.data.labels = ['Belum Ada Pendapatan'];
            occChart.data.datasets[0].data = [1];
            occChart.data.datasets[0].backgroundColor = ['#ecf0f1'];
        } else {
            occChart.data.labels = labelOkupansiAsli;
            occChart.data.datasets[0].data = dataOccPendapatan;
            occChart.data.datasets[0].backgroundColor = warnaOkupansiAsli;
        }
        document.getElementById('doughnutTitle').innerHTML = '<i class="fas fa-chart-pie text-danger me-2"></i> Persentase Pendapatan';
    } else {
        mainChart.data.datasets = datasetsPenghuni;
        mainChart.options.scales.y.ticks.stepSize = 2; 
        
        if(sumPenghuni === 0) {
            occChart.data.labels = ['Kamar Kosong Semua'];
            occChart.data.datasets[0].data = [1];
            occChart.data.datasets[0].backgroundColor = ['#ecf0f1'];
        } else {
            occChart.data.labels = labelOkupansiAsli;
            occChart.data.datasets[0].data = dataOccPenghuni;
            occChart.data.datasets[0].backgroundColor = warnaOkupansiAsli;
        }
        document.getElementById('doughnutTitle').innerHTML = '<i class="fas fa-users text-danger me-2"></i> Okupansi per Lokasi';
    }
    
    mainChart.update();
    occChart.update();
}

// ==========================================
// LOGIKA UMUM ADMIN (LOGOUT & SIDEBAR)
// ==========================================
document.getElementById('btnLogout')?.addEventListener('click', function() {
    Swal.fire({
        title: 'Keluar dari Sistem?', text: "Anda harus login kembali untuk mengelola data.", icon: 'question', showCancelButton: true, confirmButtonColor: '#ff385c', cancelButtonColor: '#6c757d', confirmButtonText: 'Ya, Keluar!', cancelButtonText: 'Batal'
    }).then((result) => { if (result.isConfirmed) window.location.href = 'logout.php'; });
});

const sidebar = document.querySelector('.sidebar');
const btnToggleSidebar = document.getElementById('btnToggleSidebar');
const sidebarOverlay = document.getElementById('sidebarOverlay');

if (btnToggleSidebar) {
    btnToggleSidebar.addEventListener('click', () => {
        sidebar.classList.add('show'); sidebarOverlay.classList.add('show'); document.body.style.overflow = 'hidden'; 
    });
}
if (sidebarOverlay) {
    sidebarOverlay.addEventListener('click', () => {
        sidebar.classList.remove('show'); sidebarOverlay.classList.remove('show'); document.body.style.overflow = 'auto'; 
    });
}
</script>
</body>
</html>
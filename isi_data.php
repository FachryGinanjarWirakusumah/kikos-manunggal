<?php
session_start();
include 'config.php';
$id_kamar = $_GET['id'];
$query = mysqli_query($conn, "SELECT * FROM kamar WHERE id = $id_kamar");
$k = mysqli_fetch_assoc($query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Isi Data Penyewa - Kinara</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f7f7f7; padding: 50px 0; }
        .form-card { background: white; border-radius: 20px; padding: 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
        .btn-primary { background: #ff385c; border: none; padding: 12px; border-radius: 10px; }
    </style>
</head>
<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="form-card">
                <h4 class="fw-bold mb-4">Lengkapi Data Pemesanan</h4>
                <p class="text-muted small">Unit: <strong><?= $k['nama_kamar']; ?></strong></p>
                
                <form action="bayar.php" method="POST">
                    <input type="hidden" name="id_kamar" value="<?= $id_kamar; ?>">
                    
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Nama Lengkap</label>
                        <input type="text" name="nama" class="form-control" placeholder="Sesuai KTP" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Nomor WhatsApp</label>
                        <input type="number" name="kontak" class="form-control" placeholder="Contoh: 0812345xxx" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-bold">Durasi Sewa</label>
                        <select name="durasi" class="form-select">
                            <option value="1">1 Bulan</option>
                            <option value="3">3 Bulan</option>
                            <option value="6">6 Bulan</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 fw-bold">Lanjut ke Pembayaran</button>
                </form>
            </div>
        </div>
    </div>
</div>
</body>
</html>
<?php 
include 'config.php';

// Validasi ID dari URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("ID kamar tidak ditemukan");
}

$id = intval($_GET['id']);

// Ambil data kamar
$stmt = $conn->prepare("SELECT * FROM kamar WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Kamar tidak ditemukan");
}

$kamar = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Kamar</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">

    <div class="row justify-content-center">
        <div class="col-md-6">

            <div class="card shadow">
                <div class="card-body">

                    <h4 class="mb-3 text-center">
                        Booking <?= htmlspecialchars($kamar['nama_kamar']); ?>
                    </h4>

                    <p class="text-center">
                        Harga: <strong>Rp<?= number_format($kamar['harga']); ?></strong>
                    </p>

                    <!-- Form Booking -->
                    <form action="process_booking.php" method="POST">

                        <input type="hidden" name="kamar_id" value="<?= $kamar['id']; ?>">

                        <div class="mb-3">
                            <label class="form-label">Nama Pelanggan</label>
                            <input type="text" name="nama" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">No HP</label>
                            <input type="text" name="hp" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Tanggal Check-in</label>
                            <input type="date" name="check_in" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Tanggal Check-out</label>
                            <input type="date" name="check_out" class="form-control" required>
                        </div>

                        <button type="submit" class="btn btn-success w-100">
                            Booking Sekarang
                        </button>

                        <a href="index.php" class="btn btn-secondary w-100 mt-2">
                            Kembali
                        </a>

                    </form>

                </div>
            </div>

        </div>
    </div>

</div>

</body>
</html>
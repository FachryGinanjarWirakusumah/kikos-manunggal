<?php include 'config.php'; ?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
    <h2>Data Booking</h2>

    <table class="table table-bordered">
        <tr>
            <th>Nama</th>
            <th>No HP</th>
            <th>Kamar</th>
            <th>Tanggal</th>
        </tr>

        <?php
        $query = "SELECT booking.*, kamar.nama_kamar 
                  FROM booking 
                  JOIN kamar ON booking.kamar_id = kamar.id";

        $result = $conn->query($query);

        while ($row = $result->fetch_assoc()):
        ?>

        <tr>
            <td><?= $row['nama_pelanggan']; ?></td>
            <td><?= $row['no_hp']; ?></td>
            <td><?= $row['nama_kamar']; ?></td>
            <td><?= $row['check_in']; ?> - <?= $row['check_out']; ?></td>
        </tr>

        <?php endwhile; ?>
    </table>
</div>

</body>
</html>
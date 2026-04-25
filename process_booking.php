<?php
include 'config.php';

$nama      = $_POST['nama'];
$hp        = $_POST['hp'];
$kamar_id  = $_POST['kamar_id'];
$check_in  = $_POST['check_in'];
$check_out = $_POST['check_out'];


// VALIDASI
if (empty($nama) || empty($hp) || empty($check_in) || empty($check_out)) {
    die("Semua field wajib diisi");
}

if ($check_out <= $check_in) {
    die("Tanggal tidak valid");
}


// CEK DOUBLE BOOKING (INI BAGIAN PALING PENTING)
$query = "SELECT * FROM booking 
          WHERE kamar_id = ?
          AND check_in < ?
          AND check_out > ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("iss", $kamar_id, $check_out, $check_in);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    die("Kamar sudah dibooking di tanggal tersebut!");
}


// INSERT DATA
$stmt = $conn->prepare("INSERT INTO booking 
(nama_pelanggan, no_hp, kamar_id, check_in, check_out)
VALUES (?, ?, ?, ?, ?)");

$stmt->bind_param("ssiss", $nama, $hp, $kamar_id, $check_in, $check_out);
$stmt->execute();

header("Location: index.php?success=1");
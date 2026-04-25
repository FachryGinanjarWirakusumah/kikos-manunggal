<?php
include 'config.php';
$id_kamar = (int)$_GET['id_kamar'];
$res = mysqli_query($conn, "SELECT id, file_name, tipe_file FROM galeri_kamar WHERE id_kamar = $id_kamar");
$data = [];
while($row = mysqli_fetch_assoc($res)) {
    $data[] = $row;
}
echo json_encode($data);
?>
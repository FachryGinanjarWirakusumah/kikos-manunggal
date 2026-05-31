<?php
// Legacy entry — forward to new location.
header('Location: admin/kamar/ambil_galeri.php?id_kamar=' . (isset($_GET['id_kamar']) ? (int)$_GET['id_kamar'] : ''));
exit;
?>
<?php
$id = $_GET['id'];
$galeri = mysqli_query($conn, "SELECT * FROM galeri_kamar WHERE id_kamar = $id");
?>

<div class="main-slider">
    <?php while($g = mysqli_fetch_assoc($galeri)): ?>
        <?php if($g['tipe_file'] == 'foto'): ?>
            <img src="img/galeri/<?= $g['file_name']; ?>" class="img-fluid rounded">
        <?php else: ?>
            <video width="100%" controls class="rounded">
                <source src="img/galeri/<?= $g['file_name']; ?>" type="video/mp4">
            </video>
        <?php endif; ?>
    <?php endwhile; ?>
</div>
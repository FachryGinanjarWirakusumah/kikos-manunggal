<?php
session_start();
include '../../config.php';

// Proteksi Admin
if (!isset($_SESSION['login']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') { 
    header("Location: ../index.php");
    exit; 
}

// 1. FUNGSI HAPUS SATU FILE GALERI VIA AJAX
if (isset($_GET['hapus_galeri'])) {
    $id = (int)$_GET['hapus_galeri'];
    $cek = mysqli_query($conn, "SELECT file_name FROM galeri_kamar WHERE id = $id");
    $data = mysqli_fetch_assoc($cek);
    
    if ($data && file_exists("../../img/galeri/" . $data['file_name'])) {
        unlink("../../img/galeri/" . $data['file_name']);
    }
    
    mysqli_query($conn, "DELETE FROM galeri_kamar WHERE id = $id");
    echo json_encode(['status' => 'success']);
    exit;
}

// 2. LOGIKA TAMBAH KAMAR
if (isset($_POST['tambah_kamar'])) {
    $nama   = mysqli_real_escape_string($conn, $_POST['nama_kamar']);
    $lokasi = mysqli_real_escape_string($conn, $_POST['lokasi']);
    $harga  = (int)$_POST['harga'];
    $tipe   = $_POST['tipe'];
    $status = $_POST['status'];
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);

    $query = "INSERT INTO kamar (nama_kamar, lokasi, harga, tipe, status, deskripsi, gambar) 
              VALUES ('$nama', '$lokasi', '$harga', '$tipe', '$status', '$deskripsi', 'default.jpg')";
    
    if (mysqli_query($conn, $query)) {
        $id_kamar_baru = mysqli_insert_id($conn);

        if (!empty($_FILES['galeri']['name'][0])) {
            if (!is_dir("../../img/galeri")) { mkdir("../../img/galeri", 0777, true); }

            foreach ($_FILES['galeri']['name'] as $key => $val) {
                if ($val == "" || $_FILES['galeri']['error'][$key] !== 0) continue; 

                $file_tmp  = $_FILES['galeri']['tmp_name'][$key];
                $file_orig = $val;
                $file_name = time() . '_' . rand(100,999) . '_' . preg_replace("/[^a-zA-Z0-9.]/", "_", $file_orig);
                $ext = strtolower(pathinfo($file_orig, PATHINFO_EXTENSION));
                $tipe_file = in_array($ext, ['mp4', 'mov', 'm4v']) ? 'video' : 'foto';

                $target_galeri = "../../img/galeri/" . $file_name;

                if (move_uploaded_file($file_tmp, $target_galeri)) {
                    mysqli_query($conn, "INSERT INTO galeri_kamar (id_kamar, file_name, tipe_file) 
                                         VALUES ('$id_kamar_baru', '$file_name', '$tipe_file')");
                    if ($key === 0) {
                        copy($target_galeri, "../../img/" . $file_name);
                        mysqli_query($conn, "UPDATE kamar SET gambar = '$file_name' WHERE id = $id_kamar_baru");
                    }
                }
            }
        }
        header("Location: kelola_kamar.php?msg=success_add");
    }
    exit;
}

// 3. LOGIKA EDIT KAMAR
if(isset($_POST['edit_kamar'])) {
    $id = (int)$_POST['id_kamar'];
    $nama = mysqli_real_escape_string($conn, $_POST['nama_kamar']);
    $lokasi = mysqli_real_escape_string($conn, $_POST['lokasi']);
    $harga = (int)$_POST['harga'];
    $tipe = $_POST['tipe'];
    $status = $_POST['status'];
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);

    mysqli_query($conn, "UPDATE kamar SET 
        nama_kamar='$nama', 
        lokasi='$lokasi', 
        harga='$harga', 
        tipe='$tipe', 
        status='$status',
        deskripsi='$deskripsi' 
        WHERE id=$id");

    if (!empty($_FILES['galeri']['name'][0])) {
        foreach ($_FILES['galeri']['name'] as $key => $val) {
            if ($val == "" || $_FILES['galeri']['error'][$key] !== 0) continue;

            $file_name = time() . '_' . rand(100,999) . '_' . preg_replace("/[^a-zA-Z0-9.]/", "_", $val);
            $file_tmp  = $_FILES['galeri']['tmp_name'][$key];
            $ext = strtolower(pathinfo($val, PATHINFO_EXTENSION));
            $tipe_file = in_array($ext, ['mp4', 'm4v', 'mov']) ? 'video' : 'foto';
            $target = "../../img/galeri/" . $file_name;

            if (move_uploaded_file($file_tmp, $target)) {
                mysqli_query($conn, "INSERT INTO galeri_kamar (id_kamar, file_name, tipe_file) 
                                     VALUES ('$id', '$file_name', '$tipe_file')");
                if ($key === 0) {
                    copy($target, "../../img/" . $file_name);
                    mysqli_query($conn, "UPDATE kamar SET gambar='$file_name' WHERE id=$id");
                }
            }
        }
    }
    header("Location: kelola_kamar.php?msg=success_update");
    exit;
}

// 4. FUNGSI GANTI SAMPUL UTAMA
if (isset($_GET['set_sampul'])) {
    $id_kamar = (int)$_GET['id_kamar'];
    $file_baru = mysqli_real_escape_string($conn, $_GET['file']);
    
    if (file_exists("../../img/galeri/" . $file_baru)) {
        copy("../../img/galeri/" . $file_baru, "../../img/" . $file_baru);
        mysqli_query($conn, "UPDATE kamar SET gambar = '$file_baru' WHERE id = $id_kamar");
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'msg' => 'File tidak ditemukan']);
    }
    exit;
}

// 5. LOGIKA HAPUS KAMAR
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];

    $q_galeri = mysqli_query($conn, "SELECT file_name FROM galeri_kamar WHERE id_kamar = $id");
    while ($g = mysqli_fetch_assoc($q_galeri)) {
        if (file_exists("../../img/galeri/" . $g['file_name'])) {
            unlink("../../img/galeri/" . $g['file_name']);
        }
    }

    $cek = mysqli_query($conn, "SELECT gambar FROM kamar WHERE id = $id");
    $data = mysqli_fetch_assoc($cek);
    if ($data['gambar'] != 'default.jpg' && file_exists("../../img/" . $data['gambar'])) {
        unlink("../../img/" . $data['gambar']);
    }

    if (mysqli_query($conn, "DELETE FROM kamar WHERE id = $id")) {
        header("Location: kelola_kamar.php?msg=success_delete");
    }
    exit;
}
?>

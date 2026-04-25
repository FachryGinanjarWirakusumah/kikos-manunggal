<?php
session_start();
include 'config.php';

// Proteksi Admin
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') { 
    exit; 
}

// 1. FUNGSI HAPUS SATU FILE GALERI VIA AJAX
if (isset($_GET['hapus_galeri'])) {
    $id = (int)$_GET['hapus_galeri'];
    $cek = mysqli_query($conn, "SELECT file_name FROM galeri_kamar WHERE id = $id");
    $data = mysqli_fetch_assoc($cek);
    
    if ($data && file_exists("img/galeri/" . $data['file_name'])) {
        unlink("img/galeri/" . $data['file_name']);
    }
    
    mysqli_query($conn, "DELETE FROM galeri_kamar WHERE id = $id");
    echo json_encode(['status' => 'success']);
    exit;
}

// 2. LOGIKA TAMBAH KAMAR
// --- LOGIKA TAMBAH KAMAR ---
if (isset($_POST['tambah_kamar'])) {
    $nama   = mysqli_real_escape_string($conn, $_POST['nama_kamar']);
    $lokasi = mysqli_real_escape_string($conn, $_POST['lokasi']);
    $harga  = (int)$_POST['harga'];
    $tipe   = $_POST['tipe'];
    $status = $_POST['status'];

    // 1. Tentukan Nama File Cover (Ambil dari file pertama)
    $cover_db = "default.jpg";
    if (!empty($_FILES['galeri']['name'][0])) {
        $cover_db = time() . '_cover_' . $_FILES['galeri']['name'][0];
    }

    // 2. Simpan Data Utama Kamar dulu
    $query = "INSERT INTO kamar (nama_kamar, lokasi, harga, tipe, status, gambar) 
              VALUES ('$nama', '$lokasi', '$harga', '$tipe', '$status', '$cover_db')";
    
    if (mysqli_query($conn, $query)) {
        $id_kamar_baru = mysqli_insert_id($conn);

        // 3. Proses Upload SEMUA file (Termasuk file pertama)
        if (!empty($_FILES['galeri']['name'][0])) {
            if (!is_dir("img/galeri")) { mkdir("img/galeri", 0777, true); }

            foreach ($_FILES['galeri']['name'] as $key => $val) {
                if ($_FILES['galeri']['name'][$key] == "") continue;

                $file_tmp  = $_FILES['galeri']['tmp_name'][$key];
                $file_orig = $_FILES['galeri']['name'][$key];
                $file_name = time() . '_' . $key . '_' . $file_orig;
                
                // Tentukan tipe file
                $ext = strtolower(pathinfo($file_orig, PATHINFO_EXTENSION));
                $tipe_file = in_array($ext, ['mp4', 'mov']) ? 'video' : 'foto';

                // Path penyimpanan galeri
                $target_galeri = "img/galeri/" . $file_name;

                if (move_uploaded_file($file_tmp, $target_galeri)) {
                    // Masukkan ke tabel galeri
                    mysqli_query($conn, "INSERT INTO galeri_kamar (id_kamar, file_name, tipe_file) 
                                        VALUES ('$id_kamar_baru', '$file_name', '$tipe_file')");
                    
                    // KHUSUS FILE PERTAMA: Copy juga ke folder img/ untuk Cover
                    if ($key === 0) {
                        copy($target_galeri, "img/" . $cover_db);
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
    $id = $_POST['id_kamar'];
    $nama = $_POST['nama_kamar'];
    $lokasi = $_POST['lokasi'];
    $harga = $_POST['harga'];
    $tipe = $_POST['tipe'];
    $status = $_POST['status'];
    $deskripsi = $_POST['deskripsi']; // Tangkap deskripsi di sini

    mysqli_query($conn, "UPDATE kamar SET 
        nama_kamar='$nama', 
        lokasi='$lokasi', 
        harga='$harga', 
        tipe='$tipe', 
        status='$status',
        deskripsi='$deskripsi' 
        WHERE id=$id");
    
    header("Location: kelola_kamar.php?msg=success_update");

    // Jika admin upload file galeri baru saat Edit
    if (!empty($_FILES['galeri']['name'][0])) {
        foreach ($_FILES['galeri']['name'] as $key => $val) {
            if ($val == "") continue;

            $file_name = time() . '_' . $key . '_' . $val;

            // JIKA FILE PERTAMA ([0]), JADIKAN COVER UTAMA (Simpan di img/)
            if ($key === 0) {
                if (move_uploaded_file($_FILES['galeri']['tmp_name'][$key], "img/" . $file_name)) {
                    mysqli_query($conn, "UPDATE kamar SET gambar='$file_name' WHERE id=$id");
                    
                    // Juga masukkan ke galeri agar muncul di detail nanti
                    mysqli_query($conn, "INSERT INTO galeri_kamar (id_kamar, file_name, tipe_file) 
                                        VALUES ('$id', '$file_name', 'foto')");
                }
            } else {
                // File berikutnya simpan di img/galeri/
                $target = "img/galeri/" . $file_name;
                if (move_uploaded_file($_FILES['galeri']['tmp_name'][$key], $target)) {
                    $ext = strtolower(pathinfo($val, PATHINFO_EXTENSION));
                    $tipe_file = in_array($ext, ['mp4', 'm4v', 'mov']) ? 'video' : 'foto';
                    mysqli_query($conn, "INSERT INTO galeri_kamar (id_kamar, file_name, tipe_file) 
                                        VALUES ('$id', '$file_name', '$tipe_file')");
                }
            }
        }
    }
    header("Location: kelola_kamar.php?msg=success_update");
    exit;
}

// FUNGSI GANTI SAMPUL UTAMA
if (isset($_GET['set_sampul'])) {
    $id_kamar = (int)$_GET['id_kamar'];
    $file_baru = mysqli_real_escape_string($conn, $_GET['file']);
    
    // 1. Copy file dari folder galeri ke folder img utama
    if (file_exists("img/galeri/" . $file_baru)) {
        copy("img/galeri/" . $file_baru, "img/" . $file_baru);
        
        // 2. Update tabel kamar kolom gambar
        mysqli_query($conn, "UPDATE kamar SET gambar = '$file_baru' WHERE id = $id_kamar");
        
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'msg' => 'File tidak ditemukan']);
    }
    exit;
}

// --- LOGIKA HAPUS KAMAR ---
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];

    // 1. Hapus semua file di Galeri (Foto & Video)
    $q_galeri = mysqli_query($conn, "SELECT file_name FROM galeri_kamar WHERE id_kamar = $id");
    while ($g = mysqli_fetch_assoc($q_galeri)) {
        if (file_exists("img/galeri/" . $g['file_name'])) {
            unlink("img/galeri/" . $g['file_name']);
        }
    }

    // 2. Hapus Cover Utama
    $cek = mysqli_query($conn, "SELECT gambar FROM kamar WHERE id = $id");
    $data = mysqli_fetch_assoc($cek);
    if ($data['gambar'] != 'default.jpg' && file_exists("img/" . $data['gambar'])) {
        unlink("img/" . $data['gambar']);
    }

    // 3. Hapus data dari Database (Tabel galeri terhapus otomatis karena ON DELETE CASCADE)
    if (mysqli_query($conn, "DELETE FROM kamar WHERE id = $id")) {
        header("Location: kelola_kamar.php?msg=success_delete");
    }
    exit;
}
<?php
session_start();
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];
    
    // Ambil instruksi redirect jika ada (dari input hidden di modal login)
    $redirect_to = isset($_POST['redirect_to']) ? $_POST['redirect_to'] : '';

    // Cari user berdasarkan email atau kontak
    $query  = "SELECT * FROM users WHERE kontak = '$username'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) === 1) {
        $row = mysqli_fetch_assoc($result);

        // Verifikasi password yang di-hash
        if (password_verify($password, $row['password'])) {
            // Set session user
            $_SESSION['login'] = true;
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['nama'] = $row['nama'];
            $_SESSION['role'] = $row['role'];

            // --- LOGIKA PENGALIHAN CERDAS ---
            
            // 1. Jika ada instruksi redirect khusus (misal dari tombol booking)
            if (!empty($redirect_to)) {
                header("Location: " . $redirect_to);
                exit;
            }

            // 2. Jika tidak ada redirect, cek role
            if ($row['role'] === 'admin') {
                header("Location: admin_dashboard.php");
            } else {
                header("Location: index.php");
            }
            exit;
        }
    }

    // Jika gagal
    echo "<script>
            alert('Username atau Password salah!');
            window.location.href = 'cek_kamar.php';
          </script>";
}
?>
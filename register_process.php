<?php
include 'config.php'; // Pastikan pakai config.php sesuai info kamu tadi

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama     = mysqli_real_escape_string($conn, $_POST['nama']);
    $kontak   = mysqli_real_escape_string($conn, $_POST['kontak']);
    $password = $_POST['password'];

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $cek_user = mysqli_query($conn, "SELECT * FROM users WHERE kontak = '$kontak'");
    
    // Head untuk memanggil library SweetAlert agar muncul di halaman ini
    echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
    // Tambahkan font Inter agar konsisten dengan web kamu
    echo "<link href='https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap' rel='stylesheet'>
          <style>*{font-family: 'Inter', sans-serif;}</style>";

    if (mysqli_num_rows($cek_user) > 0) {
        echo "<script>
            window.onload = function() {
                Swal.fire({
                    title: 'Waduh!',
                    text: 'Email atau No HP sudah terdaftar.',
                    icon: 'error',
                    confirmButtonColor: '#ff385c'
                }).then(() => {
                    window.location.href = 'index.php';
                });
            };
        </script>";
    } else {
        $query = "INSERT INTO users (nama, kontak, password, role) VALUES ('$nama', '$kontak', '$hashed_password', 'user')";
        
        if (mysqli_query($conn, $query)) {
            echo "<script>
                window.onload = function() {
                    Swal.fire({
                        title: 'Berhasil!',
                        text: 'Akun kamu sudah terdaftar sebagai user.',
                        icon: 'success',
                        confirmButtonColor: '#ff385c',
                        confirmButtonText: 'Masuk Sekarang'
                    }).then((result) => {
                        window.location.href = 'index.php';
                    });
                };
            </script>";
        } else {
            echo "Error: " . mysqli_error($conn);
        }
    }
}
?>
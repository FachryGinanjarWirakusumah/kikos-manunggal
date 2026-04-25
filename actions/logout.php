<?php
session_start(); // Memulai sesi

// Menghapus semua variabel sesi
$_SESSION = array();

// Jika ingin menghapus cookie sesi juga (opsional tapi disarankan)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Menghancurkan sesi sepenuhnya
session_destroy();

// Alihkan pengguna ke halaman utama atau login
header("Location: index.php");
exit;
?>
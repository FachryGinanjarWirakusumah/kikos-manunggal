<?php
require_once dirname(__FILE__) . '/Midtrans/Midtrans.php';
include 'config.php';

\Midtrans\Config::$serverKey = 'SB-Mid-server-LU';
\Midtrans\Config::$isProduction = false;

$notif = new \Midtrans\Notification();

$transaction = $notif->transaction_status;
$order_id = $notif->order_id;

if ($transaction == 'settlement') {
    // JIKA BAYAR BERHASIL, UPDATE TABEL KAMU
    mysqli_query($conn, "UPDATE pembayaran SET status = 'Berhasil' WHERE order_id = '$order_id'");
} else if ($transaction == 'pending') {
    mysqli_query($conn, "UPDATE pembayaran SET status = 'Pending' WHERE order_id = '$order_id'");
} else if ($transaction == 'expire' || $transaction == 'cancel') {
    mysqli_query($conn, "UPDATE pembayaran SET status = 'Gagal' WHERE order_id = '$order_id'");
}
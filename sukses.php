<?php
session_start();
include 'config.php';

// Cek apakah ada order_id yang dilempar
if (!isset($_GET['order_id']) || empty($_GET['order_id'])) {
    header("Location: index.php");
    exit;
}

$order_id = mysqli_real_escape_string($conn, $_GET['order_id']);

// Ambil data detail pesanan dari database
$query = mysqli_query($conn, "SELECT p.*, k.nama_kamar, k.lokasi, k.gambar, u.nama, u.kontak 
                              FROM pembayaran p 
                              JOIN kamar k ON p.id_kamar = k.id 
                              JOIN users u ON p.id_user = u.id 
                              WHERE p.order_id = '$order_id'");

$data = mysqli_fetch_assoc($query);

// Jika data tidak ditemukan
if (!$data) {
    header("Location: index.php");
    exit;
}

// Logika pintar penangkal error tanggal
$waktu_struk = isset($data['tanggal_bayar']) ? $data['tanggal_bayar'] : (isset($data['tanggal']) ? $data['tanggal'] : date('Y-m-d H:i:s'));
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Pembayaran - Kinara Kost</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

    <style>
        :root { --kinara-pink: #ff385c; --kinara-teal: #1abc9c; --bg-gray: #f4f7f6; }
        body { font-family: 'Inter', sans-serif; background-color: var(--bg-gray); margin: 0; padding: 40px 20px; display: flex; flex-direction: column; align-items: center; }
        
        .success-container { width: 100%; max-width: 600px; }
        
        /* AREA TOMBOL (TIDAK MASUK PDF) */
        .action-buttons { display: flex; flex-direction: column; gap: 12px; margin-top: 20px; }
        .btn-download { display: flex; align-items: center; justify-content: center; gap: 10px; width: 100%; background: var(--kinara-pink); color: white; text-decoration: none; padding: 16px; border-radius: 12px; font-weight: 700; font-size: 16px; border: none; cursor: pointer; transition: 0.3s; box-shadow: 0 4px 15px rgba(255, 56, 92, 0.2); }
        .btn-download:hover { background: #e31c5f; transform: translateY(-2px); box-shadow: 0 8px 20px rgba(255, 56, 92, 0.3); }
        
        .btn-home { display: flex; align-items: center; justify-content: center; gap: 10px; width: 100%; background: #fff; color: #222; border: 1.5px solid #ddd; text-decoration: none; padding: 15px; border-radius: 12px; font-weight: 700; font-size: 16px; cursor: pointer; transition: 0.3s; box-sizing: border-box;}
        .btn-home:hover { background: #f9f9f9; border-color: #ccc; }
    </style>
</head>
<body>

<div class="success-container">
    
    <div id="area-struk-pdf" style="background-color: #ffffff; padding: 40px; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); color: #222; font-family: 'Inter', Helvetica, Arial, sans-serif; position: relative;">
        
        <div style="text-align: center; border-bottom: 2px dashed #eeeeee; padding-bottom: 25px; margin-bottom: 25px;">
            <div style="width: 60px; height: 60px; background-color: rgba(26, 188, 156, 0.1); border-radius: 50%; margin: 0 auto 15px; text-align: center; line-height: 60px;">
                <i class="fas fa-check" style="font-size: 30px; color: #1abc9c;"></i>
            </div>
            <h1 class="translatable" data-en="KINARA KOST" style="margin: 0; font-size: 28px; font-weight: 800; color: #ff385c; letter-spacing: -1px;">KINARA KOST</h1>
            <p class="translatable" data-en="OFFICIAL PAYMENT RECEIPT" style="margin: 5px 0 0; font-size: 12px; color: #888888; font-weight: 700; letter-spacing: 2px;">BUKTI PEMBAYARAN SAH</p>
        </div>

        <table style="width: 100%; margin-bottom: 30px; font-size: 14px; border-collapse: collapse;">
            <tr>
                <td style="vertical-align: top; width: 50%;">
                    <p class="translatable" data-en="BILLED TO:" style="margin: 0 0 5px; color: #888888; font-size: 11px; font-weight: 700;">DIBAYAR OLEH:</p>
                    <p style="margin: 0 0 4px; font-weight: 700; font-size: 16px; color: #222;"><?= htmlspecialchars($data['nama']); ?></p>
                    <p style="margin: 0; color: #555555;"><?= htmlspecialchars($data['kontak']); ?></p>
                </td>
                <td style="vertical-align: top; width: 50%; text-align: right;">
                    <p style="margin: 0 0 5px; color: #888888; font-size: 11px; font-weight: 700;">ORDER ID:</p>
                    <p style="margin: 0 0 10px; font-weight: 700; color: #222;"><?= $data['order_id']; ?></p>
                    <p class="translatable" data-en="DATE:" style="margin: 0 0 2px; color: #888888; font-size: 11px; font-weight: 700;">TANGGAL:</p>
                    <p style="margin: 0; color: #555555;"><?= date('d F Y, H:i', strtotime($waktu_struk)); ?> WIB</p>
                </td>
            </tr>
        </table>

        <div style="background-color: #fafafa; border: 1px solid #eeeeee; border-radius: 12px; padding: 20px;">
            <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
                <tr>
                    <td style="padding-bottom: 15px; border-bottom: 1px solid #eeeeee; width: 70px;">
                        <img src="img/<?= $data['gambar']; ?>" crossorigin="anonymous" style="width: 60px; height: 60px; border-radius: 8px; object-fit: cover;">
                    </td>
                    <td style="padding-bottom: 15px; border-bottom: 1px solid #eeeeee; padding-left: 15px; vertical-align: middle;">
                        <h4 style="margin: 0 0 5px; font-size: 16px; color: #222222;"><?= $data['nama_kamar']; ?></h4>
                        <p style="margin: 0; color: #666666; font-size: 12px;"><?= $data['lokasi']; ?></p>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" style="padding-top: 15px;">
                        <table style="width: 100%; border-collapse: collapse;">
                            <tr>
                                <td class="translatable" data-en="Payment Status" style="padding: 5px 0; color: #555555;">Status Pembayaran</td>
                                <td style="padding: 5px 0; text-align: right; color: #1abc9c; font-weight: 700;">BERHASIL</td>
                            </tr>
                            <tr>
                                <td class="translatable" data-en="Payment Method" style="padding: 5px 0; color: #555555;">Metode</td>
                                <td style="padding: 5px 0; text-align: right; color: #222222; font-weight: 600;">Sistem Terenkripsi</td>
                            </tr>
                            <tr>
                                <td class="translatable" data-en="TOTAL" style="padding: 15px 0 5px; border-top: 1px dashed #dddddd; font-weight: 800; font-size: 18px; color: #222222; margin-top: 10px;">TOTAL BAYAR</td>
                                <td style="padding: 15px 0 5px; border-top: 1px dashed #dddddd; text-align: right; font-weight: 800; font-size: 22px; color: #ff385c; margin-top: 10px;">
                                    <?= $data['jumlah_bayar'] <= 0 ? 'FREE' : 'Rp ' . number_format($data['jumlah_bayar'], 0, ',', '.'); ?>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>

        <div style="text-align: center; margin-top: 30px; font-size: 11px; color: #999999;">
            <p style="margin: 0 0 3px;">Dokumen ini adalah bukti pembayaran yang sah.</p>
            <p style="margin: 0;">Dicetak otomatis oleh Sistem Kinara Kost.</p>
        </div>
    </div>
    <div class="action-buttons">
        <button id="btnDownloadPDF" class="btn-download translatable" data-en="Download Receipt (PDF)">
            <i class="fas fa-file-pdf"></i> Download Bukti (PDF)
        </button>
        <a href="index.php" class="btn-home translatable" data-en="Return to Home">
            <i class="fas fa-home"></i> Kembali ke Beranda
        </a>
    </div>

</div>

<script>
// 1. Logika Download PDF (Anti-Terpotong)
document.getElementById('btnDownloadPDF').addEventListener('click', function() {
    const elemenStruk = document.getElementById('area-struk-pdf');
    const namaFile = "Invoice_Kinara_<?= $data['order_id']; ?>.pdf";
    
    // Ubah status tombol saat loading
    const tombolAsli = this.innerHTML;
    this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses PDF...';
    this.disabled = true;

    // --- TRIK ANTI TERPOTONG ---
    // Simpan gaya asli body
    const styleBodyAsli = document.body.style.cssText;
    
    // Matikan efek tengah (flex) sementara agar elemen merapat ke kiri
    document.body.style.display = "block"; 
    document.body.style.padding = "20px";
    
    // Bersihkan box-shadow agar tidak aneh di PDF
    elemenStruk.style.boxShadow = 'none';
    elemenStruk.style.borderRadius = '0px';
    // ---------------------------

    const opsi = {
        margin:       0.3, // Margin kertas
        filename:     namaFile,
        image:        { type: 'jpeg', quality: 1 },
        html2canvas:  { 
            scale: 2, 
            useCORS: true,
            scrollX: 0,
            scrollY: 0
        },
        jsPDF:        { unit: 'in', format: 'a5', orientation: 'portrait' }
    };

    // Eksekusi Render
    html2pdf().set(opsi).from(elemenStruk).save().then(() => {
        // --- KEMBALIKAN TAMPILAN SEPERTI SEMULA ---
        document.body.style.cssText = styleBodyAsli;
        elemenStruk.style.boxShadow = '0 10px 30px rgba(0,0,0,0.05)';
        elemenStruk.style.borderRadius = '16px';
        
        this.innerHTML = tombolAsli;
        this.disabled = false;
    });
});

// 2. Logika Bilingual (Bahasa)
document.addEventListener('DOMContentLoaded', () => {
    const savedLang = localStorage.getItem('kinara_lang') || 'id';
    if (savedLang === 'en') {
        document.querySelectorAll('.translatable').forEach(el => {
            if(el.getAttribute('data-en')) {
                el.innerText = el.getAttribute('data-en');
            }
        });
        
        // Manual override untuk tombol karena mengandung icon
        document.getElementById('btnDownloadPDF').innerHTML = '<i class="fas fa-file-pdf"></i> Download Receipt (PDF)';
        document.querySelector('.btn-home').innerHTML = '<i class="fas fa-home"></i> Return to Home';
    }
});
</script>
</body>
</html>
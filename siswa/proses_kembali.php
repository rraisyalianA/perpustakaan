<?php
session_start();
include '../config/koneksi.php';

if (isset($_POST['request_kembali'])) {
    $id_transaksi = $_POST['id_transaksi'];
    
    // Ubah status jadi pending_return (menunggu admin verifikasi buku fisik)
    $query = "UPDATE transaksi SET status = 'pending_return' WHERE id = '$id_transaksi'";
    
    if (mysqli_query($conn, $query)) {
        echo "<script>alert('Berhasil! Silakan serahkan buku ke petugas admin.'); window.location='riwayat.php';</script>";
    } else {
        echo "<script>alert('Gagal memproses.'); window.location='riwayat.php';</script>";
    }
}
?>
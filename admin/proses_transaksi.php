<?php
session_start();
include '../config/koneksi.php';

// Pastikan cuma admin yang bisa akses
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

if (isset($_GET['id']) && isset($_GET['aksi'])) {
    $id_transaksi = $_GET['id'];
    $aksi = $_GET['aksi'];
    $tgl_sekarang = date('Y-m-d');

    if ($aksi == 'approve_pinjam') {
        // 1. Ambil data transaksi dulu buat tahu book_id-nya
        $get_transaksi = mysqli_query($conn, "SELECT * FROM transaksi WHERE id = '$id_transaksi'");
        $data = mysqli_fetch_assoc($get_transaksi);
        $book_id = $data['book_id'];

        // 2. Update status transaksi & isi tanggal pinjam
        $update = mysqli_query($conn, "UPDATE transaksi SET 
            status = 'approved', 
            tgl_pinjam = '$tgl_sekarang' 
            WHERE id = '$id_transaksi'");

        // 3. Kurangi stok buku di tabel buku
        if ($update) {
            mysqli_query($conn, "UPDATE buku SET stok = stok - 1 WHERE id = '$book_id'");
            echo "<script>alert('Peminjaman disetujui!'); window.location='approval_pinjam.php';</script>";
        }

    } elseif ($aksi == 'tolak_pinjam') {
        // Update status jadi rejected
        $update = mysqli_query($conn, "UPDATE transaksi SET status = 'rejected' WHERE id = '$id_transaksi'");
        if ($update) {
            echo "<script>alert('Peminjaman ditolak!'); window.location='approval_pinjam.php';</script>";
        }
    }
} else {
    // Kalau gak ada parameter, balikin ke dashboard
    header("Location: index.php");
}
?>
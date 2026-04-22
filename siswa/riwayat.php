<?php
session_start();
include '../config/koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') { 
    header("Location: ../login.php"); 
    exit; 
}

$user_id = $_SESSION['user_id'];

// Query Riwayat
$query = "SELECT t.*, b.judul 
          FROM transaksi t 
          JOIN buku b ON t.book_id = b.id 
          WHERE t.user_id = '$user_id' 
          ORDER BY t.id DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Riwayat Pinjam - PerpusSiswa</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/siswa.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    
    <div class="sidebar">
        <div class="sidebar-header">
            <i class="fas fa-user-graduate"></i>
             <span>PerpusSiswa</span>
        </div>
        <div class="sidebar-menu">
            <a href="index.php"><i class="fas fa-home"></i> Home</a>
            <a href="riwayat.php" class="active"><i class="fas fa-history"></i> Riwayat Pinjam</a>
            <a href="../logout.php" style="margin-top: 50px; color: #e74c3c;"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>

    <div class="main-content">
        <h1><i class="fas fa-history"></i> Riwayat Peminjaman Buku</h1>

        <div class="card">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Judul Buku</th>
                        <th>Tgl Pinjam</th>
                        <th>Status</th>
                        <th>Aksi / Info</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;
                    while($row = mysqli_fetch_assoc($result)): 
                    ?>
                    <tr>
                        <td><?= $no++; ?></td>
                        <td><b><?= $row['judul']; ?></b></td>
                        <td><?= date('d M Y', strtotime($row['tgl_pinjam'])); ?></td>
                        <td>
                            <?php 
                            if($row['status'] == 'pending') {
                                echo "<span style='color:#f39c12;'><i class='fas fa-clock'></i> Menunggu Approval</span>";
                            } elseif($row['status'] == 'approved') {
                                echo "<span style='color:#27ae60;'><i class='fas fa-book-open'></i> Sedang Dipinjam</span>";
                            } elseif($row['status'] == 'pending_return') {
                                echo "<span style='color:#3498db;'><i class='fas fa-spinner fa-spin'></i> Menunggu Verifikasi Admin</span>";
                            } elseif($row['status'] == 'rejected') {
                                echo "<span style='color:#e74c3c;'><i class='fas fa-times-circle'></i> Ditolak</span>";
                            } else {
                                echo "<span style='color:#95a5a6;'><i class='fas fa-check-double'></i> Sudah Kembali</span>";
                            }
                            ?>
                        </td>
                        <td>
                            <?php if($row['status'] == 'approved'): ?>
                                <form action="proses_kembali.php" method="POST" style="display:inline;">
                                    <input type="hidden" name="id_transaksi" value="<?= $row['id']; ?>">
                                    <button type="submit" name="request_kembali" class="btn btn-add" 
                                            style="background: #3498db; color: white;"
                                            onclick="return confirm('Bawa buku ke petugas dan ajukan pengembalian sekarang?')">
                                        <i class="fas fa-undo"></i> Kembalikan
                                    </button>
                                </form>
                            <?php elseif($row['status'] == 'pending'): ?>
                                <small style="color: #95a5a6;">Tunggu admin setujui pinjaman</small>
                            <?php elseif($row['status'] == 'pending_return'): ?>
                                <small style="color: #2980b9;">Bawa buku ke meja petugas!</small>
                            <?php else: ?>
                                <span style="color: #bdc3c7;">-</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>

                    <?php if(mysqli_num_rows($result) == 0): ?>
                        <tr>
                            <td colspan="5" style="text-align:center; padding: 40px; color: #95a5a6;">
                                <i class="fas fa-folder-open" style="font-size: 30px; display:block; margin-bottom:10px;"></i>
                                Kamu belum pernah meminjam buku.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
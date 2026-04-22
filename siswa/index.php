<?php
session_start();
include '../config/koneksi.php';

// Proteksi halaman
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') { 
    header("Location: ../login.php"); 
    exit; 
}

$user_id = $_SESSION['user_id'];
$nama_user = $_SESSION['nama'];
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

// Query Cari Buku
$query = "SELECT * FROM buku WHERE judul LIKE '%$search%' OR penulis LIKE '%$search%'";
$result = mysqli_query($conn, $query);

// Query Notifikasi
$cek_notif = mysqli_query($conn, "SELECT b.judul FROM transaksi t 
                                  JOIN buku b ON t.book_id = b.id 
                                  WHERE t.user_id = '$user_id' 
                                  AND t.status = 'approved' 
                                  AND t.tgl_pinjam = CURDATE()");
$jumlah_notif = mysqli_num_rows($cek_notif);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Home - PerpusSiswa</title>
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
            <a href="index.php" class="active"><i class="fas fa-home"></i> Home</a>
            <a href="riwayat.php"><i class="fas fa-history"></i> Riwayat Pinjam</a>
            <a href="../logout.php" style="margin-top: 50px; color: #e74c3c;"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>

    <div class="main-content">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
            <h1>Halo, <?= $nama_user ?>! 👋</h1>
            
            <div class="notif-wrapper">
                <div id="btn-notif">
                    <i class="fas fa-bell" style="font-size: 20px; color: #2c3e50;"></i>
                    <?php if($jumlah_notif > 0): ?>
                        <span class="notif-badge"><?= $jumlah_notif ?></span>
                    <?php endif; ?>
                </div>

                <div id="box-notif" class="notif-dropdown">
                    <div class="notif-header"><i class="fas fa-bell"></i> Pemberitahuan</div>
                    <div style="max-height: 300px; overflow-y: auto;">
                        <?php if($jumlah_notif > 0): ?>
                            <?php mysqli_data_seek($cek_notif, 0); while($n = mysqli_fetch_assoc($cek_notif)): ?>
                                <div class="notif-item">
                                    <i class="fas fa-check-circle" style="color: #27ae60;"></i> 
                                    Pinjaman buku <b><?= $n['judul'] ?></b> sudah disetujui!
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div style="padding: 20px; text-align: center; color: #95a5a6;">Tidak ada notifikasi baru.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <h3>🔍 Cari Koleksi Buku</h3>
            <form method="GET" style="margin-top: 15px; display: flex; gap: 10px;">
                <input type="text" name="search" placeholder="Ketik judul buku atau nama penulis..." value="<?= $search ?>" 
                       style="flex: 1; padding: 12px; border: 1px solid #ddd; border-radius: 8px;">
                <button type="submit" class="btn btn-add" style="padding: 0 25px;">Cari</button>
            </form>
        </div>

        <div class="book-grid">
            <?php if(mysqli_num_rows($result) > 0): ?>
                <?php while($row = mysqli_fetch_assoc($result)): 
                    $id_buku = $row['id'];
                    // Cek apakah siswa ini sedang meminjam buku ini
                    $cek_status = mysqli_query($conn, "SELECT status FROM transaksi 
                                                       WHERE user_id = '$user_id' 
                                                       AND book_id = '$id_buku' 
                                                       AND status IN ('pending', 'approved')");
                    $is_borrowed = mysqli_num_rows($cek_status) > 0;
                    $status_data = mysqli_fetch_assoc($cek_status);
                ?>
                    <div class="card card-book">
                        <div>
                            <?php 
                                $path_gambar = "../assets/img/cover/" . $row['cover'];
                                $tampil_gambar = (!empty($row['cover']) && file_exists($path_gambar)) ? $path_gambar : "../assets/img/default_cover.jpg";
                            ?>
                            <img src="<?= $tampil_gambar ?>" class="img-cover" alt="Cover Buku">
                            
                            <h4><?= $row['judul'] ?></h4>
                            <p>Oleh: <?= $row['penulis'] ?></p>
                            <p>📍 <b>Rak <?= $row['lokasi_rak'] ?></b></p>
                            <p>
                                <?php if($row['stok'] > 0): ?>
                                    <span style="color: #27ae60; font-weight: bold;">Tersedia: <?= $row['stok'] ?></span>
                                <?php else: ?>
                                    <span style="color: #e74c3c; font-weight: bold;">Stok Habis</span>
                                <?php endif; ?>
                            </p>
                        </div>

                        <?php if($is_borrowed): ?>
                            <button class="btn" style="width: 100%; justify-content: center; background: #95a5a6; color: white; cursor: not-allowed;" disabled>
                                <i class="fas fa-info-circle"></i> 
                                <?= ($status_data['status'] == 'pending') ? 'Sedang Diproses' : 'Sedang Dipinjam'; ?>
                            </button>
                        <?php elseif($row['stok'] > 0): ?>
                            <form action="proses_pinjam.php" method="POST">
                                <input type="hidden" name="id_buku" value="<?= $row['id'] ?>">
                                <button type="submit" name="pinjam" class="btn btn-approve" 
                                        style="width: 100%; justify-content: center;" 
                                        onclick="return confirm('Pinjam buku <?= $row['judul'] ?>?')">
                                    <i class="fas fa-book-reader"></i> Pinjam
                                </button>
                            </form>
                        <?php else: ?>
                            <button class="btn" style="width: 100%; justify-content: center; background: #e74c3c; color: white; cursor: not-allowed;" disabled>
                                Stok Habis
                            </button>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="card" style="grid-column: 1 / -1; text-align: center; color: #7f8c8d;">
                    Buku yang kamu cari tidak ditemukan.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        const btnNotif = document.getElementById('btn-notif');
        const boxNotif = document.getElementById('box-notif');
        btnNotif.addEventListener('click', (e) => {
            e.stopPropagation();
            boxNotif.style.display = boxNotif.style.display === 'block' ? 'none' : 'block';
        });
        window.addEventListener('click', () => { boxNotif.style.display = 'none'; });
    </script>
</body>
</html>

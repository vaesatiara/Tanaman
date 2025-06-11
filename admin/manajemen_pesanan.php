<?php
session_start();
include "koneksi.php";
if (!isset($_SESSION['username'])){
    header("Location:login.php?login dulu");
    exit;
}
$sql="SELECT * FROM pesanan ORDER BY id_pesanan DESC";
$query=mysqli_query($koneksi,$sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Pesanan</title>
    <link rel="stylesheet" href="css/manajemen_pesanan.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
<div class="sidebar">
    <div class="logo"><img src="uploads/logo.png" alt="The Secret Garden"></div>
    <div class="menu-label">MENU</div>
    <ul class="menu-items">
        <li>
            <a class="menu-item" onclick="location.href='dashboard.php'">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
        </li>
        <li>
            <a class="menu-item" onclick="location.href='manajemen_pesanan.php'">
                <i class="fas fa-shopping-cart"></i>
                <span>Management Pesanan</span>
            </a>
        </li>
        <li>
            <a class="menu-item" onclick="location.href='manajemen_produk.php'">
                <i class="fas fa-box"></i>
                <span>Management Produk</span>
            </a>
        </li>
       
        <li>
            <a class="menu-item" onclick="location.href='manajemen_akun.php'">
                <i class="fas fa-user"></i>
                <span>Management Akun</span>
            </a>
        </li>
       
        <li>
            <a class="menu-item" onclick="location.href='manajemen_pembayaran.php'">
                <i class="fas fa-credit-card"></i>
                <span>Management Pembayaran</span>
            </a>
        </li>
       
        <li>
            <a class="menu-item" onclick="location.href='manajemen_saran.php'">
                <i class="fas fa-heart"></i>
                <span>Management Saran</span>
            </a>
        </li>
     </ul>
</div>

<div class="main-content">
    <div class="header">
        <div class="search-bar">
            <input type="text" placeholder="Search">
            <button><i class="fas fa-search"></i></button>
        </div>
        <div class="user-profile">
            <span>Admin</span>
            <img src="images/4396a60b-6455-40ed-8331-89a96395469f.jpeg" alt="Admin Profile">
        </div>
    </div>

    <div class="content">
        <h2 class="content-title">Management Pesanan</h2>
        
        <div class="data-table">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID Pesanan</th>
                        <th>ID Pelanggan</th>
                        <th>ID Produk</th>
                        <th>Nomor Pesanan</th>
                        <th>Tanggal Pesanan</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Update Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($pesanan = mysqli_fetch_assoc($query)) { ?>
                    <tr>
                        <td><?= $pesanan['id_pesanan'] ?></td>
                        <td><?= $pesanan['id_pelanggan'] ?></td>
                        <td><?= $pesanan['id_produk'] ?></td>
                        <td><?= $pesanan['nomor_pesanan'] ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($pesanan['tgl_pesanan'])) ?></td>
                        <td>Rp <?= number_format($pesanan['total'], 0, ',', '.') ?></td>
                        <td><?= $pesanan['status_pesanan'] ?></td>
                        <td>
                            <form method="POST" action="update_status.php" style="display: inline;">
                                <input type="hidden" name="id_pesanan" value="<?= $pesanan['id_pesanan'] ?>">
                                <select name="status" onchange="this.form.submit()">
                                    <option value="<?= $pesanan['status_pesanan'] ?>"><?= $pesanan['status_pesanan'] ?></option>
                                    <option value="dikirim">Dikirim</option>
                                    <option value="selesai">Selesai</option>
                                    <option value="diproses">Diproses</option>
                                    <option value="dibatalkan">Dibatalkan</option>
                                </select>
                            </form>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>

<?php
session_start();
include "koneksi.php";
// if (!isset($_SESSION['username'])){
//     header("Location:login.php?login dulu");
//     exit;
// }
$sql="SELECT * FROM pembayaran";
$query=mysqli_query($koneksi,$sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Pembayaran - The Secret Garden</title>
    <link rel="stylesheet" href="css/manajemen_pembayaran.css">
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
                <span>Management Akun</span> </a>
        </li>
        
        <li>
            <a class="menu-item active" onclick="location.href='manajemen_pembayaran.php'">
            <i class="fas fa-percent"></i>
            <span>Management Pembayaran</span></a>
        </li>
        
        <li>
            <a class="menu-item" onclick="location.href='manajemen_saran.php'">
            <i class="fas fa-heart"></i>
            <span>Management Saran</span></a>
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
            <h2 class="content-title">Management Pembayaran</h2>
            
            <div class="data-table">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Id Pembayaran</th>
                            <th>Id Pesanan</th>
                            <th>Tanggal Pembayaran</th>
                            <th>Waktu Pembayaran</th>
                            <th>Bukti Transfer</th>
                            <th>Catatan</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($pembayaran=mysqli_fetch_assoc($query)) { ?>
                        <tr>
                            <td><?=$pembayaran['id_pembayaran']?></td>
                            <td><?=$pembayaran['id_pesanan']?></td>
                            <td><?=$pembayaran['tgl_bayar']?></td>
                            <td><?=$pembayaran['waktu_bayar']?></td>
                            <td>
                                <?php if(!empty($pembayaran['file_image'])): ?>
                                    <img src="uploads/pembayaran/<?=$pembayaran['file_image']?>" 
                                         alt="Bukti Transfer" 
                                         class="payment-image" 
                                         style="max-width:150px; height:100px; object-fit:cover; border-radius:8px; border:1px solid #ddd; cursor:pointer;"
                                         onclick="showImageModal('uploads/pembayaran/<?=$pembayaran['file_image']?>')">
                                <?php else: ?>
                                    <span class="no-image">Tidak ada bukti</span>
                                <?php endif; ?>
                            </td> 
                            <td><?=$pembayaran['catatan']?></td>
                            <td class="action-buttons">
                                <button class="btn-edit" title="Konfirmasi Pembayaran">
                                    <i class="fas fa-check"></i>
                                </button>
                                <button class="btn-delete" title="Hapus Pembayaran" onclick="return confirm('Apakah Anda yakin ingin menghapus pembayaran ini?');">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    

    <script>
    // Fungsi untuk memeriksa apakah gambar berhasil dimuat
    document.addEventListener('DOMContentLoaded', function() {
        const images = document.querySelectorAll('.payment-image');
        
        images.forEach(img => {
            img.onerror = function() {
                console.error('Gambar bukti transfer gagal dimuat:', this.src);
                this.style.background = '#f0f0f0';
                this.style.display = 'flex';
                this.style.alignItems = 'center';
                this.style.justifyContent = 'center';
                this.innerHTML = '<span style="color:#999; font-size:12px;">Gambar tidak ditemukan</span>';
            };
            
            // Log untuk debugging
            console.log('Mencoba memuat gambar bukti transfer:', img.src);
        });
    });

    // Fungsi untuk menampilkan modal gambar
    function showImageModal(imageSrc) {
        const modal = document.getElementById('imageModal');
        const modalImage = document.getElementById('modalImage');
        modalImage.src = imageSrc;
        modal.classList.add('show');
    }

    // Fungsi untuk menutup modal gambar
    function closeImageModal() {
        const modal = document.getElementById('imageModal');
        modal.classList.remove('show');
    }

    // Tutup modal jika diklik di luar gambar
    window.onclick = function(event) {
        const modal = document.getElementById('imageModal');
        if (event.target == modal) {
            modal.classList.remove('show');
        }
    }
    </script>
</body>
</html>

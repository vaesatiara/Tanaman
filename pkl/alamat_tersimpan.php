<?php
session_start();
include "koneksi.php";

// Ambil semua alamat pelanggan
$sql_alamat = "SELECT * FROM pengiriman WHERE pelanggan= '$id_pelanggan'";
$query_alamat = mysqli_query($koneksi, $sql_alamat);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alamat Tersimpan - Toko Tanaman</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/notif.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .badge-primary {
            background: #4CAF50;
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 12px;
            margin-left: 10px;
        }
        
        .alert {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        
        .empty-state i {
            font-size: 48px;
            margin-bottom: 20px;
            color: #ccc;
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <div class="logo">
                <a href="index.php">
                    <img src="images/logo.png" alt="Toko Tanaman">
                </a>
            </div>
            <nav>
                <ul>
                    <li><a href="index.php">BERANDA</a></li>
                    <li><a href="produk.php">PRODUK</a></li>
                    <li><a href="kontak.php">KONTAK</a></li>
                    <li><a href="tentang_kami.php">TENTANG KAMI</a></li>
                </ul>
            </nav>
            <div class="icons">
                <a href="keranjang.php"><i class="fas fa-shopping-cart"></i></a>
                <a href="profil.php" class="active"><i class="fas fa-user"></i></a>
            </div>
        </div>
    </header>

    <main class="profile-section">
        <div class="container">
            <div class="profile-container">
                <div class="profile-sidebar">
                    <div class="profile-header">
                        <div class="profile-avatar">
                            <img src="<?= !empty($pelanggan['foto_profil']) ? 'images/profiles/'.$pelanggan['foto_profil'] : 'images/user.jpg' ?>" alt="<?=$pelanggan['username']?>">
                        </div>
                        <div class="profile-info">
                            <h2><?=$pelanggan['username']?></h2>
                            <p><?=$pelanggan['email']?></p>
                        </div>
                    </div>
                    <div class="profile-nav">
                        <ul>
                            <li>
                                <a href="profil.php">
                                    <i class="fas fa-user"></i> Profil Saya
                                </a>
                            </li>
                            <li>
                                <a href="riwayat_pesanan.php">
                                    <i class="fas fa-shopping-bag"></i> Riwayat Pesanan
                                </a>
                            </li>
                            <li class="active">
                                <a href="alamat_tersimpan.php">
                                    <i class="fas fa-map-marker-alt"></i> Alamat Tersimpan
                                </a>
                            </li>
                            <li>
                                <a href="ubah_password.php">
                                    <i class="fas fa-lock"></i> Ubah Password
                                </a>
                            </li>
                            <li>
                                <a href="logout.php" class="logout">
                                    <i class="fas fa-sign-out-alt"></i> Logout
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
                
                <div class="profile-content">
                    <div class="content-header">
                        <h1>Alamat Tersimpan</h1>
                        <a href="tambah_alamat.php" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Tambah Alamat Baru
                        </a>
                    </div>
                    
                    <?php if(isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <?= $_SESSION['success_message']; ?>
                        <?php unset($_SESSION['success_message']); ?>
                    </div>
                    <?php endif; ?>
                    
                    <div class="address-list">
                        <?php if ($query_alamat) :  ?>
                            <?php while($alamat = mysqli_fetch_assoc($query_alamat)): ?>
                            <div class="address-card">
                             
                                    <!-- <div class="address-badge">Utama</div> -->
                                
                                
                                <h3>
                                    <?= $alamat['label_alamat'] ?>
                                   
                                        <!-- <span class="badge-primary">Alamat Utama</span> -->
                                    
                                </h3>
                                
                                <p class="address-name"><?= $alamat['nama_penerima'] ?></p>
                                <p class="address-phone"><?= $alamat['no_telepon'] ?></p>
                                <p class="address-detail">
                                    <?= $alamat['alamat_lengkap'] . ', ' . $alamat['kecamatan'] . ', ' . $alamat['kota'] . ', ' . $alamat['provinsi'] ?>
                                </p>
                                
                                <div class="address-actions">
                                    <a href="edit_alamat.php?id=<?= $alamat['id_pengiriman'] ?>" class="btn btn-outline btn-sm">
                                        <i class="fas fa-edit"></i> Ubah
                                    </a>
                                    <a href="hapus_alamat.php?id_pengiriman=<?= $alamat['id_pengiriman'] ?>" 
                                       class="btn btn-outline btn-sm btn-danger"
                                       onclick="return confirm('Yakin ingin menghapus alamat ini?')">
                                        <i class="fas fa-trash"></i> Hapus
                                    </a>
                                    <!-- <?php if(!$alamat['is_primary']): ?>
                                    <a href="alamat_tersimpan.php?set_primary=<?= $alamat['id_pengiriman'] ?>" 
                                       class="btn btn-outline btn-sm set-primary-btn">
                                        <i class="fas fa-check-circle"></i> Jadikan Alamat Utama
                                    </a>
                                    <?php endif; ?> -->
                                </div>
                            </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-map-marker-alt"></i>
                                <h3>Belum Ada Alamat Tersimpan</h3>
                                <p>Tambahkan alamat untuk mempermudah proses checkout</p>
                                <a href="tambah_alamat.php" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Tambah Alamat Pertama
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer>
        <section class="feedback">
            <div class="container">
                <h2>Kirim kritik/saran untuk kami</h2>
                <p>Ceritakan kepada kami kritik dan/atau saran Anda</p>
                
                <div class="feedback-form">
                    <input type="text" placeholder="Masukkan kritik/saran">
                    <button type="submit">KIRIM</button>
                </div>
            </div>
        </section>
        
        <div class="container">
            <div class="footer-content">
                <div class="footer-logo">
                    <img src="images/logo.png" alt="Toko Tanaman">
                    <p>Toko tanaman hias terpercaya dengan berbagai koleksi tanaman berkualitas untuk mempercantik rumah dan ruangan Anda.</p>
                </div>
                <div class="footer-links">
                    <h3 class="footer-title">Tautan Cepat</h3>
                    <ul>
                        <li><a href="index.php">BERANDA</a></li>
                        <li><a href="produk.php">PRODUK</a></li>
                        <li><a href="kontak.php">KONTAK</a></li>
                        <li><a href="tentang_kami.php">TENTANG KAMI</a></li>
                    </ul>
                </div>
                <div class="footer-links">
                    <h3 class="footer-title">Kategori</h3>
                    <ul>
                        <li><a href="#">Tanaman Hias Daun</a></li>
                        <li><a href="#">Tanaman Hias Bunga</a></li>
                    </ul>
                </div>
                <div class="footer-contact">
                    <h3 class="footer-title">Kontak Kami</h3>
                    <p><i class="fas fa-map-marker-alt"></i> Jl. Tanaman Indah No. 123, Purwokerto</p>
                    <p><i class="fas fa-phone"></i> +62 812 3456 7890</p>
                    <p><i class="fas fa-envelope"></i> info@tokotanaman.com</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2023 Toko Tanaman. All Rights Reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>

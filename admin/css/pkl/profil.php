<?php
session_start();
include "koneksi.php";

if (!isset($_SESSION['username'])){
    header("Location:login.php?login dulu");
    exit;
}

$username = $_SESSION['username'];
$sql = "SELECT * FROM pelanggan WHERE username= '$username'";
$query = mysqli_query($koneksi, $sql);
$pelanggan = mysqli_fetch_assoc($query);

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya - Toko Tanaman</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <header>
        <div class="container">
            <div class="logo">
                <a href="index.html">
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
                <a href="profil.html" class="active"><i class="fas fa-user"></i></a>
            </div>
        </div>
    </header>

    <main class="profile-section">
        <div class="container">
            <div class="profile-container">
                <div class="profile-sidebar">
                    <div class="profile-header">
                        <div class="profile-avatar">
                            <img src="images/user.jpg" alt="caselline">
                        </div>
                        <div class="profile-info">
                            <h2><?php echo $pelanggan ['username']?></h2>
                            <p><?=$pelanggan['email']?></p>
                        </div>
                    </div>
                    <div class="profile-nav">
                        <ul>
                            <li class="active">
                                <a href="profil.php">
                                    <i class="fas fa-user"></i> Profil Saya
                                </a>
                            </li>
                            <li>
                                <a href="riwayat_pesanan.php">
                                    <i class="fas fa-shopping-bag"></i> Riwayat Pesanan
                                </a>
                            </li>
                            <li>
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
                                <a href="login.php" class="logout">
                                    <i class="fas fa-sign-out-alt"></i> Logout
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
                
                <div class="profile-content">
                    <div class="content-header">
                        <h1>Profil Saya</h1>
                        <a href="edit_profil.php" class="btn btn-primary">
                            <i class="fas fa-edit"></i> Edit Profil
                        </a>
                    </div>
                    
                    <div class="profile-details">
                        <div class="profile-row">
                            <div class="profile-field">
                                <h3>Nama Lengkap</h3>
                                <p><?php echo $pelanggan['nama_lengkap']?></p>
                            </div>
                            <div class="profile-field">
                                <h3>Username</h3>
                                <p><?php echo $pelanggan['username']?></p>
                            </div>
                        </div>
                        
                        <div class="profile-row">
                            <div class="profile-field">
                                <h3>Email</h3>
                                <p><?php echo $pelanggan['email']?></p>
                            </div>
                            <div class="profile-field">
                                <h3>Nomor Telepon</h3>
                                <p><?=$pelanggan['no_hp']?></p>
                            </div>
                        </div>
                        
                        <div class="profile-row">
                            <div class="profile-field">
                                <h3>Tanggal Lahir</h3>
                                <p><?=$pelanggan['tanggal_lahir']?></p>
                            </div>
                            <div class="profile-field">
                                <h3>Jenis Kelamin</h3>
                                <p><?=$pelanggan['jenis_kelamin']?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-logo">
                    <img src="images/logo.png" alt="Toko Tanaman">
                    <p>Toko tanaman hias terpercaya dengan berbagai koleksi tanaman berkualitas untuk mempercantik rumah dan ruangan Anda.</p>
                </div>
                <div class="footer-links">
                    <h3 class="footer-title">Tautan Cepat</h3>
                    <ul>
                        <li><a href="index.html">Beranda</a></li>
                        <li><a href="produk.html">Produk</a></li>
                        <li><a href="kontak.html">Kontak</a></li>
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
                    <p><i class="fas fa-map-marker-alt"></i> Jl. Tanaman Indah No. 123, Jakarta</p>
                    <p><i class="fas fa-phone"></i> +62 812 3456 7890</p>
                    <p><i class="fas fa-envelope"></i> info@tokotanaman.com</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2023 Toko Tanaman. All Rights Reserved.</p>
            </div>
        </div>
    </footer>

    <script src="js/script.js"></script>
</body>
</html>

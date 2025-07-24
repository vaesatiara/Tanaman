<?php
session_start();
include "koneksi.php";

// Query untuk mengambil produk dengan kategori daun
$sql = "SELECT * FROM produk WHERE kategori = 'Tanaman Hias Daun' ORDER BY id_produk DESC";
$query = mysqli_query($koneksi, $sql);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tanaman Hias Daun - The Secret Garden</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/tanaman_hias_daun.css">
</head>
<body>
    <!-- Header -->
    <header>
        <div class="container">
            <div class="logo">
                <a href="index.php">
                    <img src="images/logo.png" alt="The Secret Garden Logo">
                </a>
            </div>
            <nav>
                <ul>
                    <li><a href="index.php">BERANDA</a></li>
                    <li><a href="produk.php" class="active">PRODUK</a></li>
                    <li><a href="kontak.php">KONTAK</a></li>
                    <li><a href="tentang_kami.php">TENTANG KAMI</a></li>
                </ul>
            </nav>
            <div class="icons">
                <?php if (isset($_SESSION['username'])): ?>
                    <a href="keranjang.php"><i class="fas fa-shopping-cart"></i></a>
                    <a href="profil.php"><i class="fas fa-user"></i></a>
                    <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                <?php else: ?>
                    <a href="keranjang.php"><i class="fas fa-shopping-cart"></i></a>
                    <a href="login.php"><i class="fas fa-user"></i></a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <div class="container">
            <ul>
                <li><a href="index.php">Beranda</a></li>
                <li><a href="produk.php">Produk</a></li>
                <li>Tanaman Hias Daun</li>
            </ul>
        </div>
    </div>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="hero-content">
                <h1>Tanaman Hias Daun</h1>
                <p>Koleksi tanaman dengan daun indah dan unik untuk mempercantik ruangan Anda</p>
            </div>
        </div>
    </section>

    <!-- Products Section -->
    <section class="products-section">
        <div class="container">
            <h2 class="section-title">Koleksi Tanaman Hias Daun</h2>
            
            <div class="products-grid">
                <?php while($produk = mysqli_fetch_assoc($query)) : ?>
                <div class="product-card">
                    <div class="product-image">
                        <img src="/admin/Admin_WebTanaman/uploads/<?= $produk['foto'] ?>" alt="<?= $produk['nama_tanaman'] ?>">
                        <div class="product-actions">
                            <?php if (isset($_SESSION['username'])): ?>
                                <a href="keranjang.php?id_produk=<?= $produk['id_produk'] ?>" class="action-btn">
                                    <i class="fas fa-shopping-cart"></i>
                                </a>
                            <?php else: ?>
                                <a href="login.php" onclick="alert('Silakan login terlebih dahulu untuk menambahkan ke keranjang.');" class="action-btn">
                                    <i class="fas fa-shopping-cart"></i>
                                </a>
                            <?php endif; ?>
                            <a href="detail_produk.php?id_produk=<?= $produk['id_produk'] ?>" class="action-btn">
                                <i class="fas fa-eye"></i>
                            </a>
                        </div>
                    </div>
                    <div class="product-info">
                        <h3><a href="detail_produk.php?id_produk=<?= $produk['id_produk'] ?>"><?= $produk['nama_tanaman'] ?></a></h3>
                        <div class="product-category"><?= $produk['kategori'] ?></div>
                        <div class="product-rating">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star-half-alt"></i>
                            <span>(4.5)</span>
                        </div>
                        <div class="product-price">Rp <?= number_format($produk['harga'], 0, ',', '.') ?></div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </section>

    <!-- Newsletter -->
    <section class="newsletter">
        <div class="container">
            <div class="newsletter-content">
                <h2>Kirim kritik/Saran untuk kami</h2>
                <p>ceritakan kepada kami kritik dan saran anda</p>
                <form class="newsletter-form">
                    <input type="email" placeholder="Masukkan kritik/saran">
                    <button type="submit" class="btn btn-primary">Kirim</button>
                </form>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-logo">
                    <img src="images/logo.png" alt="The Secret Garden Logo">
                    <p>Temukan berbagai jenis tanaman hias berkualitas untuk mempercantik ruangan Anda.</p>
                    <div class="social-icons">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
                <div class="footer-links">
                    <h3>Tautan Cepat</h3>
                    <ul>
                        <li><a href="index.php">Beranda</a></li>
                        <li><a href="produk.php">Produk</a></li>
                        <li><a href="kontak.php">Kontak</a></li>
                        <li><a href="tentang_kami.php">Tentang Kami</a></li>
                    </ul>
                </div>
                <div class="footer-links">
                    <h3>Kategori</h3>
                    <ul>
                        <li><a href="tanaman_hias_daun.php">Tanaman Hias Daun</a></li>
                        <li><a href="tanaman_hias_bunga.php">Tanaman Hias Bunga</a></li>
                    </ul>
                </div>
                <div class="footer-contact">
                    <h3>Kontak Kami</h3>
                    <p><i class="fas fa-map-marker-alt"></i> Jl. Tanaman Indah No. 123, Jakarta Selatan</p>
                    <p><i class="fas fa-phone"></i> +62 812 3456 7890</p>
                    <p><i class="fas fa-envelope"></i> info@thesecretgarden.com</p>
                    <p><i class="fas fa-clock"></i> Senin - Minggu: 08.00 - 20.00 WIB</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2023 The Secret Garden. All Rights Reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>

<?php
session_start();
include "koneksi.php";

// Cek apakah ada parameter pencarian
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Query berdasarkan pencarian
if (!empty($search)) {
    $sql = "SELECT * FROM produk WHERE nama_tanaman LIKE '%$search%' ORDER BY id_produk DESC";
} else {
    $sql = "SELECT * FROM produk ORDER BY id_produk DESC";
}

$query = mysqli_query($koneksi, $sql);
$total_results = mysqli_num_rows($query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produk Tanaman - The Secret Garden</title>
    <link rel="stylesheet" href="css/produk.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    
     <!-- Header -->
    <header>
        <div class="container">
            <div class="logo">
                <a href="index.php">
                    <img src="images/logo.png" alt="The Secret Garden">
                </a>
            </div>
            <nav>
                <ul>
                    <li><a href="index.php">BERANDA</li>
                    <li><a href="produk.php" class="active">PRODUK</a></li>
                    <li><a href="kontak.php">KONTAK</a></li>
                    <li><a href="tentang_kami.php">TENTANG KAMI</a></li>
                </ul>
            </nav>
            
    <div class="icons">
    <?php if (isset($_SESSION['username'])): ?>
        <a href="keranjang.php" class="cart-icon">
            <i class="fas fa-shopping-cart"></i>
            <?php
            $totalItems = 0;
            if(isset($_SESSION['keranjang']) && !empty($_SESSION['keranjang'])) {
                foreach($_SESSION['keranjang'] as $id => $qty) {
                    if(!empty($id)) {
                        $totalItems += $qty;
                    }
                }
            }
            
            if($totalItems > 0) {
                echo '<span class="cart-badge">' . $totalItems . '</span>';
            }
            ?>
        </a>
        <a href="profil.php"><i class="fas fa-user"></i></a>
        <a href="logout.php"> <i class="fas fa-sign-out-alt"></i> Logout</a>
    <?php else: ?>
        <a href="login.php" class="cart-icon">
            <i class="fas fa-shopping-cart"></i>
        </a>
        <a href="login.php">Login</a>
    <?php endif; ?>
</div>
        </div>
    </header>

    <div class="breadcrumb">
        <div class="container">
            <ul>
                <li><a href="index.php">Beranda</a></li>
                <li>Produk</li>
                <?php if (!empty($search)): ?>
                <li>Pencarian: "<?= htmlspecialchars($search) ?>"</li>
                <?php endif; ?>
            </ul>
        </div>
    </div>

    <section class="hero-section">
        <div class="container">
            <div class="hero-content">
                <?php if (!empty($search)): ?>
                    <h1>Hasil Pencarian</h1>
                    <p>Menampilkan <?= $total_results ?> hasil untuk "<?= htmlspecialchars($search) ?>"</p>
                <?php else: ?>
                    <h1>Koleksi Tanaman Hias</h1>
                    <p>Temukan berbagai jenis tanaman hias berkualitas untuk mempercantik ruangan Anda</p>
                <?php endif; ?>
                
                <!-- Search bar di halaman produk -->
                <form action="produk.php" method="GET" class="search-form" style="margin-top: 20px;">
                    <input type="text" name="search" placeholder="Cari tanaman..." value="<?= htmlspecialchars($search) ?>" style="padding: 10px; width: 300px; border: 1px solid #ddd; border-radius: 5px;">
                    <button type="submit" style="padding: 10px 20px; background: #4CAF50; color: white; border: none; border-radius: 5px; cursor: pointer;">
                        <i class="fas fa-search"></i> Cari
                    </button>
                    <?php if (!empty($search)): ?>
                    <a href="produk.php" style="padding: 10px 20px; background: #f44336; color: white; text-decoration: none; border-radius: 5px; margin-left: 10px;">
                        <i class="fas fa-times"></i> Reset
                    </a>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </section>

    <?php if (empty($search)): ?>
    <section class="category-section">
        <div class="container">
            <h2 class="section-title">Kategori Tanaman</h2>
            <div class="category-cards">
                <a href="tanaman_hias_daun.php" class="category-card">
                    <div class="category-image">
                        <img src="images/daun1.jpg" alt="Tanaman Hias Daun">
                        <div class="category-overlay">
                            <span>Lihat Kategori</span>
                        </div>
                    </div>
                    <div class="category-info">
                        <h3>Tanaman Hias Daun</h3>
                        <p>Koleksi tanaman dengan daun indah dan unik</p>
                    </div>
                </a>
                
                <a href="tanaman_hias_bunga.php" class="category-card">
                    <div class="category-image">
                        <img src="images/tulip.jpg" alt="Tanaman Hias Bunga">
                        <div class="category-overlay">
                            <span>Lihat Kategori</span>
                        </div>
                    </div>
                    <div class="category-info">
                        <h3>Tanaman Hias Bunga</h3>
                        <p>Koleksi tanaman dengan bunga cantik dan mempesona</p>
                    </div>
                </a>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Section untuk menampilkan produk dari database -->
    <section class="product-detail">
        <div class="container">
            <?php if ($total_results > 0): ?>
            <div class="products-grid">
                <?php while($produk = mysqli_fetch_assoc($query)) : ?> 
                    <div class="product-card" data-category="<?= strtolower($produk['kategori']) ?>">
                        <div class="product-image">
                            <img id="main-product-image" src="/admin/Admin_WebTanaman/uploads/<?= $produk['foto'] ?>" alt="<?= $produk['nama_tanaman'] ?>">
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
                            <div class="product-price">
                                Rp <span><?= number_format($produk['harga']) ?></span>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
            <?php else: ?>
            <div style="text-align: center; padding: 50px;">
                <i class="fas fa-search" style="font-size: 64px; color: #ccc; margin-bottom: 20px;"></i>
                <h3>Tidak ada produk yang ditemukan</h3>
                <p>Maaf, tidak ada tanaman yang sesuai dengan pencarian "<?= htmlspecialchars($search) ?>"</p>
                <a href="produk.php" class="btn" style="margin-top: 20px;">Lihat Semua Produk</a>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <section class="newsletter">
        <div class="container">
            <div class="newsletter-content">
                <h2>Kirim kritik/saran untuk kami</h2>
                <p>Ceritakan kepada kami kritik dan/atau saran Anda</p>
                <form class="newsletter-form">
                    <input type="email" placeholder="Alamat Email Anda" required>
                    <button type="submit" class="btn btn-primary">Berlangganan</button>
                </form>
            </div>
        </div>
    </section>

    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-logo">
                    <img src="images/logo.png" alt="The Secret Garden">
                    <p>Toko tanaman hias terpercaya dengan berbagai koleksi tanaman berkualitas untuk mempercantik rumah dan ruangan Anda.</p>
                    <div class="social-icons">
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-facebook"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
                
                <div class="footer-links">
                    <h3>Tautan Cepat</h3>
                    <ul>
                        <li><a href="index.php">BERANDA</a></li>
                        <li><a href="produk.php">PRODUK</a></li>
                        <li><a href="kontak.php">KONTAK</a></li>
                    </ul>
                </div>
                
                <div class="footer-links">
                    <h3>Kategori</h3>
                    <ul>
                        <li><a href="tanaman_hias_bunga.php?category=bunga">Tanaman Hias Bunga</a></li>
                        <li><a href="tanaman_hias_daun.php?category=daun">Tanaman Hias Daun</a></li>
                    </ul>
                </div>
                
                <div class="footer-contact">
                    <h3>Kontak Kami</h3>
                    <p><i class="fas fa-map-marker-alt"></i> Jl. Tanaman Indah No. 123, Purwokerto</p>
                    <p><i class="fas fa-phone"></i> 0812-3456-7890</p>
                    <p><i class="fas fa-envelope"></i> thesecretgarden@gmail.com</p>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; 2023 The Secret Garden. Hak Cipta Dilindungi.</p>
            </div>
        </div>
    </footer>

</body>
</html>

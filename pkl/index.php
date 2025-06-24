<?php
session_start();
include "koneksi.php";

// Query untuk mengambil produk unggulan (misalnya 4 produk terbaru)
$sql_featured = "SELECT * FROM produk ORDER BY id_produk DESC LIMIT 4";
$query_featured = mysqli_query($koneksi, $sql_featured);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The Secret Garden - Toko Tanaman Hias</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/keranjang.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <header>
        <div class="container">
            <div class="logo">
                <a href="index.php">
                    <img src="images/logo.png" alt="THE SECRET GARDEN">
                </a>
            </div>
            <nav>
                <ul>
                    <li><a href="index.php" class="active">BERANDA</a></li>
                    <li><a href="produk.php">PRODUK</a></li>
                    <li><a href="kontak.php">KONTAK</a></li>
                    <li><a href="tentang_kami.php">TENTANG KAMI</a></li>
                </ul>
            </nav>
            <div class="icons">
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
                <?php if (isset($_SESSION['username'])): ?>
                <a href="profil.php"><i class="fas fa-user"></i></a>
                <a href="logout.php"> <i class="fas fa-sign-out-alt"></i> Logout</a>
                <?php else: ?>
                <a href="login.php">Login</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <h1>Ubah Ruangan Anda
                    Menjadi Hidup dengan Sentuhan Hijau</h1>
                <p>Yuk, buat nuansa alam ke dalam hidupmu dan ciptakan ruang yang lebih hidup dengan
                    <strong>The Secret Garden</strong></p>
                <form action="produk.php" method="GET" class="search-box">
                    <input type="text" name="search" placeholder="Cari tanaman..." value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
                    <button type="submit"><i class="fas fa-search"></i></button>
                </form>
            </div>
            <div class="hero-image">
                <img src="images/2.png" alt="Tanaman Hias">
            </div>
        </div>
    </section>

    <section class="featured-products">
        <div class="container">
            <h1>Produk Unggulan</h1>
            <p>Tanaman paling populer minggu ini</p>
            
            <div class="product-grid">
                <?php 
                $counter = 0;
                while($produk = mysqli_fetch_assoc($query_featured)) : 
                    $counter++;
                ?>
                <div class="product-card">
                    <?php if($counter == 1): ?>
                    <div class="product-badge">Best Seller</div>
                    <?php elseif($counter == 3): ?>
                    <div class="product-badge">Sale</div>
                    <?php endif; ?>
                    
                    <img src="/admin/Admin_WebTanaman/uploads/<?= $produk['foto'] ?>" alt="<?= $produk['nama_tanaman'] ?>">
                    <h3><a href="detail_produk.php?id_produk=<?= $produk['id_produk'] ?>"><?= $produk['nama_tanaman'] ?></a></h3>
                    <div class="rating">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star-half-alt"></i>
                        <span>(4.5)</span>
                    </div>
                    <p class="price">Rp <?= number_format($produk['harga'], 0, ',', '.') ?></p>
                </div>
                <?php endwhile; ?>
            </div>
            
            <div class="view-all">
                <a href="produk.php" class="btn">Lihat Semua Produk</a>
            </div>
        </div>
    </section>

    <section class="why-us">
        <div class="container">
            <h2>Mengapa Memilih Kami?</h2>
            <p>Keunggulan berbelanja di Toko Tanaman</p>
            
            <div class="benefits-grid">
                <div class="benefit-card">
                    <div class="icon">
                        <i class="fas fa-leaf"></i>
                    </div>
                    <h3>Tanaman Berkualitas</h3>
                    <p>Semua tanaman kami dipilih dengan teliti dan dirawat dengan baik sebelum dikirim ke pelanggan.</p>
                </div>
                
                <div class="benefit-card">
                    <div class="icon">
                        <i class="fas fa-truck"></i>
                    </div>
                    <h3>Pengiriman Aman</h3>
                    <p>Kami mengemas tanaman dengan hati-hati untuk memastikan keamanan selama pengiriman.</p>
                </div>
                
                <div class="benefit-card">
                    <div class="icon">
                        <i class="fas fa-heart"></i>
                    </div>
                    <h3>Garansi Tanaman</h3>
                    <p>Jika tanaman rusak saat tiba, kami akan mengganti dengan yang baru tanpa biaya tambahan.</p>
                </div>
                
                <div class="benefit-card">
                    <div class="icon">
                        <i class="fas fa-headset"></i>
                    </div>
                    <h3>Konsultasi Gratis</h3>
                    <p>Tim kami siap membantu Anda dengan tips perawatan dan saran untuk tanaman Anda.</p>
                </div>
            </div>
        </div>
    </section>

    <section id="about-us" class="about-us">
        <div class="container">
            <h1><b>About Us</b></h1>
            
            <div class="about-content">
                <p>Selamat datang di <strong>The Secret Garden </strong>Tempat di mana tanaman pilihan mengubah ruangan biasa menjadi spot yang penuh gaya dan kesejukan, Kami percaya alam itu keren, dan kami disini untuk membantu kamu menghadirkan sentuhan hijau yang ngga cuma estetis, tapi juga bikin mood kamu makin oke. </p>
                <br> <br>
                <div class="testimonials">
                    <div class="testimonial-card">
                        <img src="images/ulasan1.jpg" alt="Testimonial 1">
                        <div class="rating">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <p>"Kirain bakal biasa aja, tapi ternyata pas dateng lebih bagus dari ekspetasi!, SUKAA BANGET!! tanamannya cantik, segar, dikemas rapi. 
                            pokoknyaa recommended!!"</p>
                        <h4>Micele</h4>
                    </div>
                    
                    <div class="testimonial-card">
                        <img src="images/ulasan2.jpg" alt="Testimonial 2">
                        <div class="rating">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <p>"awalnya sempet ragu mau kasih tanaman buat Valentine, tapi ternyata pacarku suka bangett! katanya lebih meaningful daripada bunga yang cuma tahan berapa hari. sekarang tiap hari dia rawat dan katanya ini jadi hadiah favoritnya. makasihh minn,sukses terus!"</p>
                        <h4>JooHyuk</h4>
                    </div>
                    
                    <div class="testimonial-card">
                        <img src="images/ulasan3.jpg" alt="Testimonial 3">
                        <div class="rating">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star-half-alt"></i>
                        </div>
                        <p>"Zaman nenek muda, kalau mau tanaman harus cari sendiri dikebun. Sekarang tinggal pesan diwebsite ini, eh datangnya cepat dan bagus sekali, Tanamannya sehat, hijau segar, ngga ada yang layu, cucu-cucu nenek  sampai bilang rumah jadi lebih adem. Semoga makin laris, ya."</p>
                        <h4>Nenek</h4>
                    </div>
                </div>
            </div>
        </div>
    </section>

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

    <script src="js/script.js"></script>
</body>
</html>

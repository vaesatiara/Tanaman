<?php
session_start();
include "koneksi.php";
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tentang Kami - The Secret Garden</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/tentang_kami.css">
    <link rel="stylesheet" href="css/keranjang.css">
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
                    <li><a href="index.php">BERANDA</a></li>
                    <li><a href="produk.php">PRODUK</a></li>
                    <li><a href="kontak.php">KONTAK</a></li>
                    <li><a href="tentang_kami.php" class="active">TENTANG KAMI</a></li>
                    
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

  
    <div class="breadcrumb">
        <div class="container">
            <ul>
                <li><a href="index.html">Beranda</a></li>
                <li>Tentang Kami</li>
            </ul>
        </div>
    </div>

    
    <div class="page-banner">
        <div class="container">
            <div class="banner-content">
                <h1>Tentang Kami</h1>
                <p>Mengenal lebih dekat dengan tim di balik The Secret Garden</p>
            </div>
        </div>
    </div>

    
    <section class="about-section">
        <div class="container">
            <div class="about-intro">
                <div class="about-image">
                    <img src="images/login.jpg" alt="The Secret Garden Store">
                </div>
                <div class="about-content">
                    <h2>Selamat Datang di The Secret Garden</h2>
                    <p>The Secret Garden adalah toko tanaman hias yang didirikan pada tahun 2020 dengan tujuan untuk menyediakan berbagai jenis tanaman hias berkualitas dengan harga terjangkau.</p>
                    <p>Kami percaya bahwa kehadiran tanaman di dalam rumah dan ruangan tidak hanya mempercantik tampilan, tetapi juga memberikan manfaat kesehatan dan kesejahteraan bagi penghuninya.</p>
                    <p>Dengan pengalaman dan pengetahuan yang kami miliki, kami berkomitmen untuk membantu pelanggan menemukan tanaman yang tepat untuk ruangan mereka dan memberikan tips perawatan yang optimal.</p>
                    <p>Toko kami berlokasi di Purwokerto, Jawa Tengah, dan siap melayani kebutuhan tanaman hias Anda.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="team-section">
        <div class="container">
            <div class="section-header">
                <h2>Tim Kami</h2>
                <p>Kenali orang-orang hebat di balik website The Secret Garden</p>
            </div>
            
            <div class="team-grid">
                <div class="team-member">
                    <div class="member-image">
                        <img src="images/syifaa.jpg" alt="Syifa">
                    </div>
                    <div class="member-info">
                        <h3>Syifa Amalia Zikrina</h3>
                        <p class="position">Front-end Developer</p>
                       <p class="bio">Syifa adalah seorang front-end developer yang bertanggung jawab untuk membuat tampilan website The Secret Garden menjadi menarik dan responsif. Dengan keahliannya dalam HTML, CSS, ia menciptakan pengalaman pengguna yang optimal.</p>
                        <div class="social-icons">
                            <a href="#"><i class="fab fa-instagram"></i></a>
                            <a href="#"><i class="fab fa-linkedin-in"></i></a>
                            <a href="#"><i class="fab fa-github"></i></a>
                        </div>
                    </div>
                </div>
                
                <div class="team-member">
                    <div class="member-image">
                        <img src="images/vaesa.jpg" alt="Vaesa">
                    </div>
                    <div class="member-info">
                        <h3>Vaesa Tiara El Fatieah</h3>
                        <p class="position">Back-end Developer</p>
                        <p class="bio">Vaesa adalah seorang back-end developer yang ahli dalam membangun dan mengelola sistem di balik website The Secret Garden. Ia mengembangkan database, API, dan logika server yang memastikan website berjalan dengan lancar dan aman.</p>
                        <div class="social-icons">
                            <a href="#"><i class="fab fa-instagram"></i></a>
                            <a href="#"><i class="fab fa-linkedin-in"></i></a>
                            <a href="#"><i class="fab fa-github"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
            

    <!-- Footer -->
    <section class="feedback-section">
        <div class="container">
            <h2>Kirim kritik/saran untuk kami</h2>
            <p>Ceritakan kepada kami kritik dan/atau saran Anda</p>
            <form class="feedback-form">
                <input type="text" placeholder="Masukkan kritik/saran">
                <button type="submit">KIRIM</button>
            </form>
        </div>
    </section>

    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-about">
                    <img src="images/logo.png" alt="The Secret Garden">
                    <p>Toko tanaman hias terpercaya dengan berbagai koleksi tanaman berkualitas untuk mempercantik rumah dan ruangan Anda.</p>
                    <div class="social-icons">
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
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
                        <li><a href="tentang_kami.php">TENTANG KAMI</a></li>
                    </ul>
                </div>
                
                <div class="footer-category">
                    <h3>Kategori</h3>
                    <ul>
                        <li><a href="tanaman_hias_daun.php">Tanaman Hias Daun</a></li>
                        <li><a href="tanaman_hias_bunga.php">Tanaman Hias Bunga</a></li>
                    </ul>
                </div>
                
                <div class="footer-contact">
                    <h3>Kontak Kami</h3>
                    <div class="contact-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <div>Jl. Tanaman Indah No. 123, Purwokerto, Jawa Tengah</div>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-phone"></i>
                        <div>0812-3456-7890</div>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-envelope"></i>
                        <div>thesecretgarden@gmail.com</div>
                    </div>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; 2023 The Secret Garden. Hak Cipta Dilindungi.</p>
            </div>
        </div>
    </footer>
</body>
</html>

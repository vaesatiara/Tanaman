<?php
session_start();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kontak - The Secret Garden</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/kontak.css">
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
                    <li><a href="index.php">BERANDA</li>
                    <li><a href="produk.php">PRODUK</a></li>
                    <li><a href="kontak.php" class="active">KONTAK</a></li>
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
    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <div class="container">
            <ul>
                <li><a href="index.php">Beranda</a></li>
                <li>Kontak</li>
            </ul>
        </div>
    </div>

    
    <div class="page-banner">
        <div class="container">
            <div class="banner-content">
                <h1>Hubungi Kami</h1>
                <p>Kami siap membantu Anda dengan pertanyaan, saran, atau pesanan tanaman hias.</p>
            </div>
        </div>
    </div>

    <section class="contact-section">
        <div class="container">
            <div class="contact-wrapper">
                <div class="contact-info">
                    <h2>Informasi Kontak</h2>
                    <p>Jangan ragu untuk menghubungi kami melalui berbagai cara berikut:</p>
                    
                    <div class="info-item">
                        <div class="icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div class="details">
                            <h3>Alamat Toko</h3>
                            <p>Jl. Tanaman Indah No. 123, Purwokerto, Jawa Tengah, Indonesia</p>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="icon">
                            <i class="fas fa-phone-alt"></i>
                        </div>
                        <div class="details">
                            <h3>Telepon</h3>
                            <p>0812-3456-7890</p>
                            <p>0274-5678-9012</p>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="details">
                            <h3>Email</h3>
                            <p>thesecretgarden@gmail.com</p>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="details">
                            <h3>Jam Operasional</h3>
                            <p>Senin - Jumat: 08.00 - 17.00</p>
                            <p>Sabtu: 09.00 - 15.00</p>
                            <p>Minggu: Tutup</p>
                        </div>
                    </div>
                    
                    <div class="social-media">
                        <h3>Ikuti Kami</h3>
                        <div class="social-icons">
                            <a href="#"><i class="fab fa-instagram"></i></a>
                            <a href="#"><i class="fab fa-facebook-f"></i></a>
                            <a href="#"><i class="fab fa-twitter"></i></a>
                            <a href="#"><i class="fab fa-youtube"></i></a>
                        </div>
                    </div>
                </div>
               
                <form action="prosesT_saran.php" method="post">
                <div class="contact-form-container">
               
                    <h2>Kirim Pesan</h2>
                    <p>Isi formulir di bawah ini dan kami akan segera menghubungi Anda kembali.</p>
                    
                    
                        <div class="form-group">
                            <label for="name">Nama Lengkap</label>
                            <input type="text" name="nama_pengirim" id_saran="" class="form-control">
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="text" name="email" id_saran="" class="form-control">
                        </div>
                        
                        <div class="form-group">
                            <label for="phone">Nomor Telepon</label>
                            <input type="number" name="no_hp" id_saran="" class="form-control">
                        </div>
                        
                        
                        <div class="form-group">
                            <label for="message">Pesan</label>
                            <textarea id_saran="" name="isi_saran" rows="5" required></textarea>
                        </div>
                        
                        <input type="submit" value="KIRIM PESAN">
                    
                      
                        </div>
            </form>
            
        </div>
</div>
</div>
 
    </section>

  

    
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
                        <li><a href="tanaman_hias_bunga.php">Tanaman Hias Bunga</a></li>
                        <li><a href="tanaman_hias_daun.php">Tanaman Hias Daun</a></li>
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

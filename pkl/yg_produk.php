<?php
session_start();
include "koneksi.php";

// if (!isset($_SESSION['username'])){
//     header("Location:login.php?login dulu");
//     exit;
// }

$sql="SELECT * FROM produk ORDER BY id_produk DESC";
$query=mysqli_query($koneksi,$sql);
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
    
    <header>
        <div class="container">
            <div class="logo">
                <a href="index.html">
                    <img src="images/logo.png" alt="The Secret Garden">
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
                <a href="keranjang.php"><i class="fas fa-shopping-cart"></i></a>
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
                <li><a href="index.php">Beranda</a></li>
                <li>Produk</li>
            </ul>
        </div>
    </div>

    
    <section class="hero-section">
        <div class="container">
            <div class="hero-content">
                <h1>Koleksi Tanaman Hias</h1>
                <p>Temukan berbagai jenis tanaman hias berkualitas untuk mempercantik ruangan Anda</p>
            </div>
        </div>
    </section>

   
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
  
    <?php while($produk=mysqli_fetch_assoc($query)) : ?> 
    <section class="product-detail">
    <div class="container">
            <div class="products-grid">
               
            <div class="product-card" data-category="daun">
                
                
    <div class="product-image">
    <img id="main-product-image" src="uploads/<?=$produk['foto']?>"alt="<?= $produk['foto'] ?>">
            <div class="product-actions">
                  <?php if (isset($_SESSION['username'])): ?>
        <a href="keranjang.php?id_produk=<?php echo $produk['id_produk']; ?>" class="action-btn">
            <i class="fas fa-shopping-cart"></i>  
        </a>
        <?php else: ?>
        <a href="login.php" onclick="alert('Silakan login terlebih dahulu untuk menambahkan ke keranjang.');" class="action-btn">
            <i class="fas fa-shopping-cart"></i>
        </a>
         <?php endif; ?>
                <a href="detail_produk.php?id_produk=<?=$produk['id_produk'] ?>" class="action-btn"><i class="fas fa-eye"></i></a>
                        </div></div>
                   
                    <div class="product-info">
                        <h3><a href="detail_produk.php"><?=$produk['nama_tanaman'] ?></a></h3>
                        <div class="product-category"><?=$produk['kategori']?></div>
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
    
    
    
                   
                <div class="product-card" data-category="daun">
                    <div class="product-badge">Terlaris</div>
                    <div class="product-image">
                        <img src="images/Calathea Orbifolia.jpg" alt="Calathea Orbifolia">
                        <div class="product-actions">
                            <a href="keranjang.html" class="action-btn"><i class="fas fa-shopping-cart"></i></a>
                            <a href="detail_orbifiola.html" class="action-btn"><i class="fas fa-eye"></i></a>
                        </div>
                    </div>
                    <div class="product-info">
                        <h3><a href="detail_produk.html">Calathea Orbifolia</a></h3>
                        <div class="product-category">Tanaman Hias Daun</div>
                        <div class="product-rating">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star-half-alt"></i>
                            <span>(4.5)</span>
                        </div>
                        <div class="product-price">
                            <span class="price">Rp. 399.000 </span>
                        </div>
                    </div>
                </div>

                <div class="product-card" data-category="daun">
                    <div class="product-image">
                        <img src="images/calathea pinstripe.jpg" alt="Calathea Pinstripe">
                        <div class="product-actions">
                            <a href="#" class="action-btn"><i class="fas fa-shopping-cart"></i></a>
                            <a href="detail_pinstripe.html" class="action-btn"><i class="fas fa-eye"></i></a>
                        </div>
                    </div>
                    <div class="product-info">
                        <h3><a href="#">Calathea Pinstripe</a></h3>
                        <div class="product-category">Tanaman Hias Daun</div>
                        <div class="product-rating">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star-half-alt"></i>
                            <span>(4.3)</span>
                        </div>
                        <div class="product-price">
                            <span class="price">Rp 375.000</span>
                        </div>
                    </div>
                </div>

                <div class="product-card" data-category="daun">
                    <div class="product-badge">Baru</div>
                    <div class="product-image">
                        <img src="images/chinese money.jpg" alt="Chinese Money ">
                        <div class="product-actions">
                            <a href="#" class="action-btn"><i class="fas fa-shopping-cart"></i></a>
                            <a href="detail_chinese.html" class="action-btn"><i class="fas fa-eye"></i></a>
                        </div>
                    </div>
                    <div class="product-info">
                        <h3><a href="#">Chinese Money Plant</a></h3>
                        <div class="product-category">Tanaman Hias Daun</div>
                        <div class="product-rating">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star-half-alt"></i>
                            <span>(4.5)</span>
                        </div>
                        <div class="product-price">
                            <span class="price">Rp 275.000</span>
                        </div>
                    </div>
                </div>

                
                <div class="product-card" data-category="daun">
                    <div class="product-badge">Terlaris</div>
                    <div class="product-image">
                        <img src="images/ficus.jpg" alt="Ficus Elastika">
                        <div class="product-actions">
                            <a href="#" class="action-btn"><i class="fas fa-shopping-cart"></i></a>
                            <a href="detail_ficus.html" class="action-btn"><i class="fas fa-eye"></i></a>
                        </div>
                    </div>
                    <div class="product-info">
                        <h3><a href="#">Ficus Elastika</a></h3>
                        <div class="product-category">Tanaman Hias Bunga</div>
                        <div class="product-rating">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star-half-alt"></i>
                            <span>(4.5)</span>
                        </div>
                        <div class="product-price">
                            <span class="price">Rp 399.000</span>
                        </div>
                    </div>
                </div>

                <div class="product-card" data-category="daun">
                    <div class="product-image">
                        <img src="images/Aspidistra elatior.jpg" alt="Aspidistra Elastior">
                        <div class="product-actions">
                            <a href="#" class="action-btn"><i class="fas fa-shopping-cart"></i></a>
                            <a href="detail_aspidistra.html" class="action-btn"><i class="fas fa-eye"></i></a>
                        </div>
                    </div>
                    <div class="product-info">
                        <h3><a href="#">Aspidistra Elastior</a></h3>
                        <div class="product-category">Tanaman Hias Bunga</div>
                        <div class="product-rating">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star-half-alt"></i>
                            <span>(4.7)</span>
                        </div>
                        <div class="product-price">
                            <span class="price">Rp 299.000</span>
                        </div>
                    </div>
                </div>

                <div class="product-card" data-category="daun">
                    <div class="product-badge">Baru</div>
                    <div class="product-image">
                        <img src="images/monstera1 (1).jpeg" alt="monstera">
                        <div class="product-actions">
                            <a href="#" class="action-btn"><i class="fas fa-shopping-cart"></i></a>
                            <a href="detail_monstera.html" class="action-btn"><i class="fas fa-eye"></i></a>
                        </div>
                    </div>
                    <div class="product-info">
                        <h3><a href="#">Monstera Deliciosa</a></h3>
                        <div class="product-category">Tanaman Hias Daun</div>
                        <div class="product-rating">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star-half-alt"></i>
                            <span>(4.5)</span>
                        </div>
                        <div class="product-price">
                            <span class="price">Rp 299.000</span>
                        </div>
                    </div>
                </div>

               
                <div class="product-card" data-category="daun">
                    <div class="product-image">
                        <img src="images/peperomia watermelon.jpg" alt="Peperomia Watermelon">
                        <div class="product-actions">
                            <a href="#" class="action-btn"><i class="fas fa-shopping-cart"></i></a>
                            <a href="detail_peperomia.html" class="action-btn"><i class="fas fa-eye"></i></a>
                        </div>
                    </div>
                    <div class="product-info">
                        <h3><a href="#">Peperomia Watermelon</a></h3>
                        <div class="product-category">Tanaman Hias Daun</div>
                        <div class="product-rating">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star-half-alt"></i>
                            <span>(4.6)</span>
                        </div>
                        <div class="product-price">
                            <span class="price">Rp 275.000</span>
                        </div>
                    </div>
                </div>

                <div class="product-card" data-category="daun">
                    <div class="product-image">
                        <img src="images/zamioculcas.jpg" alt="Zamioculacas Zamiifolia">
                        <div class="product-actions">
                            <a href="#" class="action-btn"><i class="fas fa-shopping-cart"></i></a>
                            <a href="detail_zamioculacas.html" class="action-btn"><i class="fas fa-eye"></i></a>
                        </div>
                    </div>
                    <div class="product-info">
                        <h3><a href="#">Zamioculacas Zamiifolia</a></h3>
                        <div class="product-category">Tanaman Hias Daun</div>
                        <div class="product-rating">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star-half-alt"></i>
                            <span>(4.8)</span>
                        </div>
                        <div class="product-price">
                            <span class="price">Rp 399.000</span>
                        </div>
                    </div>
                </div>

                <div class="product-card" data-category="daun">
                    <div class="product-image">
                        <img src="images/calateha roseopicta.jpg" alt="Calathea Roseopicta">
                        <div class="product-actions">
                            <a href="#" class="action-btn"><i class="fas fa-shopping-cart"></i></a>
                            <a href="detail_roseopicta.html" class="action-btn"><i class="fas fa-eye"></i></a>
                        </div>
                    </div>
                    <div class="product-info">
                        <h3><a href="#">Calathea Roseopicta</a></h3>
                        <div class="product-category">Tanaman Hias Daun</div>
                        <div class="product-rating">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="far fa-star"></i>
                            <span>(4.0)</span>
                        </div>
                        <div class="product-price">
                            <span class="price">Rp 349.000</span>
                        </div>
                    </div>
                </div>

                
                <div class="product-card" data-category="bunga">
                    <div class="product-image">
                        <img src="images/krenyem.jpg" alt="Krenyem / Geranium">
                        <div class="product-actions">
                            <a href="#" class="action-btn"><i class="fas fa-shopping-cart"></i></a>
                            <a href="detail_krenyem.html" class="action-btn"><i class="fas fa-eye"></i></a>
                        </div>
                    </div>
                    <div class="product-info">
                        <h3><a href="#">Krenyem / Geranium</a></h3>
                        <div class="product-category">Tanaman Hias Bunga</div>
                        <div class="product-rating">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star-half-alt"></i>
                            <span>(4.5)</span>
                        </div>
                        <div class="product-price">
                            <span class="price">Rp 60.000</span>
                        </div>
                    </div>
                </div>

                <div class="product-card" data-category="bunga">
                    <div class="product-image">
                        <img src="images/Anyelir mini.jpg" alt="Anyelir Mini">
                        <div class="product-actions">
                            <a href="#" class="action-btn"><i class="fas fa-shopping-cart"></i></a>
                            <a href="detail_anyelir.html" class="action-btn"><i class="fas fa-eye"></i></a>
                        </div>
                    </div>
                    <div class="product-info">
                        <h3><a href="#">Anyelir Mini</a></h3>
                        <div class="product-category">Tanaman Hias Bunga</div>
                        <div class="product-rating">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star-half-alt"></i>
                            <span>(4.5)</span>
                        </div>
                        <div class="product-price">
                            <span class="price">Rp 75.000</span>
                        </div>
                    </div>
                </div>

                <div class="product-card" data-category="bunga">
                    <div class="product-image">
                        <img src="images/tulip.jpg" alt="Tulip">
                        <div class="product-actions">
                            <a href="#" class="action-btn"><i class="fas fa-shopping-cart"></i></a>
                            <a href="detail_tulip.html" class="action-btn"><i class="fas fa-eye"></i></a>
                        </div>
                    </div>
                    <div class="product-info">
                        <h3><a href="#">Tulip</a></h3>
                        <div class="product-category">Tanaman Hias Bunga</div>
                        <div class="product-rating">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star-half-alt"></i>
                            <span>(4.7)</span>
                        </div>
                        <div class="product-price">
                            <span class="price">Rp 95.000</span>
                        </div>
                    </div>
                </div>
            

            <div class="product-card" data-category="bunga">
                <div class="product-image">
                    <img src="images/saliyah.jpg" alt="saliyah">
                    <div class="product-actions">
                        <a href="keranjang.html" class="action-btn"><i class="fas fa-shopping-cart"></i></a>
                        <a href="detail_saliyah.html" class="action-btn"><i class="fas fa-eye"></i></a>
                    </div>
                </div>
                <div class="product-info">
                    <h3><a href="#">Saliyah/Alzaela</a></h3>
                    <div class="product-category">Tanaman Hias Bunga</div>
                    <div class="product-rating">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star-half-alt"></i>
                        <span>(4.6)</span>
                    </div>
                    <div class="product-price">
                        <span class="price">Rp 125.000</span>
                    </div>
                </div>
            </div>

            <div class="product-card" data-category="bunga">
                <div class="product-image">
                    <img src="images/oxalis (1).jpeg" alt="Oxalis Triangularis">
                    <div class="product-actions">
                        <a href="keranjang.html" class="action-btn"><i class="fas fa-shopping-cart"></i></a>
                        <a href="detail_oxalis.html" class="action-btn"><i class="fas fa-eye"></i></a>
                    </div>
                </div>
                <div class="product-info">
                    <h3><a href="#">Oxalis Triangularis</a></h3>
                    <div class="product-category">Tanaman Hias Bunga</div>
                    <div class="product-rating">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="far fa-star"></i>
                        <span>(4.3)</span>
                    </div>
                    <div class="product-price">
                        <span class="price">Rp 75.000</span>
                    </div>
                </div>
            </div>

            <div class="product-card" data-category="bunga">
                <div class="product-image">
                    <img src="images/krisan.jpg" alt="Krisan Aster Kuning">
                    <div class="product-actions">
                        <a href="keranjang.html" class="action-btn"><i class="fas fa-shopping-cart"></i></a>
                        <a href="detail_krisan.html" class="action-btn"><i class="fas fa-eye"></i></a>
                    </div>
                </div>
                <div class="product-info">
                    <h3><a href="#">Krisan Aster Kuning</a></h3>
                    <div class="product-category">Tanaman Hias Bunga</div>
                    <div class="product-rating">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star-half-alt"></i>
                        <span>(4.5)</span>
                    </div>
                    <div class="product-price">
                        <span class="price">Rp 115.000</span>
                    </div>
                </div>
            </div>

            <div class="product-card" data-category="bunga">
                <div class="product-badge">Baru</div>
                <div class="product-image">
                    <img src="images/mawar pink.jpg" alt="Mawar Pink">
                    <div class="product-actions">
                        <a href="keranjang.html" class="action-btn"><i class="fas fa-shopping-cart"></i></a>
                        <a href="detail_mawar.html" class="action-btn"><i class="fas fa-eye"></i></a>
                    </div>
                </div>
                <div class="product-info">
                    <h3><a href="#">Mawar Pink</a></h3>
                    <div class="product-category">Tanaman Hias Bunga</div>
                    <div class="product-rating">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <span>(4.9)</span>
                    </div>
                    <div class="product-price">
                        <span class="price">Rp 85.000</span>
                    </div>
                </div>
            </div>

            <div class="product-card" data-category="bunga">
                <div class="product-badge">Baru</div>
                <div class="product-image">
                    <img src="images/lavender.jpg" alt="lavender">
                    <div class="product-actions">
                        <a href="keranjang.html" class="action-btn"><i class="fas fa-shopping-cart"></i></a>
                        <a href="detail_lavender.html" class="action-btn"><i class="fas fa-eye"></i></a>
                    </div>
                </div>
                <div class="product-info">
                    <h3><a href="">Lavender</a></h3>
                    <div class="product-category">Tanaman Hias Bunga</div>
                    <div class="product-rating">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star-half-alt"></i>
                        <span>(4.5)</span>
                    </div>
                    <div class="product-price">
                        <span class="price">Rp 65.000</span>
                    </div>
                </div>
            </div>

            <div class="product-card" data-category="bunga">
                    <div class="product-badge">Terlaris</div>
                    <div class="product-image">
                        <img src="images/anggrek.jpg" alt="anggrek">
                        <div class="product-actions">
                            <a href="keranjang.html" class="action-btn"><i class="fas fa-shopping-cart"></i></a>
                            <a href="detail_anggrek.html" class="action-btn"><i class="fas fa-eye"></i></a>
                        </div>
                    </div>
                    <div class="product-info">
                        <h3><a href="detail_produk.html">Anggrek</a></h3>
                        <div class="product-category">Tanaman Hias Daun</div>
                        <div class="product-rating">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star-half-alt"></i>
                            <span>(4.7)</span>
                        </div>
                        <div class="product-price">
                            <span class="price">Rp 185.000</span>
                        </div>
                    </div>
                </div>
                </div>
    </div>
    </div>
         
    </selection>
<?php endwhile ?>

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

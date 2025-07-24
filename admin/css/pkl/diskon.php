<?php
session_start();
include "koneksi.php";

// Get redirect information
$redirect = isset($_GET['redirect']) ? $_GET['redirect'] : 'keranjang.php';
$id_produk = isset($_GET['id_produk']) ? $_GET['id_produk'] : '';

// Handle discount application
if(isset($_GET['apply'])) {
    $kode_diskon = $_GET['apply'];
    
    // Get discount details
    $query = $koneksi->query("SELECT * FROM diskon WHERE kode_diskon='$kode_diskon' AND status='aktif'");
    
    if($query->num_rows > 0) {
        $diskon = $query->fetch_assoc();
        
        // Save discount to session
        $_SESSION['diskon'] = [
            'kode' => $diskon['kode_diskon'],
            'nilai' => $diskon['nilai_diskon'],
            'tipe' => $diskon['tipe_diskon'] // 'persen' or 'nominal'
        ];
        
        // Redirect back
        if($redirect == 'checkout_item' && !empty($id_produk)) {
            header("Location: checkout_item.php?id_produk=$id_produk");
        } else {
            header("Location: keranjang.php");
        }
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pilih Diskon - Toko Tanaman</title>
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
                    <li><a href="index.html">BERANDA</a></li>
                    <li><a href="produk.html">PRODUK</a></li>
                    <li><a href="kontak.html">KONTAK</a></li>
                    <li><a href="tentang.html">TENTANG KAMI</a></li>
                </ul>
            </nav>
            <div class="icons">
                <a href="keranjang.php" class="cart-icon">
                    <i class="fas fa-shopping-cart"></i>
                    <?php
                    // Count total items in cart
                    $totalItems = 0;
                    if(isset($_SESSION['keranjang']) && !empty($_SESSION['keranjang'])) {
                        foreach($_SESSION['keranjang'] as $id => $qty) {
                            if(!empty($id)) {
                                $totalItems += $qty;
                            }
                        }
                    }
                    
                    // Only show badge if there are items
                    if($totalItems > 0) {
                        echo '<span class="cart-badge">' . $totalItems . '</span>';
                    }
                    ?>
                </a>
                <a href="profil.html"><i class="fas fa-user"></i></a>
            </div>
        </div>
    </header>

    <main class="discount-section">
        <div class="container">
            <h1 class="discount-title">Pilih Diskon</h1>
            <p class="discount-subtitle">Pilih diskon yang ingin Anda gunakan untuk pesanan Anda</p>
            
            <div class="discount-container">
                <?php
                // Query available discounts
                $query = $koneksi->query("SELECT * FROM diskon WHERE status='aktif' ORDER BY nilai_diskon DESC");
                
                if($query->num_rows > 0) {
                    while($diskon = $query->fetch_assoc()) {
                        // Format discount value
                        if($diskon['tipe_diskon'] == 'persen') {
                            $nilai_diskon = $diskon['nilai_diskon'] . '%';
                        } else {
                            $nilai_diskon = 'Rp ' . number_format($diskon['nilai_diskon'], 0, ',', '.');
                        }
                        
                        // Create redirect URL
                        $apply_url = "diskon.php?apply=" . $diskon['kode_diskon'];
                        if($redirect == 'checkout_item' && !empty($id_produk)) {
                            $apply_url .= "&redirect=checkout_item&id_produk=$id_produk";
                        } else {
                            $apply_url .= "&redirect=keranjang.php";
                        }
                ?>
                <div class="discount-card">
                    <div class="discount-value">
                        <span><?php echo $nilai_diskon; ?></span>
                    </div>
                    <div class="discount-info">
                        <h3><?php echo $diskon['nama_diskon']; ?></h3>
                        <p class="discount-code"><?php echo $diskon['kode_diskon']; ?></p>
                        <p class="discount-desc"><?php echo $diskon['deskripsi']; ?></p>
                        <p class="discount-expiry">Berlaku hingga: <?php echo date('d M Y', strtotime($diskon['tanggal_berakhir'])); ?></p>
                    </div>
                    <div class="discount-action">
                        <a href="<?php echo $apply_url; ?>" class="apply-discount-btn">Pakai</a>
                    </div>
                </div>
                <?php
                    }
                } else {
                    echo '<div class="no-discount">Tidak ada diskon yang tersedia saat ini.</div>';
                }
                ?>
                
                <!-- Example static discount cards for display purposes -->
                <div class="discount-card">
                    <div class="discount-value">
                        <span>10%</span>
                    </div>
                    <div class="discount-info">
                        <h3>Diskon Pelanggan Baru</h3>
                        <p class="discount-code">NEWCUST10</p>
                        <p class="discount-desc">Diskon 10% untuk pelanggan baru. Minimal pembelian Rp 100.000.</p>
                        <p class="discount-expiry">Berlaku hingga: 31 Des 2023</p>
                    </div>
                    <div class="discount-action">
                        <a href="#" class="apply-discount-btn">Pakai</a>
                    </div>
                </div>
                
                <div class="discount-card">
                    <div class="discount-value">
                        <span>Rp 50.000</span>
                    </div>
                    <div class="discount-info">
                        <h3>Diskon Akhir Tahun</h3>
                        <p class="discount-code">YEAREND50</p>
                        <p class="discount-desc">Potongan Rp 50.000 untuk pembelian minimal Rp 300.000.</p>
                        <p class="discount-expiry">Berlaku hingga: 31 Des 2023</p>
                    </div>
                    <div class="discount-action">
                        <a href="#" class="apply-discount-btn">Pakai</a>
                    </div>
                </div>
                
                <div class="discount-card">
                    <div class="discount-value">
                        <span>15%</span>
                    </div>
                    <div class="discount-info">
                        <h3>Diskon Member</h3>
                        <p class="discount-code">MEMBER15</p>
                        <p class="discount-desc">Diskon khusus untuk member. Berlaku untuk semua produk.</p>
                        <p class="discount-expiry">Berlaku hingga: 31 Des 2023</p>
                    </div>
                    <div class="discount-action">
                        <a href="#" class="apply-discount-btn">Pakai</a>
                    </div>
                </div>
            </div>
            
            <div class="back-container">
                <a href="<?php echo $redirect == 'checkout_item' && !empty($id_produk) ? 'checkout_item.php?id_produk='.$id_produk : 'keranjang.php'; ?>" class="back-btn">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
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
        <br><br><br><br>
        
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
                        <li><a href="tentang.html">Tentang Kami</a></li>
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

    <style>
        /* Cart Badge Styles */
        .cart-icon {
            position: relative;
            display: inline-block;
        }
        
        .cart-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background-color: #dc3545;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
        }
        
        /* Discount Page Styles */
        .discount-section {
            padding: 60px 0;
        }
        
        .discount-title {
            font-size: 1.75rem;
            text-align: center;
            margin-bottom: 10px;
        }
        
        .discount-subtitle {
            text-align: center;
            color: var(--text-muted);
            margin-bottom: 40px;
        }
        
        .discount-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        
        .discount-card {
            display: flex;
            background-color: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: relative;
        }
        
        .discount-card::before {
            content: '';
            position: absolute;
            top: 0;
            bottom: 0;
            left: 80px;
            width: 1px;
            background: repeating-linear-gradient(
                to bottom,
                #e5e5e5,
                #e5e5e5 5px,
                transparent 5px,
                transparent 10px
            );
        }
        
        .discount-value {
            width: 80px;
            background-color: var(--primary-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 1.2rem;
            padding: 15px 0;
        }
        
        .discount-info {
            flex: 1;
            padding: 15px;
        }
        
        .discount-info h3 {
            margin-bottom: 5px;
            font-size: 1.1rem;
        }
        
        .discount-code {
            color: var(--primary-color);
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .discount-desc {
            color: var(--text-muted);
            font-size: 0.9rem;
            margin-bottom: 10px;
        }
        
        .discount-expiry {
            color: #dc3545;
            font-size: 0.8rem;
        }
        
        .discount-action {
            display: flex;
            align-items: center;
            padding: 0 15px;
        }
        
        .apply-discount-btn {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .apply-discount-btn:hover {
            background-color: #7BC89A;
        }
        
        .no-discount {
            grid-column: 1 / -1;
            text-align: center;
            padding: 30px;
            background-color: #f8f9fa;
            border-radius: 10px;
            color: var(--text-muted);
        }
        
        .back-container {
            text-align: center;
        }
        
        .back-btn {
            display: inline-flex;
            align-items: center;
            padding: 10px 20px;
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 5px;
            color: var(--text-muted);
            transition: all 0.3s ease;
        }
        
        .back-btn:hover {
            background-color: #e9ecef;
            color: var(--dark-color);
        }
        
        .back-btn i {
            margin-right: 8px;
        }
        
        /* Responsive Styles */
        @media (max-width: 768px) {
            .discount-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</body>
</html>

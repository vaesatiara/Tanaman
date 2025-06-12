<?php
session_start();
include "koneksi.php";

// Handle adding product to cart if ID is provided in URL
if(isset($_GET['id_produk'])) {
    $id_produk = $_GET['id_produk'];
    
    // If product doesn't exist in cart, initialize it
    if(!isset($_SESSION['keranjang'][$id_produk])) {
        $_SESSION['keranjang'][$id_produk] = 1;
    } else {
        // If product exists, increment quantity
        $_SESSION['keranjang'][$id_produk] += 1;
    }
    
    // Remove empty entries if any
    unset($_SESSION['keranjang']['']);
    
    // Redirect back to cart page to prevent duplicate additions on refresh
    header("Location: keranjang.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang Belanja - Toko Tanaman</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                <a href="keranjang.php" class="cart-icon active">
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

    <main class="cart-section">
        <div class="container">
            <h1 class="cart-title">Keranjang Belanja</h1>
            <p class="cart-subtitle">Tinjau item yang Anda tambahkan ke keranjang</p>
            
            <div class="cart-container">
                <div class="cart-items">
                    <div class="cart-header">
                        <div class="header-product">Produk</div>
                        <div class="header-name">Nama</div>
                        <div class="header-price">Harga</div>
                        <div class="header-quantity">Jumlah</div>
                        <div class="header-subtotal">Subtotal</div>
                        <div class="header-remove"></div>
                    </div>
                    
                    <?php
                    // Initialize total
                    $totalHarga = 0;
                    
                    // Check if cart exists and is not empty
                    if(isset($_SESSION['keranjang']) && !empty($_SESSION['keranjang'])) {
                        foreach ($_SESSION['keranjang'] as $id_produk => $jumlah) {
                            // Skip empty product IDs
                            if(empty($id_produk)) continue;
                            
                            $ambil = $koneksi->query("SELECT * FROM produk WHERE id_produk='$id_produk'");
                            $pecah = $ambil->fetch_assoc();
                            
                            // Calculate subtotal for this item
                            $subtotal = $pecah['harga'] * $jumlah;
                            $totalHarga += $subtotal;
                    ?>
                    <div class="cart-item">
                        <div class="product-info">
                            <img src="/admin/Admin_WebTanaman/uploads/<?php echo $pecah['foto']; ?>" class="product-image" alt="<?php echo $pecah['nama_tanaman']; ?>">
                            <div class="product-details">
                                <h3><?php echo $pecah['nama_tanaman']; ?></h3>
                                <p class="size">Ukuran: Sedang</p>
                                <a href="checkout_item.php?id_produk=<?php echo $id_produk; ?>" class="checkout-item-btn">
                                    <i class="fas fa-shopping-bag"></i> Checkout Produk Ini
                                </a>
                            </div>
                        </div>
                        <div class="item-price">
                            <span class="harga" data-harga="<?php echo $pecah['harga']; ?>">
                                Rp <?php echo number_format($pecah['harga'], 0, ',', '.'); ?>
                            </span>
                        </div>
                        <div class="item-quantity">
                            <div class="quantity-control" data-idproduk="<?php echo $id_produk; ?>">
                                <button type="button" class="btn-minus">-</button>
                                <input type="text" name="jumlah" value="<?php echo $jumlah; ?>" readonly>
                                <button type="button" class="btn-plus">+</button>
                            </div>
                        </div>
                        <div class="item-subtotal subtotal">
                            Rp <?php echo number_format($subtotal, 0, ',', '.'); ?>
                        </div>
                        <div class="item-remove">
                            <a href="hapusK.php?id_produk=<?php echo $id_produk; ?>" class="btn-delete">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
                    </div>
                    <?php 
                        }
                    } else {
                        echo "<div class='empty-cart-message'>Keranjang belanja Anda kosong</div>";
                    }
                    ?>
                    
                    <div class="cart-actions">
                        <a href="produk.php" class="continue-shopping-btn">
                            <i class="fas fa-arrow-left"></i> Lanjutkan Belanja
                        </a>
                        <a href="hapus_keranjang.php" class="empty-cart-btn">
                            <i class="fas fa-trash"></i> Kosongkan Keranjang
                        </a>
                    </div>
                </div>
                
                <div class="order-summary">
                      
                    <h2 class="summary-title">Ringkasan Pesanan</h2>
                    
                    
                    <div class="summary-row">
                        <span>Subtotal</span>
                        <span>Rp <?php echo number_format($totalHarga, 0, ',', '.'); ?></span>
                    </div>
                    
                    <div class="summary-row">
                        <span>Diskon</span>
                        <span>- Rp 0</span>
                    </div>
                    
                    <?php
                 
                    $biayaPengiriman = 25000;
                    $totalBayar = $totalHarga + $biayaPengiriman;
                    ?>
                    
                    <div class="summary-row">
                        
                        <span>Estimasi Pengiriman</span>
                        <span>Rp <?php echo number_format($biayaPengiriman, 0, ',', '.'); ?></span>
                    </div>
                    
                    <div class="summary-row total">
                        <span>Total</span>
                        <span>Rp <?php echo number_format($totalBayar, 0, ',', '.'); ?></span>
                    </div>
                    
                    <div class="promo-section">
                        <h3 class="promo-title">Kode Promo</h3>
                        <a href="diskon.php" class="promo-link">
                            <div class="promo-banner">
                                <i class="fas fa-ticket-alt"></i>
                                <span>Lihat Diskon Tersedia</span>
                                <i class="fas fa-chevron-right"></i>
                            </div>
                        </a>
                    </div>
                    
                    <a href="<?php echo !empty($_SESSION['keranjang']) ? 'alamat_pengiriman.php' : 'javascript:void(0)'; ?>" 
                       class="checkout-btn <?php echo empty($_SESSION['keranjang']) ? 'disabled' : ''; ?>">
                        Lanjutkan ke Pembayaran
                    </a>
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
         <br>
         <br>
         <br>
         <br>  
        
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

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Add event listeners to all plus buttons
        document.querySelectorAll('.btn-plus').forEach(function(btn) {
            btn.addEventListener('click', function() {
                ubahJumlah(this, 1);
            });
        });

        // Add event listeners to all minus buttons
        document.querySelectorAll('.btn-minus').forEach(function(btn) {
            btn.addEventListener('click', function() {
                ubahJumlah(this, -1);
            });
        });
    });

    function ubahJumlah(btn, delta) {
        // Get the container and necessary elements
        const container = btn.closest('.quantity-control');
        const input = container.querySelector('input[name="jumlah"]');
        const idProduk = container.getAttribute('data-idproduk');
        
        // Find the price and subtotal elements
        const itemContainer = btn.closest('.cart-item');
        const hargaEl = itemContainer.querySelector('.harga');
        const subtotalEl = itemContainer.querySelector('.subtotal');

        // Get the current values
        const harga = parseInt(hargaEl.getAttribute('data-harga'));
        let jumlah = parseInt(input.value) || 0;

        // Update quantity (minimum 1)
        jumlah += delta;
        if (jumlah < 1) jumlah = 1;
        
        // Update the input field
        input.value = jumlah;

        // Calculate and update subtotal
        const subtotal = harga * jumlah;
        subtotalEl.textContent = "Rp " + subtotal.toLocaleString('id-ID');

        // Send AJAX request to update the cart in the session and database
        fetch('update_keranjang.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'id_produk=' + idProduk + '&jumlah=' + jumlah
        })
        .then(response => response.text())
        .then(data => {
            console.log("Update berhasil:", data);
            
            // Optionally update the order summary without page refresh
            // This would require additional code to recalculate totals
        })
        .catch(error => {
            console.error("Gagal update:", error);
        });
    }
    </script>

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
        
        /* Checkout Item Button */
        .checkout-item-btn {
            display: inline-block;
            margin-top: 10px;
            padding: 5px 10px;
            background-color: var(--primary-color);
            color: white;
            border-radius: 4px;
            font-size: 12px;
            transition: all 0.3s ease;
        }
        
        .checkout-item-btn:hover {
            background-color: #7BC89A;
            transform: translateY(-2px);
        }
        
        .checkout-item-btn i {
            margin-right: 5px;
        }

        /* Promo Link Styles */
        .promo-link {
            text-decoration: none;
            display: block;
        }

        .promo-banner {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            background-color: #f8f9fa;
            border: 1px dashed var(--primary-color);
            border-radius: var(--border-radius);
            color: var(--dark-color);
            transition: all 0.3s ease;
        }

        .promo-banner:hover {
            background-color: #e8f5ee;
        }

        .promo-banner i:first-child {
            color: var(--primary-color);
            margin-right: 10px;
        }

        .promo-banner span {
            flex: 1;
        }

        .promo-banner i:last-child {
            color: var(--text-muted);
        }
    </style>
</body>
</html>

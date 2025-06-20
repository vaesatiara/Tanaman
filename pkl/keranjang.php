<?php
session_start();
include "koneksi.php";

// Handle adding product to cart if ID is provided in URL
if(isset($_GET['id_produk'])) {
    $id_produk = $_GET['id_produk'];
    
    // If user is logged in, save to database as well
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        
        // Check if product already exists in database cart
        $check_cart = $koneksi->prepare("SELECT * FROM keranjang WHERE id_user = ? AND id_produk = ?");
        $check_cart->bind_param("ss", $user_id, $id_produk);
        $check_cart->execute();
        $result = $check_cart->get_result();
        
        if ($result->num_rows > 0) {
            // Update quantity
            $update_cart = $koneksi->prepare("UPDATE keranjang SET jumlah = jumlah + 1 WHERE id_user = ? AND id_produk = ?");
            $update_cart->bind_param("ss", $user_id, $id_produk);
            $update_cart->execute();
        } else {
            // Insert new item
            $insert_cart = $koneksi->prepare("INSERT INTO keranjang (id_user, id_produk, jumlah) VALUES (?, ?, 1)");
            $insert_cart->bind_param("ss", $user_id, $id_produk);
            $insert_cart->execute();
        }
    }
    
    // Also update session cart
    if(!isset($_SESSION['keranjang'][$id_produk])) {
        $_SESSION['keranjang'][$id_produk] = 1;
    } else {
        $_SESSION['keranjang'][$id_produk] += 1;
    }
    
    unset($_SESSION['keranjang']['']);
    header("Location: keranjang.php");
    exit;
}

// Load cart from database if user is logged in
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $cart_query = $koneksi->prepare("SELECT id_produk, jumlah FROM keranjang WHERE id_user = ?");
    $cart_query->bind_param("s", $user_id);
    $cart_query->execute();
    $cart_result = $cart_query->get_result();
    
    // Sync database cart with session cart
    $_SESSION['keranjang'] = array();
    while ($cart_item = $cart_result->fetch_assoc()) {
        $_SESSION['keranjang'][$cart_item['id_produk']] = $cart_item['jumlah'];
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang Belanja - Toko Tanaman</title>
    <link rel="stylesheet" href="css/keranjang.css">
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
                <a href="keranjang.php" class="cart-icon active">
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
                <a href="profil.html"><i class="fas fa-user"></i></a>
            </div>
        </div>
    </header>

    <main class="cart-section">
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
                    $totalHarga = 0;
                    
                    if(isset($_SESSION['keranjang']) && !empty($_SESSION['keranjang'])) {
                        foreach ($_SESSION['keranjang'] as $id_produk => $jumlah) {
                            if(empty($id_produk)) continue;
                            
                            $ambil = $koneksi->query("SELECT * FROM produk WHERE id_produk='$id_produk'");
                            $pecah = $ambil->fetch_assoc();
                            
                            if (!$pecah) continue; // Skip if product not found
                            
                            $subtotal = $pecah['harga'] * $jumlah;
                            $totalHarga += $subtotal;
                    ?>
                    <div class="cart-item">
                        <div class="product-info">
                            <img src="/admin/Admin_WebTanaman/uploads/<?php echo $pecah['foto']; ?>" class="product-image" alt="<?php echo $pecah['nama_tanaman']; ?>">
                            <div class="product-details">
                                <h3><?php echo $pecah['nama_tanaman']; ?></h3>
                                <a href="alamat_pengiriman.php?source=individual_checkout&id_produk=<?php echo $id_produk; ?>&qty=<?php echo $jumlah; ?>" class="checkout-item-btn">
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
                    
                    <!-- Updated checkout button to pass cart source -->
                    <a href="<?php echo !empty($_SESSION['keranjang']) ? 'alamat_pengiriman.php?source=cart' : 'javascript:void(0)'; ?>" 
                       class="checkout-btn <?php echo empty($_SESSION['keranjang']) ? 'disabled' : ''; ?>">
                        Lanjutkan ke Pembayaran
                    </a>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer remains the same -->
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
                        <li><a href="tanaman_hias_daun.php">Tanaman Hias Daun</a></li>
                        <li><a href="tanaman_hias_bunga.php">Tanaman Hias Bunga</a></li>
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
            document.querySelectorAll('.btn-plus').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    ubahJumlah(this, 1);
                });
            });

            document.querySelectorAll('.btn-minus').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    ubahJumlah(this, -1);
                });
            });
        });

        function ubahJumlah(btn, delta) {
            const container = btn.closest('.quantity-control');
            const input = container.querySelector('input[name="jumlah"]');
            const idProduk = container.getAttribute('data-idproduk');
            
            const itemContainer = btn.closest('.cart-item');
            const hargaEl = itemContainer.querySelector('.harga');
            const subtotalEl = itemContainer.querySelector('.subtotal');

            const harga = parseInt(hargaEl.getAttribute('data-harga'));
            let jumlah = parseInt(input.value) || 0;

            jumlah += delta;
            if (jumlah < 1) jumlah = 1;
            
            input.value = jumlah;

            const subtotal = harga * jumlah;
            subtotalEl.textContent = "Rp " + subtotal.toLocaleString('id-ID');

            // Update both session and database
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
                // Update summary totals
                updateSummaryTotals();
            })
            .catch(error => {
                console.error("Gagal update:", error);
            });
        }

        function updateSummaryTotals() {
            let subtotal = 0;
            document.querySelectorAll('.subtotal').forEach(function(el) {
                const value = el.textContent.replace(/[^\d]/g, '');
                subtotal += parseInt(value) || 0;
            });
            
            const shipping = 25000;
            const total = subtotal + shipping;
            
            // Update summary display
            document.querySelector('.summary-row:first-child span:last-child').textContent = 
                'Rp ' + subtotal.toLocaleString('id-ID');
            document.querySelector('.summary-row.total span:last-child').textContent = 
                'Rp ' + total.toLocaleString('id-ID');
        }
    </script>
</body>
</html>
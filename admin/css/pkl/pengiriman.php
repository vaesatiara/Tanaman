<?php
session_start();
include "koneksi.php";

// Fungsi untuk mendapatkan data pesanan
function getOrderData($source = 'cart', $product_id = null, $quantity = 1) {
    global $koneksi;
    $orderItems = [];
    $totalHarga = 0;
    
    if ($source === 'cart') {
        // Ambil dari keranjang
        if (isset($_SESSION['keranjang']) && !empty($_SESSION['keranjang'])) {
            foreach ($_SESSION['keranjang'] as $id_produk => $jumlah) {
                if (empty($id_produk)) continue;
                
                $query = $koneksi->query("SELECT * FROM produk WHERE id_produk='$id_produk'");
                $produk = $query->fetch_assoc();
                
                if ($produk) {
                    $subtotal = $produk['harga'] * $jumlah;
                    $orderItems[] = [
                        'id_produk' => $id_produk,
                        'nama_tanaman' => $produk['nama_tanaman'],
                        'harga' => $produk['harga'],
                        'foto' => $produk['foto'],
                        'jumlah' => $jumlah,
                        'subtotal' => $subtotal
                    ];
                    $totalHarga += $subtotal;
                }
            }
        }
    } else if ($source === 'buy_now' && $product_id) {
        // Ambil dari beli sekarang
        $query = $koneksi->query("SELECT * FROM produk WHERE id_produk='$product_id'");
        $produk = $query->fetch_assoc();
        
        if ($produk) {
            $subtotal = $produk['harga'] * $quantity;
            $orderItems[] = [
                'id_produk' => $product_id,
                'nama_tanaman' => $produk['nama_tanaman'],
                'harga' => $produk['harga'],
                'foto' => $produk['foto'],
                'jumlah' => $quantity,
                'subtotal' => $subtotal
            ];
            $totalHarga = $subtotal;
        }
    }
    
    return [
        'items' => $orderItems,
        'subtotal' => $totalHarga
    ];
}

// Fungsi untuk mendapatkan nama metode pengiriman
function getShippingName($method = 'jne') {
    $shippingNames = [
        'jne' => 'JNE Regular',
        'jnt' => 'J&T Express',
        'ninja' => 'Ninja Xpress',
        'anteraja' => 'AnterAja Regular'
    ];
    
    return isset($shippingNames[$method]) ? $shippingNames[$method] : 'JNE Regular';
}

// Fungsi untuk mendapatkan nama metode pembayaran
function getPaymentMethodName($method) {
    $paymentNames = [
        'bca' => 'Transfer Bank BCA',
        'bni' => 'Transfer Bank BNI', 
        'mandiri' => 'Transfer Bank Mandiri',
        'gopay' => 'GoPay',
        'ovo' => 'OVO',
        'dana' => 'DANA'
    ];
    
    return isset($paymentNames[$method]) ? $paymentNames[$method] : 'Tidak diketahui';
}

// Ambil data dari SESSION yang disimpan dari metode_pembayaran.php
$orderData = null;
$shippingAddress = null;
$shippingName = 'JNE Regular';
$shippingCost = 25000;
$paymentMethod = 'Tidak diketahui';
$total = 0;

if (isset($_SESSION['order_data'])) {
    $sessionData = $_SESSION['order_data'];
    
    // Ambil data pesanan dari session
    $orderData = [
        'items' => $sessionData['order_items'],
        'subtotal' => $sessionData['subtotal']
    ];
    
    // Ambil alamat pengiriman
    if (isset($sessionData['shipping_address_id'])) {
        $address_id = $sessionData['shipping_address_id'];
        $query = $koneksi->query("SELECT * FROM pengiriman WHERE id_pengiriman='$address_id'");
        $shippingAddress = $query->fetch_assoc();
    }
    
    // Ambil data pengiriman dan pembayaran
    $shippingName = getShippingName($sessionData['shipping_method']);
    $shippingCost = $sessionData['shipping_cost'];
    $paymentMethod = getPaymentMethodName($sessionData['payment_method']);
    $total = $sessionData['total_amount'];
    
} else {
    // Fallback jika tidak ada data di session
    // Redirect kembali ke halaman awal
    header("Location: keranjang.php");
    exit();
}

// Generate nomor pesanan unik
$nomor_pesanan = 'TN' . date('Ymd') . rand(1000, 9999);

// Simpan pesanan ke database (opsional)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_order'])) {
    // Insert ke tabel pesanan
    $insert_pesanan = $koneksi->prepare("INSERT INTO pesanan (nomor_pesanan, total_harga, status_pesanan, metode_pembayaran, tgl_pesanan) VALUES (?, ?, 'Menunggu Pembayaran', ?, NOW())");
    $insert_pesanan->bind_param("sis", $nomor_pesanan, $total, $paymentMethod);
    
    if ($insert_pesanan->execute()) {
        // Hapus keranjang jika dari cart
        if ($sessionData['source'] === 'cart') {
            unset($_SESSION['keranjang']);
        }
        
        // Simpan nomor pesanan ke session untuk tracking
        $_SESSION['last_order'] = $nomor_pesanan;
        
        // Clear order data dari session
        unset($_SESSION['order_data']);
        
        // Redirect ke halaman sukses atau tracking
        header("Location: status_pesanan.php?order=" . $nomor_pesanan);
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Pesanan - Toko Tanaman</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <header>
        <div class="container">
            <div class="logo">
                <a href="index.php">
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
                <a href="profil.php"><i class="fas fa-user"></i></a>
            </div>
        </div>
    </header>

    <main class="checkout-section">
        <div class="container">
            <div class="checkout-steps">
                <div class="step completed">
                    <div class="step-number"><i class="fas fa-check"></i></div>
                    <div class="step-label">Alamat</div>
                </div>
                <div class="step completed">
                    <div class="step-number"><i class="fas fa-check"></i></div>
                    <div class="step-label">Pengiriman</div>
                </div>
                <div class="step completed">
                    <div class="step-number"><i class="fas fa-check"></i></div>
                    <div class="step-label">Pembayaran</div>
                </div>
                <div class="step active">
                    <div class="step-number">4</div>
                    <div class="step-label">Konfirmasi</div>
                </div>
            </div>
            
            <div class="checkout-content">
                <div class="confirmation-container">
                    <div class="confirmation-header">
                        <div class="success-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <h1>Konfirmasi Pesanan</h1>
                        <p>Periksa kembali detail pesanan Anda sebelum melanjutkan pembayaran</p>
                    </div>
                    
                    <div class="confirmation-details">
                        <div class="detail-section">
                            <h2>Informasi Pengiriman</h2>
                            <?php if ($shippingAddress): ?>
                            <div class="address-info">
                                <p class="address-name"><strong><?= htmlspecialchars($shippingAddress['nama_penerima']) ?></strong> (<?= htmlspecialchars($shippingAddress['label_alamat']) ?>)</p>
                                <p class="address-phone"><?= htmlspecialchars($shippingAddress['no_telepon']) ?></p>
                                <p class="address-detail"><?= htmlspecialchars($shippingAddress['alamat_lengkap']) ?>, <?= htmlspecialchars($shippingAddress['kecamatan']) ?>, <?= htmlspecialchars($shippingAddress['kota']) ?>, <?= htmlspecialchars($shippingAddress['provinsi']) ?></p>
                            </div>
                            <div class="shipping-method">
                                <p><strong>Metode Pengiriman:</strong> <?= $shippingName ?></p>
                                <p><strong>Biaya Pengiriman:</strong> Rp<?= number_format($shippingCost, 0, ',', '.') ?></p>
                            </div>
                            <?php else: ?>
                            <p>Alamat pengiriman tidak ditemukan.</p>
                            <?php endif; ?>
                        </div>
                        
                        <div class="detail-section">
                            <h2>Metode Pembayaran</h2>
                            <p><strong><?= $paymentMethod ?></strong></p>
                        </div>
                        
                        <div class="detail-section">
                            <h2>Detail Pesanan</h2>
                            <?php if ($orderData && !empty($orderData['items'])): ?>
                            <div class="order-items">
                                <?php foreach ($orderData['items'] as $item): ?>
                                <div class="order-item">
                                    <img src="uploads/<?= htmlspecialchars($item['foto']) ?>" alt="<?= htmlspecialchars($item['nama_tanaman']) ?>" class="item-image">
                                    <div class="item-info">
                                        <h3><?= htmlspecialchars($item['nama_tanaman']) ?></h3>
                                        <p><?= $item['jumlah'] ?> x Rp<?= number_format($item['harga'], 0, ',', '.') ?></p>
                                    </div>
                                    <div class="item-price">Rp<?= number_format($item['subtotal'], 0, ',', '.') ?></div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <div class="order-summary">
                                <div class="summary-row">
                                    <span>Subtotal</span>
                                    <span>Rp<?= number_format($orderData['subtotal'], 0, ',', '.') ?></span>
                                </div>
                                <div class="summary-row">
                                    <span>Pengiriman</span>
                                    <span>Rp<?= number_format($shippingCost, 0, ',', '.') ?></span>
                                </div>
                                <div class="summary-row total">
                                    <span><strong>Total</strong></span>
                                    <span><strong>Rp<?= number_format($total, 0, ',', '.') ?></strong></span>
                                </div>
                            </div>
                            <?php else: ?>
                            <p>Tidak ada item dalam pesanan.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="confirmation-actions">
                        <a href="metode_pembayaran.php" class="btn btn-outline">
                            <i class="fas fa-arrow-left"></i> Kembali ke Pembayaran
                        </a>
                        
                        <form action="" method="post" style="display: inline;">
                            <input type="hidden" name="confirm_order" value="1">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-check"></i> Konfirmasi Pesanan
                            </button>
                        </form>
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
</body>
</html>
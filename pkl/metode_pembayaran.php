<?php
session_start();
include "koneksi.php";

// Fungsi untuk mendapatkan data pesanan (diambil dari metode_pengiriman.php)
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

// Fungsi untuk mendapatkan alamat pengiriman yang dipilih (diambil dari metode_pengiriman.php)
function getSelectedAddress() {
    global $koneksi;
    
    // Jika ada alamat yang dipilih dari form sebelumnya
    if (isset($_POST['shipping_address_id'])) {
        $address_id = $_POST['shipping_address_id'];
        $query = $koneksi->query("SELECT * FROM pengiriman WHERE id_pengiriman='$address_id'");
        return $query->fetch_assoc();
    }
    
    // Jika tidak ada, ambil alamat default atau yang pertama
    $query = $koneksi->query("SELECT * FROM pengiriman ORDER BY id_pengiriman DESC LIMIT 1");
    return $query->fetch_assoc();
}

// Fungsi untuk menghitung biaya pengiriman berdasarkan metode (diambil dari metode_pengiriman.php)
function getShippingCost($method = 'jne') {
    $shippingCosts = [
        'jne' => 25000,
        'jnt' => 30000,
        'ninja' => 28000,
        'anteraja' => 26000
    ];
    
    return isset($shippingCosts[$method]) ? $shippingCosts[$method] : 25000;
}

// Fungsi untuk mendapatkan nama metode pengiriman (diambil dari metode_pengiriman.php)
function getShippingName($method = 'jne') {
    $shippingNames = [
        'jne' => 'JNE Regular',
        'jnt' => 'J&T Express',
        'ninja' => 'Ninja Xpress',
        'anteraja' => 'AnterAja Regular'
    ];
    
    return isset($shippingNames[$method]) ? $shippingNames[$method] : 'JNE Regular';
}

// Ambil data dari POST (dikirim dari metode_pengiriman.php)
$source = isset($_POST['order_source']) ? $_POST['order_source'] : 'cart';
$product_id = isset($_POST['product_id']) ? $_POST['product_id'] : null;
$quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
$shipping_address_id = isset($_POST['shipping_address_id']) ? $_POST['shipping_address_id'] : null;
$selectedShipping = isset($_POST['shipping_method']) ? $_POST['shipping_method'] : 'jne';
$shippingCost = isset($_POST['shipping_cost']) ? (int)$_POST['shipping_cost'] : getShippingCost($selectedShipping);
$total_amount = isset($_POST['total_amount']) ? (int)$_POST['total_amount'] : 0;

// Ambil data pesanan menggunakan fungsi yang sama dari metode_pengiriman.php
$orderData = getOrderData($source, $product_id, $quantity);

// Ambil alamat pengiriman yang dipilih
$shippingAddress = null;
if ($shipping_address_id) {
    $query = $koneksi->query("SELECT * FROM pengiriman WHERE id_pengiriman='$shipping_address_id'");
    $shippingAddress = $query->fetch_assoc();
} else {
    $shippingAddress = getSelectedAddress();
}

// Hitung ulang jika data tidak ada
if ($total_amount == 0) {
    $shippingCost = getShippingCost($selectedShipping);
    $total_amount = $orderData['subtotal'] + $shippingCost;
}

$shippingName = getShippingName($selectedShipping);

// Proses jika form pembayaran disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['payment_method'])) {
    $payment_method = $_POST['payment_method'];
    
    // Simpan ke session untuk halaman konfirmasi
    $_SESSION['order_data'] = [
        'source' => $source,
        'product_id' => $product_id,
        'quantity' => $quantity,
        'shipping_address_id' => $shipping_address_id,
        'shipping_method' => $selectedShipping,
        'shipping_cost' => $shippingCost,
        'payment_method' => $payment_method,
        'total_amount' => $total_amount,
        'order_items' => $orderData['items'],
        'subtotal' => $orderData['subtotal']
    ];
    
    // Redirect ke halaman konfirmasi
    header("Location: konfirmasi_pesanan.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Metode Pembayaran - Toko Tanaman</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .payment-method-summary {
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 15px;
            margin: 15px 0;
            display: none;
        }
        
        .payment-method-summary.show {
            display: block;
        }
        
        .payment-method-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .payment-method-logo {
            width: 40px;
            height: 40px;
        }
        
        .payment-method-logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }
        
        .payment-method-details h4 {
            margin: 0;
            font-size: 16px;
            color: #333;
        }
        
        .payment-method-details p {
            margin: 5px 0 0 0;
            font-size: 14px;
            color: #666;
        }
    </style>
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
                <div class="step active">
                    <div class="step-number">3</div>
                    <div class="step-label">Pembayaran</div>
                </div>
                <div class="step">
                    <div class="step-number">4</div>
                    <div class="step-label">Konfirmasi</div>
                </div>
            </div>
            
            <div class="checkout-content">
                <div class="checkout-form">
                    <h2>Metode Pembayaran</h2>
                    
                    <!-- Back to Shipping Method button -->
                    <a href="metode_pengiriman.php?source=<?= $source ?><?= $product_id ? '&id_produk='.$product_id.'&qty='.$quantity : '' ?>" class="btn btn-outline" style="margin-bottom: 20px; display: inline-block;">
                        <i class="fas fa-arrow-left"></i> Kembali ke Metode Pengiriman
                    </a>
                    
                    <form id="paymentForm" action="" method="post">
                        <!-- Hidden inputs untuk menyimpan data pesanan -->
                        <input type="hidden" name="order_source" value="<?= htmlspecialchars($source) ?>">
                        <?php if ($source === 'buy_now'): ?>
                            <input type="hidden" name="product_id" value="<?= htmlspecialchars($product_id) ?>">
                            <input type="hidden" name="quantity" value="<?= $quantity ?>">
                        <?php endif; ?>
                        <input type="hidden" name="shipping_address_id" value="<?= htmlspecialchars($shipping_address_id) ?>">
                        <input type="hidden" name="shipping_method" value="<?= htmlspecialchars($selectedShipping) ?>">
                        <input type="hidden" name="shipping_cost" value="<?= $shippingCost ?>">
                        <input type="hidden" name="total_amount" value="<?= $total_amount ?>">
                        <input type="hidden" name="payment_method" id="selectedPaymentMethod" value="">
                        
                        <div class="payment-group">
                            <h4>Transfer Bank</h4>
                            <div class="payment-options">
                                <div class="payment-option">
                                    <input type="radio" name="payment_method_radio" id="bca" value="bca" data-name="Transfer Bank BCA" data-logo="images/bca.png">
                                    <label for="bca" class="payment-label">
                                        <div class="payment-logo">
                                            <img src="images/bca.jpg" alt="BCA">
                                        </div>
                                        <div class="payment-info">
                                            <h4>Bank BCA</h4>
                                            <p>Pembayaran akan diverifikasi dalam 24 jam</p>
                                        </div>
                                    </label>
                                </div>
                                
                                <div class="payment-option">
                                    <input type="radio" name="payment_method_radio" id="bni" value="bni" data-name="Transfer Bank BNI" data-logo="images/bni.png">
                                    <label for="bni" class="payment-label">
                                        <div class="payment-logo">
                                            <img src="images/bni.jpg" alt="BNI">
                                        </div>
                                        <div class="payment-info">
                                            <h4>Bank BNI</h4>
                                            <p>Pembayaran akan diverifikasi dalam 24 jam</p>
                                        </div>
                                    </label>
                                </div>
                                
                                <div class="payment-option">
                                    <input type="radio" name="payment_method_radio" id="mandiri" value="mandiri" data-name="Transfer Bank Mandiri" data-logo="images/mandiri.png">
                                    <label for="mandiri" class="payment-label">
                                        <div class="payment-logo">
                                            <img src="images/mandiri (1).jpg" alt="Mandiri">
                                        </div>
                                        <div class="payment-info">
                                            <h4>Bank Mandiri</h4>
                                            <p>Pembayaran akan diverifikasi dalam 24 jam</p>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                       
                                    
                </div>
                
                <!-- Order Summary - DIAMBIL DARI metode_pengiriman.php -->
                <div class="order-summary">
                    <h2 class="summary-title">Ringkasan Pesanan</h2>
                    
                    <?php if (!empty($orderData['items'])): ?>
                        <div class="summary-items">
                            <?php foreach ($orderData['items'] as $item): ?>
                            <div class="summary-item">
                                <img src="/admin/Admin_WebTanaman/uploads/<?= htmlspecialchars($item['foto']) ?>" alt="<?= htmlspecialchars($item['nama_tanaman']) ?>" class="item-image">
                                <div class="item-info">
                                    <h3><?= htmlspecialchars($item['nama_tanaman']) ?></h3>
                                    <p><?= $item['jumlah'] ?> x Rp<?= number_format($item['harga'], 0, ',', '.') ?></p>
                                </div>
                                <div class="item-price">Rp<?= number_format($item['subtotal'], 0, ',', '.') ?></div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="summary-row">
                            <span>Subtotal</span>
                            <span>Rp<?= number_format($orderData['subtotal'], 0, ',', '.') ?></span>
                        </div>
                        
                        <div class="summary-row">
                            <span>Pengiriman (<?= $shippingName ?>)</span>
                            <span>Rp<?= number_format($shippingCost, 0, ',', '.') ?></span>
                        </div>
                        
                        <!-- Payment Method Summary -->
                        <div class="payment-method-summary" id="paymentMethodSummary">
                            <div class="summary-row">
                                <span>Metode Pembayaran</span>
                                <span></span>
                            </div>
                            <div class="payment-method-info" id="paymentMethodInfo">
                                <div class="payment-method-logo" id="paymentMethodLogo">
                                    <img src="#" alt="" id="paymentMethodImage">
                                </div>
                                <div class="payment-method-details">
                                    <h4 id="paymentMethodName"></h4>
                                    <p id="paymentMethodDesc"></p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="summary-row total">
                            <span>Total</span>
                            <span>Rp<?= number_format($total_amount, 0, ',', '.') ?></span>
                        </div>

                        <button type="submit" form="paymentForm" class="checkout-btn" id="paymentBtn" disabled>
                            Lanjutkan ke Konfirmasi
                        </button>
                    <?php else: ?>
                        <div class="empty-order">
                            <p>Tidak ada item dalam pesanan</p>
                            <a href="produk.php" class="btn btn-primary">Belanja Sekarang</a>
                        </div>
                    <?php endif; ?>
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
        // Get all payment options
        const paymentOptions = document.querySelectorAll('.payment-option input[type="radio"]');
        const paymentBtn = document.getElementById('paymentBtn');
        const paymentMethodSummary = document.getElementById('paymentMethodSummary');
        const paymentMethodName = document.getElementById('paymentMethodName');
        const paymentMethodImage = document.getElementById('paymentMethodImage');
        const paymentMethodDesc = document.getElementById('paymentMethodDesc');
        const selectedPaymentMethod = document.getElementById('selectedPaymentMethod');
        
        // Payment method descriptions
        const paymentDescriptions = {
            'bca': 'Transfer melalui Bank BCA',
            'bni': 'Transfer melalui Bank BNI',
            'mandiri': 'Transfer melalui Bank Mandiri',
            'gopay': 'Pembayaran melalui aplikasi Gojek',
            'ovo': 'Pembayaran melalui aplikasi OVO',
            'dana': 'Pembayaran melalui aplikasi DANA'
        };
        
        // Add event listeners to each payment option
        paymentOptions.forEach(option => {
            option.addEventListener('change', function() {
                // Add selected class to the parent div and remove from others
                document.querySelectorAll('.payment-option').forEach(opt => {
                    opt.classList.remove('selected');
                });
                this.closest('.payment-option').classList.add('selected');
                
                // Update payment method summary
                const paymentValue = this.value;
                const paymentName = this.getAttribute('data-name');
                const paymentLogo = this.getAttribute('data-logo');
                
                // Show payment method summary
                paymentMethodSummary.classList.add('show');
                
                // Update payment method details
                paymentMethodName.textContent = paymentName;
                paymentMethodImage.src = paymentLogo;
                paymentMethodImage.alt = paymentName;
                paymentMethodDesc.textContent = paymentDescriptions[paymentValue] || '';
                
                // Set hidden input value
                selectedPaymentMethod.value = paymentValue;
                
                // Enable submit button when payment method is selected
                paymentBtn.disabled = false;
                paymentBtn.style.opacity = '1';
                paymentBtn.style.cursor = 'pointer';
            });
        });
        
        // Initially disable button
        paymentBtn.style.opacity = '0.5';
        paymentBtn.style.cursor = 'not-allowed';
    });
    </script>
</body>
</html>
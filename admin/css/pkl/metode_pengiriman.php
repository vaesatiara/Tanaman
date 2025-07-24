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

// PERBAIKAN: Fungsi untuk mendapatkan alamat pengiriman yang dipilih
function getSelectedAddress() {
    global $koneksi;
    
    // Prioritas 1: Jika ada alamat yang dipilih dari form POST
    if (isset($_POST['selected_address_id'])) {
        $address_id = $_POST['selected_address_id'];
        $query = $koneksi->query("SELECT * FROM pengiriman WHERE id_pengiriman='$address_id'");
        return $query->fetch_assoc();
    }
    
    // Prioritas 2: Jika ada alamat yang tersimpan di session (PERBAIKAN INI YANG KURANG)
    if (isset($_SESSION['alamat_terpilih'])) {
        $address_id = $_SESSION['alamat_terpilih'];
        $query = $koneksi->query("SELECT * FROM pengiriman WHERE id_pengiriman='$address_id'");
        $result = $query->fetch_assoc();
        if ($result) {
            return $result;
        }
    }
    
    // Prioritas 3: Jika ada alamat dari order_data session
    if (isset($_SESSION['order_data']['shipping_address_id'])) {
        $address_id = $_SESSION['order_data']['shipping_address_id'];
        $query = $koneksi->query("SELECT * FROM pengiriman WHERE id_pengiriman='$address_id'");
        $result = $query->fetch_assoc();
        if ($result) {
            return $result;
        }
    }
    
    // Prioritas 4: Jika tidak ada, ambil alamat default atau yang pertama
    $query = $koneksi->query("SELECT * FROM pengiriman ORDER BY id_pengiriman DESC LIMIT 1");
    return $query->fetch_assoc();
}

// Fungsi untuk menghitung biaya pengiriman berdasarkan metode
function getShippingCost($method = 'jne') {
    $shippingCosts = [
        'jne' => 25000,
        'jnt' => 30000,
        'ninja' => 28000,
        'anteraja' => 26000
    ];
    
    return isset($shippingCosts[$method]) ? $shippingCosts[$method] : 25000;
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

// Tentukan sumber data berdasarkan parameter atau session
$source = 'cart'; // default
if (isset($_GET['source'])) {
    $source = $_GET['source'];
} elseif (isset($_POST['order_source'])) {
    $source = $_POST['order_source'];
} elseif (isset($_SESSION['order_data']['source'])) {
    $source = $_SESSION['order_data']['source'];
}

$product_id = null;
if (isset($_GET['id_produk'])) {
    $product_id = $_GET['id_produk'];
} elseif (isset($_POST['product_id'])) {
    $product_id = $_POST['product_id'];
} elseif (isset($_SESSION['order_data']['product_id'])) {
    $product_id = $_SESSION['order_data']['product_id'];
}

$quantity = 1;
if (isset($_GET['qty'])) {
    $quantity = (int)$_GET['qty'];
} elseif (isset($_POST['quantity'])) {
    $quantity = (int)$_POST['quantity'];
} elseif (isset($_SESSION['order_data']['quantity'])) {
    $quantity = $_SESSION['order_data']['quantity'];
}

// Ambil data pesanan
$orderData = getOrderData($source, $product_id, $quantity);

// Ambil alamat pengiriman yang dipilih
$shippingAddress = getSelectedAddress();

// Tentukan metode pengiriman (default atau dari form/session)
$selectedShipping = 'jne'; // default
if (isset($_POST['shipping_method'])) {
    $selectedShipping = $_POST['shipping_method'];
} elseif (isset($_SESSION['shipping_method'])) {
    $selectedShipping = $_SESSION['shipping_method'];
} elseif (isset($_SESSION['order_data']['shipping_method'])) {
    $selectedShipping = $_SESSION['order_data']['shipping_method'];
}

$shippingCost = getShippingCost($selectedShipping);
$shippingName = getShippingName($selectedShipping);

// Hitung total
$total = $orderData['subtotal'] + $shippingCost;

// Proses jika form metode pengiriman disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_shipping'])) {
    $selectedShipping = $_POST['shipping_method'];
    $shippingCost = getShippingCost($selectedShipping);
    $shippingName = getShippingName($selectedShipping);
    $total = $orderData['subtotal'] + $shippingCost;
    
    // Simpan ke session
    $_SESSION['shipping_method'] = $selectedShipping;
}

// Debug: Tampilkan informasi session (hapus setelah testing)
if (isset($_GET['debug'])) {
    echo "<pre>";
    echo "Session alamat_terpilih: " . (isset($_SESSION['alamat_terpilih']) ? $_SESSION['alamat_terpilih'] : 'tidak ada') . "\n";
    echo "Session order_data: ";
    print_r($_SESSION['order_data'] ?? 'tidak ada');
    echo "Shipping Address: ";
    print_r($shippingAddress);
    echo "</pre>";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ringkasan Pesanan - Toko Tanaman</title>
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
                <div class="step active">
                    <div class="step-number">2</div>
                    <div class="step-label">Pengiriman</div>
                </div>
                <div class="step">
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
                    <h2>Metode Pengiriman</h2>
                     <a href="alamat_pengiriman.php" class="btn btn-outline" style="margin-bottom: 20px; display: inline-block;">
                        <i class="fas fa-arrow-left"></i> Kembali ke Alamat Pengiriman
                    </a>
                    
                    <!-- Alamat Pengiriman -->
                    <div class="address-section">
                        <h3>Alamat Pengiriman</h3>
                        
                        <?php if ($shippingAddress): ?>
                        <div class="address-summary">
                            <div class="address-info">
                                <p class="address-name"><strong><?= htmlspecialchars($shippingAddress['nama_penerima']) ?></strong> (<?= htmlspecialchars($shippingAddress['label_alamat']) ?>)</p>
                                <p class="address-phone"><?= htmlspecialchars($shippingAddress['no_telepon']) ?></p>
                                <p class="address-detail"><?= htmlspecialchars($shippingAddress['alamat_lengkap']) ?>, <?= htmlspecialchars($shippingAddress['kecamatan']) ?>, <?= htmlspecialchars($shippingAddress['kota']) ?>, <?= htmlspecialchars($shippingAddress['provinsi']) ?></p>
                            </div>
                            <a href="alamat_pengiriman.php?source=<?= $source ?><?= $product_id ? '&id_produk='.$product_id.'&qty='.$quantity : '' ?>" class="btn btn-outline btn-sm">Ubah Alamat</a>
                        </div>
                        <?php else: ?>
                        <div class="no-address">
                            <p>Belum ada alamat pengiriman yang dipilih.</p>
                            <a href="alamat_pengiriman.php?source=<?= $source ?><?= $product_id ? '&id_produk='.$product_id.'&qty='.$quantity : '' ?>" class="btn btn-primary">Pilih Alamat</a>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Metode Pengiriman -->
                    <?php if ($shippingAddress): ?>
                    <div class="shipping-section">
                        <h3>Pilih Metode Pengiriman</h3>
                        
                        <form method="post" id="shippingForm">
                            <!-- Hidden inputs untuk menyimpan data pesanan -->
                            <input type="hidden" name="order_source" value="<?= $source ?>">
                            <?php if ($source === 'buy_now'): ?>
                                <input type="hidden" name="product_id" value="<?= $product_id ?>">
                                <input type="hidden" name="quantity" value="<?= $quantity ?>">
                            <?php endif; ?>
                            <input type="hidden" name="selected_address_id" value="<?= $shippingAddress['id_pengiriman'] ?>">
                            <input type="hidden" name="update_shipping" value="1">
                            
                            <div class="shipping-options">
                                <div class="shipping-option <?= $selectedShipping == 'jne' ? 'selected' : '' ?>">
                                    <input type="radio" name="shipping_method" id="jne" value="jne" <?= $selectedShipping == 'jne' ? 'checked' : '' ?> onchange="updateShipping()">
                                    <label for="jne" class="shipping-label">
                                        <div class="shipping-logo">
                                            <img src="images/jne (1).jpg" alt="JNE">
                                        </div>
                                        <div class="shipping-info">
                                            <h4>JNE Regular</h4>
                                            <p>Estimasi tiba 2-3 hari</p>
                                        </div>
                                        <div class="shipping-price">Rp25.000</div>
                                    </label>
                                </div>
                                
                                <div class="shipping-option <?= $selectedShipping == 'jnt' ? 'selected' : '' ?>">
                                    <input type="radio" name="shipping_method" id="jnt" value="jnt" <?= $selectedShipping == 'jnt' ? 'checked' : '' ?> onchange="updateShipping()">
                                    <label for="jnt" class="shipping-label">
                                        <div class="shipping-logo">
                                            <img src="images/j&t.jpg" alt="J&T">
                                        </div>
                                        <div class="shipping-info">
                                            <h4>J&T Express</h4>
                                            <p>Estimasi tiba 1-2 hari</p>
                                        </div>
                                        <div class="shipping-price">Rp30.000</div>
                                    </label>
                                </div>
                                
                                <div class="shipping-option <?= $selectedShipping == 'ninja' ? 'selected' : '' ?>">
                                    <input type="radio" name="shipping_method" id="ninja" value="ninja" <?= $selectedShipping == 'ninja' ? 'checked' : '' ?> onchange="updateShipping()">
                                    <label for="ninja" class="shipping-label">
                                        <div class="shipping-logo">
                                            <img src="images/ninja.jpg" alt="Ninja Xpress">
                                        </div>
                                        <div class="shipping-info">
                                            <h4>Ninja Xpress</h4>
                                            <p>Estimasi tiba 1-2 hari</p>
                                        </div>
                                        <div class="shipping-price">Rp28.000</div>
                                    </label>
                                </div>
                                
                                <div class="shipping-option <?= $selectedShipping == 'anteraja' ? 'selected' : '' ?>">
                                    <input type="radio" name="shipping_method" id="anteraja" value="anteraja" <?= $selectedShipping == 'anteraja' ? 'checked' : '' ?> onchange="updateShipping()">
                                    <label for="anteraja" class="shipping-label">
                                        <div class="shipping-logo">
                                            <img src="images/anteraja.jpg" alt="AnterAja">
                                        </div>
                                        <div class="shipping-info">
                                            <h4>AnterAja Regular</h4>
                                            <p>Estimasi tiba 2-3 hari</p>
                                        </div>
                                        <div class="shipping-price">Rp26.000</div>
                                    </label>
                                </div>
                            </div>
                        </form>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Order Summary -->
                <div class="order-summary">
                    <h2 class="summary-title">Ringkasan Pesanan</h2>
                    
                    <?php if (!empty($orderData['items'])): ?>
                        <div class="summary-items">
                            <?php foreach ($orderData['items'] as $item): ?>
                            <div class="summary-item">
                                <img src="/admin/Admin_WebTanaman/uploads/<?= $item['foto'] ?>" alt="<?= $item['nama_tanaman'] ?>" class="item-image">
                                <div class="item-info">
                                    <h3><?= $item['nama_tanaman'] ?></h3>
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
                            <span id="shipping-cost">Rp<?= number_format($shippingCost, 0, ',', '.') ?></span>
                        </div>
                        
                        <div class="summary-row total">
                            <span>Total</span>
                            <span id="total-price">Rp<?= number_format($total, 0, ',', '.') ?></span>
                        </div>
                        
                        <?php if ($shippingAddress): ?>
                        <form action="metode_pembayaran.php" method="post">
                            <!-- Hidden inputs untuk menyimpan semua data -->
                            <input type="hidden" name="order_source" value="<?= $source ?>">
                            <?php if ($source === 'buy_now'): ?>
                                <input type="hidden" name="product_id" value="<?= $product_id ?>">
                                <input type="hidden" name="quantity" value="<?= $quantity ?>">
                            <?php endif; ?>
                            <input type="hidden" name="shipping_address_id" value="<?= $shippingAddress['id_pengiriman'] ?>">
                            <input type="hidden" name="shipping_method" value="<?= $selectedShipping ?>">
                            <input type="hidden" name="shipping_cost" value="<?= $shippingCost ?>">
                            <input type="hidden" name="total_amount" value="<?= $total ?>">
                            
                            <button type="submit" class="checkout-btn">Lanjutkan ke Pembayaran</button>
                        </form>
                        <?php else: ?>
                        <p class="text-center">Silakan pilih alamat pengiriman terlebih dahulu</p>
                        <?php endif; ?>
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
        function updateShipping() {
            // Submit form otomatis ketika metode pengiriman berubah
            document.getElementById('shippingForm').submit();
        }

        // Update visual selection
        document.addEventListener('DOMContentLoaded', function() {
            const shippingOptions = document.querySelectorAll('input[name="shipping_method"]');
            
            shippingOptions.forEach(option => {
                option.addEventListener('change', function() {
                    // Remove selected class from all options
                    document.querySelectorAll('.shipping-option').forEach(opt => {
                        opt.classList.remove('selected');
                    });
                    
                    // Add selected class to current option
                    this.closest('.shipping-option').classList.add('selected');
                });
            });
        });
    </script>
</body>
</html>

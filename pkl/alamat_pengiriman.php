<?php
session_start();
include "koneksi.php";

// Periksa apakah user sudah login
if (!isset($_SESSION['username']) && !isset($_SESSION['id_pelanggan'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'] ?? '';
$id_pelanggan = $_SESSION['id_pelanggan'] ?? '';

// Enhanced debugging
error_log("=== ALAMAT PENGIRIMAN DEBUG START ===");
error_log("GET: " . print_r($_GET, true));
error_log("POST: " . print_r($_POST, true));
error_log("SESSION keranjang: " . print_r($_SESSION['keranjang'] ?? [], true));
error_log("SESSION order_data: " . print_r($_SESSION['order_data'] ?? [], true));

// Get parameters with comprehensive fallback
$source = $_GET['source'] ?? $_POST['order_source'] ?? $_SESSION['order_data']['source'] ?? 'cart';
$product_id = $_GET['id_produk'] ?? $_POST['product_id'] ?? $_SESSION['order_data']['product_id'] ?? null;
$quantity = (int)($_GET['qty'] ?? $_POST['quantity'] ?? $_SESSION['order_data']['quantity'] ?? 1);

error_log("Processed params - Source: $source, Product ID: $product_id, Quantity: $quantity");

// PERBAIKAN: Enhanced function to get complete order data with proper product ID handling
function getCompleteOrderData($id_pelanggan, $source = 'cart', $product_id = null, $quantity = 1) {
    global $koneksi;
    $orderItems = [];
    $totalHarga = 0;
    
    error_log("getCompleteOrderData called - Source: $source, Product ID: $product_id, Quantity: $quantity");
    
    if ($source === 'buy_now' && $product_id) {
        // Handle Buy Now - get specific product
        error_log("Processing BUY NOW for product ID: $product_id");
        
        $stmt = $koneksi->prepare("SELECT * FROM produk WHERE id_produk = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $produk = $result->fetch_assoc();
        
        if ($produk) {
            $subtotal = $produk['harga'] * $quantity;
            $orderItems[] = [
                'id_produk' => (int)$product_id, // PERBAIKAN: Pastikan integer
                'nama_tanaman' => $produk['nama_tanaman'],
                'harga' => (float)$produk['harga'],
                'foto' => $produk['foto'],
                'jumlah' => (int)$quantity,
                'subtotal' => (float)$subtotal,
                'source' => 'buy_now'
            ];
            $totalHarga = $subtotal;
            
            error_log("Buy now product found: " . $produk['nama_tanaman'] . " - Price: " . $produk['harga']);
        } else {
            error_log("ERROR: Product not found for ID: $product_id");
        }
        
    } else if ($source === 'cart' || $source === 'individual_checkout') {
        // Handle Cart or Individual Item Checkout
        error_log("Processing CART checkout");
        
        if ($source === 'individual_checkout' && $product_id) {
            // Individual item from cart
            error_log("Individual checkout for product: $product_id");
            
            if (isset($_SESSION['keranjang'][$product_id])) {
                $cart_quantity = (int)$_SESSION['keranjang'][$product_id];
                
                $stmt = $koneksi->prepare("SELECT * FROM produk WHERE id_produk = ?");
                $stmt->bind_param("i", $product_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $produk = $result->fetch_assoc();
                
                if ($produk) {
                    $subtotal = $produk['harga'] * $cart_quantity;
                    $orderItems[] = [
                        'id_produk' => (int)$product_id, // PERBAIKAN: Pastikan integer
                        'nama_tanaman' => $produk['nama_tanaman'],
                        'harga' => (float)$produk['harga'],
                        'foto' => $produk['foto'],
                        'jumlah' => $cart_quantity,
                        'subtotal' => (float)$subtotal,
                        'source' => 'individual_checkout'
                    ];
                    $totalHarga = $subtotal;
                }
            }
        } else {
            // Full cart checkout
            error_log("Full cart checkout");
            
            if (isset($_SESSION['keranjang']) && !empty($_SESSION['keranjang'])) {
                foreach ($_SESSION['keranjang'] as $id_produk => $jumlah) {
                    if (empty($id_produk) || $jumlah <= 0) continue;
                    
                    $stmt = $koneksi->prepare("SELECT * FROM produk WHERE id_produk = ?");
                    $stmt->bind_param("i", $id_produk);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $produk = $result->fetch_assoc();
                    
                    if ($produk) {
                        $subtotal = $produk['harga'] * $jumlah;
                        $orderItems[] = [
                            'id_produk' => (int)$id_produk, // PERBAIKAN: Pastikan integer
                            'nama_tanaman' => $produk['nama_tanaman'],
                            'harga' => (float)$produk['harga'],
                            'foto' => $produk['foto'],
                            'jumlah' => (int)$jumlah,
                            'subtotal' => (float)$subtotal,
                            'source' => 'cart'
                        ];
                        $totalHarga += $subtotal;
                        
                        error_log("Cart item added: " . $produk['nama_tanaman'] . " x " . $jumlah);
                    }
                }
            } else {
                error_log("WARNING: Cart is empty or not set");
            }
        }
    }
    
    error_log("Total items found: " . count($orderItems));
    error_log("Total amount: $totalHarga");
    error_log("=== ALAMAT PENGIRIMAN DEBUG END ===");
    
    return [
        'items' => $orderItems,
        'subtotal' => $totalHarga
    ];
}

// Get order data
$orderData = getCompleteOrderData($id_pelanggan, $source, $product_id, $quantity);

// PERBAIKAN: Enhanced session storage with complete order data
$_SESSION['order_data'] = [
    'order_items' => $orderData['items'], // PERBAIKAN: Gunakan key yang konsisten
    'subtotal' => $orderData['subtotal'],
    'source' => $source,
    'product_id' => $product_id,
    'quantity' => $quantity,
    'timestamp' => time(),
    'id_pelanggan' => $id_pelanggan // PERBAIKAN: Tambahkan ID pelanggan
];

// Shipping functions
function getShippingCost($method = 'jne') {
    $shippingCosts = [
        'jne' => 25000,
        'jnt' => 30000,
        'ninja' => 28000,
        'anteraja' => 26000
    ];
    return isset($shippingCosts[$method]) ? $shippingCosts[$method] : 25000;
}

function getShippingName($method = 'jne') {
    $shippingNames = [
        'jne' => 'JNE Regular',
        'jnt' => 'J&T Express',
        'ninja' => 'Ninja Xpress',
        'anteraja' => 'AnterAja Regular'
    ];
    return isset($shippingNames[$method]) ? $shippingNames[$method] : 'JNE Regular';
}

// Calculate totals
$selectedShipping = $_SESSION['shipping_method'] ?? 'jne';
$shippingCost = getShippingCost($selectedShipping);
$shippingName = getShippingName($selectedShipping);
$total = $orderData['subtotal'] + $shippingCost;

// PERBAIKAN: Process address selection with enhanced data preservation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pilih_alamat'])) {
    $selected_address_id = (int)$_POST['alamat_id'];
    $_SESSION['alamat_terpilih'] = $selected_address_id;
    
    // PERBAIKAN: Update session order data with complete information
    $_SESSION['order_data']['shipping_address_id'] = $selected_address_id;
    $_SESSION['order_data']['shipping_method'] = $selectedShipping;
    $_SESSION['order_data']['shipping_cost'] = $shippingCost;
    $_SESSION['order_data']['total_amount'] = $total;
    
    // PERBAIKAN: Validasi data sebelum redirect
    if (empty($_SESSION['order_data']['order_items'])) {
        $_SESSION['error_message'] = "Data pesanan tidak valid. Silakan coba lagi.";
        header("Location: " . $_SERVER['PHP_SELF'] . "?source=$source" . ($product_id ? "&id_produk=$product_id&qty=$quantity" : ""));
        exit;
    }
    
    // Log untuk debugging
    error_log("Address selected: $selected_address_id");
    error_log("Order data before redirect: " . json_encode($_SESSION['order_data']));
    
    // Redirect to next step
    header("Location: metode_pengiriman.php");
    exit;
}

// Get user addresses
$sql_alamat = "SELECT * FROM pengiriman WHERE id_pelanggan = ? ORDER BY is_primary DESC, id_pengiriman DESC";
$stmt_alamat = $koneksi->prepare($sql_alamat);
$stmt_alamat->bind_param("s", $id_pelanggan);
$stmt_alamat->execute();
$query_alamat = $stmt_alamat->get_result();
?>
p
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alamat Pengiriman - Toko Tanaman</title>
    <link rel="stylesheet" href="css/alamat_pengiriman.css">
    <link rel="stylesheet" href="css/style-additions.css">
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
            <!-- Checkout Steps -->
            <div class="checkout-steps">
                <div class="step active">
                    <div class="step-number">1</div>
                    <div class="step-label">Alamat</div>
                </div>
                <div class="step">
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
                    <h2><i class="fas fa-map-marker-alt"></i> Pilih Alamat Pengiriman</h2>
                    
                    <!-- Enhanced Source Info Banner -->
                    <div class="source-info-banner <?= $source === 'cart' ? 'cart-source' : ($source === 'individual_checkout' ? 'individual-source' : 'buy-now-source') ?>">
                        <i class="fas fa-<?= $source === 'cart' ? 'shopping-cart' : ($source === 'individual_checkout' ? 'shopping-bag' : 'bolt') ?>"></i>
                        <div class="source-details">
                            <?php if ($source === 'buy_now'): ?>
                                <strong>Beli Sekarang</strong>
                                <span>
                                    <?php if (!empty($orderData['items'])): ?>
                                        <?= htmlspecialchars($orderData['items'][0]['nama_tanaman']) ?> (<?= $quantity ?> pcs)
                                    <?php else: ?>
                                        Produk (<?= $quantity ?> pcs)
                                    <?php endif; ?>
                                </span>
                            <?php elseif ($source === 'individual_checkout'): ?>
                                <strong>Checkout Item Terpisah</strong>
                                <span>
                                    <?php if (!empty($orderData['items'])): ?>
                                        <?= htmlspecialchars($orderData['items'][0]['nama_tanaman']) ?> (<?= $orderData['items'][0]['jumlah'] ?> pcs)
                                    <?php else: ?>
                                        1 item dari keranjang
                                    <?php endif; ?>
                                </span>
                            <?php else: ?>
                                <strong>Checkout dari Keranjang Belanja</strong>
                                <span><?= count($orderData['items']) ?> item dalam keranjang</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Alert Messages -->
                    <?php if(isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <span><?= $_SESSION['success_message']; ?></span>
                        <?php unset($_SESSION['success_message']); ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if(isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <span><?= $_SESSION['error_message']; ?></span>
                        <?php unset($_SESSION['error_message']); ?>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Validation for empty order -->
                    <?php if (empty($orderData['items'])): ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <span>Tidak ada item dalam pesanan. Silakan kembali ke halaman produk.</span>
                    </div>
                    <div class="empty-order-actions">
                        <a href="produk.php" class="btn btn-primary">
                            <i class="fas fa-arrow-left"></i> Kembali ke Produk
                        </a>
                        <?php if ($source === 'cart' || $source === 'individual_checkout'): ?>
                        <a href="keranjang.php" class="btn btn-outline">
                            <i class="fas fa-shopping-cart"></i> Lihat Keranjang
                        </a>
                        <?php endif; ?>
                    </div>
                    <?php else: ?>
                    
                    <!-- Address List -->
                    <div class="address-list">
                        <?php if ($query_alamat && $query_alamat->num_rows > 0) :  ?>
                            <?php while($alamat = $query_alamat->fetch_assoc()): ?>
                            <form method="POST" class="address-form-select" id="form_<?= $alamat['id_pengiriman'] ?>">
                                <input type="hidden" name="alamat_id" value="<?= $alamat['id_pengiriman'] ?>">
                                <input type="hidden" name="order_source" value="<?= htmlspecialchars($source) ?>">
                                <?php if ($source === 'buy_now' || $source === 'individual_checkout'): ?>
                                    <input type="hidden" name="product_id" value="<?= htmlspecialchars($product_id) ?>">
                                    <input type="hidden" name="quantity" value="<?= $quantity ?>">
                                <?php endif; ?>
                                <input type="hidden" name="pilih_alamat" value="1">
                                
                                <div class="address-card <?= (isset($_SESSION['alamat_terpilih']) && $_SESSION['alamat_terpilih'] == $alamat['id_pengiriman']) ? 'selected' : '' ?>" 
                                     onclick="selectAddress(<?= $alamat['id_pengiriman'] ?>)">
                                    
                                    <?php if (isset($alamat['is_primary']) && $alamat['is_primary']): ?>
                                        <span class="address-badge">Utama</span>
                                    <?php endif; ?>
                                    
                                    <h3>
                                        <i class="fas fa-home"></i>
                                        <?= htmlspecialchars($alamat['label_alamat']) ?>
                                    </h3>
                                    
                                    <p class="address-name">
                                        <strong><?= htmlspecialchars($alamat['nama_penerima']) ?></strong>
                                    </p>
                                    
                                    <p class="address-phone">
                                        <i class="fas fa-phone"></i> 
                                        <?= htmlspecialchars($alamat['no_telepon']) ?>
                                    </p>
                                    
                                    <p class="address-detail">
                                        <i class="fas fa-map-marker-alt"></i> 
                                        <?= htmlspecialchars($alamat['alamat_lengkap'] . ', ' . $alamat['kecamatan'] . ', ' . $alamat['kota'] . ', ' . $alamat['provinsi']) ?>
                                    </p>
                                    
                                    <div class="address-actions" onclick="event.stopPropagation();">
                                        <a href="edit_alamat.php?id=<?= $alamat['id_pengiriman'] ?>" class="btn btn-outline">
                                            <i class="fas fa-edit"></i> Ubah
                                        </a>
                                        <a href="hapus_alamat.php?id_pengiriman=<?= $alamat['id_pengiriman'] ?>" 
                                           class="btn btn-outline btn-danger"
                                           onclick="return confirm('Yakin ingin menghapus alamat ini?')">
                                            <i class="fas fa-trash"></i> Hapus
                                        </a>
                                    </div>
                                    
                                    <button type="submit" name="pilih_alamat" class="use-address-btn">
                                        <i class="fas fa-check"></i> Gunakan Alamat Ini
                                    </button>
                                </div>
                            </form>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-map-marker-alt"></i>
                                <h3>Belum Ada Alamat Tersimpan</h3>
                                <p>Tambahkan alamat untuk melanjutkan checkout</p>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Add Address Button -->
                    <button type="button" class="form-toggle" onclick="toggleAddressForm()">
                        <i class="fas fa-plus"></i> Tambah Alamat Baru
                    </button>

                    <!-- New Address Form -->
                    <div class="address-form" id="addressForm">
                        <form action="prosesT_pengiriman.php" method="POST">
                            <div class="form-grid">
                                <div class="form-group full-width">
                                    <label for="label_alamat" class="required">Label Alamat</label>
                                    <input type="text" id="label_alamat" name="label_alamat" 
                                           placeholder="Contoh: Rumah, Kantor, Kos" required>
                                    <div class="field-help">Berikan nama untuk alamat ini agar mudah diingat</div>
                                </div>
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="nama_penerima" class="required">Nama Penerima</label>
                                        <input type="text" id="nama_penerima" name="nama_penerima" 
                                               placeholder="Nama lengkap penerima" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="no_telepon" class="required">Nomor Telepon</label>
                                        <input type="tel" id="no_telepon" name="no_telepon" 
                                               placeholder="08xxxxxxxxxx" required>
                                        <div class="field-help">Nomor yang dapat dihubungi kurir</div>
                                    </div>
                                </div>
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="provinsi" class="required">Provinsi</label>
                                        <select id="provinsi" name="provinsi" required>
                                            <option value="">Pilih Provinsi</option>
                                            <option value="DKI Jakarta">DKI Jakarta</option>
                                            <option value="Jawa Barat">Jawa Barat</option>
                                            <option value="Jawa Tengah">Jawa Tengah</option>
                                            <option value="Jawa Timur">Jawa Timur</option>
                                            <option value="Yogyakarta">D.I. Yogyakarta</option>
                                            <option value="Banten">Banten</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="kota" class="required">Kota/Kabupaten</label>
                                        <select id="kota" name="kota" required>
                                            <option value="">Pilih Kota/Kabupaten</option>
                                            <option value="Yogyakarta">D.I. Yogyakarta</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="kecamatan" class="required">Kecamatan</label>
                                        <select id="kecamatan" name="kecamatan" required>
                                            <option value="">Pilih Kecamatan</option>
                                            <option value="Yogyakarta">D.I. Yogyakarta</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="form-group full-width">
                                    <label for="alamat_lengkap" class="required">Alamat Lengkap</label>
                                    <textarea id="alamat_lengkap" name="alamat_lengkap" rows="4" 
                                              placeholder="Nama jalan, nomor rumah, RT/RW, patokan, dll" required></textarea>
                                    <div class="char-counter"><span id="alamat-count">0</span>/200 karakter</div>
                                </div>
                                
                                <div class="form-check">
                                    <input type="checkbox" id="is_primary" name="is_primary" class="form-check-input">
                                    <label for="is_primary" class="form-check-label">Jadikan sebagai alamat utama</label>
                                </div>
                                
                                <!-- Hidden fields for order data -->
                                <input type="hidden" name="order_source" value="<?= htmlspecialchars($source) ?>">
                                <?php if ($source === 'buy_now' || $source === 'individual_checkout'): ?>
                                    <input type="hidden" name="product_id" value="<?= htmlspecialchars($product_id) ?>">
                                    <input type="hidden" name="quantity" value="<?= $quantity ?>">
                                <?php endif; ?>
                            </div>
                            
                            <div class="form-actions">
                                <button type="button" class="btn btn-outline" onclick="toggleAddressForm()">
                                    <i class="fas fa-times"></i> Batal
                                </button>
                                <button type="submit" class="btn btn-primary" id="submit-btn">
                                    <i class="fas fa-save"></i> Simpan Alamat
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <?php endif; ?>
                </div>

                <!-- PERBAIKAN: Enhanced Order Summary dengan data produk yang lengkap -->
                <div class="order-summary">
                    <h2><i class="fas fa-receipt"></i> Ringkasan Pesanan</h2>
                    
                    <?php if (!empty($orderData['items'])): ?>
                        <div class="summary-items">
                            <?php foreach ($orderData['items'] as $item): ?>
                            <div class="summary-item" data-product-id="<?= $item['id_produk'] ?>">
                                <img src="/admin/uploads/<?= htmlspecialchars($item['foto']) ?>" 
                                     alt="<?= htmlspecialchars($item['nama_tanaman']) ?>" 
                                     class="item-image"
                                     onerror="this.src='/images/placeholder-product.jpg'">
                                <div class="item-info">
                                    <h3><?= htmlspecialchars($item['nama_tanaman']) ?></h3>
                                    <p class="item-quantity"><?= $item['jumlah'] ?> x Rp<?= number_format($item['harga'], 0, ',', '.') ?></p>
                                    <p class="item-source"><?= ucfirst($item['source'] ?? $source) ?></p>
                                    <!-- PERBAIKAN: Hidden data untuk JavaScript -->
                                    <input type="hidden" class="item-data" value="<?= htmlspecialchars(json_encode($item)) ?>">
                                </div>
                                <div class="item-price">
                                    <strong>Rp<?= number_format($item['subtotal'], 0, ',', '.') ?></strong>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="summary-calculations">
                            <div class="summary-row">
                                <span>Subtotal (<?= count($orderData['items']) ?> item)</span>
                                <span>Rp<?= number_format($orderData['subtotal'], 0, ',', '.') ?></span>
                            </div>
                            
                            
                            <div class="summary-row total">
                                <span><strong>Total</strong></span>
                                <span><strong>Rp<?= number_format($total, 0, ',', '.') ?></strong></span>
                            </div>
                        </div>
                        
                        <div class="checkout-info">
                            <p><i class="fas fa-info-circle"></i> Pilih alamat untuk melanjutkan ke pengiriman</p>
                        </div>
                    <?php else: ?>
                        <div class="empty-order">
                            <i class="fas fa-shopping-cart"></i>
                            <h3>Pesanan Kosong</h3>
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
        function selectAddress(id) {
            // Remove selected class from all cards
            document.querySelectorAll('.address-card').forEach(card => {
                card.classList.remove('selected');
            });
            
            // Add selected class to clicked card
            event.currentTarget.classList.add('selected');
            
            // Add loading state
            const button = event.currentTarget.querySelector('.use-address-btn');
            const originalText = button.innerHTML;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
            button.disabled = true;
            
            // PERBAIKAN: Validasi data sebelum submit
            const orderItems = [];
            document.querySelectorAll('.item-data').forEach(input => {
                try {
                    const itemData = JSON.parse(input.value);
                    orderItems.push(itemData);
                } catch (e) {
                    console.error('Error parsing item data:', e);
                }
            });
            
            if (orderItems.length === 0) {
                alert('Data pesanan tidak valid. Silakan refresh halaman dan coba lagi.');
                button.innerHTML = originalText;
                button.disabled = false;
                return;
            }
            
            // Submit form after short delay for visual feedback
            setTimeout(() => {
                document.getElementById('form_' + id).submit();
            }, 500);
        }
        
        function toggleAddressForm() {
            const form = document.getElementById('addressForm');
            const button = document.querySelector('.form-toggle');
            
            if (form.classList.contains('show')) {
                form.classList.remove('show');
                button.innerHTML = '<i class="fas fa-plus"></i> Tambah Alamat Baru';
            } else {
                form.classList.add('show');
                button.innerHTML = '<i class="fas fa-minus"></i> Tutup Form';
                form.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            // Character counter
            const alamatTextarea = document.getElementById('alamat_lengkap');
            const alamatCount = document.getElementById('alamat-count');
            
            if (alamatTextarea && alamatCount) {
                alamatTextarea.addEventListener('input', function() {
                    alamatCount.textContent = this.value.length;
                    
                    if (this.value.length > 180) {
                        alamatCount.style.color = 'var(--danger)';
                    } else if (this.value.length > 150) {
                        alamatCount.style.color = 'var(--warning)';
                    } else {
                        alamatCount.style.color = 'var(--text-muted)';
                    }
                });
            }
            
            // Mark selected address
            <?php if(isset($_SESSION['alamat_terpilih'])): ?>
            const selectedAddressId = <?= $_SESSION['alamat_terpilih'] ?>;
            const selectedCard = document.querySelector(`#form_${selectedAddressId} .address-card`);
            if (selectedCard) {
                selectedCard.classList.add('selected');
            }
            <?php endif; ?>
            
            // Phone number formatting
            const phoneInput = document.getElementById('no_telepon');
            if (phoneInput) {
                phoneInput.addEventListener('input', function() {
                    let value = this.value.replace(/\D/g, '');
                    
                    if (value.length > 0 && !value.startsWith('08')) {
                        if (value.startsWith('8')) {
                            value = '0' + value;
                        } else if (value.startsWith('62')) {
                            value = '0' + value.substring(2);
                        }
                    }
                    
                    if (value.length > 13) {
                        value = value.substring(0, 13);
                    }
                    
                    this.value = value;
                });
            }
        });
        
        // PERBAIKAN: Enhanced debug logging
        console.log('=== Order Data Debug ===');
        console.log('Source:', '<?= $source ?>');
        console.log('Product ID:', '<?= $product_id ?>');
        console.log('Quantity:', <?= $quantity ?>);
        console.log('Order Items:', <?= json_encode($orderData['items']) ?>);
        console.log('Subtotal:', <?= $orderData['subtotal'] ?>);
        console.log('Total:', <?= $total ?>);
        console.log('Cart Session:', <?= json_encode($_SESSION['keranjang'] ?? []) ?>);
        console.log('Session Order Data:', <?= json_encode($_SESSION['order_data'] ?? []) ?>);
        console.log('========================');
    </script>

    <style>
        /* Enhanced source info banner styles */
        .source-info-banner {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid;
        }
        
        .cart-source {
            background-color: #e8f5e8;
            border-left-color: #28a745;
            color: #155724;
        }
        
        .individual-source {
            background-color: #e1ecf4;
            border-left-color: #007bff;
            color: #004085;
        }
        
        .buy-now-source {
            background-color: #fff3cd;
            border-left-color: #ffc107;
            color: #856404;
        }
        
        .source-info-banner i {
            font-size: 24px;
            margin-right: 15px;
        }
        
        .source-details strong {
            display: block;
            font-size: 16px;
            margin-bottom: 4px;
        }
        
        .source-details span {
            font-size: 14px;
            opacity: 0.8;
        }
        
        /* Enhanced summary styles */
        .summary-calculations {
            border-top: 1px solid #eee;
            padding-top: 15px;
            margin-top: 15px;
        }
        
        .summary-item {
            display: flex;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #f0f0f0;
            position: relative;
        }
        
        .summary-item:last-child {
            border-bottom: none;
        }
        
        .item-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
            margin-right: 15px;
        }
        
        .item-info {
            flex: 1;
        }
        
        .item-info h3 {
            margin: 0 0 5px 0;
            font-size: 14px;
            font-weight: 600;
        }
        
        .item-quantity {
            margin: 0;
            font-size: 12px;
            color: #666;
        }
        
        .item-source {
            margin: 2px 0 0 0;
            font-size: 10px;
            color: #999;
            text-transform: capitalize;
        }
        
        .item-price {
            font-weight: 600;
            color: #28a745;
        }
        
        .item-data {
            display: none;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
        }
        
        .summary-row.total {
            border-top: 2px solid #28a745;
            margin-top: 10px;
            padding-top: 15px;
            font-size: 18px;
        }
        
        /* Empty order actions */
        .empty-order-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
            flex-wrap: wrap;
        }
        
        /* Loading states */
        .use-address-btn:disabled,
        #submit-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        
        /* Enhanced address card hover effects */
        .address-card {
            transition: all 0.3s ease;
        }
        
        .address-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .address-card.selected {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.2);
            border-color: #28a745;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .checkout-content {
                flex-direction: column;
            }
            
            .order-summary {
                margin-top: 30px;
            }
            
            .summary-item {
                flex-direction: column;
                text-align: center;
            }
            
            .item-image {
                margin-right: 0;
                margin-bottom: 10px;
            }
        }
    </style>
</body>
</html>

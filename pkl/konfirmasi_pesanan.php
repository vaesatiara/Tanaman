<?php
session_start();
include "koneksi.php";

// Periksa apakah user sudah login
if (!isset($_SESSION['username'])) {
    die("Akses ditolak. Silakan login terlebih dahulu.");
}

// Periksa apakah ada data pesanan di session
if (!isset($_SESSION['order_data'])) {
    header("Location: keranjang.php");
    exit();
}

// Ambil data dari session
$sessionData = $_SESSION['order_data'];

// Validasi data item pesanan
if (isset($sessionData['order_items']) && is_array($sessionData['order_items'])) {
    foreach ($sessionData['order_items'] as $key => $item) {
        if (!isset($item['id_produk']) || empty($item['id_produk'])) {
            error_log("Warning: Item tanpa id_produk ditemukan: " . json_encode($item));
        }
    }
} else {
    error_log("Error: Data order_items tidak valid");
}

// Fungsi untuk mendapatkan ID pelanggan yang valid
function getValidCustomerId($koneksi) {
    $possible_session_keys = ['id_pelanggan', 'user_id', 'customer_id', 'pelanggan_id'];
    
    foreach ($possible_session_keys as $key) {
        if (isset($_SESSION[$key]) && !empty($_SESSION[$key])) {
            $id = $_SESSION[$key];
            
            $query = $koneksi->query("SELECT id_pelanggan FROM pelanggan WHERE id_pelanggan = '$id'");
            if ($query && $query->num_rows > 0) {
                return $id;
            }
        }
    }
    
    if (isset($_SESSION['username'])) {
        $username = $_SESSION['username'];
        $query = $koneksi->query("SELECT id_pelanggan FROM pelanggan WHERE username = '$username' OR email = '$username'");
        if ($query && $query->num_rows > 0) {
            $row = $query->fetch_assoc();
            return $row['id_pelanggan'];
        }
    }
    
    return null;
}

// Dapatkan ID pelanggan yang valid
$id_pelanggan = getValidCustomerId($koneksi);

if (!$id_pelanggan) {
    die("Error: ID pelanggan tidak ditemukan. Silakan login ulang.");
}

$sql_produk="SELECT * FROM produk";
$query=mysqli_query($koneksi,$sql_produk);

// Fungsi helper
function getPaymentMethodName($method) {
    $paymentNames = [
        'bca' => 'Transfer Bank BCA',
        'bni' => 'Transfer Bank BNI', 
        'mandiri' => 'Transfer Bank Mandiri',
        'gopay' => 'GoPay',
        'ovo' => 'OVO',
        'dana' => 'DANA'
    ];
    return isset($paymentNames[$method]) ? $paymentNames[$method] : 'Transfer Bank';
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

// Fungsi untuk menghitung total pesanan
function calculateOrderTotal($orderItems, $shippingCost = 0) {
    $subtotal = 0;
    
    foreach ($orderItems as $item) {
        $subtotal += $item['subtotal'];
    }
    
    $total = $subtotal + $shippingCost;
    
    return [
        'subtotal' => $subtotal,
        'shipping_cost' => $shippingCost,
        'total' => $total
    ];
}

// Variabel untuk menampung data pesanan
$nomor_pesanan = '';
$tanggal_pesanan = '';
$status_pesanan = 'diproses';
$order_saved = false;
$error_message = '';

// Hitung total pesanan dari data session
$orderCalculation = calculateOrderTotal(
    $sessionData['order_items'], 
    isset($sessionData['shipping_cost']) ? $sessionData['shipping_cost'] : 0
);

// Debugging data item sebelum penyimpanan
error_log("Data order items sebelum penyimpanan: " . json_encode($sessionData['order_items']));

// Proses penyimpanan pesanan ke database
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['konfirmasi_pesanan'])) {
    
    try {
        // Validasi ulang ID pelanggan sebelum menyimpan
        $check_customer = $koneksi->query("SELECT id_pelanggan FROM pelanggan WHERE id_pelanggan = '$id_pelanggan'");
        if (!$check_customer || $check_customer->num_rows == 0) {
            throw new Exception("ID pelanggan tidak valid atau tidak ditemukan di database");
        }
        
        // Mulai transaksi database
        $koneksi->begin_transaction();
        
        // Generate nomor pesanan unik
        $nomor_pesanan = "TN" . date("YmdHis") . rand(100, 999);
        $tanggal_pesanan = date("Y-m-d H:i:s");
        
        // Hitung ulang total untuk memastikan akurasi
        $finalCalculation = calculateOrderTotal(
            $sessionData['order_items'], 
            isset($sessionData['shipping_cost']) ? $sessionData['shipping_cost'] : 0
        );
        
        $total_pesanan = $finalCalculation['total'];
        $metode_pembayaran = getPaymentMethodName($sessionData['payment_method']);
        
        // Validasi total tidak boleh 0 atau negatif
        if ($total_pesanan <= 0) {
            throw new Exception("Total pesanan tidak valid: Rp" . number_format($total_pesanan, 0, ',', '.'));
        }
        
        // Insert pesanan utama
        $sql_pesanan = "INSERT INTO pesanan (id_pelanggan, nomor_pesanan, tgl_pesanan, total, status_pesanan) 
                        VALUES (?, ?, ?, ?, ?)";
        
        $stmt = $koneksi->prepare($sql_pesanan);
        if (!$stmt) {
            throw new Exception("Prepare statement gagal: " . $koneksi->error);
        }
        
        $stmt->bind_param("issds", $id_pelanggan, $nomor_pesanan, $tanggal_pesanan, $total_pesanan, $status_pesanan);
        
        if (!$stmt->execute()) {
            throw new Exception("Gagal menyimpan pesanan: " . $stmt->error);
        }
        
        $id_pesanan = $koneksi->insert_id;
        
        // Insert detail pesanan untuk setiap item
        $sql_detail = "INSERT INTO detail_pesanan (id_pesanan, id_produk, jumlah, harga_satuan, subtotal) VALUES (?, ?, ?, ?, ?)";
        $stmt_detail = $koneksi->prepare($sql_detail);

        if ($stmt_detail) {
            foreach ($sessionData['order_items'] as $item) {
                // Pastikan id_produk ada dan valid
                if (!isset($item['id_produk']) || empty($item['id_produk'])) {
                    error_log("Error: id_produk tidak ditemukan untuk item: " . json_encode($item));
                    continue;
                }
                
                $id_produk = $item['id_produk'];
                $jumlah = $item['jumlah'];
                $harga = $item['harga'];
                $subtotal = $item['subtotal'];
                
                // Log untuk debugging
                error_log("Menyimpan detail pesanan: id_pesanan=$id_pesanan, id_produk=$id_produk, jumlah=$jumlah, harga=$harga, subtotal=$subtotal");
                
                $stmt_detail->bind_param("iiidd", $id_pesanan, $id_produk, $jumlah, $harga, $subtotal);
                if (!$stmt_detail->execute()) {
                    error_log("Error menyimpan detail pesanan: " . $stmt_detail->error);
                }
            }
        }
        
        // Simpan data pengiriman jika ada
        if (isset($sessionData['shipping_address_id']) && !empty($sessionData['shipping_address_id'])) {
            $sql_pengiriman = "INSERT INTO detail_pengiriman (id_pesanan, id_alamat, metode_pengiriman, biaya_pengiriman) 
                              VALUES (?, ?, ?, ?)";
            $stmt_pengiriman = $koneksi->prepare($sql_pengiriman);
            
            if ($stmt_pengiriman) {
                $shipping_method = isset($sessionData['shipping_method']) ? $sessionData['shipping_method'] : 'jne';
                $shipping_cost = isset($sessionData['shipping_cost']) ? $sessionData['shipping_cost'] : 0;
                
                $stmt_pengiriman->bind_param("iisd", $id_pesanan, $sessionData['shipping_address_id'], $shipping_method, $shipping_cost);
                $stmt_pengiriman->execute();
            }
        }
        
        // Commit transaksi
        $koneksi->commit();
        
        // Bersihkan keranjang jika pembelian dari keranjang
        if (isset($sessionData['source']) && $sessionData['source'] === 'cart') {
            unset($_SESSION['keranjang']);
        }
        
        // Set flag bahwa pesanan berhasil disimpan
        $order_saved = true;
        $tanggal_pesanan = date("d M Y, H:i");
        
        // PERBAIKAN: Simpan data pesanan untuk halaman konfirmasi pembayaran
        $_SESSION['last_order_number'] = $nomor_pesanan;
        $_SESSION['last_order_id'] = $id_pesanan;
        
        // PERBAIKAN: Simpan data current_order untuk referensi pembayaran
        $_SESSION['current_order'] = [
            'id_pesanan' => $id_pesanan,
            'nomor_pesanan' => $nomor_pesanan,
            'total' => $total_pesanan,
            'status' => $status_pesanan
        ];
        
    } catch (Exception $e) {
        $koneksi->rollback();
        $error_message = $e->getMessage();
        error_log("Error konfirmasi pesanan: " . $e->getMessage());
    }
}

// Ambil data untuk ditampilkan
$orderData = [
    'items' => $sessionData['order_items'],
    'subtotal' => $orderCalculation['subtotal']
];

$shippingAddress = null;
// Ambil alamat pengiriman
if (isset($sessionData['shipping_address_id'])) {
    $query = $koneksi->query("SELECT * FROM pengiriman WHERE id_pengiriman='" . $sessionData['shipping_address_id'] . "'");
    if ($query) {
        $shippingAddress = $query->fetch_assoc();
    }
}

// Data untuk ditampilkan
if (!$order_saved) {
    $nomor_pesanan = 'TN' . date('Ymd') . rand(1000, 9999);
    $tanggal_pesanan = date("d M Y, H:i");
}

$paymentMethod = getPaymentMethodName($sessionData['payment_method']);
$shippingName = getShippingName(isset($sessionData['shipping_method']) ? $sessionData['shipping_method'] : 'jne');
$shippingCost = $orderCalculation['shipping_cost'];
$total = $orderCalculation['total'];

// Fungsi untuk mendapatkan label status yang user-friendly
function getStatusLabel($status) {
    $labels = [
        'diproses' => 'Sedang Diproses',
        'dikirim' => 'Sedang Dikirim',
        'selesai' => 'Selesai',
        'dibatalkan' => 'Dibatalkan'
    ];
    
    return isset($labels[$status]) ? $labels[$status] : $status;
}

$display_status = getStatusLabel($status_pesanan);
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

    <main class="receipt-section">
        <div class="container">
            <div class="receipt-container">
                
                <?php if (!$order_saved): ?>
                <!-- Form Konfirmasi Pesanan -->
                <div class="confirmation-form">
                    <div class="receipt-header">
                        <div class="store-logo">
                            <img src="images/logo.png" alt="TOKO TANAMAN">
                        </div>
                        <h1>Konfirmasi Pesanan Anda</h1>
                        <p>Periksa kembali detail pesanan sebelum melanjutkan</p>
                    </div>
                    
                    <?php if (!empty($error_message)): ?>
                        <div class="error-message" style="background-color: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 20px 0;">
                            <p><strong>Error:</strong> <?= htmlspecialchars($error_message) ?></p>
                            <p><a href="metode_pembayaran.php" class="btn btn-secondary">Kembali ke Pembayaran</a></p>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Preview Data Pesanan -->
                    <div class="receipt-details">
                        <div class="receipt-row">
                            <div class="receipt-label">Nomor Pesanan:</div>
                            <div class="receipt-value"><?= htmlspecialchars($nomor_pesanan) ?></div>
                        </div>
                        <div class="receipt-row">
                            <div class="receipt-label">Tanggal Pesanan:</div>
                            <div class="receipt-value"><?= htmlspecialchars($tanggal_pesanan) ?> WIB</div>
                        </div>
                        <div class="receipt-row">
                            <div class="receipt-label">Status:</div>
                            <div class="receipt-value status-pending"><?= htmlspecialchars($display_status) ?></div>
                        </div>
                        <div class="receipt-row">
                            <div class="receipt-label">Metode Pembayaran:</div>
                            <div class="receipt-value"><?= htmlspecialchars($paymentMethod) ?></div>
                        </div>
                    </div>
                    
                    <!-- Ringkasan Pesanan -->
                    <div class="order-summary">
                        <h3>Ringkasan Pesanan</h3>
                        
                        <div class="summary-items">
                            <?php foreach ($orderData['items'] as $item): ?>
                            <div class="summary-item">
                                <img src="uploads/<?= htmlspecialchars($item['foto']) ?>" alt="<?= htmlspecialchars($item['nama_tanaman']) ?>" class="item-image">
                                <div class="item-info">
                                    <h4><?= htmlspecialchars($item['nama_tanaman']) ?></h4>
                                    <p><?= $item['jumlah'] ?> x Rp<?= number_format($item['harga'], 0, ',', '.') ?></p>
                                </div>
                                <div class="item-price">Rp<?= number_format($item['subtotal'], 0, ',', '.') ?></div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="summary-total">
                            <div class="summary-row">
                                <span>Subtotal:</span>
                                <span>Rp<?= number_format($orderData['subtotal'], 0, ',', '.') ?></span>
                            </div>
                            <div class="summary-row">
                                <span>Pengiriman (<?= htmlspecialchars($shippingName) ?>):</span>
                                <span>Rp<?= number_format($shippingCost, 0, ',', '.') ?></span>
                            </div>
                            <div class="summary-row total">
                                <span><strong>Total:</strong></span>
                                <span><strong>Rp<?= number_format($total, 0, ',', '.') ?></strong></span>
                            </div>
                        </div>
                    </div>
                    
                    <?php if (empty($error_message) && $total > 0): ?>
                    <form method="POST" style="text-align: center; margin: 30px 0;">
                        <button type="submit" name="konfirmasi_pesanan" class="btn btn-primary" style="padding: 15px 30px; font-size: 16px;">
                            <i class="fas fa-check"></i> Konfirmasi & Buat Pesanan
                        </button>
                    </form>
                    <?php endif; ?>
                </div>
                
                <?php else: ?>
                <!-- Halaman Sukses -->
                <div class="receipt-header">
                    <div class="store-logo">
                        <img src="images/logo.png" alt="TOKO TANAMAN">
                    </div>
                    <div class="success-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h1>Pesanan Berhasil Dibuat!</h1>
                    <p>Terima kasih telah berbelanja di TOKO TANAMAN</p>
                </div>
                
                <div class="receipt-details">
                    <div class="receipt-row">
                        <div class="receipt-label">Nomor Pesanan:</div>
                        <div class="receipt-value"><?= htmlspecialchars($nomor_pesanan) ?></div>
                    </div>
                    <div class="receipt-row">
                        <div class="receipt-label">Tanggal Pesanan:</div>
                        <div class="receipt-value"><?= htmlspecialchars($tanggal_pesanan) ?> WIB</div>
                    </div>
                    <div class="receipt-row">
                        <div class="receipt-label">Status Pesanan:</div>
                        <div class="receipt-value status-pending"><?= htmlspecialchars($display_status) ?></div>
                    </div>
                    <div class="receipt-row">
                        <div class="receipt-label">Metode Pembayaran:</div>
                        <div class="receipt-value"><?= htmlspecialchars($paymentMethod) ?></div>
                    </div>
                </div>

                <div class="payment-instructions">
                    <h2>Instruksi Pembayaran</h2>
                    <p>Silakan transfer ke rekening di bawah dalam waktu 24 jam:</p>
                    
                    
                    
                    <div class="payment-total">
                        <div class="total-label">Total Pembayaran</div>
                        <div class="total-amount">
                            <span>Rp<?= number_format($total, 0, ',', '.') ?></span>
                        </div>
                        <button class="copy-btn" data-clipboard-text="<?= $total ?>">
                            <i class="fas fa-copy"></i> Salin
                        </button>
                    </div>
                    
                    <p class="payment-note">Catatan: Mohon transfer tepat hingga 3 digit terakhir untuk memudahkan verifikasi.</p>
                    
                    <!-- PERBAIKAN: Link ke konfirmasi pembayaran dengan parameter yang tepat -->
                    <a href="konfirmasi_pembayaran.php?new_order=1&order_id=<?= $id_pesanan ?>" class="btn btn-primary">Konfirmasi Pembayaran</a>
                </div>
                
                <!-- INFORMASI PENGIRIMAN -->
                <div class="shipping-info">
                    <h2>Informasi Pengiriman</h2>
                    
                    <div class="shipping-details">
                        <?php if ($shippingAddress): ?>
                        <div class="address-summary">
                            <div class="address-info">
                                <p class="address-name"><strong><?= htmlspecialchars($shippingAddress['nama_penerima']) ?></strong> (<?= htmlspecialchars($shippingAddress['label_alamat']) ?>)</p>
                                <p class="address-phone"><?= htmlspecialchars($shippingAddress['no_telepon']) ?></p>
                                <p class="address-detail"><?= htmlspecialchars($shippingAddress['alamat_lengkap']) ?>, <?= htmlspecialchars($shippingAddress['kecamatan']) ?>, <?= htmlspecialchars($shippingAddress['kota']) ?>, <?= htmlspecialchars($shippingAddress['provinsi']) ?></p>
                            </div>
                        </div>
                        <?php else: ?>
                        <div class="no-address">
                            <p>Belum ada alamat pengiriman yang dipilih.</p>
                        </div>
                        <?php endif; ?>
                        
                        <div class="shipping-method">
                            <h3>Metode Pengiriman</h3>
                            <div class="courier-logo">
                                <img src="images/jne (1).jpg" alt="<?= htmlspecialchars($shippingName) ?>">
                            </div>
                            <p class="courier-name"><?= htmlspecialchars($shippingName) ?></p>
                            <p class="estimated-time">Estimasi tiba 2-3 hari</p>
                            <p class="shipping-cost">Biaya: Rp<?= number_format($shippingCost, 0, ',', '.') ?></p>
                        </div>
                    </div>
                </div>
                
                <!-- DETAIL PESANAN -->
                <div class="order-details">
                    <h2>Detail Pesanan</h2>
                    
                    <div class="order-summary">
                        <h2 class="summary-title">Ringkasan Pesanan</h2>
                        
                        <div class="summary-items">
                            <?php foreach ($orderData['items'] as $item): ?>
                            <div class="summary-item">
                                <img src="Admin_WebTanaman/uploads<?= htmlspecialchars($item['foto']) ?>" alt="<?= htmlspecialchars($item['nama_tanaman']) ?>" class="item-image">
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
                            <span>Pengiriman (<?= htmlspecialchars($shippingName) ?>)</span>
                            <span>Rp<?= number_format($shippingCost, 0, ',', '.') ?></span>
                        </div>
                        
                        <div class="summary-row total">
                            <span>Total</span>
                            <span>Rp<?= number_format($total, 0, ',', '.') ?></span>
                        </div>
                    </div>
                </div>
                
                <div class="receipt-actions">
                    <a href="index.php" class="btn btn-outline">
                        <i class="fas fa-home"></i> Kembali ke Beranda
                    </a>
                    <a href="riwayat_pesanan.php" class="btn btn-secondary">
                        <i class="fas fa-history"></i> Lihat Riwayat Pesanan
                    </a>
                </div>
                <?php endif; ?>
                
                <div class="help-section">
                    <h3>Butuh Bantuan?</h3>
                    <p>Hubungi kami di: <a href="tel:+6281234567890">+62 812 3456 7890</a> atau <a href="mailto:help@tokotanaman.com">help@tokotanaman.com</a></p>
                </div>
            </div>
        </div>
    </main>

    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-logo">
                    <img src="images/logo.png" alt="Toko Tanaman">
                    <p>Toko tanaman hias terlengkap dan terpercaya</p>
                </div>
                <div class="footer-links">
                    <h3 class="footer-title">Tautan Cepat</h3>
                    <ul>
                        <li><a href="index.php">Beranda</a></li>
                        <li><a href="produk.php">Produk</a></li>
                        <li><a href="tentang_kami.php">Tentang Kami</a></li>
                    </ul>
                </div>
                <div class="footer-contact">
                    <h3 class="footer-title">Kontak Kami</h3>
                    <p><i class="fas fa-map-marker-alt"></i> Jl. Tanaman Indah No. 123, Jakarta</p>
                    <p><i class="fas fa-phone"></i> +62 812 3456 7890</p>
                    <p><i class="fas fa-envelope"></i> info@tokotanaman.com</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2023 Toko Tanaman. All Rights Reserved.</p>
            </div>
        </div>
    </footer>

    <script src="js/script.js"></script>
</body>
</html>

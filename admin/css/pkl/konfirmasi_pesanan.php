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

// PERBAIKAN: Ambil data dari database ringkasan_pesanan berdasarkan temp_order_id atau session_id
function getOrderDataFromDatabase($koneksi, $temp_order_id = null, $session_id = null) {
    $orderItems = [];
    $orderInfo = [];
    
    if ($temp_order_id) {
        // PERBAIKAN: JOIN dengan tabel produk untuk memastikan data produk valid
        $query = $koneksi->query("
            SELECT r.*, p.nama_tanaman as produk_nama, p.harga as produk_harga, p.foto as produk_foto
            FROM ringkasan_pesanan r 
            LEFT JOIN produk p ON r.id_produk = p.id_produk 
            WHERE r.temp_order_id = '$temp_order_id' AND r.status_pesanan = 'temp' 
            ORDER BY r.id_ringkasan
        ");
    } else if ($session_id) {
        $query = $koneksi->query("
            SELECT r.*, p.nama_tanaman as produk_nama, p.harga as produk_harga, p.foto as produk_foto
            FROM ringkasan_pesanan r 
            LEFT JOIN produk p ON r.id_produk = p.id_produk 
            WHERE r.session_id = '$session_id' AND r.status_pesanan = 'temp' 
            ORDER BY r.id_ringkasan
        ");
    } else {
        return ['items' => [], 'info' => []];
    }
    
    if ($query && $query->num_rows > 0) {
        $subtotal = 0;
        while ($row = $query->fetch_assoc()) {
            // PERBAIKAN: Validasi ID produk tidak boleh NULL atau kosong
            if (!empty($row['id_produk']) && $row['id_produk'] > 0) {
                $orderItems[] = [
                    'id_produk' => (int)$row['id_produk'], // PERBAIKAN: Cast ke integer
                    'nama_tanaman' => $row['nama_tanaman'] ?: $row['produk_nama'], // Fallback ke data produk
                    'foto' => $row['foto'] ?: $row['produk_foto'],
                    'harga' => (float)($row['harga'] ?: $row['produk_harga']),
                    'jumlah' => (int)$row['jumlah'],
                    'subtotal' => (float)$row['subtotal']
                ];
                $subtotal += $row['subtotal'];
            }
            
            // Ambil info pesanan dari record pertama
            if (empty($orderInfo)) {
                $orderInfo = [
                    'shipping_method' => $row['shipping_method'],
                    'shipping_cost' => $row['shipping_cost'],
                    'payment_method' => $row['payment_method'],
                    'total' => $row['total'], // PERBAIKAN: Ubah dari total_amount ke total
                    'subtotal' => $subtotal
                ];
            }
        }
    }
    
    return ['items' => $orderItems, 'info' => $orderInfo];
}

// PERBAIKAN: Ambil data dari database, bukan dari session
$temp_order_id = isset($sessionData['temp_order_id']) ? $sessionData['temp_order_id'] : null;
$session_id = session_id();

$databaseData = getOrderDataFromDatabase($koneksi, $temp_order_id, $session_id);

// Jika tidak ada data di database, fallback ke session data
if (empty($databaseData['items']) && isset($sessionData['order_items'])) {
    $orderItems = $sessionData['order_items'];
    $orderInfo = $sessionData;
} else {
    $orderItems = $databaseData['items'];
    $orderInfo = $databaseData['info'];
    
    // Update session dengan data dari database
    if (!empty($orderInfo)) {
        $_SESSION['order_data'] = array_merge($_SESSION['order_data'], $orderInfo);
        $sessionData = $_SESSION['order_data'];
    }
}

// PERBAIKAN: Validasi data item pesanan sebelum menyimpan
if (empty($orderItems)) {
    header("Location: keranjang.php?error=no_order_data");
    exit();
}

// PERBAIKAN: Validasi ID produk sebelum menyimpan
foreach ($sessionData['order_items'] as $item) {
    if (empty($item['id_produk']) || $item['id_produk'] <= 0) {
        throw new Exception("ID produk tidak valid: " . json_encode($item));
    }
    
    // Cek apakah produk masih ada di database
    $check_product = $koneksi->prepare("SELECT id_produk FROM produk WHERE id_produk = ?");
    $check_product->bind_param("i", $item['id_produk']);
    $check_product->execute();
    $product_result = $check_product->get_result();
    
    if (!$product_result || $product_result->num_rows == 0) {
        throw new Exception("Produk dengan ID " . $item['id_produk'] . " tidak ditemukan di database");
    }
}

// Fungsi untuk mendapatkan ID pelanggan yang valid
function getValidCustomerId($koneksi) {
    $possible_session_keys = ['id_pelanggan', 'user_id', 'customer_id', 'pelanggan_id'];
    
    foreach ($possible_session_keys as $key) {
        if (isset($_SESSION[$key]) && !empty($_SESSION[$key])) {
            $id = $_SESSION[$key];
            
            $stmt = $koneksi->prepare("SELECT id_pelanggan FROM pelanggan WHERE id_pelanggan = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result && $result->num_rows > 0) {
                return $id;
            }
        }
    }
    
    if (isset($_SESSION['username'])) {
        $username = $_SESSION['username'];
        $stmt = $koneksi->prepare("SELECT id_pelanggan FROM pelanggan WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
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

// Fungsi helper untuk nama pembayaran dan pengiriman
function getPaymentMethodName($method) {
    $paymentNames = [
        'bca' => 'Transfer Bank BCA',
        'bni' => 'Transfer Bank BNI', 
        'mandiri' => 'Transfer Bank Mandiri',
       
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
$status_pesanan = 'menunggu pembayaran';
$order_saved = false;
$error_message = '';

// Hitung total pesanan dari data yang sudah diambil
$orderCalculation = calculateOrderTotal(
    $orderItems, 
    isset($orderInfo['shipping_cost']) ? $orderInfo['shipping_cost'] : (isset($sessionData['shipping_cost']) ? $sessionData['shipping_cost'] : 0)
);

// PERBAIKAN: Proses penyimpanan pesanan ke database
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['konfirmasi_pesanan'])) {
    
    try {
        // Validasi ulang ID pelanggan sebelum menyimpan
        $check_customer = $koneksi->prepare("SELECT id_pelanggan FROM pelanggan WHERE id_pelanggan = ?");
        $check_customer->bind_param("i", $id_pelanggan);
        $check_customer->execute();
        $customer_result = $check_customer->get_result();
        
        if (!$customer_result || $customer_result->num_rows == 0) {
            throw new Exception("ID pelanggan tidak valid atau tidak ditemukan di database");
        }
        
        // Mulai transaksi database
        $koneksi->begin_transaction();
        
        // Generate nomor pesanan unik
        $nomor_pesanan = "TN" . date("YmdHis") . rand(100, 999);
        
        // PERBAIKAN: Set tanggal dan status pesanan dengan benar
        $tanggal_pesanan = date("Y-m-d H:i:s"); // Format datetime untuk database
        $status_pesanan = 'menunggu pembayaran'; // Set status default
        
        // Hitung ulang total untuk memastikan akurasi
        $finalCalculation = calculateOrderTotal(
            $orderItems, 
            isset($orderInfo['shipping_cost']) ? $orderInfo['shipping_cost'] : (isset($sessionData['shipping_cost']) ? $sessionData['shipping_cost'] : 0)
        );
        
        $total_pesanan = $finalCalculation['total'];
        $payment_method = isset($orderInfo['payment_method']) ? $orderInfo['payment_method'] : $sessionData['payment_method'];
        $metode_pembayaran = getPaymentMethodName($payment_method);
        
        // Validasi total tidak boleh 0 atau negatif
        if ($total_pesanan <= 0) {
            throw new Exception("Total pesanan tidak valid: Rp" . number_format($total_pesanan, 0, ',', '.'));
        }
        
        // PERBAIKAN: Insert pesanan utama dengan tanggal dan status yang benar
        $first_item = $sessionData['order_items'][0];
        $id_produk_utama = $first_item['id_produk'];
        $jumlah_utama = $first_item['jumlah'];

        // PERBAIKAN: Pastikan semua field required terisi
        $sql_pesanan = "INSERT INTO pesanan (id_pelanggan, id_produk, nomor_pesanan, tgl_pesanan, jumlah, total, status_pesanan) 
                        VALUES (?, ?, ?, ?, ?, ?, ?)";

        $stmt = $koneksi->prepare($sql_pesanan);
        if (!$stmt) {
            throw new Exception("Prepare statement gagal: " . $koneksi->error);
        }

        // PERBAIKAN: Bind parameter dengan tipe data yang tepat
        $stmt->bind_param("iisiids", 
            $id_pelanggan,          // integer
            $id_produk_utama,       // integer  
            $nomor_pesanan,         // string
            $tgl_pesanan,       // string (datetime)
            $jumlah_utama,          // integer
            $total_pesanan,         // double/float
            $status_pesanan         // string
        );

        if (!$stmt->execute()) {
            throw new Exception("Gagal menyimpan pesanan: " . $stmt->error);
        }

        $id_pesanan = $koneksi->insert_id;
        
        // Commit transaksi
        $koneksi->commit();

        // PERBAIKAN: Ambil data pesanan terbaru dari database untuk konfirmasi
        $sql_get_saved_order = "SELECT nomor_pesanan, tgl_pesanan, status_pesanan FROM pesanan WHERE id_pesanan = ?";
        $stmt_get_order = $koneksi->prepare($sql_get_saved_order);
        if ($stmt_get_order) {
            $stmt_get_order->bind_param("i", $id_pesanan);
            $stmt_get_order->execute();
            $result_saved = $stmt_get_order->get_result();
            
            if ($result_saved->num_rows > 0) {
                $saved_order = $result_saved->fetch_assoc();
                $nomor_pesanan = $saved_order['nomor_pesanan'];
                $tanggal_pesanan = date("d M Y, H:i", strtotime($saved_order['tgl_pesanan']));
                $status_pesanan = $saved_order['status_pesanan'];
                $display_status = getStatusLabel($status_pesanan);
            }
        }
        
        // Bersihkan keranjang jika pembelian dari keranjang
        if (isset($sessionData['source']) && $sessionData['source'] === 'cart') {
            unset($_SESSION['keranjang']);
        }
        
        // Set flag bahwa pesanan berhasil disimpan
        $order_saved = true;
        
        // PERBAIKAN: Simpan data pesanan untuk halaman konfirmasi pembayaran
        $_SESSION['last_order_number'] = $nomor_pesanan;
        $_SESSION['last_order_id'] = $id_pesanan;
        
        // PERBAIKAN: Simpan data current_order untuk referensi pembayaran
        $_SESSION['current_order'] = [
            'id_pesanan' => $id_pesanan,
            'nomor_pesanan' => $nomor_pesanan,
            'total' => $total_pesanan,
            'status_pesanan' => $status_pesanan,
            'items' => $orderItems
        ];
        
    } catch (Exception $e) {
        $koneksi->rollback();
        $error_message = $e->getMessage();
    }
}

// Ambil data untuk ditampilkan - MENGGUNAKAN DATA DARI DATABASE
$orderData = [
    'items' => $orderItems,
    'subtotal' => $orderCalculation['subtotal']
];

$shippingAddress = null;
// Ambil alamat pengiriman
if (isset($sessionData['shipping_address_id'])) {
    $stmt = $koneksi->prepare("SELECT * FROM pengiriman WHERE id_pengiriman = ?");
    $stmt->bind_param("i", $sessionData['shipping_address_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result) {
        $shippingAddress = $result->fetch_assoc();
    }
}

// Data untuk ditampilkan
if (!$order_saved) {
    $nomor_pesanan = 'TN' . date('Ymd') . rand(1000, 9999);
    $tanggal_pesanan = date("d M Y, H:i");
}

$payment_method = isset($orderInfo['payment_method']) ? $orderInfo['payment_method'] : $sessionData['payment_method'];
$paymentMethod = getPaymentMethodName($payment_method);
$shipping_method = isset($orderInfo['shipping_method']) ? $orderInfo['shipping_method'] : (isset($sessionData['shipping_method']) ? $sessionData['shipping_method'] : 'jne');
$shippingName = getShippingName($shipping_method);
$shippingCost = $orderCalculation['shipping_cost'];
$total = $orderCalculation['total'];

// Fungsi untuk mendapatkan label status yang user-friendly
function getStatusLabel($status) {
    $labels = [
        'menunggu pembayaran' => 'Menunggu Pembayaran',
        'menunggu_konfirmasi' => 'Menunggu Konfirmasi',
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
                        <div class="receipt-value"><?= htmlspecialchars("

10 Jul 2025, 03:20") ?> WIB</div>
                    </div>
                        <div class="receipt-row">
                            <div class="receipt-label">Status:</div>
                            <div class="receipt-value status-pending"><?= htmlspecialchars($display_status) ?></div>
                        </div>
                        <div class="receipt-row">
                            <div class="receipt-label">Metode Pembayaaran:</div>
                            <div class="receipt-value"><?= htmlspecialchars($paymentMethod) ?></div>
                        </div>
                    </div>
                    
                    <!-- Ringkasan Pesanan -->
                    <div class="order-summary">
                        <h3>Ringkasan Pesanan</h3>
                        
                        <div class="summary-items">
                            <?php foreach ($orderData['items'] as $item): ?>
                            <div class="summary-item">
                                <img src="/admin/Admin_WebTanaman/uploads/<?= $item['foto'] ?>" alt="<?= $item['nama_tanaman'] ?>" class="item-image">
                                <div class="item-info">
                                    <h4><?= htmlspecialchars($item['nama_tanaman']) ?></h4>
                                    <p><?= $item['jumlah'] ?> x Rp<?= number_format($item['harga'], 0, ',', '.') ?></p>
                                    <small>ID Produk: <?= $item['id_produk'] ?></small>
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
                    
                    <a href="konfirmasi_pembayaran.php?id_pesanan=<?= $id_pesanan ?>" class="btn btn-primary">Konfirmasi Pembayaran</a>
                </div>
                
                <!-- DETAIL PESANAN -->
                <div class="order-details">
                    <h2>Detail Pesanan</h2>
                    
                    <div class="order-summary">
                        <h2 class="summary-title">Ringkasan Pesanan</h2>
                        
                        <div class="summary-items">
                            <?php foreach ($orderData['items'] as $item): ?>
                            <div class="summary-item">
                                <img src="/admin/Admin_WebTanaman/uploads/<?= htmlspecialchars($item['foto']) ?>" alt="<?= htmlspecialchars($item['nama_tanaman']) ?>" class="item-image">
                                <div class="item-info">
                                    <h3><?= htmlspecialchars($item['nama_tanaman']) ?></h3>
                                    <p><?= $item['jumlah'] ?> x Rp<?= number_format($item['harga'], 0, ',', '.') ?></p>
                                    <small>ID: <?= $item['id_produk'] ?></small>
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

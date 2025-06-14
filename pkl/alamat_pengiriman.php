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

// Fungsi untuk mendapatkan data pesanan dari database
function getOrderDataFromDatabase($id_pelanggan, $source = 'cart') {
    global $koneksi;
    $orderItems = [];
    $totalHarga = 0;
    
    // Query untuk mengambil data ringkasan pesanan dari database
    $query = "SELECT r.*, p.nama_tanaman, p.foto 
              FROM ringkasan_pesanan r 
              JOIN produk p ON r.id_produk = p.id_produk 
              WHERE r.id_pelanggan = '$id_pelanggan'";
              
    if ($source === 'buy_now' && isset($_GET['id_produk'])) {
        $id_produk = $_GET['id_produk'];
        $query .= " AND r.id_produk = '$id_produk'";
    }
    
    $result = mysqli_query($koneksi, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $subtotal = $row['harga'] * $row['jumlah'];
            $orderItems[] = [
                'id_produk' => $row['id_produk'],
                'nama_tanaman' => $row['nama_tanaman'],
                'harga' => $row['harga'],
                'foto' => $row['foto'],
                'jumlah' => $row['jumlah'],
                'subtotal' => $subtotal,
                'id_ringkasan' => $row['id_ringkasan']
            ];
            $totalHarga += $subtotal;
        }
    } else {
        // Fallback ke metode lama jika tidak ada data di database
        return getOrderDataFromSession($source);
    }
    
    return [
        'items' => $orderItems,
        'subtotal' => $totalHarga
    ];
}

// Fungsi fallback untuk mendapatkan data dari session jika database kosong
function getOrderDataFromSession($source = 'cart') {
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
    } else if ($source === 'buy_now' && isset($_GET['id_produk'])) {
        // Ambil dari beli sekarang
        $id_produk = $_GET['id_produk'];
        $quantity = isset($_GET['qty']) ? (int)$_GET['qty'] : 1;
        
        $query = $koneksi->query("SELECT * FROM produk WHERE id_produk='$id_produk'");
        $produk = $query->fetch_assoc();
        
        if ($produk) {
            $subtotal = $produk['harga'] * $quantity;
            $orderItems[] = [
                'id_produk' => $id_produk,
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

// Tentukan sumber data berdasarkan parameter
$source = isset($_GET['source']) ? $_GET['source'] : (isset($_POST['order_source']) ? $_POST['order_source'] : (isset($_SESSION['order_data']['source']) ? $_SESSION['order_data']['source'] : 'cart'));
$product_id = isset($_GET['id_produk']) ? $_GET['id_produk'] : (isset($_POST['product_id']) ? $_POST['product_id'] : (isset($_SESSION['order_data']['product_id']) ? $_SESSION['order_data']['product_id'] : null));
$quantity = isset($_GET['qty']) ? (int)$_GET['qty'] : (isset($_POST['quantity']) ? (int)$_POST['quantity'] : (isset($_SESSION['order_data']['quantity']) ? $_SESSION['order_data']['quantity'] : 1));

// Ambil data pesanan dari database
$orderData = getOrderDataFromDatabase($id_pelanggan, $source);

// Simpan data pesanan ke session untuk persistensi
$_SESSION['order_data'] = [
    'items' => $orderData['items'],
    'subtotal' => $orderData['subtotal'],
    'source' => $source,
    'product_id' => $product_id,
    'quantity' => $quantity
];

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

// Tentukan metode pengiriman (default atau dari session)
$selectedShipping = isset($_SESSION['shipping_method']) ? $_SESSION['shipping_method'] : 'jne';
$shippingCost = getShippingCost($selectedShipping);
$shippingName = getShippingName($selectedShipping);

// Hitung total
$total = $orderData['subtotal'] + $shippingCost;

// Proses jika alamat dipilih
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pilih_alamat'])) {
    $selected_address_id = $_POST['id_pengiriman'];
    $_SESSION['alamat_terpilih'] = $selected_address_id;
    
    // Simpan data pesanan ke session
    $_SESSION['order_data']['shipping_address_id'] = $selected_address_id;
    $_SESSION['order_data']['shipping_method'] = $selectedShipping;
    $_SESSION['order_data']['shipping_cost'] = $shippingCost;
    $_SESSION['order_data']['total'] = $total;
    
    // Simpan metode pengiriman ke database untuk setiap item
    if (!empty($orderData['items'])) {
        foreach ($orderData['items'] as $item) {
            if (isset($item['id_ringkasan'])) {
                $id_ringkasan = $item['id_ringkasan'];
                $update_query = "UPDATE ringkasan_pesanan SET 
                                metode_pengiriman = '$selectedShipping'
                                WHERE id_ringkasan = '$id_ringkasan'";
                mysqli_query($koneksi, $update_query);
            }
        }
    }
    
    // Redirect ke halaman metode pengiriman
    header("Location: metode_pengiriman.php");
    exit;
}

// Query alamat milik user
$sql_alamat = "SELECT * FROM pengiriman WHERE id_pelanggan = '$id_pelanggan' ORDER BY is_primary DESC, id_pengiriman DESC";
$query_alamat = mysqli_query($koneksi, $sql_alamat);

if ($query_alamat === false) {
    $_SESSION['error_message'] = "Terjadi kesalahan saat mengambil data alamat: " . mysqli_error($koneksi);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alamat Pengiriman - Toko Tanaman</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .address-form {
    background: #f9f9f9;
    padding: 20px;
    border-radius: 8px;
    margin-top: 20px;
    display: none;
    animation: slideDown 0.3s ease;
}

.address-form.show {
    display: block;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.form-grid {
    display: grid;
    gap: 20px;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group.full-width {
    grid-column: 1 / -1;
}

.form-group label {
    margin-bottom: 5px;
    font-weight: 500;
    color: #333;
}

.form-group label.required::after {
    content: " *";
    color: #dc3545;
}

.form-group input,
.form-group select,
.form-group textarea {
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #4CAF50;
    box-shadow: 0 0 0 2px rgba(76, 175, 80, 0.2);
}

.field-help {
    font-size: 12px;
    color: #666;
    margin-top: 5px;
}

.char-counter {
    font-size: 12px;
    color: #666;
    text-align: right;
    margin-top: 5px;
}

.form-check {
    display: flex;
    align-items: center;
    gap: 8px;
}

.form-check-input {
    width: auto;
}

.form-actions {
    display: flex;
    gap: 10px;
    justify-content: flex-end;
    margin-top: 20px;
}

.btn {
    padding: 10px 20px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    transition: all 0.3s ease;
}

.btn-primary {
    background: #4CAF50;
    color: white;
}

.btn-primary:hover {
    background: #45a049;
}

.btn-outline {
    background: transparent;
    color: #666;
    border: 1px solid #ddd;
}

.btn-outline:hover {
    background: #f5f5f5;
}

.checkout-section {
    padding: 40px 0;
    background-color: #f8f9fa;
}

.checkout-steps {
    display: flex;
    justify-content: center;
    margin-bottom: 40px;
    background: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.step {
    display: flex;
    align-items: center;
    margin: 0 30px;
    color: #999;
}

.step.active {
    color: #4CAF50;
}

.step-number {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: #e0e0e0;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 10px;
    font-weight: bold;
}

.step.active .step-number {
    background: #4CAF50;
    color: white;
}

.checkout-content {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 30px;
    max-width: 1200px;
    margin: 0 auto;
}

.checkout-form {
    background: white;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.order-summary {
    background: white;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    height: fit-content;
    position: sticky;
    top: 20px;
}

.address-card {
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 15px;
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
}

.address-card:hover {
    border-color: #4CAF50;
    box-shadow: 0 4px 12px rgba(76, 175, 80, 0.2);
}

.address-card.selected {
    border-color: #4CAF50;
    background-color: #f8fff8;
}

.address-card.selected::before {
    content: '✓';
    position: absolute;
    top: 15px;
    right: 15px;
    background: #4CAF50;
    color: white;
    border-radius: 50%;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    font-weight: bold;
}

.use-address-btn {
    background-color: #4CAF50;
    color: white;
    border: none;
    padding: 12px 20px;
    border-radius: 6px;
    cursor: pointer;
    margin-top: 15px;
    display: block;
    width: 100%;
    text-align: center;
    font-weight: bold;
    font-size: 14px;
    transition: background 0.3s ease;
}

.use-address-btn:hover {
    background-color: #45a049;
}

.alert {
    padding: 15px;
    margin-bottom: 20px;
    border-radius: 6px;
    display: flex;
    align-items: center;
}

.alert i {
    margin-right: 10px;
}

.alert-success {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.alert-error {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.empty-state {
    text-align: center;
    padding: 50px;
    color: #666;
    background: #f9f9f9;
    border-radius: 8px;
    margin: 20px 0;
}

.empty-state i {
    font-size: 48px;
    margin-bottom: 20px;
    color: #ccc;
}

.form-toggle {
    background: #4CAF50;
    color: white;
    border: none;
    padding: 12px 24px;
    border-radius: 6px;
    cursor: pointer;
    margin-top: 15px;
    font-size: 14px;
    transition: background 0.3s ease;
    text-decoration: none;
    display: inline-block;
}

.form-toggle:hover {
    background: #45a049;
}

.summary-items {
    margin-bottom: 20px;
}

.summary-item {
    display: flex;
    align-items: center;
    padding: 15px 0;
    border-bottom: 1px solid #eee;
}

.item-image {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 6px;
    margin-right: 15px;
}

.item-info {
    flex: 1;
}

.item-info h3 {
    margin: 0 0 5px 0;
    font-size: 14px;
    color: #333;
}

.item-info p {
    margin: 0;
    font-size: 12px;
    color: #666;
}

.item-price {
    font-weight: bold;
    color: #4CAF50;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    padding: 10px 0;
    border-bottom: 1px solid #eee;
}

.summary-row.total {
    border-bottom: none;
    font-weight: bold;
    font-size: 16px;
    color: #4CAF50;
    margin-top: 10px;
    padding-top: 15px;
    border-top: 2px solid #4CAF50;
}

.address-actions {
    margin-top: 10px;
}

.address-actions .btn {
    margin-right: 5px;
}

.btn-sm {
    padding: 5px 10px;
    font-size: 12px;
}

.btn-danger {
    color: #dc3545;
}

.btn-danger:hover {
    background: #fff1f1;
}

@media (max-width: 768px) {
    .checkout-content {
        grid-template-columns: 1fr;
        gap: 20px;
    }
    
    .form-row {
        grid-template-columns: 1fr;
    }
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
            </div>
            
            <div class="checkout-content">
                <div class="checkout-form">
                    <h2>Pilih Alamat Pengiriman</h2>
                    
                    <div style="background: #e3f2fd; padding: 10px 15px; border-radius: 6px; margin-bottom: 20px; font-size: 14px;">
                        <i class="fas fa-<?= $source === 'cart' ? 'shopping-cart' : 'bolt' ?>"></i>
                        <?php if ($source === 'cart'): ?>
                            Checkout dari Keranjang Belanja
                        <?php else: ?>
                            Beli Sekarang - <?= isset($orderData['items'][0]) ? htmlspecialchars($orderData['items'][0]['nama_tanaman']) : 'Produk' ?>
                        <?php endif; ?>
                    </div>
                    
                    <?php if(isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <?= $_SESSION['success_message']; ?>
                        <?php unset($_SESSION['success_message']); ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if(isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <?= $_SESSION['error_message']; ?>
                        <?php unset($_SESSION['error_message']); ?>
                    </div>
                    <?php endif; ?>
                    
                    <div class="address-list">
                        <?php if ($query_alamat && mysqli_num_rows($query_alamat) > 0) :  ?>
                            <?php while($alamat = mysqli_fetch_assoc($query_alamat)): ?>
                            <form method="POST" class="address-form-select" id="form_<?= $alamat['id_pengiriman'] ?>">
                                <input type="hidden" name="alamat_id" value="<?= $alamat['id_pengiriman'] ?>">
                                <input type="hidden" name="order_source" value="<?= htmlspecialchars($source) ?>">
                                <?php if ($source === 'buy_now'): ?>
                                    <input type="hidden" name="product_id" value="<?= htmlspecialchars($product_id) ?>">
                                    <input type="hidden" name="quantity" value="<?= $quantity ?>">
                                <?php endif; ?>
                                <input type="hidden" name="pilih_alamat" value="1">
                                
                                <div class="address-card <?= (isset($_SESSION['alamat_terpilih']) && $_SESSION['alamat_terpilih'] == $alamat['id_pengiriman']) ? 'selected' : '' ?>" 
                                     onclick="selectAndSubmit(<?= $alamat['id_pengiriman'] ?>)">
                                    <h3>
                                        <?= htmlspecialchars($alamat['label_alamat']) ?>
                                        <?php if (isset($alamat['is_primary']) && $alamat['is_primary']): ?>
                                            <span style="background: #28a745; color: white; padding: 2px 8px; border-radius: 12px; font-size: 11px; margin-left: 8px;">Utama</span>
                                        <?php endif; ?>
                                    </h3>
                                    
                                    <p class="address-name"><strong><?= htmlspecialchars($alamat['nama_penerima']) ?></strong></p>
                                    <p class="address-phone"><i class="fas fa-phone"></i> <?= htmlspecialchars($alamat['no_telepon']) ?></p>
                                    <p class="address-detail"><i class="fas fa-map-marker-alt"></i> 
                                        <?= htmlspecialchars($alamat['alamat_lengkap'] . ', ' . $alamat['kecamatan'] . ', ' . $alamat['kota'] . ', ' . $alamat['provinsi']) ?>
                                    </p>
                                    
                                    <div class="address-actions" onclick="event.stopPropagation();">
                                        <a href="edit_alamat.php?id=<?= $alamat['id_pengiriman'] ?>" class="btn btn-outline btn-sm">
                                            <i class="fas fa-edit"></i> Ubah
                                        </a>
                                        <a href="hapus_alamat.php?id_pengiriman=<?= $alamat['id_pengiriman'] ?>" 
                                           class="btn btn-outline btn-sm btn-danger"
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
                    
                    <button type="button" class="form-toggle" onclick="toggleAddressForm()">
                        <i class="fas fa-plus"></i> Tambah Alamat Baru
                    </button>

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
                                <?php if ($source === 'buy_now'): ?>
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
                </div>

                <div class="order-summary">
                    <h2><i class="fas fa-receipt"></i> Ringkasan Pesanan</h2>
                    
                    <?php if (!empty($orderData['items'])): ?>
                        <div class="summary-items">
                            <?php foreach ($orderData['items'] as $item): ?>
                            <div class="summary-item">
                                <img src="/admin/uploads/<?= htmlspecialchars($item['foto']) ?>" alt="<?= htmlspecialchars($item['nama_tanaman']) ?>" class="item-image">
                                <div class="item-info">
                                    <h3><?= htmlspecialchars($item['nama_tanaman']) ?></h3>
                                    <p><?= $item['jumlah'] ?> x Rp<?= number_format($item['harga'], 0, ',', '.') ?></p>
                                    <?php if (isset($item['id_ringkasan'])): ?>
                                    <small style="color: #666;">ID Ringkasan: <?= $item['id_ringkasan'] ?></small>
                                    <?php endif; ?>
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
                        
                        <div class="summary-row total">
                            <span>Total</span>
                            <span>Rp<?= number_format($total, 0, ',', '.') ?></span>
                        </div>
                        
                        <div style="text-align: center; margin-top: 20px; padding: 15px; background: #e8f5e8; border-radius: 6px;">
                            <p><i class="fas fa-info-circle"></i> Pilih alamat untuk melanjutkan</p>
                        </div>
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
        <div class="container">
            <div class="footer-content">
                <div class="footer-logo">
                    <img src="images/logo.png" alt="Toko Tanaman">
                    <p>Toko tanaman hias terpercaya</p>
                </div>
                <div class="footer-contact">
                    <h3>Kontak Kami</h3>
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
        function selectAndSubmit(id) {
            // Remove selected class from all cards
            document.querySelectorAll('.address-card').forEach(card => {
                card.classList.remove('selected');
            });
            
            // Add selected class to clicked card
            event.currentTarget.classList.add('selected');
            
            // Submit the form automatically
            document.getElementById('form_' + id).submit();
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
                // Scroll to form
                form.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        }
        
        // Character counter for alamat_lengkap
        document.addEventListener('DOMContentLoaded', function() {
            const alamatTextarea = document.getElementById('alamat_lengkap');
            const alamatCount = document.getElementById('alamat-count');
            
            if (alamatTextarea && alamatCount) {
                alamatTextarea.addEventListener('input', function() {
                    alamatCount.textContent = this.value.length;
                });
            }
            
            // Mark selected address if exists in session
            <?php if(isset($_SESSION['alamat_terpilih'])): ?>
            const selectedAddressId = <?= $_SESSION['alamat_terpilih'] ?>;
            const selectedCard = document.querySelector(`.address-card[data-id="${selectedAddressId}"]`);
            if (selectedCard) {
                selectedCard.classList.add('selected');
            }
            <?php endif; ?>
        });
        
        // Debug logging
        console.log('Order Source:', '<?= $source ?>');
        console.log('Product ID:', '<?= $product_id ?>');
        console.log('Quantity:', <?= $quantity ?>);
        console.log('Order Items:', <?= json_encode($orderData['items']) ?>);
    </script>
</body>
</html>

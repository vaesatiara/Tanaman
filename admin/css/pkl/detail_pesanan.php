<?php
session_start();
include "koneksi.php";

// Periksa apakah user sudah login
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Periksa apakah ada ID pesanan
if (!isset($_GET['id_pesanan']) || empty($_GET['id_pesanan'])) {
    header("Location: riwayat_pesanan.php");
    exit();
}

$id_pesanan = intval($_GET['id_pesanan']);

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

// Ambil data pelanggan
$username = $_SESSION['username'];
$sql_pelanggan = "SELECT * FROM pelanggan WHERE username = '$username'";
$query_pelanggan = mysqli_query($koneksi, $sql_pelanggan);
$pelanggan = mysqli_fetch_assoc($query_pelanggan);

// Fungsi untuk mengambil produk pesanan dengan foto yang benar
function getOrderProducts($koneksi, $id_pesanan) {
    $products = [];
    
    // Coba beberapa cara untuk mendapatkan produk pesanan
    $queries = [
        // Method 1: Join dengan detail_pesanan
        "SELECT p.nama_tanaman, p.foto, dp.jumlah, dp.harga_satuan as harga, dp.subtotal, p.id_produk, p.kategori
         FROM produk p 
         JOIN detail_pesanan dp ON p.id_produk = dp.id_produk 
         WHERE dp.id_pesanan = $id_pesanan",
        
        // Method 2: Jika struktur berbeda
        "SELECT p.nama_tanaman, p.foto, 1 as jumlah, p.harga, p.harga as subtotal, p.id_produk, p.kategori
         FROM produk p 
         JOIN pesanan ps ON p.id_produk = ps.id_produk 
         WHERE ps.id_pesanan = $id_pesanan",
         
        // Method 3: Fallback dengan data dummy jika tidak ada relasi
        "SELECT 'Produk Pesanan' as nama_tanaman, 'default.jpg' as foto, 1 as jumlah, 
                total as harga, total as subtotal, 0 as id_produk, 'Umum' as kategori
         FROM pesanan WHERE id_pesanan = $id_pesanan"
    ];
    
    foreach ($queries as $sql) {
        $result = $koneksi->query($sql);
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $products[] = $row;
            }
            return $products;
        }
    }
    
    return $products;
}

// Fungsi untuk mengecek dan menentukan path foto yang benar - DIPERBAIKI
function getProductImagePath($foto) {
    // Jika foto kosong atau default, langsung return default image
    if (empty($foto) || $foto == 'default.jpg') {
        return 'images/default-product.jpg';
    }
    
    // Cek beberapa kemungkinan lokasi foto
    $possible_paths = [
        'uploads/' . $foto,
        'images/products/' . $foto,
        'images/produk/' . $foto,
        'images/' . $foto,
        'assets/images/products/' . $foto,
        'assets/images/' . $foto,
        'assets/uploads/' . $foto,
        'img/products/' . $foto,
        'img/' . $foto
    ];
    
    foreach ($possible_paths as $path) {
        if (file_exists($path)) {
            return $path;
        }
    }
    
    // Jika tidak ditemukan di server, coba return path asli (mungkin foto ada tapi path berbeda)
    return !empty($foto) ? 'uploads/' . $foto : 'images/default-product.jpg';
}

// Fungsi untuk mengambil detail pesanan lengkap
function getOrderDetail($koneksi, $id_pesanan, $id_pelanggan) {
    // Ambil data pesanan utama
    $sql_order = "SELECT * FROM pesanan WHERE id_pesanan = $id_pesanan AND id_pelanggan = '$id_pelanggan'";
    $result = $koneksi->query($sql_order);
    
    if (!$result || $result->num_rows == 0) {
        return null;
    }
    
    $order = $result->fetch_assoc();
    
    // Ambil detail produk pesanan
    $order['items'] = getOrderProducts($koneksi, $id_pesanan);
    
    // Ambil data pengiriman jika ada
    $sql_shipping = "SELECT * FROM detail_pengiriman WHERE id_pesanan = $id_pesanan";
    $result_shipping = $koneksi->query($sql_shipping);
    
    if ($result_shipping && $result_shipping->num_rows > 0) {
        $order['shipping_info'] = $result_shipping->fetch_assoc();
    }
    
    return $order;
}

// Fungsi helper untuk status badge
function getStatusBadgeClass($status) {
    switch ($status) {
        case 'diproses':
            return 'status-processing';
        case 'menunggu_verifikasi':
            return 'status-pending';
        case 'diverifikasi':
            return 'status-verified';
        case 'dikirim':
            return 'status-shipped';
        case 'selesai':
            return 'status-completed';
        case 'dibatalkan':
            return 'status-cancelled';
        default:
            return 'status-pending';
    }
}

// Fungsi helper untuk nama status
function getStatusName($status) {
    switch ($status) {
        case 'diproses':
            return 'Belum Dibayar';
        case 'menunggu_verifikasi':
            return 'Menunggu Verifikasi';
        case 'diverifikasi':
            return 'Sedang Diproses';
        case 'dikirim':
            return 'Dikirim';
        case 'selesai':
            return 'Selesai';
        case 'dibatalkan':
            return 'Dibatalkan';
        default:
            return ucfirst($status);
    }
}

// Ambil detail pesanan
$orderDetail = getOrderDetail($koneksi, $id_pesanan, $id_pelanggan);

if (!$orderDetail) {
    echo "<script>alert('Pesanan tidak ditemukan atau Anda tidak memiliki akses ke pesanan ini.'); window.location.href='riwayat_pesanan.php';</script>";
    exit();
}

// Hitung total dan subtotal
$subtotal = 0;
foreach ($orderDetail['items'] as $item) {
    $subtotal += $item['subtotal'];
}

$shipping_cost = isset($orderDetail['shipping_info']['biaya_pengiriman']) ? $orderDetail['shipping_info']['biaya_pengiriman'] : 0;
$total = $subtotal + $shipping_cost;

// Fungsi helper untuk payment method
function getPaymentMethodDisplay($method) {
    $methods = [
        'bca' => ['name' => 'Transfer Bank BCA', 'icon' => 'fas fa-university'],
        'bni' => ['name' => 'Transfer Bank BNI', 'icon' => 'fas fa-university'],
        'mandiri' => ['name' => 'Transfer Bank Mandiri', 'icon' => 'fas fa-university'],
        'gopay' => ['name' => 'GoPay', 'icon' => 'fas fa-mobile-alt'],
        'ovo' => ['name' => 'OVO', 'icon' => 'fas fa-mobile-alt'],
        'dana' => ['name' => 'DANA', 'icon' => 'fas fa-mobile-alt']
    ];
    
    return $methods[$method] ?? ['name' => 'Transfer Bank', 'icon' => 'fas fa-credit-card'];
}

// Fungsi helper untuk shipping method
function getShippingMethodDisplay($method) {
    $methods = [
        'jne' => ['name' => 'JNE Regular', 'icon' => 'fas fa-truck'],
        'jnt' => ['name' => 'J&T Express', 'icon' => 'fas fa-truck'],
        'ninja' => ['name' => 'Ninja Xpress', 'icon' => 'fas fa-truck'],
        'anteraja' => ['name' => 'AnterAja Regular', 'icon' => 'fas fa-truck']
    ];
    
    return $methods[$method] ?? ['name' => 'JNE Regular', 'icon' => 'fas fa-truck'];
}

$paymentMethod = 'bca'; // Default atau ambil dari database jika ada
$shippingMethod = isset($orderDetail['shipping_info']['metode_pengiriman']) ? $orderDetail['shipping_info']['metode_pengiriman'] : 'jne';

$paymentDisplay = getPaymentMethodDisplay($paymentMethod);
$shippingDisplay = getShippingMethodDisplay($shippingMethod);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pesanan - Toko Tanaman</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Order Summary Styles */
        .order-summary-card {
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 12px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .order-header {
            padding: 20px;
            border-bottom: 1px solid #f0f0f0;
            background: #fafafa;
        }

        .order-info h3.order-number {
            margin: 0 0 8px 0;
            font-size: 18px;
            font-weight: 600;
            color: #333;
        }

        .order-info .order-date {
            margin: 0 0 12px 0;
            color: #666;
            font-size: 14px;
        }

        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-processing {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }

        .status-pending {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .status-verified {
            background-color: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }

        .status-shipped {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .status-completed {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .status-cancelled {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .order-items-container {
            padding: 20px;
        }

        .items-title {
            margin: 0 0 15px 0;
            font-size: 16px;
            font-weight: 600;
            color: #333;
        }

        .order-items {
            margin-bottom: 15px;
        }

        .order-item {
            display: flex;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #f5f5f5;
        }

        .order-item:last-child {
            border-bottom: none;
        }

        .item-image {
            width: 80px;
            height: 80px;
            margin-right: 15px;
            flex-shrink: 0;
            position: relative;
            overflow: hidden;
            border-radius: 8px;
            border: 1px solid #e0e0e0;
            background-color: #f8f9fa;
        }

        .item-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: opacity 0.3s ease;
        }

        .item-image img.loading {
            opacity: 0.7;
        }

        .item-image .image-placeholder {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f8f9fa;
            color: #6c757d;
            font-size: 24px;
            z-index: 1;
        }

        .item-image .image-placeholder.hidden {
            display: none;
        }

        .item-details {
            flex: 1;
            min-width: 0;
        }

        .item-details h5 {
            margin: 0 0 5px 0;
            font-size: 15px;
            font-weight: 500;
            color: #333;
            line-height: 1.3;
        }

        .item-quantity {
            margin: 0 0 3px 0;
            color: #666;
            font-size: 13px;
        }

        .item-id {
            color: #999;
            font-size: 11px;
        }

        .item-price {
            text-align: right;
            font-weight: 600;
            color: #28a745;
            font-size: 14px;
        }

        .show-more-container {
            text-align: center;
            margin: 15px 0;
        }

        .show-more-btn {
            background: none;
            border: 1px solid #ddd;
            color: #666;
            padding: 8px 16px;
            border-radius: 20px;
            cursor: pointer;
            font-size: 13px;
            transition: all 0.3s ease;
        }

        .show-more-btn:hover {
            background: #f8f9fa;
            border-color: #28a745;
            color: #28a745;
        }

        .show-more-btn i {
            margin-right: 5px;
        }

        .order-summary-total {
            padding: 20px;
            border-top: 1px solid #f0f0f0;
            background: #fafafa;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .summary-row:last-child {
            margin-bottom: 0;
        }

        .total-row {
            padding-top: 8px;
            border-top: 1px solid #e0e0e0;
            font-size: 16px;
            color: #333;
        }

        .order-detail-section {
            background: white;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        
        .order-detail-section h2 {
            margin: 0 0 20px 0;
            font-size: 20px;
            color: #333;
            border-bottom: 2px solid #28a745;
            padding-bottom: 10px;
        }
        
        .order-detail-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .info-item {
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid #28a745;
        }
        
        .info-item h3 {
            margin: 0 0 8px 0;
            font-size: 14px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .info-item p {
            margin: 0;
            font-size: 16px;
            font-weight: 600;
            color: #333;
        }
        
        .shipping-info, .payment-info {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
            margin-bottom: 15px;
        }
        
        .method-icon {
            width: 50px;
            height: 50px;
            background: #28a745;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
        }
        
        .method-details h3 {
            margin: 0 0 5px 0;
            font-size: 16px;
            color: #333;
        }
        
        .method-details p {
            margin: 0;
            color: #666;
            font-size: 14px;
        }
        
        .address-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #17a2b8;
        }
        
        .address-info h3 {
            margin: 0 0 15px 0;
            color: #333;
            font-size: 16px;
        }
        
        .address-info p {
            margin: 0 0 8px 0;
            color: #555;
        }
        
        .address-info p:last-child {
            margin-bottom: 0;
        }
        
        .recipient-name {
            font-weight: 600;
            color: #333 !important;
        }
        
        .tracking-info {
            background: #e8f5e8;
            padding: 15px;
            border-radius: 8px;
            margin-top: 15px;
            border-left: 4px solid #28a745;
        }
        
        .tracking-info p {
            margin: 0;
            color: #155724;
            font-weight: 500;
        }
        
        .order-actions {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            margin-top: 30px;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            padding: 12px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            border: 2px solid;
            transition: all 0.3s ease;
        }
        
        .btn i {
            margin-right: 8px;
        }
        
        .btn-primary {
            background: #28a745;
            color: white;
            border-color: #28a745;
        }
        
        .btn-primary:hover {
            background: #218838;
            border-color: #1e7e34;
        }
        
        .btn-outline {
            background: white;
            color: #666;
            border-color: #ddd;
        }
        
        .btn-outline:hover {
            background: #f8f9fa;
            border-color: #999;
            color: #333;
        }
        
        .btn-back {
            background: #6c757d;
            color: white;
            border-color: #6c757d;
        }
        
        .btn-back:hover {
            background: #5a6268;
            border-color: #545b62;
        }
        
        @media (max-width: 768px) {
            .order-detail-info {
                grid-template-columns: 1fr;
            }
            
            .shipping-info, .payment-info {
                flex-direction: column;
                text-align: center;
            }
            
            .order-actions {
                flex-direction: column;
            }
            
            .order-item {
                flex-direction: column;
                align-items: flex-start;
                text-align: left;
            }

            .item-image {
                margin-right: 0;
                margin-bottom: 10px;
                width: 100px;
                height: 100px;
            }

            .item-price {
                text-align: left;
                margin-top: 5px;
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
                <a href="profil.php" class="active"><i class="fas fa-user"></i></a>
            </div>
        </div>
    </header>

    <main class="profile-section">
        <div class="container">
            <div class="profile-container">
                <div class="profile-sidebar">
                    <div class="profile-header">
                        <div class="profile-avatar">
                            <img src="images/user.jpg" alt="<?= htmlspecialchars($pelanggan['username']) ?>">
                        </div>
                        <div class="profile-info">
                            <h2><?= htmlspecialchars($pelanggan['username']) ?></h2>
                            <p><?= htmlspecialchars($pelanggan['email']) ?></p>
                        </div>
                    </div>
                    <div class="profile-nav">
                        <ul>
                            <li>
                                <a href="profil.php">
                                    <i class="fas fa-user"></i> Profil Saya
                                </a>
                            </li>
                            <li class="active">
                                <a href="riwayat_pesanan.php">
                                    <i class="fas fa-shopping-bag"></i> Riwayat Pesanan
                                </a>
                            </li>
                            <li>
                                <a href="alamat_tersimpan.php">
                                    <i class="fas fa-map-marker-alt"></i> Alamat Tersimpan
                                </a>
                            </li>
                            <li>
                                <a href="ubah_password.php">
                                    <i class="fas fa-lock"></i> Ubah Password
                                </a>
                            </li>
                            <li>
                                <a href="logout.php" class="logout">
                                    <i class="fas fa-sign-out-alt"></i> Logout
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
                
                <div class="profile-content">
                    <div class="content-header">
                        <h1>Detail Pesanan</h1>
                        <a href="riwayat_pesanan.php" class="btn btn-back">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                    
                    <!-- Informasi Pesanan -->
                    <div class="order-detail-section">
                        <h2>Informasi Pesanan</h2>
                        <div class="order-detail-info">
                            <div class="info-item">
                                <h3>Nomor Pesanan</h3>
                                <p><?= htmlspecialchars($orderDetail['nomor_pesanan']) ?></p>
                            </div>
                            <div class="info-item">
                                <h3>Tanggal Pembelian</h3>
                                <p><?= date('d M Y, H:i', strtotime($orderDetail['tgl_pesanan'])) ?> WIB</p>
                            </div>
                            <div class="info-item">
                                <h3>Status Pesanan</h3>
                                <p>
                                    <span class="status-badge <?= getStatusBadgeClass($orderDetail['status_pesanan']) ?>">
                                        <?= getStatusName($orderDetail['status_pesanan']) ?>
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Detail Produk -->
                    <div class="order-detail-section">
                        <h2>Detail Produk</h2>
                        <div class="order-summary-card">
                            <div class="order-items-container">
                                <div class="order-items visible-items">
                                    <?php foreach ($orderDetail['items'] as $item): ?>
                                    <div class="order-item">
                                        <div class="item-image">
                                            <div class="image-placeholder" id="placeholder-<?= $item['id_produk'] ?>">
                                                <i class="fas fa-seedling"></i>
                                            </div>
                                            <img src="<?= getProductImagePath($item['foto']) ?>" 
                                                 alt="<?= htmlspecialchars($item['nama_tanaman']) ?>"
                                                 onload="imageLoaded(this, <?= $item['id_produk'] ?>)"
                                                 onerror="imageError(this, <?= $item['id_produk'] ?>)"
                                                 style="position: relative; z-index: 2;">
                                        </div>
                                        <div class="item-details">
                                            <h5><?= htmlspecialchars($item['nama_tanaman']) ?></h5>
                                            <p class="item-quantity"><?= $item['jumlah'] ?> x Rp<?= number_format($item['harga'], 0, ',', '.') ?></p>
                                            <?php if (isset($item['kategori'])): ?>
                                            <small class="item-id">Kategori: <?= htmlspecialchars($item['kategori']) ?></small>
                                            <?php endif; ?>
                                        </div>
                                        <div class="item-price">
                                            <span>Rp<?= number_format($item['subtotal'], 0, ',', '.') ?></span>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            
                            <div class="order-summary-total">
                                <div class="summary-row">
                                    <span>Total Harga (<?= count($orderDetail['items']) ?> Produk):</span>
                                    <span>Rp<?= number_format($subtotal, 0, ',', '.') ?></span>
                                </div>
                                <div class="summary-row">
                                    <span>Biaya Pengiriman:</span>
                                    <span>Rp<?= number_format($shipping_cost, 0, ',', '.') ?></span>
                                </div>
                                <div class="summary-row total-row">
                                    <span><strong>Total Pembayaran:</strong></span>
                                    <span><strong>Rp<?= number_format($total, 0, ',', '.') ?></strong></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Informasi Pengiriman -->
                    <div class="order-detail-section">
                        <h2>Informasi Pengiriman</h2>
                        
                        <div class="address-info">
                            <h3>Alamat Pengiriman</h3>
                            
                            <p class="recipient-name"><?= htmlspecialchars($pelanggan['username']) ?></p>
                            <p><?= htmlspecialchars($pelanggan['no_telepon'] ?? '0812-3456-7890') ?></p>
                            <p><?= htmlspecialchars($pelanggan['alamat'] ?? 'Jl. Tanaman Indah No. 123, Purwokerto, 53116') ?></p>
                        </div>
                        
                        <div class="shipping-info">
                            <div class="method-icon">
                                <i class="<?= $shippingDisplay['icon'] ?>"></i>
                            </div>
                            <div class="method-details">
                                <h3><?= $shippingDisplay['name'] ?></h3>
                                <p>Estimasi pengiriman: 2-3 hari kerja</p>
                            </div>
                        </div>
                        
                        <?php if ($orderDetail['status_pesanan'] === 'dikirim' || $orderDetail['status_pesanan'] === 'selesai'): ?>
                        <div class="tracking-info">
                            <p><i class="fas fa-truck"></i> No. Resi: JP<?= str_pad($orderDetail['id_pesanan'], 6, "0", STR_PAD_LEFT) ?>789012</p>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Rincian Pembayaran -->
                    <div class="order-detail-section">
                        <h2>Rincian Pembayaran</h2>
                        
                        <div class="payment-info">
                            <div class="method-icon">
                                <i class="<?= $paymentDisplay['icon'] ?>"></i>
                            </div>
                            <div class="method-details">
                                <h3><?= $paymentDisplay['name'] ?></h3>
                                <p><?= $orderDetail['status_pesanan'] === 'diproses' ? 'Menunggu pembayaran' : 'Pembayaran telah dikonfirmasi' ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="order-actions">
                        <?php if ($orderDetail['status_pesanan'] === 'selesai'): ?>
                            <a href="produk.php" class="btn btn-primary">
                                <i class="fas fa-shopping-cart"></i> Beli Lagi
                            </a>
                        <?php elseif ($orderDetail['status_pesanan'] === 'dikirim'): ?>
                            <a href="lacak_pengiriman.php?id_pesanan=<?= $orderDetail['id_pesanan'] ?>" class="btn btn-primary">
                                <i class="fas fa-truck"></i> Lacak Pengiriman
                            </a>
                        <?php elseif ($orderDetail['status_pesanan'] === 'diproses'): ?>
                            <a href="konfirmasi_pembayaran.php?order_id=<?= $orderDetail['id_pesanan'] ?>" class="btn btn-primary">
                                <i class="fas fa-credit-card"></i> Bayar Sekarang
                            </a>
                        <?php endif; ?>
                        
                        <a href="kontak.php" class="btn btn-outline">
                            <i class="fas fa-headset"></i> Hubungi Penjual
                        </a>
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

    <script>
        // JavaScript untuk menampilkan/menyembunyikan item pesanan
        function toggleOrderItems(summaryId) {
            const hiddenItems = document.getElementById("hidden-items-" + summaryId);
            const showMoreBtn = document.querySelector("#" + summaryId + " .show-more-btn");
            const showText = showMoreBtn.querySelector(".show-text");
            const hideText = showMoreBtn.querySelector(".hide-text");

            if (hiddenItems.style.display === "none" || hiddenItems.style.display === "") {
                // Tampilkan item tersembunyi
                hiddenItems.style.display = "block";
                showText.style.display = "none";
                hideText.style.display = "inline-flex";
            } else {
                // Sembunyikan item
                hiddenItems.style.display = "none";
                showText.style.display = "inline-flex";
                hideText.style.display = "none";
            }
        }

        // Fungsi untuk copy text
        function copyToClipboard(text) {
            if (navigator.clipboard) {
                navigator.clipboard.writeText(text).then(() => {
                    showNotification("Berhasil disalin!", "success");
                });
            } else {
                // Fallback untuk browser lama
                const textArea = document.createElement("textarea");
                textArea.value = text;
                document.body.appendChild(textArea);
                textArea.select();
                document.execCommand("copy");
                document.body.removeChild(textArea);
                showNotification("Berhasil disalin!", "success");
            }
        }

        // Fungsi untuk menampilkan notifikasi
        function showNotification(message, type = "info") {
            const notification = document.createElement("div");
            notification.className = `notification notification-${type}`;
            notification.textContent = message;
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 12px 20px;
                background: ${type === "success" ? "#28a745" : "#17a2b8"};
                color: white;
                border-radius: 6px;
                z-index: 1000;
                animation: slideInRight 0.3s ease-out;
            `;

            document.body.appendChild(notification);

            setTimeout(() => {
                notification.remove();
            }, 3000);
        }

        // CSS untuk animasi notifikasi
        const style = document.createElement("style");
        style.textContent = `
            @keyframes slideInRight {
                from {
                    transform: translateX(100%);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>

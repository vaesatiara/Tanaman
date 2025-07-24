<?php
session_start();
include "koneksi.php";

// Periksa apakah user sudah login
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
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

// Ambil data pelanggan
$username = $_SESSION['username'];
$sql = "SELECT * FROM pelanggan WHERE username = '$username'";
$query = mysqli_query($koneksi, $sql);
$pelanggan = mysqli_fetch_assoc($query);

// Filter status pesanan
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';

// Query untuk mengambil riwayat pesanan
$sql_orders = "
    SELECT 
        id_pesanan,
        nomor_pesanan,
        tgl_pesanan,
        total,
        status_pesanan
    FROM pesanan 
    WHERE id_pelanggan = '$id_pelanggan'
";

// Tambahkan filter status jika diperlukan
if ($status_filter !== 'all') {
    $sql_orders .= " AND status_pesanan = '$status_filter'";
}

$sql_orders .= " ORDER BY tgl_pesanan DESC";

// Query langsung tanpa prepared statement
$result_orders = $koneksi->query($sql_orders);

// PERBAIKAN: Fungsi untuk mendapatkan status badge class
function getStatusBadgeClass($status) {
    switch ($status) {
        case 'diproses':
            return 'status-processing';
        case 'menunggu_verifikasi':
            return 'status-pending-verification';
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

// PERBAIKAN: Fungsi untuk mendapatkan nama status yang user-friendly
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

// PERBAIKAN: Fungsi untuk mendapatkan deskripsi status
function getStatusDescription($status) {
    switch ($status) {
        case 'diproses':
            return 'Pesanan belum dibayar. Silakan lakukan pembayaran.';
        case 'menunggu_verifikasi':
            return 'Pembayaran telah dikonfirmasi dan sedang menunggu verifikasi admin.';
        case 'diverifikasi':
            return 'Pembayaran telah diverifikasi. Pesanan sedang diproses.';
        case 'dikirim':
            return 'Pesanan sedang dalam perjalanan.';
        case 'selesai':
            return 'Pesanan telah selesai dan diterima.';
        case 'dibatalkan':
            return 'Pesanan telah dibatalkan.';
        default:
            return '';
    }
}

// Fungsi untuk mendapatkan produk pesanan
function getOrderProducts($koneksi, $id_pesanan) {
    $products = [];
    
    // Cara 1: Cek tabel detail_pesanan dengan join ke produk
    $query1 = $koneksi->query("
        SELECT 
            p.nama_tanaman,
            p.foto,
            p.harga,
            dp.jumlah,
            dp.harga as harga_beli
        FROM detail_pesanan dp 
        JOIN produk p ON p.id_produk = dp.id_produk 
        WHERE dp.id_pesanan = '$id_pesanan'
    ");
    
    if ($query1 && $query1->num_rows > 0) {
        while ($row = $query1->fetch_assoc()) {
            $products[] = [
                'nama_tanaman' => $row['nama_tanaman'],
                'foto' => $row['foto'],
                'harga' => $row['harga_beli'] ?? $row['harga'],
                'jumlah' => $row['jumlah'] ?? 1
            ];
        }
        return $products;
    }
    
    // Cara 2: Cek jika ada relasi langsung di tabel pesanan
    $query2 = $koneksi->query("
        SELECT 
            p.nama_tanaman,
            p.foto,
            p.harga,
            ps.jumlah
        FROM pesanan ps 
        LEFT JOIN produk p ON p.id_produk = ps.id_produk 
        WHERE ps.id_pesanan = '$id_pesanan' AND p.id_produk IS NOT NULL
    ");
    
    if ($query2 && $query2->num_rows > 0) {
        while ($row = $query2->fetch_assoc()) {
            $products[] = [
                'nama_tanaman' => $row['nama_tanaman'],
                'foto' => $row['foto'],
                'harga' => $row['harga'],
                'jumlah' => $row['jumlah'] ?? 1
            ];
        }
        return $products;
    }
    
    return $products;
}

// Fungsi untuk mendapatkan path foto yang benar
function getImagePath($foto) {
    if (empty($foto) || $foto == 'default.jpg') {
        return 'images/default-product.jpg';
    }
    
    // Cek beberapa kemungkinan lokasi foto
    $possible_paths = [
        'admin/images/' . $foto,
        'images/products/' . $foto,
        'images/' . $foto,
        'uploads/' . $foto
    ];
    
    foreach ($possible_paths as $path) {
        if (file_exists($path)) {
            return $path;
        }
    }
    
    // Jika tidak ditemukan, return default
    return 'images/default-product.jpg';
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pesanan - Toko Tanaman</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .status-processing { 
            background-color: #fff3cd; 
            color: #856404; 
            border: 1px solid #ffeaa7;
        }
        .status-pending-verification { 
            background-color: #f3e2f3; 
            color: #7b1fa2; 
            border: 1px solid #e1bee7;
        }
        .status-verified { 
            background-color: #d1ecf1; 
            color: #0c5460; 
            border: 1px solid #bee5eb;
        }
        .status-shipped { 
            background-color: #cce5ff; 
            color: #004085; 
            border: 1px solid #b3d7ff;
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
        
        .order-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .tab-btn {
            padding: 10px 16px;
            border: 1px solid #ddd;
            background: white;
            border-radius: 25px;
            cursor: pointer;
            text-decoration: none;
            color: #666;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .tab-btn.active, .tab-btn:hover {
            background-color: #28a745;
            color: white;
            border-color: #28a745;
            transform: translateY(-1px);
        }
        
        .empty-orders {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }
        
        .empty-orders i {
            font-size: 64px;
            margin-bottom: 20px;
            color: #ddd;
        }
        
        .order-card {
            border: 1px solid #ddd;
            border-radius: 12px;
            margin-bottom: 20px;
            background: white;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        .order-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 16px rgba(0,0,0,0.15);
        }
        
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            border-bottom: 1px solid #eee;
            background-color: #f8f9fa;
            border-radius: 12px 12px 0 0;
        }
        
        .order-info p {
            margin: 2px 0;
            font-size: 14px;
        }
        
        .order-date {
            color: #666;
            font-weight: 500;
        }
        
        .order-number {
            font-weight: 600;
            color: #333;
            font-size: 16px;
        }
        
        .order-items {
            padding: 20px;
        }
        
        .order-item {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .order-item:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }
        
        .order-item img {
            width: 70px;
            height: 70px;
            object-fit: cover;
            border-radius: 8px;
            margin-right: 15px;
            border: 1px solid #ddd;
        }
        
        .item-details h3 {
            margin: 0 0 5px 0;
            font-size: 16px;
            color: #333;
            font-weight: 600;
        }
        
        .item-details p {
            margin: 0;
            color: #666;
            font-size: 14px;
        }
        
        .order-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            border-top: 1px solid #eee;
            background-color: #f8f9fa;
            border-radius: 0 0 12px 12px;
        }
        
        .order-total {
            font-weight: 600;
            color: #333;
            font-size: 16px;
        }
        
        .total-amount {
            color: #28a745;
            font-weight: 700;
            font-size: 18px;
        }
        
        .order-actions {
            display: flex;
            gap: 10px;
        }
        
        .btn {
            padding: 10px 16px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            border: 1px solid;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-outline {
            background: white;
            color: #666;
            border-color: #ddd;
        }
        
        .btn-outline:hover {
            background: #f8f9fa;
            border-color: #28a745;
            color: #28a745;
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
        
        .more-items {
            padding: 15px 0;
            color: #666;
            font-style: italic;
            text-align: center;
            background-color: #f8f9fa;
            border-radius: 6px;
            margin-top: 10px;
        }
        
        .status-info {
            padding: 15px 20px;
            margin: 15px 20px;
            border-radius: 8px;
            border-left: 4px solid;
            font-size: 14px;
            font-weight: 500;
        }
        
        .status-info.completed {
            background-color: #d4edda;
            border-left-color: #28a745;
            color: #155724;
        }
        
        .status-info.processing {
            background-color: #fff3cd;
            border-left-color: #ffc107;
            color: #856404;
        }
        
        .status-info.pending-verification {
            background-color: #f3e2f3;
            border-left-color: #7b1fa2;
            color: #7b1fa2;
        }
        
        .status-info.verified {
            background-color: #d1ecf1;
            border-left-color: #17a2b8;
            color: #0c5460;
        }
        
        .status-info i {
            margin-right: 8px;
            font-size: 16px;
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
                        <h1>Riwayat Pesanan</h1>
                    </div>
                    
                    <div class="order-tabs">
                        <a href="?status=all" class="tab-btn <?= $status_filter === 'all' ? 'active' : '' ?>">
                            <i class="fas fa-list"></i> Semua
                        </a>
                        <a href="?status=diproses" class="tab-btn <?= $status_filter === 'diproses' ? 'active' : '' ?>">
                            <i class="fas fa-clock"></i> Belum Dibayar
                        </a>
                        <a href="?status=menunggu_verifikasi" class="tab-btn <?= $status_filter === 'menunggu_verifikasi' ? 'active' : '' ?>">
                            <i class="fas fa-hourglass-half"></i> Menunggu Verifikasi
                        </a>
                        <a href="?status=diverifikasi" class="tab-btn <?= $status_filter === 'diverifikasi' ? 'active' : '' ?>">
                            <i class="fas fa-check-circle"></i> Sedang Diproses
                        </a>
                        <a href="?status=dikirim" class="tab-btn <?= $status_filter === 'dikirim' ? 'active' : '' ?>">
                            <i class="fas fa-shipping-fast"></i> Dikirim
                        </a>
                        <a href="?status=selesai" class="tab-btn <?= $status_filter === 'selesai' ? 'active' : '' ?>">
                            <i class="fas fa-check-double"></i> Selesai
                        </a>
                        <a href="?status=dibatalkan" class="tab-btn <?= $status_filter === 'dibatalkan' ? 'active' : '' ?>">
                            <i class="fas fa-times-circle"></i> Dibatalkan
                        </a>
                    </div>
                    
                    <div class="order-list">
                        <?php if ($result_orders && $result_orders->num_rows > 0): ?>
                            <?php while ($pesanan = $result_orders->fetch_assoc()): ?>
                                <div class="order-card">
                                    <div class="order-header">
                                        <div class="order-info">
                                            <p class="order-date">
                                                <i class="fas fa-calendar-alt"></i> 
                                                <?= date('d M Y, H:i', strtotime($pesanan['tgl_pesanan'])) ?> WIB
                                            </p>
                                            <p class="order-number">
                                                <i class="fas fa-receipt"></i> 
                                                <?= htmlspecialchars($pesanan['nomor_pesanan']) ?>
                                            </p>
                                        </div>
                                        <div class="order-status">
                                            <span class="status-badge <?= getStatusBadgeClass($pesanan['status_pesanan']) ?>">
                                                <?= getStatusName($pesanan['status_pesanan']) ?>
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <?php 
                                    $status_description = getStatusDescription($pesanan['status_pesanan']);
                                    if ($status_description): 
                                    ?>
                                    <div class="status-info <?= str_replace('_', '-', $pesanan['status_pesanan']) ?>">
                                        <p>
                                            <?php if ($pesanan['status_pesanan'] === 'diverifikasi'): ?>
                                                <i class="fas fa-check-circle"></i>
                                            <?php elseif ($pesanan['status_pesanan'] === 'diproses'): ?>
                                                <i class="fas fa-clock"></i>
                                            <?php elseif ($pesanan['status_pesanan'] === 'menunggu_verifikasi'): ?>
                                                <i class="fas fa-hourglass-half"></i>
                                            <?php elseif ($pesanan['status_pesanan'] === 'dikirim'): ?>
                                                <i class="fas fa-shipping-fast"></i>
                                            <?php elseif ($pesanan['status_pesanan'] === 'selesai'): ?>
                                                <i class="fas fa-check-double"></i>
                                            <?php else: ?>
                                                <i class="fas fa-info-circle"></i>
                                            <?php endif; ?>
                                            <?= $status_description ?>
                                        </p>
                                    </div>
                                    <?php endif; ?>

                                    <?php
                                    // Ambil data produk dari tabel pesanan
                                    $id_pesanan_current = $pesanan['id_pesanan'];
                                    $products = getOrderProducts($koneksi, $id_pesanan_current);
                                    ?>

                                    <div class="order-items">
                                        <?php
                                        if ($products && count($products) > 0) {
                                            $index = 0;
                                            foreach ($products as $produk) {
                                                if ($index < 3) { // Tampilkan maksimal 3 item
                                                    $foto_path = getImagePath($produk['foto']);
                                        ?>
                                                    <div class="order-item">
                                                        <img src="<?= $foto_path ?>" 
                                                            alt="<?= htmlspecialchars($produk['nama_tanaman']) ?>"
                                                            onerror="this.src='images/default-product.jpg'">
                                                        <div class="item-details">
                                                            <h3><?= htmlspecialchars($produk['nama_tanaman']) ?></h3>
                                                            <p><i class="fas fa-shopping-cart"></i> <?= $produk['jumlah'] ?> x Rp<?= number_format($produk['harga'], 0, ',', '.') ?></p>
                                                        </div>
                                                    </div>
                                        <?php
                                                    $index++;
                                                }
                                            }
                                            
                                            if (count($products) > 3) {
                                        ?>
                                            <div class="more-items">
                                                <p><i class="fas fa-plus-circle"></i> +<?= count($products) - 3 ?> produk lainnya</p>
                                            </div>
                                        <?php
                                            }
                                        } else {
                                        ?>
                                            <div class="order-item">
                                                <img src="images/default-product.jpg" alt="Default Product">
                                                <div class="item-details">
                                                    <h3>Pesanan #<?= htmlspecialchars($pesanan['nomor_pesanan']) ?></h3>
                                                    <p><i class="fas fa-info-circle"></i> Detail produk akan ditampilkan setelah pembayaran</p>
                                                </div>
                                            </div>
                                        <?php
                                        }
                                        ?>
                                    </div>
                                    
                                    <div class="order-footer">
                                        <div class="order-total">
                                            <i class="fas fa-calculator"></i> Total Pesanan: 
                                            <span class="total-amount">Rp<?= number_format($pesanan['total'], 0, ',', '.') ?></span>
                                        </div>
                                        <div class="order-actions">
                                            <a href="detail_pesanan.php?id_pesanan=<?= $pesanan['id_pesanan'] ?>" class="btn btn-outline">
                                                <i class="fas fa-eye"></i> Detail Pesanan
                                            </a>
                                            
                                            <?php if ($pesanan['status_pesanan'] === 'diproses'): ?>
                                                <a href="konfirmasi_pembayaran.php?order_id=<?= $pesanan['id_pesanan'] ?>" class="btn btn-primary">
                                                    <i class="fas fa-credit-card"></i> Bayar Sekarang
                                                </a>
                                            <?php elseif ($pesanan['status_pesanan'] === 'menunggu_verifikasi'): ?>
                                                <span class="btn btn-outline" style="background-color: #f3e2f3; color: #7b1fa2; border-color: #e1bee7; cursor: default;">
                                                    <i class="fas fa-hourglass-half"></i> Menunggu Verifikasi
                                                </span>
                                            <?php elseif ($pesanan['status_pesanan'] === 'diverifikasi'): ?>
                                                <span class="btn btn-outline" style="background-color: #d1ecf1; color: #0c5460; border-color: #bee5eb; cursor: default;">
                                                    <i class="fas fa-cogs"></i> Sedang Diproses
                                                </span>
                                            <?php elseif ($pesanan['status_pesanan'] === 'dikirim'): ?>
                                                <a href="lacak_pengiriman.php?id_pesanan=<?= $pesanan['id_pesanan'] ?>" class="btn btn-outline">
                                                    <i class="fas fa-map-marker-alt"></i> Lacak Pengiriman
                                                </a>
                                            <?php elseif ($pesanan['status_pesanan'] === 'selesai'): ?>
                                                <a href="produk.php" class="btn btn-primary">
                                                    <i class="fas fa-redo"></i> Beli Lagi
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="empty-orders">
                                <i class="fas fa-shopping-bag"></i>
                                <h3>Belum Ada Pesanan</h3>
                                <p>Anda belum memiliki riwayat pesanan<?= $status_filter !== 'all' ? ' dengan status ' . getStatusName($status_filter) : '' ?>.</p>
                                <a href="produk.php" class="btn btn-primary" style="margin-top: 20px;">
                                    <i class="fas fa-shopping-cart"></i> Mulai Belanja
                                </a>
                            </div>
                        <?php endif; ?>
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
        // Auto refresh halaman setiap 30 detik untuk update status pesanan
        setTimeout(function() {
            location.reload();
        }, 30000);
        
        // Smooth scroll untuk tab navigation
        document.querySelectorAll('.tab-btn').forEach(function(tab) {
            tab.addEventListener('click', function(e) {
                // Add loading effect
                this.style.opacity = '0.7';
                setTimeout(() => {
                    this.style.opacity = '1';
                }, 200);
            });
        });
        
        // Animation untuk order cards
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };
        
        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);
        
        // Apply animation to order cards
        document.querySelectorAll('.order-card').forEach(card => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            observer.observe(card);
        });
    </script>
</body>
</html>

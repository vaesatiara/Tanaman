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

// Fungsi untuk mendapatkan status badge class
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

// Fungsi untuk mendapatkan nama status yang user-friendly
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

// Fungsi untuk mendapatkan produk pesanan
function getOrderProducts($koneksi, $id_pesanan) {
    $products = [];
    
    // Coba beberapa cara untuk mendapatkan produk pesanan
    // Cara 1: Jika ada relasi langsung di tabel pesanan
    $query1 = $koneksi->query("SELECT p.* FROM produk p 
                              JOIN pesanan ps ON p.id_produk = ps.id_produk 
                              WHERE ps.id_pesanan = '$id_pesanan'");
    
    if ($query1 && $query1->num_rows > 0) {
        while ($row = $query1->fetch_assoc()) {
            $products[] = $row;
        }
        return $products;
    }
    
    // Cara 2: Jika ada tabel detail_pesanan
    $query2 = $koneksi->query("SELECT p.* FROM produk p 
                              JOIN detail_pesanan dp ON p.id_produk = dp.id_produk 
                              WHERE dp.id_pesanan = '$id_pesanan'");
    
    if ($query2 && $query2->num_rows > 0) {
        while ($row = $query2->fetch_assoc()) {
            $products[] = $row;
        }
        return $products;
    }
    
    // Cara 3: Jika produk disimpan dalam kolom terpisah di tabel pesanan
    $query3 = $koneksi->query("SELECT nama_produk, foto, harga_produk FROM pesanan WHERE id_pesanan = '$id_pesanan'");
    
    if ($query3 && $query3->num_rows > 0) {
        $row = $query3->fetch_assoc();
        if (!empty($row['nama_produk'])) {
            $products[] = [
                'nama_tanaman' => $row['nama_produk'],
                'foto' => $row['foto'] ?? 'default.jpg',
                'harga' => $row['harga_produk'] ?? 0
            ];
        }
    }
    
    return $products;
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
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            text-transform: uppercase;
        }
        .status-processing { background-color: #fff3cd; color: #856404; }
        .status-pending { background-color: #f8d7da; color: #721c24; }
        .status-verified { background-color: #d1ecf1; color: #0c5460; }
        .status-shipped { background-color: #d4edda; color: #155724; }
        .status-completed { background-color: #d4edda; color: #155724; }
        .status-cancelled { background-color: #f8d7da; color: #721c24; }
        
        .order-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .tab-btn {
            padding: 8px 16px;
            border: 1px solid #ddd;
            background: white;
            border-radius: 20px;
            cursor: pointer;
            text-decoration: none;
            color: #666;
            font-size: 14px;
        }
        
        .tab-btn.active, .tab-btn:hover {
            background-color: #28a745;
            color: white;
            border-color: #28a745;
        }
        
        .empty-orders {
            text-align: center;
            padding: 40px 20px;
            color: #666;
        }
        
        .empty-orders i {
            font-size: 48px;
            margin-bottom: 20px;
            color: #ddd;
        }
        
        .order-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            margin-bottom: 20px;
            background: white;
        }
        
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            border-bottom: 1px solid #eee;
        }
        
        .order-info p {
            margin: 2px 0;
            font-size: 14px;
        }
        
        .order-date {
            color: #666;
        }
        
        .order-number {
            font-weight: 500;
            color: #333;
        }
        
        .order-items {
            padding: 15px 20px;
        }
        
        .order-item {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .order-item:last-child {
            margin-bottom: 0;
        }
        
        .order-item img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 4px;
            margin-right: 15px;
        }
        
        .item-details h3 {
            margin: 0 0 5px 0;
            font-size: 16px;
            color: #333;
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
            padding: 15px 20px;
            border-top: 1px solid #eee;
            background-color: #f8f9fa;
        }
        
        .order-total {
            font-weight: 500;
            color: #333;
        }
        
        .total-amount {
            color: #28a745;
            font-weight: 600;
        }
        
        .order-actions {
            display: flex;
            gap: 10px;
        }
        
        .btn {
            padding: 8px 16px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 14px;
            border: 1px solid;
            cursor: pointer;
        }
        
        .btn-outline {
            background: white;
            color: #666;
            border-color: #ddd;
        }
        
        .btn-outline:hover {
            background: #f8f9fa;
        }
        
        .btn-primary {
            background: #28a745;
            color: white;
            border-color: #28a745;
        }
        
        .btn-primary:hover {
            background: #218838;
        }
        
        .more-items {
            padding: 10px 0;
            color: #666;
            font-style: italic;
        }
        
        .status-info {
            padding: 10px 20px;
            background-color: #e8f5e8;
            border-left: 4px solid #28a745;
            margin: 10px 0;
        }
        
        .status-info.processing {
            background-color: #fff3cd;
            border-left-color: #ffc107;
        }
        
        .status-info.verified {
            background-color: #d1ecf1;
            border-left-color: #17a2b8;
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
                        <a href="?status=all" class="tab-btn <?= $status_filter === 'all' ? 'active' : '' ?>">Semua</a>
                        <a href="?status=diproses" class="tab-btn <?= $status_filter === 'diproses' ? 'active' : '' ?>">Belum Dibayar</a>
                        <a href="?status=diverifikasi" class="tab-btn <?= $status_filter === 'diverifikasi' ? 'active' : '' ?>">Sedang Diproses</a>
                        <a href="?status=dikirim" class="tab-btn <?= $status_filter === 'dikirim' ? 'active' : '' ?>">Dikirim</a>
                        <a href="?status=selesai" class="tab-btn <?= $status_filter === 'selesai' ? 'active' : '' ?>">Selesai</a>
                        <a href="?status=dibatalkan" class="tab-btn <?= $status_filter === 'dibatalkan' ? 'active' : '' ?>">Dibatalkan</a>
                    </div>
                    
                    <div class="order-list">
                        <?php if ($result_orders && $result_orders->num_rows > 0): ?>
                            <?php while ($pesanan = $result_orders->fetch_assoc()): ?>
                                <div class="order-card">
                                    <div class="order-header">
                                        <div class="order-info">
                                            <p class="order-date"><?= date('d M Y', strtotime($pesanan['tgl_pesanan'])) ?></p>
                                            <p class="order-number"><?= htmlspecialchars($pesanan['nomor_pesanan']) ?></p>
                                        </div>
                                        <div class="order-status">
                                            <span class="status-badge <?= getStatusBadgeClass($pesanan['status_pesanan']) ?>">
                                                <?= getStatusName($pesanan['status_pesanan']) ?>
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <?php if ($pesanan['status_pesanan'] === 'diverifikasi'): ?>
                                    <div class="status-info verified">
                                        <p><i class="fas fa-check-circle"></i> Pembayaran telah dikonfirmasi. Pesanan sedang diproses dan akan segera dikirim.</p>
                                    </div>
                                    <?php elseif ($pesanan['status_pesanan'] === 'diproses'): ?>
                                    <div class="status-info processing">
                                        <p><i class="fas fa-clock"></i> Menunggu pembayaran. Silakan lakukan pembayaran untuk melanjutkan pesanan.</p>
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
                                        ?>
                                                    <div class="order-item">
                                                        <img src="admin/images/<?= htmlspecialchars($produk['foto']) ?>" 
                                                            alt="<?= htmlspecialchars($produk['nama_tanaman']) ?>"
                                                            onerror="this.src='images/default-product.jpg'">
                                                        <div class="item-details">
                                                            <h3><?= htmlspecialchars($produk['nama_tanaman']) ?></h3>
                                                            <p>1 x Rp<?= number_format($produk['harga'], 0, ',', '.') ?></p>
                                                        </div>
                                                    </div>
                                        <?php
                                                    $index++;
                                                }
                                            }
                                            
                                            if (count($products) > 3) {
                                        ?>
                                            <div class="more-items">
                                                <p>+<?= count($products) - 3 ?> produk lainnya</p>
                                            </div>
                                        <?php
                                            }
                                        } else {
                                        ?>
                                            <div class="order-item">
                                                <div class="item-details">
                                                    <h3>Pesanan #<?= htmlspecialchars($pesanan['nomor_pesanan']) ?></h3>
                                                    <p>Detail produk akan ditampilkan setelah pembayaran</p>
                                                </div>
                                            </div>
                                        <?php
                                        }
                                        ?>
                                    </div>
                                    
                                    <div class="order-footer">
                                        <div class="order-total">
                                            Total Pesanan: <span class="total-amount">Rp<?= number_format($pesanan['total'], 0, ',', '.') ?></span>
                                        </div>
                                        <div class="order-actions">
                                            <a href="detail_pesanan.php?id_pesanan=<?= $pesanan['id_pesanan'] ?>" class="btn btn-outline">Detail Pesanan</a>
                                            
                                            <?php if ($pesanan['status_pesanan'] === 'diproses'): ?>
                                                <a href="konfirmasi_pembayaran.php?order_id=<?= $pesanan['id_pesanan'] ?>" class="btn btn-primary">Bayar Sekarang</a>
                                            <?php elseif ($pesanan['status_pesanan'] === 'diverifikasi'): ?>
                                                <span class="btn btn-outline" style="background-color: #d1ecf1; color: #0c5460;">Sedang Diproses</span>
                                            <?php elseif ($pesanan['status_pesanan'] === 'dikirim'): ?>
                                                <a href="lacak_pengiriman.php?id_pesanan=<?= $pesanan['id_pesanan'] ?>" class="btn btn-outline">Lacak Pengiriman</a>
                                            <?php elseif ($pesanan['status_pesanan'] === 'selesai'): ?>
                                                <a href="produk.php" class="btn btn-primary">Beli Lagi</a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="empty-orders">
                                <i class="fas fa-shopping-bag"></i>
                                <h3>Belum Ada Pesanan</h3>
                                <p>Anda belum memiliki riwayat pesanan. Mulai berbelanja sekarang!</p>
                                <a href="produk.php" class="btn btn-primary" style="margin-top: 20px;">Mulai Belanja</a>
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
    </script>
</body>
</html>

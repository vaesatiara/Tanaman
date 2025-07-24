<?php
session_start();
include "koneksi.php";

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'get_orders':
            try {
                $stmt = $koneksi->query("SELECT 
                    p.id_pesanan, 
                    p.id_produk, 
                    p.id_pelanggan, 
                    p.nomor_pesanan, 
                    p.tgl_pesanan, 
                    p.status_pesanan, 
                    p.jumlah, 
                    p.total,
                    pel.username as nama_pelanggan,
                    pel.email as email_pelanggan,
                    pr.nama_tanaman,
                    pay.id_pembayaran,
                    pay.tgl_bayar,
                    pay.file_image as bukti_pembayaran
                FROM pesanan p
                LEFT JOIN pelanggan pel ON p.id_pelanggan = pel.id_pelanggan
                LEFT JOIN produk pr ON p.id_produk = pr.id_produk
                LEFT JOIN pembayaran pay ON p.id_pesanan = pay.id_pesanan
                ORDER BY p.tgl_pesanan DESC");
                
                $orders = $stmt->fetch_all(MYSQLI_ASSOC);
                echo json_encode(['success' => true, 'data' => $orders]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
            exit;
            
        case 'get_order_detail':
            $orderId = intval($_POST['order_id'] ?? 0);
            
            if ($orderId <= 0) {
                echo json_encode(['success' => false, 'error' => 'ID pesanan tidak valid']);
                exit;
            }
            
            try {
                $stmt = $koneksi->prepare("SELECT 
                    p.id_pesanan, 
                    p.id_produk, 
                    p.id_pelanggan, 
                    p.nomor_pesanan, 
                    p.tgl_pesanan, 
                    p.status_pesanan, 
                    p.jumlah, 
                    p.total,
                    pel.username as nama_pelanggan,
                    pel.email as email_pelanggan,
                    pel.no_hp,
                    pr.nama_tanaman,
                    pr.harga as harga_produk,
                    pay.id_pembayaran,
                    pay.tgl_bayar,
                    pay.waktu_bayar,
                    pay.file_image as bukti_pembayaran,
                    pay.catatan as catatan_pembayaran
                FROM pesanan p
                LEFT JOIN pelanggan pel ON p.id_pelanggan = pel.id_pelanggan
                LEFT JOIN produk pr ON p.id_produk = pr.id_produk
                LEFT JOIN pembayaran pay ON p.id_pesanan = pay.id_pesanan
                WHERE p.id_pesanan = ?");
                
                if (!$stmt) {
                    throw new Exception('Prepare statement failed: ' . $koneksi->error);
                }
                
                $stmt->bind_param("i", $orderId);
                
                if (!$stmt->execute()) {
                    throw new Exception('Execute failed: ' . $stmt->error);
                }
                
                $result = $stmt->get_result();
                $order = $result->fetch_assoc();
                
                if ($order) {
                    echo json_encode(['success' => true, 'data' => $order]);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Pesanan tidak ditemukan']);
                }
                
                $stmt->close();
            } catch (Exception $e) {
                error_log("Error in get_order_detail: " . $e->getMessage());
                echo json_encode(['success' => false, 'error' => 'Gagal memuat detail pesanan: ' . $e->getMessage()]);
            }
            exit;
            
        case 'verify_payment':
            $orderId = intval($_POST['order_id'] ?? 0);
            $verified = intval($_POST['verified'] ?? 0);
            $notes = trim($_POST['notes'] ?? '');
            
            if ($orderId <= 0) {
                echo json_encode(['success' => false, 'error' => 'ID pesanan tidak valid']);
                exit;
            }
            
            try {
                $koneksi->begin_transaction();
                
                // Cek status pesanan saat ini
                $stmt = $koneksi->prepare("SELECT status_pesanan, nomor_pesanan FROM pesanan WHERE id_pesanan = ?");
                if (!$stmt) {
                    throw new Exception('Prepare statement failed: ' . $koneksi->error);
                }
                
                $stmt->bind_param("i", $orderId);
                $stmt->execute();
                $result = $stmt->get_result();
                $currentOrder = $result->fetch_assoc();
                $stmt->close();
                
                if (!$currentOrder) {
                    throw new Exception('Pesanan tidak ditemukan');
                }
                
                // Hanya bisa verifikasi jika status masih menunggu verifikasi
                if (!in_array($currentOrder['status_pesanan'], ['menunggu_verifikasi', 'menunggu_pembayaran'])) {
                    throw new Exception('Pesanan sudah diverifikasi atau tidak bisa diverifikasi');
                }
                
                // Update status berdasarkan verifikasi - pastikan konsisten dengan enum database
                $newStatus = $verified ? 'diverifikasi' : 'dibatalkan';
                
                $stmt = $koneksi->prepare("UPDATE pesanan SET status_pesanan = ? WHERE id_pesanan = ?");
                if (!$stmt) {
                    throw new Exception('Prepare statement failed: ' . $koneksi->error);
                }
                
                $stmt->bind_param("si", $newStatus, $orderId);
                $result = $stmt->execute();
                $stmt->close();
                
                if (!$result) {
                    throw new Exception('Gagal memperbarui status pesanan');
                }
                
                // Update catatan verifikasi di tabel pembayaran jika ada
                if (!empty($notes)) {
                    $stmt = $koneksi->prepare("UPDATE pembayaran SET catatan = CONCAT(IFNULL(catatan, ''), '\n\nCatatan Admin: ', ?) WHERE id_pesanan = ?");
                    if ($stmt) {
                        $stmt->bind_param("si", $notes, $orderId);
                        $stmt->execute();
                        $stmt->close();
                    }
                }
                
                $koneksi->commit();
                
                $message = $verified ? 'Pembayaran berhasil diverifikasi' : 'Pembayaran ditolak dan pesanan dibatalkan';
                echo json_encode([
                    'success' => true, 
                    'new_status' => $newStatus, 
                    'message' => $message,
                    'order_number' => $currentOrder['nomor_pesanan']
                ]);
                
            } catch (Exception $e) {
                $koneksi->rollback();
                error_log("Error in verify_payment: " . $e->getMessage());
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
            exit;
            
        case 'update_status':
            $orderId = intval($_POST['order_id'] ?? 0);
            $status = trim($_POST['status'] ?? '');
            $notes = trim($_POST['notes'] ?? '');
            
            if ($orderId <= 0) {
                echo json_encode(['success' => false, 'error' => 'ID pesanan tidak valid']);
                exit;
            }
            
            if (empty($status)) {
                echo json_encode(['success' => false, 'error' => 'Status tidak boleh kosong']);
                exit;
            }
            
            // Validasi status yang diperbolehkan berdasarkan enum database
            $allowedStatuses = [
                'menunggu_pembayaran',
                'menunggu_verifikasi',
                'diverifikasi',
                'terverifikasi', 
                'diproses',
                'dikemas', 
                'dikirim', 
                'selesai',
                'dibatalkan'
            ];
            
            if (!in_array($status, $allowedStatuses)) {
                echo json_encode(['success' => false, 'error' => 'Status tidak valid: ' . $status]);
                exit;
            }
            
            try {
                $koneksi->begin_transaction();
                
                // Cek apakah pesanan ada dan statusnya
                $stmt = $koneksi->prepare("SELECT status_pesanan, nomor_pesanan FROM pesanan WHERE id_pesanan = ?");
                if (!$stmt) {
                    throw new Exception('Prepare statement failed: ' . $koneksi->error);
                }
                
                $stmt->bind_param("i", $orderId);
                $stmt->execute();
                $result = $stmt->get_result();
                $currentOrder = $result->fetch_assoc();
                $stmt->close();
                
                if (!$currentOrder) {
                    throw new Exception('Pesanan tidak ditemukan');
                }
                
                $currentStatus = $currentOrder['status_pesanan'];
                
                // Validasi transisi status
                if (!isValidStatusTransition($currentStatus, $status)) {
                    throw new Exception('Transisi status tidak valid dari "' . getStatusText($currentStatus) . '" ke "' . getStatusText($status) . '"');
                }
                
                // Update status
                $stmt = $koneksi->prepare("UPDATE pesanan SET status_pesanan = ? WHERE id_pesanan = ?");
                if (!$stmt) {
                    throw new Exception('Prepare statement failed: ' . $koneksi->error);
                }
                
                $stmt->bind_param("si", $status, $orderId);
                $result = $stmt->execute();
                $stmt->close();
                
                if (!$result) {
                    throw new Exception('Gagal memperbarui status pesanan');
                }
                
                // Log perubahan status jika ada catatan
                if (!empty($notes)) {
                    // Create log table if not exists
                    $koneksi->query("CREATE TABLE IF NOT EXISTS log_status_pesanan (
                        id_log INT AUTO_INCREMENT PRIMARY KEY,
                        id_pesanan INT NOT NULL,
                        status_lama VARCHAR(50),
                        status_baru VARCHAR(50),
                        catatan TEXT,
                        tgl_update TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                    )");
                    
                    $stmt = $koneksi->prepare("INSERT INTO log_status_pesanan (id_pesanan, status_lama, status_baru, catatan, tgl_update) VALUES (?, ?, ?, ?, NOW())");
                    if ($stmt) {
                        $stmt->bind_param("isss", $orderId, $currentStatus, $status, $notes);
                        $stmt->execute();
                        $stmt->close();
                    }
                }
                
                $koneksi->commit();
                
                // Log perubahan status
                error_log("Status pesanan ID $orderId diubah dari {$currentStatus} ke $status");
                
                echo json_encode([
                    'success' => true, 
                    'message' => 'Status pesanan berhasil diperbarui',
                    'old_status' => $currentStatus,
                    'new_status' => $status,
                    'order_number' => $currentOrder['nomor_pesanan']
                ]);
                
            } catch (Exception $e) {
                $koneksi->rollback();
                error_log("Error in update_status: " . $e->getMessage());
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
            exit;
            
        case 'get_stats':
            try {
                $stats = [];
                
                // Total orders today
                $stmt = $koneksi->query("SELECT COUNT(*) as count FROM pesanan WHERE DATE(tgl_pesanan) = CURDATE()");
                $result = $stmt->fetch_assoc();
                $stats['total'] = $result['count'];
                
                // Pending orders (menunggu verifikasi)
                $stmt = $koneksi->query("SELECT COUNT(*) as count FROM pesanan WHERE status_pesanan IN ('menunggu_verifikasi', 'menunggu_pembayaran')");
                $result = $stmt->fetch_assoc();
                $stats['pending'] = $result['count'];
                
                // Processing orders (terverifikasi + diproses + dikemas)
                $stmt = $koneksi->query("SELECT COUNT(*) as count FROM pesanan WHERE status_pesanan IN ('terverifikasi', 'diverifikasi', 'diproses', 'dikemas')");
                $result = $stmt->fetch_assoc();
                $stats['processing'] = $result['count'];
                
                // Shipped orders (dikirim)
                $stmt = $koneksi->query("SELECT COUNT(*) as count FROM pesanan WHERE status_pesanan = 'dikirim'");
                $result = $stmt->fetch_assoc();
                $stats['shipped'] = $result['count'];
                
                // Completed orders (selesai)
                $stmt = $koneksi->query("SELECT COUNT(*) as count FROM pesanan WHERE status_pesanan = 'selesai'");
                $result = $stmt->fetch_assoc();
                $stats['completed'] = $result['count'];
                
                // Canceled orders (dibatalkan)
                $stmt = $koneksi->query("SELECT COUNT(*) as count FROM pesanan WHERE status_pesanan = 'dibatalkan'");
                $result = $stmt->fetch_assoc();
                $stats['canceled'] = $result['count'];
                
                echo json_encode(['success' => true, 'data' => $stats]);
            } catch (Exception $e) {
                error_log("Error in get_stats: " . $e->getMessage());
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
            exit;
            
        default:
            echo json_encode(['success' => false, 'error' => 'Action tidak dikenal']);
            exit;
    }
}

// Function to validate status transitions
function isValidStatusTransition($currentStatus, $newStatus) {
    $validTransitions = [
        'menunggu_pembayaran' => ['menunggu_verifikasi', 'dibatalkan'],
        'menunggu_verifikasi' => ['diverifikasi', 'dibatalkan'],
        'diverifikasi' => ['diproses', 'dibatalkan'],
        'terverifikasi' => ['diproses', 'dibatalkan'],
        'diproses' => ['dikemas', 'dikirim', 'dibatalkan'],
        'dikemas' => ['dikirim', 'dibatalkan'],
        'dikirim' => ['selesai'],
        'selesai' => [],
        'dibatalkan' => []
    ];
    
    if ($currentStatus === $newStatus) {
        return true;
    }
    
    return in_array($newStatus, $validTransitions[$currentStatus] ?? []);
}

// Get status text for display
function getStatusText($status) {
    $statusMap = [
        'menunggu_pembayaran' => 'Menunggu Pembayaran',
        'menunggu_verifikasi' => 'Menunggu Verifikasi',
        'diverifikasi' => 'Diverifikasi',
        'terverifikasi' => 'Terverifikasi',
        'diproses' => 'Diproses',
        'dikemas' => 'Dikemas',
        'dikirim' => 'Dikirim',
        'selesai' => 'Selesai',
        'dibatalkan' => 'Dibatalkan'
    ];
    return $statusMap[$status] ?? ucfirst(str_replace('_', ' ', $status));
}

// Get orders for initial page load
try {
    $sql = "SELECT 
        p.id_pesanan, 
        p.id_produk, 
        p.id_pelanggan, 
        p.nomor_pesanan, 
        p.tgl_pesanan, 
        p.status_pesanan, 
        p.jumlah, 
        p.total,
        pel.username as nama_pelanggan,
        pr.nama_tanaman,
        pay.id_pembayaran
    FROM pesanan p
    LEFT JOIN pelanggan pel ON p.id_pelanggan = pel.id_pelanggan
    LEFT JOIN produk pr ON p.id_produk = pr.id_produk
    LEFT JOIN pembayaran pay ON p.id_pesanan = pay.id_pesanan
    ORDER BY p.tgl_pesanan DESC";
    $query = mysqli_query($koneksi, $sql);
    $initialOrders = [];
    if ($query) {
        while ($row = mysqli_fetch_assoc($query)) {
            $initialOrders[] = $row;
        }
    }
} catch (Exception $e) {
    $initialOrders = [];
    error_log("Error loading initial orders: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Pesanan - The Secret Garden</title>
    <link rel="stylesheet" href="css/manajemen_pesanan.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .payment-proof {
            max-width: 300px;
            max-height: 200px;
            border-radius: 8px;
            border: 1px solid #ddd;
            margin: 10px 0;
            cursor: pointer;
        }
        
        .payment-proof-container {
            text-align: center;
            margin: 15px 0;
        }
        
        .btn-view-proof {
            background-color: #17a2b8;
            color: white;
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            margin-left: 10px;
        }
        
        .btn-view-proof:hover {
            background-color: #138496;
        }
        
        .status-menunggu-pembayaran {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .status-menunggu-verifikasi {
            background-color: #f3e2f3;
            color: #7b1fa2;
            border: 1px solid #e1bee7;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .status-diverifikasi, .status-terverifikasi {
            background-color: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .status-diproses {
            background-color: #cce5ff;
            color: #004085;
            border: 1px solid #b3d7ff;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .status-dikemas {
            background-color: #e2e3e5;
            color: #383d41;
            border: 1px solid #d6d8db;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .status-dikirim {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .status-selesai {
            background-color: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .status-dibatalkan {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .verification-needed {
            background-color: #fff3cd;
            color: #856404;
            padding: 8px 12px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
            border: 1px solid #ffeaa7;
        }
        
        .verification-completed {
            background-color: #d4edda;
            color: #155724;
            padding: 8px 12px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
            border: 1px solid #c3e6cb;
        }
        
        .order-detail-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-bottom: 2rem;
        }
        
        .detail-section {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 8px;
            border: 1px solid #e9ecef;
        }
        
        .detail-section h4 {
            margin-bottom: 1rem;
            color: #495057;
            border-bottom: 2px solid #dee2e6;
            padding-bottom: 0.5rem;
        }
        
        .detail-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem 0;
            border-bottom: 1px solid #e9ecef;
        }
        
        .detail-item:last-child {
            border-bottom: none;
        }
        
        .detail-item label {
            font-weight: 600;
            color: #6c757d;
            min-width: 120px;
        }
        
        .detail-item span {
            text-align: right;
            flex: 1;
        }
        
        .total-amount {
            font-size: 1.2em;
            font-weight: bold;
            color: #28a745;
        }
        
        .status-select {
            padding: 5px 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background: white;
            font-size: 12px;
            min-width: 120px;
        }
        
        .status-select:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 2px rgba(0,123,255,.25);
        }
        
        .loading {
            opacity: 0.6;
            pointer-events: none;
        }
        
        .btn-action {
            padding: 6px 12px;
            margin: 2px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-detail {
            background-color: #007bff;
            color: white;
        }
        
        .btn-detail:hover {
            background-color: #0056b3;
        }
        
        .btn-verify {
            background-color: #28a745;
            color: white;
        }
        
        .btn-verify:hover {
            background-color: #1e7e34;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .table th {
            background-color: #f8f9fa;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            color: #495057;
            border-bottom: 2px solid #dee2e6;
        }
        
        .table td {
            padding: 12px;
            border-bottom: 1px solid #dee2e6;
            vertical-align: middle;
        }
        
        .table tbody tr:hover {
            background-color: #f8f9fa;
        }
        
        .text-center {
            text-align: center;
        }
        
        @media (max-width: 768px) {
            .order-detail-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            
            .table {
                font-size: 12px;
            }
            
            .table th,
            .table td {
                padding: 8px 4px;
            }
        }
    </style>
</head>
<body>
    <!-- Mobile Menu Button -->
    <button class="mobile-menu-btn" id="mobileMenuBtn">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="logo">
            <img src="uploads/logo.png" alt="The Secret Garden">
        </div>
        <div class="menu-label">MENU</div>
        <ul class="menu-items">
            <li>
                <a class="menu-item" onclick="location.href='dashboard.php'">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li>
                <a class="menu-item active" onclick="location.href='manajemen_pesanan.php'">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Management Pesanan</span>
                </a>
            </li>
            <li>
                <a class="menu-item" onclick="location.href='manajemen_produk.php'">
                    <i class="fas fa-box"></i>
                    <span>Management Produk</span>
                </a>
            </li>
            <li>
                <a class="menu-item" onclick="location.href='manajemen_akun.php'">
                    <i class="fas fa-user"></i>
                    <span>Management Akun</span>
                </a>
            </li>
            <li>
                <a class="menu-item" onclick="location.href='manajemen_pembayaran.php'">
                    <i class="fas fa-percent"></i>
                    <span>Management Pembayaran</span>
                </a>
            </li>
            <li>
                <a class="menu-item" onclick="location.href='manajemen_saran.php'">
                    <i class="fas fa-heart"></i>
                    <span>Management Saran</span>
                </a>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Header -->
        <div class="header">
            <div class="search-bar">
                <input type="text" placeholder="Cari pesanan, customer, produk..." id="searchInput">
                <button><i class="fas fa-search"></i></button>
            </div>
            <div class="user-profile">
                <span>Admin</span>
                <img src="images/4396a60b-6455-40ed-8331-89a96395469f.jpeg" alt="Admin Profile">
            </div>
        </div>

        <div class="content">
            <!-- Page Title -->
            <h2 class="content-title">Management Pesanan</h2>

            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-header">
                        <span class="stat-title">Total Pesanan</span>
                        <i class="fas fa-shopping-cart stat-icon"></i>
                    </div>
                    <div class="stat-value" id="totalOrders">0</div>
                    <div class="stat-info">
                        <i class="fas fa-calendar-day"></i>
                        <span>Hari ini</span>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <span class="stat-title">Menunggu Verifikasi</span>
                        <i class="fas fa-clock stat-icon"></i>
                    </div>
                    <div class="stat-value pending" id="pendingOrders">0</div>
                    <div class="stat-info">
                        <i class="fas fa-exclamation-circle"></i>
                        <span>Perlu verifikasi pembayaran</span>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <span class="stat-title">Diproses</span>
                        <i class="fas fa-cogs stat-icon"></i>
                    </div>
                    <div class="stat-value processing" id="processingOrders">0</div>
                    <div class="stat-info">
                        <i class="fas fa-spinner"></i>
                        <span>Sedang diproses</span>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <span class="stat-title">Dikirim</span>
                        <i class="fas fa-truck stat-icon"></i>
                    </div>
                    <div class="stat-value shipped" id="shippedOrders">0</div>
                    <div class="stat-info">
                        <i class="fas fa-shipping-fast"></i>
                        <span>Dalam pengiriman</span>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <span class="stat-title">Selesai</span>
                        <i class="fas fa-check-circle stat-icon"></i>
                    </div>
                    <div class="stat-value completed" id="completedOrders">0</div>
                    <div class="stat-info">
                        <i class="fas fa-check-double"></i>
                        <span>Pesanan selesai</span>
                    </div>
                </div>
            </div>

            <!-- Orders Table -->
            <div class="data-table">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID Pesanan</th>
                            <th>Nomor Pesanan</th>
                            <th>Pelanggan</th>
                            <th>Produk</th>
                            <th>Tanggal</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Verifikasi Pembayaran</th>
                            <th>Update Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="ordersTableBody">
                        <?php if (!empty($initialOrders)): ?>
                            <?php foreach ($initialOrders as $order): ?>
                                <tr>
                                    <td><?= htmlspecialchars($order['id_pesanan'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($order['nomor_pesanan'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($order['nama_pelanggan'] ?? $order['id_pelanggan'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($order['nama_tanaman'] ?? $order['id_produk'] ?? '-') ?></td>
                                    <td>
                                        <?php 
                                        if ($order['tgl_pesanan']) {
                                            $date = new DateTime($order['tgl_pesanan']);
                                            echo $date->format('d/m/Y H:i');
                                        } else {
                                            echo '-';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php 
                                        if ($order['total']) {
                                            echo 'Rp ' . number_format($order['total'], 0, ',', '.');
                                        } else {
                                            echo '-';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <span class="status-badge status-<?= str_replace('_', '-', $order['status_pesanan']) ?>">
                                            <?= getStatusText($order['status_pesanan']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if (in_array($order['status_pesanan'], ['menunggu_verifikasi', 'menunggu_pembayaran'])): ?>
                                            <button class="btn-action btn-verify" onclick="showPaymentVerification(<?= $order['id_pesanan'] ?>)">
                                                <i class="fas fa-credit-card"></i> Verifikasi
                                            </button>
                                        <?php elseif (!in_array($order['status_pesanan'], ['menunggu_pembayaran'])): ?>
                                            <span class="verification-completed">
                                                <i class="fas fa-check"></i> Terverifikasi
                                            </span>
                                        <?php else: ?>
                                            <span class="verification-needed">
                                                <i class="fas fa-clock"></i> Belum Bayar
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!in_array($order['status_pesanan'], ['selesai', 'dibatalkan']) && !in_array($order['status_pesanan'], ['menunggu_verifikasi', 'menunggu_pembayaran'])): ?>
                                            <select class="status-select" 
                                                    onchange="showStatusConfirmation(<?= $order['id_pesanan'] ?>, this.value, '<?= $order['status_pesanan'] ?>')" 
                                                    data-order-id="<?= $order['id_pesanan'] ?>" 
                                                    data-current-status="<?= $order['status_pesanan'] ?>">
                                                <?php
                                                $availableStatuses = [];
                                                switch($order['status_pesanan']) {
                                                    case 'terverifikasi':
                                                    case 'diverifikasi':
                                                        $availableStatuses = ['diverifikasi', 'diproses'];
                                                        break;
                                                    case 'diproses':
                                                        $availableStatuses = ['diproses', 'dikemas', 'dikirim'];
                                                        break;
                                                    case 'dikemas':
                                                        $availableStatuses = ['dikemas', 'dikirim'];
                                                        break;
                                                    case 'dikirim':
                                                        $availableStatuses = ['dikirim', 'selesai'];
                                                        break;
                                                    default:
                                                        $availableStatuses = [$order['status_pesanan']];
                                                }
                                                
                                                foreach($availableStatuses as $status):
                                                ?>
                                                    <option value="<?= $status ?>" <?= $status === $order['status_pesanan'] ? 'selected' : '' ?>>
                                                        <?= getStatusText($status) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        <?php elseif (in_array($order['status_pesanan'], ['menunggu_verifikasi', 'menunggu_pembayaran'])): ?>
                                            <span class="text-sm verification-needed">
                                                <i class="fas fa-exclamation-triangle"></i> Verifikasi pembayaran dulu
                                            </span>
                                        <?php elseif ($order['status_pesanan'] === 'selesai'): ?>
                                            <span class="text-sm" style="color: #28a745;">
                                                <i class="fas fa-check-circle"></i> Pesanan selesai
                                            </span>
                                        <?php else: ?>
                                            <span class="text-sm" style="color: #dc3545;">
                                                <i class="fas fa-times-circle"></i> Pesanan dibatalkan
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="action-buttons">
                                        <button class="btn-action btn-detail" onclick="showOrderDetail(<?= $order['id_pesanan'] ?>)" title="Lihat Detail">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="10" class="text-center">Tidak ada pesanan ditemukan</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Payment Verification Modal -->
    <div id="paymentVerificationModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-credit-card"></i> Verifikasi Pembayaran</h3>
                <span class="modal-close" data-modal="paymentVerificationModal">&times;</span>
            </div>
            <div class="modal-body">
                <div class="confirm-content">
                    <div class="confirm-icon">
                        <i class="fas fa-credit-card"></i>
                    </div>
                    <div class="confirm-text">
                        <h4>Verifikasi Pembayaran Pesanan</h4>
                        <p id="paymentVerificationText"></p>
                        <div class="payment-details" id="paymentDetails">
                        </div>
                        <div class="payment-proof-container" id="paymentProofContainer">
                        </div>
                        <div class="form-group">
                            <label for="paymentNote">Catatan Verifikasi (Opsional):</label>
                            <textarea id="paymentNote" placeholder="Tambahkan catatan verifikasi pembayaran..." rows="3"></textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-cancel" onclick="cancelPaymentVerification()">Batal</button>
                <button type="button" class="btn btn-danger" onclick="rejectPayment()">
                    <i class="fas fa-times"></i> Tolak Pembayaran
                </button>
                <button type="button" class="btn btn-confirm" onclick="confirmPayment()">
                    <i class="fas fa-check"></i> Konfirmasi Pembayaran
                </button>
            </div>
        </div>
    </div>

    <!-- Order Detail Modal -->
    <div id="orderDetailModal" class="modal">
        <div class="modal-content modal-large">
            <div class="modal-header">
                <h3><i class="fas fa-receipt"></i> Detail Pesanan</h3>
                <span class="modal-close" data-modal="orderDetailModal">&times;</span>
            </div>
            <div class="modal-body">
                <div id="orderDetailContent">
                    <!-- Content will be populated by JavaScript -->
                </div>
            </div>
        </div>
    </div>

    <!-- Status Confirmation Modal -->
    <div id="statusConfirmModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-exclamation-triangle"></i> Konfirmasi Update Status</h3>
                <span class="modal-close" data-modal="statusConfirmModal">&times;</span>
            </div>
            <div class="modal-body">
                <div class="confirm-content">
                    <div class="confirm-icon">
                        <i class="fas fa-question-circle"></i>
                    </div>
                    <div class="confirm-text">
                        <h4>Apakah Anda yakin ingin mengubah status pesanan?</h4>
                        <p id="statusChangeText"></p>
                        <div class="form-group">
                            <label for="statusNote">Catatan (Opsional):</label>
                            <textarea id="statusNote" placeholder="Tambahkan catatan untuk perubahan status..." rows="3"></textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-cancel" onclick="cancelStatusUpdate()">Batal</button>
                <button type="button" class="btn btn-confirm" onclick="confirmStatusUpdate()">Ya, Update Status</button>
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div id="toast" class="toast">
        <div class="toast-content">
            <div class="toast-icon">
                <i class="toast-icon-element"></i>
            </div>
            <div class="toast-message"></div>
            <button class="toast-close" onclick="closeToast()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>

    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <script>
        // Global variables
        let currentOrderId = null;
        let currentStatus = null;
        let currentOrder = null;
        let isUpdating = false;
        
        // DOM Ready
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM loaded, initializing...');
            
            // Initialize mobile menu
            const mobileMenuBtn = document.getElementById('mobileMenuBtn');
            const sidebar = document.getElementById('sidebar');
            const sidebarOverlay = document.getElementById('sidebarOverlay');
            
            if (mobileMenuBtn) {
                mobileMenuBtn.addEventListener('click', function() {
                    sidebar.classList.toggle('show');
                    sidebarOverlay.classList.toggle('show');
                });
            }
            
            if (sidebarOverlay) {
                sidebarOverlay.addEventListener('click', function() {
                    sidebar.classList.remove('show');
                    sidebarOverlay.classList.remove('show');
                });
            }
            
            // Initialize modals
            document.querySelectorAll('.modal-close').forEach(closeBtn => {
                closeBtn.addEventListener('click', function() {
                    const modalId = this.getAttribute('data-modal');
                    const modal = document.getElementById(modalId);
                    if (modal) {
                        modal.classList.remove('show');
                    }
                });
            });
            
            // Close modals when clicking outside
            document.querySelectorAll('.modal').forEach(modal => {
                modal.addEventListener('click', function(e) {
                    if (e.target === this) {
                        this.classList.remove('show');
                    }
                });
            });
            
            // Load orders and stats
            loadOrders();
            loadStats();
            
            // Search functionality
            const searchInput = document.getElementById('searchInput');
            if (searchInput) {
                let searchTimeout;
                searchInput.addEventListener('input', function() {
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(() => {
                        loadOrders(this.value);
                    }, 500);
                });
                
                searchInput.addEventListener('keyup', function(e) {
                    if (e.key === 'Enter') {
                        clearTimeout(searchTimeout);
                        loadOrders(this.value);
                    }
                });
            }
            
            // Auto refresh every 30 seconds
            setInterval(() => {
                if (!isUpdating) {
                    loadStats();
                }
            }, 30000);
        });
        
        // Check if payment needs verification
        function needsPaymentVerification(status) {
            return ['menunggu_verifikasi', 'menunggu_pembayaran'].includes(status);
        }
        
        // Check if payment is verified
        function isPaymentVerified(status) {
            return !['menunggu_pembayaran', 'menunggu_verifikasi'].includes(status);
        }
        
        // Load orders from server
        function loadOrders(searchTerm = '') {
            console.log('Loading orders...');
            
            const tableBody = document.getElementById('ordersTableBody');
            if (tableBody && !isUpdating) {
                tableBody.classList.add('loading');
            }
            
            fetch('manajemen_pesanan.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=get_orders'
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(result => {
                console.log('Orders response:', result);
                
                if (!result.success) {
                    console.error('Server error:', result.error);
                    showToast('Error server: ' + result.error, 'error');
                    return;
                }
                
                const orders = result.data || [];
                if (!tableBody) return;
                
                tableBody.innerHTML = '';
                tableBody.classList.remove('loading');
                
                // Filter orders if search term exists
                let filteredOrders = orders;
                if (searchTerm) {
                    const term = searchTerm.toLowerCase();
                    filteredOrders = orders.filter(order => 
                        (order.id_pesanan && order.id_pesanan.toString().includes(term)) ||
                        (order.nomor_pesanan && order.nomor_pesanan.toLowerCase().includes(term)) ||
                        (order.nama_pelanggan && order.nama_pelanggan.toLowerCase().includes(term)) ||
                        (order.nama_tanaman && order.nama_tanaman.toLowerCase().includes(term)) ||
                        (order.tgl_pesanan && order.tgl_pesanan.toLowerCase().includes(term)) ||
                        (order.total && order.total.toString().includes(term)) ||
                        (order.status_pesanan && order.status_pesanan.toLowerCase().includes(term))
                    );
                }
                
                if (filteredOrders.length === 0) {
                    const row = document.createElement('tr');
                    row.innerHTML = `<td colspan="10" class="text-center">Tidak ada pesanan ditemukan</td>`;
                    tableBody.appendChild(row);
                    return;
                }
                
                filteredOrders.forEach(order => {
                    const row = document.createElement('tr');
                    
                    // Format date
                    let formattedDate = '-';
                    if (order.tgl_pesanan) {
                        try {
                            const orderDate = new Date(order.tgl_pesanan);
                            formattedDate = orderDate.toLocaleDateString('id-ID', {
                                day: '2-digit',
                                month: '2-digit',
                                year: 'numeric',
                                hour: '2-digit',
                                minute: '2-digit'
                            });
                        } catch (e) {
                            formattedDate = order.tgl_pesanan;
                        }
                    }
                    
                    // Format total as currency
                    let formattedTotal = '-';
                    if (order.total) {
                        try {
                            formattedTotal = new Intl.NumberFormat('id-ID', {
                                style: 'currency',
                                currency: 'IDR',
                                minimumFractionDigits: 0
                            }).format(order.total);
                        } catch (e) {
                            formattedTotal = 'Rp ' + order.total.toLocaleString('id-ID');
                        }
                    }
                    
                    const status = order.status_pesanan || '';
                    const needsVerification = needsPaymentVerification(status);
                    const paymentVerified = isPaymentVerified(status);
                    
                    // Verification button or status
                    let verificationButton = '';
                    if (needsVerification) {
                        verificationButton = `
                            <button class="btn-action btn-verify" onclick="showPaymentVerification(${order.id_pesanan})" title="Verifikasi Pembayaran">
                                <i class="fas fa-credit-card"></i> Verifikasi
                            </button>
                        `;
                    } else if (paymentVerified) {
                        verificationButton = '<span class="verification-completed"><i class="fas fa-check"></i> Terverifikasi</span>';
                    } else {
                        verificationButton = '<span class="verification-needed"><i class="fas fa-clock"></i> Belum Bayar</span>';
                    }
                    
                    // Status update dropdown
                    let statusUpdateDropdown = '';
                    if (paymentVerified && !['selesai', 'dibatalkan'].includes(status)) {
                        const availableStatuses = getAvailableStatuses(status);
                        console.log('Available statuses for', status, ':', availableStatuses); // Debug log
                        statusUpdateDropdown = `
                            <select class="status-select" 
                                    onchange="showStatusConfirmation(${order.id_pesanan}, this.value, '${status}')" 
                                    data-order-id="${order.id_pesanan}" 
                                    data-current-status="${status}">
                                ${availableStatuses.map(s => 
                                    `<option value="${s}" ${status === s ? 'selected' : ''}>${getStatusText(s)}</option>`
                                ).join('')}
                            </select>
                        `;
                    } else if (needsVerification) {
                        statusUpdateDropdown = '<span class="text-sm verification-needed"><i class="fas fa-exclamation-triangle"></i> Verifikasi pembayaran dulu</span>';
                    } else if (status === 'selesai') {
                        statusUpdateDropdown = '<span class="text-sm" style="color: #28a745;"><i class="fas fa-check-circle"></i> Pesanan selesai</span>';
                    } else if (status === 'dibatalkan') {
                        statusUpdateDropdown = '<span class="text-sm" style="color: #dc3545;"><i class="fas fa-times-circle"></i> Pesanan dibatalkan</span>';
                    } else {
                        statusUpdateDropdown = '<span class="text-sm" style="color: #6c757d;"><i class="fas fa-info-circle"></i> Belum ada pembayaran</span>';
                    }
                    
                    row.innerHTML = `
                        <td>${order.id_pesanan || '-'}</td>
                        <td>${order.nomor_pesanan || '-'}</td>
                        <td>${order.nama_pelanggan || order.id_pelanggan || '-'}</td>
                        <td>${order.nama_tanaman || order.id_produk || '-'}</td>
                        <td>${formattedDate}</td>
                        <td>${formattedTotal}</td>
                        <td><span class="status-badge status-${status.replace('_', '-')}">${getStatusText(status)}</span></td>
                        <td>${verificationButton}</td>
                        <td>${statusUpdateDropdown}</td>
                        <td class="action-buttons">
                            <button class="btn-action btn-detail" onclick="showOrderDetail(${order.id_pesanan})" title="Lihat Detail">
                                <i class="fas fa-eye"></i>
                            </button>
                        </td>
                    `;
                    
                    tableBody.appendChild(row);
                });
            })
            .catch(error => {
                console.error('Error loading orders:', error);
                showToast('Gagal memuat data pesanan: ' + error.message, 'error');
                if (tableBody) {
                    tableBody.classList.remove('loading');
                }
            });
        }
        
        // Get available statuses based on current status
        function getAvailableStatuses(currentStatus) {
            const statusFlow = {
        'diverifikasi': ['diverifikasi', 'diproses'],
        'terverifikasi': ['terverifikasi', 'diproses'],
                'diproses': ['diproses', 'dikemas', 'dikirim'],
                'dikemas': ['dikirim', 'selesai'], // Changed from ['dikemas', 'dikirim'] to allow direct completion
                'dikirim': ['dikirim', 'selesai'],
                'selesai': ['selesai'],
                'dibatalkan': ['dibatalkan']
            };
            
            const result = statusFlow[currentStatus] || [currentStatus];
            console.log('Status flow for', currentStatus, ':', result); // Debug log
            return result;
        }
        
        // Load stats from server
        function loadStats() {
            fetch('manajemen_pesanan.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=get_stats'
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    const stats = result.data;
                    const totalElement = document.getElementById('totalOrders');
                    const pendingElement = document.getElementById('pendingOrders');
                    const processingElement = document.getElementById('processingOrders');
                    const shippedElement = document.getElementById('shippedOrders');
                    const completedElement = document.getElementById('completedOrders');
                    
                    if (totalElement) totalElement.textContent = stats.total || 0;
                    if (pendingElement) pendingElement.textContent = stats.pending || 0;
                    if (processingElement) processingElement.textContent = stats.processing || 0;
                    if (shippedElement) shippedElement.textContent = stats.shipped || 0;
                    if (completedElement) completedElement.textContent = stats.completed || 0;
                } else {
                    console.error('Error loading stats:', result.error);
                }
            })
            .catch(error => {
                console.error('Error loading stats:', error);
            });
        }
        
        // Show order detail modal
        function showOrderDetail(orderId) {
            fetch('manajemen_pesanan.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=get_order_detail&order_id=${orderId}`
            })
            .then(response => response.json())
            .then(result => {
                if (!result.success) {
                    showToast(result.error || 'Pesanan tidak ditemukan', 'error');
                    return;
                }
                
                const order = result.data;
                
                const orderDate = new Date(order.tgl_pesanan);
                const formattedDate = orderDate.toLocaleDateString('id-ID', {
                    day: '2-digit',
                    month: 'long',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
                
                const formattedTotal = new Intl.NumberFormat('id-ID', {
                    style: 'currency',
                    currency: 'IDR',
                    minimumFractionDigits: 0
                }).format(order.total);
                
                const paymentVerified = isPaymentVerified(order.status_pesanan);
                const paymentStatus = paymentVerified ? 
                    '<span class="status-badge status-terverifikasi"><i class="fas fa-check"></i> Terverifikasi</span>' : 
                    '<span class="status-badge status-menunggu-verifikasi"><i class="fas fa-clock"></i> Belum Verifikasi</span>';
                
                let paymentInfo = '';
                if (order.tgl_bayar) {
                    const paymentDate = new Date(order.tgl_bayar + ' ' + (order.waktu_bayar || '00:00:00'));
                    const formattedPaymentDate = paymentDate.toLocaleDateString('id-ID', {
                        day: '2-digit',
                        month: 'long',
                        year: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                    paymentInfo = `
                        <div class="detail-item">
                            <label>Tanggal Pembayaran:</label>
                            <span>${formattedPaymentDate}</span>
                        </div>
                    `;
                }
                
                let paymentProofInfo = '';
                if (order.bukti_pembayaran) {
                    paymentProofInfo = `
                        <div class="detail-item">
                            <label>Bukti Pembayaran:</label>
                            <span>
                                <button class="btn-view-proof" onclick="window.open('admin/pembayaran/${order.bukti_pembayaran}', '_blank')">
                                    <i class="fas fa-external-link-alt"></i> Lihat Bukti
                                </button>
                            </span>
                        </div>
                    `;
                }
                
                const modalContent = document.getElementById('orderDetailContent');
                modalContent.innerHTML = `
                    <div class="order-detail-grid">
                        <div class="detail-section">
                            <h4><i class="fas fa-info-circle"></i> Informasi Pesanan</h4>
                            <div class="detail-item">
                                <label>ID Pesanan:</label>
                                <span>${order.id_pesanan}</span>
                            </div>
                            <div class="detail-item">
                                <label>Nomor Pesanan:</label>
                                <span>${order.nomor_pesanan || '-'}</span>
                            </div>
                            <div class="detail-item">
                                <label>Tanggal Pesanan:</label>
                                <span>${formattedDate}</span>
                            </div>
                            <div class="detail-item">
                                <label>Status Pesanan:</label>
                                <span class="status-badge status-${order.status_pesanan.replace('_', '-')}">${getStatusText(order.status_pesanan)}</span>
                            </div>
                            <div class="detail-item">
                                <label>Total:</label>
                                <span class="total-amount">${formattedTotal}</span>
                            </div>
                        </div>
                        
                        <div class="detail-section">
                            <h4><i class="fas fa-user"></i> Informasi Pelanggan</h4>
                            <div class="detail-item">
                                <label>ID Pelanggan:</label>
                                <span>${order.id_pelanggan || '-'}</span>
                            </div>
                            <div class="detail-item">
                                <label>Nama:</label>
                                <span>${order.nama_pelanggan || '-'}</span>
                            </div>
                            <div class="detail-item">
                                <label>Email:</label>
                                <span>${order.email_pelanggan || '-'}</span>
                            </div>
                            <div class="detail-item">
                                <label>Telepon:</label>
                                <span>${order.no_hp || '-'}</span>
                            </div>
                        </div>
                        
                        <div class="detail-section">
                            <h4><i class="fas fa-box"></i> Informasi Produk</h4>
                            <div class="detail-item">
                                <label>ID Produk:</label>
                                <span>${order.id_produk || '-'}</span>
                            </div>
                            <div class="detail-item">
                                <label>Nama Produk:</label>
                                <span>${order.nama_tanaman || '-'}</span>
                            </div>
                            <div class="detail-item">
                                <label>Jumlah:</label>
                                <span>${order.jumlah || 1}</span>
                            </div>
                            <div class="detail-item">
                                <label>Harga Satuan:</label>
                                <span>Rp${(order.harga_produk || 0).toLocaleString('id-ID')}</span>
                            </div>
                        </div>
                        
                        <div class="detail-section">
                            <h4><i class="fas fa-credit-card"></i> Informasi Pembayaran</h4>
                            <div class="detail-item">
                                <label>Status Pembayaran:</label>
                                ${paymentStatus}
                            </div>
                            ${paymentInfo}
                            ${paymentProofInfo}
                            ${order.catatan_pembayaran ? `
                            <div class="detail-item">
                                <label>Catatan:</label>
                                <span>${order.catatan_pembayaran}</span>
                            </div>
                            ` : ''}
                        </div>
                    </div>
                `;
                
                document.getElementById('orderDetailModal').classList.add('show');
            })
            .catch(error => {
                console.error('Error loading order detail:', error);
                showToast('Gagal memuat detail pesanan', 'error');
            
            });
        }
        
        // Show status confirmation modal
        function showStatusConfirmation(orderId, newStatus, currentStatusParam = null) {
    console.log('=== Status Confirmation Debug ===');
    console.log('Order ID:', orderId);
    console.log('New Status:', newStatus);
    console.log('Current Status Param:', currentStatusParam);
    
    // Get current status from the select element or parameter
    const currentSelect = document.querySelector(`.status-select[data-order-id="${orderId}"]`);
    console.log('Select element found:', currentSelect);
    
    const currentStatus = currentStatusParam || (currentSelect ? currentSelect.getAttribute('data-current-status') : null);
    console.log('Current status determined:', currentStatus);
    
    // Don't allow status change if it's the same
    if (newStatus === currentStatus) {
        console.log('Same status selected, resetting dropdown');
        if (currentSelect) {
            currentSelect.value = currentStatus;
        }
        return;
    }
    
    // Check if transition is valid
    const availableStatuses = getAvailableStatuses(currentStatus);
    console.log('Available statuses:', availableStatuses);
    
    // The UI dropdown only shows forward progression, not cancellation.
    // The server-side isValidStatusTransition handles all valid transitions including cancellation.
    // So, we only check if the newStatus is in the *UI's* available statuses for forward movement.
    if (!availableStatuses.includes(newStatus)) {
        console.log('Invalid status transition from', currentStatus, 'to', newStatus);
        showToast('Transisi status tidak valid dari "' + getStatusText(currentStatus) + '" ke "' + getStatusText(newStatus) + '"', 'error');
        if (currentSelect) {
            currentSelect.value = currentStatus;
        }
        return;
    }
    
    // Set global variables
    window.currentOrderId = orderId;
    window.currentStatus = newStatus;
    
    console.log('Setting global vars - Order ID:', window.currentOrderId, 'Status:', window.currentStatus);
    
    const statusText = getStatusText(newStatus);
    const currentStatusText = getStatusText(currentStatus);
    
    document.getElementById('statusChangeText').textContent = `Anda akan mengubah status pesanan #${orderId} dari "${currentStatusText}" menjadi "${statusText}"`;
    document.getElementById('statusNote').value = '';
    
    document.getElementById('statusConfirmModal').classList.add('show');
    console.log('Modal should be shown now');
}
        
        // Cancel status update
        function cancelStatusUpdate() {
            const select = document.querySelector(`.status-select[data-order-id="${window.currentOrderId}"]`);
            if (select) {
                // Reset to previous value using data attribute
                const originalStatus = select.getAttribute('data-current-status');
                select.value = originalStatus;
            }
            
            document.getElementById('statusConfirmModal').classList.remove('show');
            window.currentOrderId = null;
            window.currentStatus = null;
        }
        
        // Confirm status update
        function confirmStatusUpdate() {
    console.log('=== Confirm Status Update Debug ===');
    console.log('Is updating:', window.isUpdating);
    console.log('Current Order ID:', window.currentOrderId);
    console.log('Current Status:', window.currentStatus);
    
    if (window.isUpdating) {
        console.log('Already updating, returning');
        return;
    }
    
    if (!window.currentOrderId || !window.currentStatus) {
        console.log('Missing order ID or status');
        showToast('Data pesanan tidak lengkap', 'error');
        return;
    }
    
    const note = document.getElementById('statusNote').value;
    window.isUpdating = true;
    
    console.log('Sending update request...');
    
    // Disable buttons
    const buttons = document.querySelectorAll('#statusConfirmModal button');
    buttons.forEach(btn => btn.disabled = true);
    
    const requestBody = `action=update_status&order_id=${window.currentOrderId}&status=${window.currentStatus}&notes=${encodeURIComponent(note)}`;
    console.log('Request body:', requestBody);
    
    fetch('manajemen_pesanan.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: requestBody
    })
    .then(response => {
        console.log('Response received:', response);
        return response.json();
    })
    .then(result => {
        console.log('Status update result:', result);
        
        if (result.success) {
            showToast(`Status pesanan #${result.order_number || window.currentOrderId} berhasil diperbarui menjadi "${getStatusText(window.currentStatus)}"`, 'success');
            
            // Update the data-current-status attribute after successful update
            const select = document.querySelector(`.status-select[data-order-id="${window.currentOrderId}"]`);
            if (select) {
                select.setAttribute('data-current-status', window.currentStatus);
                console.log('Updated select data-current-status to:', window.currentStatus);
            }
            
            // Reload data
            loadOrders();
            loadStats();
        } else {
            console.error('Update failed:', result.error);
            showToast(result.error || 'Gagal memperbarui status pesanan', 'error');
            
            // Reset select to original value on error
            const select = document.querySelector(`.status-select[data-order-id="${window.currentOrderId}"]`);
            if (select) {
                const originalStatus = select.getAttribute('data-current-status');
                select.value = originalStatus;
                console.log('Reset select to original status:', originalStatus);
            }
        }
        
        document.getElementById('statusConfirmModal').classList.remove('show');
        window.currentOrderId = null;
        window.currentStatus = null;
    })
    .catch(error => {
        console.error('Error updating status:', error);
        showToast('Terjadi kesalahan saat memperbarui status: ' + error.message, 'error');
        
        // Reset select to original value on error
        const select = document.querySelector(`.status-select[data-order-id="${window.currentOrderId}"]`);
        if (select) {
            const originalStatus = select.getAttribute('data-current-status');
            select.value = originalStatus;
        }
        
        document.getElementById('statusConfirmModal').classList.remove('show');
        window.currentOrderId = null;
        window.currentStatus = null;
    })
    .finally(() => {
        window.isUpdating = false;
        buttons.forEach(btn => btn.disabled = false);
        console.log('Update process completed');
    });
}
        
        // Get status text for display
        function getStatusText(status) {
            switch(status) {
                case 'menunggu_pembayaran': return 'Menunggu Pembayaran';
                case 'menunggu_verifikasi': return 'Menunggu Verifikasi';
                case 'diverifikasi': return 'Diverifikasi';
                case 'terverifikasi': return 'Terverifikasi';
                case 'diproses': return 'Diproses';
                case 'dikemas': return 'Dikemas';
                case 'dikirim': return 'Dikirim';
                case 'selesai': return 'Selesai';
                case 'dibatalkan': return 'Dibatalkan';
                default: return status;
            }
        }
        
        // Show toast notification
        function showToast(message, type = 'info') {
            const toast = document.getElementById('toast');
            if (!toast) return;
            
            const toastIcon = toast.querySelector('.toast-icon-element');
            const toastMessage = toast.querySelector('.toast-message');
            
            toastMessage.textContent = message;
            
            let iconClass;
            switch (type) {
                case 'success':
                    iconClass = 'fas fa-check-circle';
                    break;
                case 'error':
                    iconClass = 'fas fa-exclamation-circle';
                    break;
                case 'warning':
                    iconClass = 'fas fa-exclamation-triangle';
                    break;
                default:
                    iconClass = 'fas fa-info-circle';
            }
            
            toastIcon.className = iconClass;
            toast.className = 'toast';
            toast.classList.add(type, 'show');
            
            setTimeout(() => {
                closeToast();
            }, 5000);
        }
        
        // Close toast
        function closeToast() {
            const toast = document.getElementById('toast');
            if (toast) {
                toast.classList.remove('show');
            }
        }

        // Show payment verification modal
        function showPaymentVerification(orderId) {
            currentOrderId = orderId;
            fetch('manajemen_pesanan.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=get_order_detail&order_id=${orderId}`
            })
            .then(response => response.json())
            .then(result => {
                if (!result.success) {
                    showToast(result.error || 'Gagal memuat detail pembayaran', 'error');
                    return;
                }
                
                currentOrder = result.data; // Store current order details globally
                
                const modalText = document.getElementById('paymentVerificationText');
                const paymentDetailsDiv = document.getElementById('paymentDetails');
                const paymentProofContainer = document.getElementById('paymentProofContainer');
                
                modalText.innerHTML = `Verifikasi pembayaran untuk pesanan <strong>#${currentOrder.nomor_pesanan || currentOrder.id_pesanan}</strong> dari <strong>${currentOrder.nama_pelanggan || 'Pelanggan'}</strong>.`;
                
                let paymentInfoHtml = `
                    <div class="detail-section">
                        <h4><i class="fas fa-info-circle"></i> Detail Pembayaran</h4>
                        <div class="detail-item">
                            <label>Produk:</label>
                            <span>${currentOrder.nama_tanaman || '-'}</span>
                        </div>
                        <div class="detail-item">
                            <label>Jumlah:</label>
                            <span>${currentOrder.jumlah || 1}</span>
                        </div>
                        <div class="detail-item">
                            <label>Total Pembayaran:</label>
                            <span class="total-amount">${new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(currentOrder.total || 0)}</span>
                        </div>
                `;
                if (currentOrder.tgl_bayar) {
                    const paymentDate = new Date(currentOrder.tgl_bayar + ' ' + (currentOrder.waktu_bayar || '00:00:00'));
                    paymentInfoHtml += `
                        <div class="detail-item">
                            <label>Tanggal Bayar:</label>
                            <span>${paymentDate.toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' })}</span>
                        </div>
                    `;
                }
                if (currentOrder.catatan_pembayaran) {
                    paymentInfoHtml += `
                        <div class="detail-item">
                            <label>Catatan Pelanggan:</label>
                            <span>${currentOrder.catatan_pembayaran}</span>
                        </div>
                    `;
                }
                paymentInfoHtml += `</div>`; // Close detail-section
                paymentDetailsDiv.innerHTML = paymentInfoHtml;
                
                paymentProofContainer.innerHTML = '';
                if (currentOrder.bukti_pembayaran) {
                    paymentProofContainer.innerHTML = `
                        <div class="detail-section">
                            <h4><i class="fas fa-image"></i> Bukti Pembayaran</h4>
                            <img src="admin/pembayaran/${currentOrder.bukti_pembayaran}" alt="Bukti Pembayaran" class="payment-proof" onclick="window.open(this.src, '_blank')">
                            <p class="text-muted-foreground text-xs">Klik gambar untuk melihat ukuran penuh</p>
                        </div>
                    `;
                } else {
                    paymentProofContainer.innerHTML = '<p class="text-muted-foreground">Tidak ada bukti pembayaran diunggah.</p>';
                }
                
                document.getElementById('paymentNote').value = ''; // Clear previous notes
                document.getElementById('paymentVerificationModal').classList.add('show');
            })
            .catch(error => {
                console.error('Error fetching payment detail:', error);
                showToast('Gagal memuat detail pembayaran', 'error');
            });
        }

        // Cancel payment verification
        function cancelPaymentVerification() {
            document.getElementById('paymentVerificationModal').classList.remove('show');
            currentOrderId = null;
            currentOrder = null;
        }

        // Confirm payment verification (approve)
        function confirmPayment() {
            if (isUpdating) return;
            isUpdating = true;
            
            const note = document.getElementById('paymentNote').value;
            const buttons = document.querySelectorAll('#paymentVerificationModal button');
            buttons.forEach(btn => btn.disabled = true);

            fetch('manajemen_pesanan.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=verify_payment&order_id=${currentOrderId}&verified=1&notes=${encodeURIComponent(note)}`
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    showToast(`Pembayaran pesanan #${result.order_number || currentOrderId} berhasil diverifikasi.`, 'success');
                    loadOrders();
                    loadStats();
                } else {
                    showToast(result.error || 'Gagal memverifikasi pembayaran', 'error');
                }
                document.getElementById('paymentVerificationModal').classList.remove('show');
            })
            .catch(error => {
                console.error('Error confirming payment:', error);
                showToast('Terjadi kesalahan saat memverifikasi pembayaran: ' + error.message, 'error');
            })
            .finally(() => {
                isUpdating = false;
                buttons.forEach(btn => btn.disabled = false);
                currentOrderId = null;
                currentOrder = null;
            });
        }

        // Reject payment verification (cancel order)
        function rejectPayment() {
            if (isUpdating) return;
            isUpdating = true;
            
            const note = document.getElementById('paymentNote').value;
            const buttons = document.querySelectorAll('#paymentVerificationModal button');
            buttons.forEach(btn => btn.disabled = true);

            fetch('manajemen_pesanan.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=verify_payment&order_id=${currentOrderId}&verified=0&notes=${encodeURIComponent(note)}`
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    showToast(`Pembayaran pesanan #${result.order_number || currentOrderId} ditolak dan pesanan dibatalkan.`, 'warning');
                    loadOrders();
                    loadStats();
                } else {
                    showToast(result.error || 'Gagal menolak pembayaran', 'error');
                }
                document.getElementById('paymentVerificationModal').classList.remove('show');
            })
            .catch(error => {
                console.error('Error rejecting payment:', error);
                showToast('Terjadi kesalahan saat menolak pembayaran: ' + error.message, 'error');
            })
            .finally(() => {
                isUpdating = false;
                buttons.forEach(btn => btn.disabled = false);
                currentOrderId = null;
                currentOrder = null;
            });
        }
    </script>
</body>
</html>

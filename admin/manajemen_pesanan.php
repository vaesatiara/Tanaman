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
                    id_pesanan, 
                    id_produk, 
                    id_pelanggan, 
                    nomor_pesanan, 
                    tgl_pesanan, 
                    status_pesanan, 
                    jumlah, 
                    total 
                FROM pesanan 
                ORDER BY tgl_pesanan DESC");
                
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
                    id_pesanan, 
                    id_produk, 
                    id_pelanggan, 
                    nomor_pesanan, 
                    tgl_pesanan, 
                    status_pesanan, 
                    jumlah, 
                    total 
                FROM pesanan 
                WHERE id_pesanan = ?");
                $stmt->bind_param("i", $orderId);
                $stmt->execute();
                $result = $stmt->get_result();
                $order = $result->fetch_assoc();
                
                if ($order) {
                    echo json_encode(['success' => true, 'data' => $order]);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Pesanan tidak ditemukan']);
                }
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
            exit;
            
        case 'verify_payment':
            $orderId = intval($_POST['order_id'] ?? 0);
            $verified = intval($_POST['verified'] ?? 0);
            $notes = $_POST['notes'] ?? '';
            
            if ($orderId <= 0) {
                echo json_encode(['success' => false, 'error' => 'ID pesanan tidak valid']);
                exit;
            }
            
            try {
                // Cek status pesanan saat ini
                $stmt = $koneksi->prepare("SELECT status_pesanan FROM pesanan WHERE id_pesanan = ?");
                $stmt->bind_param("i", $orderId);
                $stmt->execute();
                $result = $stmt->get_result();
                $currentOrder = $result->fetch_assoc();
                
                if (!$currentOrder) {
                    echo json_encode(['success' => false, 'error' => 'Pesanan tidak ditemukan']);
                    exit;
                }
                
                // Hanya bisa verifikasi jika status masih menunggu konfirmasi
                if ($currentOrder['status_pesanan'] !== 'menunggu-konfirmasi') {
                    echo json_encode(['success' => false, 'error' => 'Pesanan sudah diverifikasi atau tidak bisa diverifikasi']);
                    exit;
                }
                
                $newStatus = $verified ? 'dikonfirmasi' : 'dibatalkan';
                
                $stmt = $koneksi->prepare("UPDATE pesanan SET status_pesanan = ? WHERE id_pesanan = ?");
                $stmt->bind_param("si", $newStatus, $orderId);
                $result = $stmt->execute();
                
                if ($result) {
                    echo json_encode(['success' => true, 'new_status' => $newStatus, 'message' => 'Verifikasi pembayaran berhasil']);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Gagal memperbarui status pesanan']);
                }
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
            exit;
            
        case 'update_status':
            $orderId = intval($_POST['order_id'] ?? 0);
            $status = $_POST['status'] ?? '';
            $notes = $_POST['notes'] ?? '';
            
            if ($orderId <= 0) {
                echo json_encode(['success' => false, 'error' => 'ID pesanan tidak valid']);
                exit;
            }
            
            // Validasi status yang diperbolehkan
            $allowedStatuses = [
                'menunggu-konfirmasi',
                'dikonfirmasi', 
                'diproses', 
                'dikemas', 
                'menunggu-dikirim',
                'dikirim', 
                'selesai',
                'dibatalkan'
            ];
            
            if (!in_array($status, $allowedStatuses)) {
                echo json_encode(['success' => false, 'error' => 'Status tidak valid: ' . $status]);
                exit;
            }
            
            try {
                // Cek apakah pesanan ada
                $stmt = $koneksi->prepare("SELECT status_pesanan FROM pesanan WHERE id_pesanan = ?");
                $stmt->bind_param("i", $orderId);
                $stmt->execute();
                $result = $stmt->get_result();
                $currentOrder = $result->fetch_assoc();
                
                if (!$currentOrder) {
                    echo json_encode(['success' => false, 'error' => 'Pesanan tidak ditemukan']);
                    exit;
                }
                
                // Update status
                $stmt = $koneksi->prepare("UPDATE pesanan SET status_pesanan = ? WHERE id_pesanan = ?");
                $stmt->bind_param("si", $status, $orderId);
                $result = $stmt->execute();
                
                if ($result) {
                    // Log perubahan status (optional)
                    error_log("Status pesanan ID $orderId diubah dari {$currentOrder['status_pesanan']} ke $status");
                    
                    echo json_encode([
                        'success' => true, 
                        'message' => 'Status pesanan berhasil diperbarui',
                        'old_status' => $currentOrder['status_pesanan'],
                        'new_status' => $status
                    ]);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Gagal memperbarui status pesanan']);
                }
            } catch (Exception $e) {
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
                
                // Pending orders (menunggu konfirmasi)
                $stmt = $koneksi->query("SELECT COUNT(*) as count FROM pesanan WHERE status_pesanan = 'menunggu-konfirmasi'");
                $result = $stmt->fetch_assoc();
                $stats['pending'] = $result['count'];
                
                // Processing orders (dikonfirmasi + diproses + dikemas)
                $stmt = $koneksi->query("SELECT COUNT(*) as count FROM pesanan WHERE status_pesanan IN ('dikonfirmasi', 'diproses', 'dikemas')");
                $result = $stmt->fetch_assoc();
                $stats['processing'] = $result['count'];
                
                // Shipped orders (dikirim)
                $stmt = $koneksi->query("SELECT COUNT(*) as count FROM pesanan WHERE status_pesanan = 'dikirim'");
                $result = $stmt->fetch_assoc();
                $stats['shipped'] = $result['count'];
                
                // Canceled orders (dibatalkan)
                $stmt = $koneksi->query("SELECT COUNT(*) as count FROM pesanan WHERE status_pesanan = 'dibatalkan'");
                $result = $stmt->fetch_assoc();
                $stats['canceled'] = $result['count'];
                
                echo json_encode(['success' => true, 'data' => $stats]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
            exit;
            
        default:
            echo json_encode(['success' => false, 'error' => 'Action tidak dikenal']);
            exit;
    }
}

// Get orders for initial page load
try {
    $sql = "SELECT 
        id_pesanan, 
        id_produk, 
        id_pelanggan, 
        nomor_pesanan, 
        tgl_pesanan, 
        status_pesanan, 
        jumlah, 
        total 
    FROM pesanan 
    ORDER BY tgl_pesanan DESC";
    $query = mysqli_query($koneksi, $sql);
    $initialOrders = [];
    while ($row = mysqli_fetch_assoc($query)) {
        $initialOrders[] = $row;
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
            </div>

            <!-- Orders Table -->
            <div class="data-table">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID Pesanan</th>
                            <th>Nomor Pesanan</th>
                            <th>ID Pelanggan</th>
                            <th>ID Produk</th>
                            <th>Tanggal</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Verifikasi Pembayaran</th>
                            <th>Update Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="ordersTableBody">
                        <!-- Orders will be populated by JavaScript -->
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
                            <!-- Payment details will be populated here -->
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
            
            // Load orders and stats
            loadOrders();
            loadStats();
            
            // Search functionality
            const searchInput = document.getElementById('searchInput');
            if (searchInput) {
                searchInput.addEventListener('keyup', function(e) {
                    if (e.key === 'Enter') {
                        loadOrders(this.value);
                    }
                });
            }
        });
        
        // Check if payment is verified
        function isPaymentVerified(status) {
            return status !== 'menunggu-konfirmasi';
        }
        
        // Load orders from server
        function loadOrders(searchTerm = '') {
            console.log('Loading orders...');
            
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
                const tableBody = document.getElementById('ordersTableBody');
                if (!tableBody) return;
                
                tableBody.innerHTML = '';
                
                // Filter orders if search term exists
                let filteredOrders = orders;
                if (searchTerm) {
                    const term = searchTerm.toLowerCase();
                    filteredOrders = orders.filter(order => 
                        (order.id_pesanan && order.id_pesanan.toString().includes(term)) ||
                        (order.nomor_pesanan && order.nomor_pesanan.toLowerCase().includes(term)) ||
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
                    const paymentVerified = isPaymentVerified(status);
                    
                    // Verification button or status
                    let verificationButton = '';
                    if (!paymentVerified) {
                        verificationButton = `
                            <button class="btn-action btn-detail" onclick="showPaymentVerification(${order.id_pesanan})" title="Verifikasi Pembayaran">
                                <i class="fas fa-credit-card"></i> Verifikasi
                            </button>
                        `;
                    } else {
                        verificationButton = '<span class="status-badge status-selesai"><i class="fas fa-check"></i> Terverifikasi</span>';
                    }
                    
                    // Status update dropdown
                    let statusUpdateDropdown = '';
                    if (paymentVerified) {
                        statusUpdateDropdown = `
                            <select class="status-select" 
                                    onchange="showStatusConfirmation(${order.id_pesanan}, this.value, '${status}')" 
                                    data-order-id="${order.id_pesanan}" 
                                    data-current-status="${status}">
                                <option value="dikonfirmasi" ${status === 'dikonfirmasi' ? 'selected' : ''}>Dikonfirmasi</option>
                                <option value="diproses" ${status === 'diproses' ? 'selected' : ''}>Diproses</option>
                                <option value="dikemas" ${status === 'dikemas' ? 'selected' : ''}>Dikemas</option>
                                <option value="menunggu-dikirim" ${status === 'menunggu-dikirim' ? 'selected' : ''}>Menunggu Dikirim</option>
                                <option value="dikirim" ${status === 'dikirim' ? 'selected' : ''}>Dikirim</option>
                                <option value="selesai" ${status === 'selesai' ? 'selected' : ''}>Selesai</option>
                                <option value="dibatalkan" ${status === 'dibatalkan' ? 'selected' : ''}>Dibatalkan</option>
                            </select>
                        `;
                    } else {
                        statusUpdateDropdown = '<span class="text-sm" style="color: #dc3545; font-weight: 500;"><i class="fas fa-exclamation-triangle"></i> Verifikasi pembayaran dulu</span>';
                    }
                    
                    row.innerHTML = `
                        <td>${order.id_pesanan || '-'}</td>
                        <td>${order.nomor_pesanan || '-'}</td>
                        <td>${order.id_pelanggan || '-'}</td>
                        <td>${order.id_produk || '-'}</td>
                        <td>${formattedDate}</td>
                        <td>${formattedTotal}</td>
                        <td><span class="status-badge status-${status}">${getStatusText(status)}</span></td>
                        <td>${verificationButton}</td>
                        <td>${statusUpdateDropdown}</td>
                        <td class="action-buttons">
                            <button class="btn-action btn-detail" onclick="showOrderDetail(${order.id_pesanan})">
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
            });
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
                    
                    if (totalElement) totalElement.textContent = stats.total || 0;
                    if (pendingElement) pendingElement.textContent = stats.pending || 0;
                    if (processingElement) processingElement.textContent = stats.processing || 0;
                    if (shippedElement) shippedElement.textContent = stats.shipped || 0;
                } else {
                    console.error('Error loading stats:', result.error);
                }
            })
            .catch(error => {
                console.error('Error loading stats:', error);
            });
        }
        
        // Show payment verification modal
        function showPaymentVerification(orderId) {
            console.log('Showing payment verification for order:', orderId);
            
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
                currentOrder = order;
                currentOrderId = orderId;
                
                const formattedTotal = new Intl.NumberFormat('id-ID', {
                    style: 'currency',
                    currency: 'IDR',
                    minimumFractionDigits: 0
                }).format(order.total);
                
                const orderDate = new Date(order.tgl_pesanan);
                const formattedDate = orderDate.toLocaleDateString('id-ID', {
                    day: '2-digit',
                    month: 'long',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
                
                document.getElementById('paymentVerificationText').textContent = 
                    `Apakah pembayaran untuk pesanan #${order.nomor_pesanan || order.id_pesanan} sudah diterima?`;
                
                document.getElementById('paymentDetails').innerHTML = `
                    <div class="payment-detail-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin: 1rem 0; padding: 1rem; background: #f9f9f9; border-radius: 8px;">
                        <div>
                            <strong>Total Pembayaran:</strong><br>
                            <span style="font-size: 1.2em; color: #28a745; font-weight: bold;">${formattedTotal}</span>
                        </div>
                        <div>
                            <strong>Tanggal Pesanan:</strong><br>
                            ${formattedDate}
                        </div>
                        <div>
                            <strong>ID Pelanggan:</strong><br>
                            ${order.id_pelanggan}
                        </div>
                        <div>
                            <strong>ID Produk:</strong><br>
                            ${order.id_produk}
                        </div>
                    </div>
                `;
                
                document.getElementById('paymentNote').value = '';
                document.getElementById('paymentVerificationModal').classList.add('show');
            })
            .catch(error => {
                console.error('Error loading order detail:', error);
                showToast('Gagal memuat detail pesanan', 'error');
            });
        }
        
        // Confirm payment
        function confirmPayment() {
            const note = document.getElementById('paymentNote').value;
            
            console.log('Confirming payment for order:', currentOrderId);
            
            fetch('manajemen_pesanan.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=verify_payment&order_id=${currentOrderId}&verified=1&notes=${encodeURIComponent(note)}`
            })
            .then(response => response.json())
            .then(result => {
                console.log('Payment verification result:', result);
                
                if (result.success) {
                    showToast('Pembayaran berhasil dikonfirmasi', 'success');
                    loadOrders();
                    loadStats();
                } else {
                    showToast(result.error || 'Gagal mengkonfirmasi pembayaran', 'error');
                }
                
                document.getElementById('paymentVerificationModal').classList.remove('show');
                currentOrderId = null;
                currentOrder = null;
            })
            .catch(error => {
                console.error('Error confirming payment:', error);
                showToast('Terjadi kesalahan saat mengkonfirmasi pembayaran', 'error');
                
                document.getElementById('paymentVerificationModal').classList.remove('show');
                currentOrderId = null;
                currentOrder = null;
            });
        }
        
        // Reject payment
        function rejectPayment() {
            const note = document.getElementById('paymentNote').value;
            
            console.log('Rejecting payment for order:', currentOrderId);
            
            fetch('manajemen_pesanan.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=verify_payment&order_id=${currentOrderId}&verified=0&notes=${encodeURIComponent(note)}`
            })
            .then(response => response.json())
            .then(result => {
                console.log('Payment rejection result:', result);
                
                if (result.success) {
                    showToast('Pembayaran ditolak dan pesanan dibatalkan', 'warning');
                    loadOrders();
                    loadStats();
                } else {
                    showToast(result.error || 'Gagal menolak pembayaran', 'error');
                }
                
                document.getElementById('paymentVerificationModal').classList.remove('show');
                currentOrderId = null;
                currentOrder = null;
            })
            .catch(error => {
                console.error('Error rejecting payment:', error);
                showToast('Terjadi kesalahan saat menolak pembayaran', 'error');
                
                document.getElementById('paymentVerificationModal').classList.remove('show');
                currentOrderId = null;
                currentOrder = null;
            });
        }
        
        // Cancel payment verification
        function cancelPaymentVerification() {
            document.getElementById('paymentVerificationModal').classList.remove('show');
            currentOrderId = null;
            currentOrder = null;
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
                    '<span class="status-badge status-selesai"><i class="fas fa-check"></i> Terverifikasi</span>' : 
                    '<span class="status-badge status-menunggu-konfirmasi"><i class="fas fa-clock"></i> Belum Verifikasi</span>';
                
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
                                <label>ID Pelanggan:</label>
                                <span>${order.id_pelanggan || '-'}</span>
                            </div>
                            <div class="detail-item">
                                <label>ID Produk:</label>
                                <span>${order.id_produk || '-'}</span>
                            </div>
                            <div class="detail-item">
                                <label>Tanggal Pesanan:</label>
                                <span>${formattedDate}</span>
                            </div>
                            <div class="detail-item">
                                <label>Status Pembayaran:</label>
                                ${paymentStatus}
                            </div>
                            <div class="detail-item">
                                <label>Status Pesanan:</label>
                                <span class="status-badge status-${order.status_pesanan}">${getStatusText(order.status_pesanan)}</span>
                            </div>
                            <div class="detail-item">
                                <label>Total:</label>
                                <span class="total-amount">${formattedTotal}</span>
                            </div>
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
            console.log('Status confirmation:', { orderId, newStatus, currentStatusParam });
            
            // Get current status from the select element or parameter
            const currentSelect = document.querySelector(`.status-select[data-order-id="${orderId}"]`);
            const currentStatus = currentStatusParam || (currentSelect ? currentSelect.getAttribute('data-current-status') : null);
            
            // Don't allow status change if it's the same
            if (newStatus === currentStatus) {
                if (currentSelect) {
                    currentSelect.value = currentStatus; // Reset select to current status
                }
                return;
            }
            
            currentOrderId = orderId;
            currentStatus = newStatus;
            
            const statusText = getStatusText(newStatus);
            document.getElementById('statusChangeText').textContent = `Anda akan mengubah status pesanan #${orderId} menjadi "${statusText}"`;
            document.getElementById('statusNote').value = '';
            
            document.getElementById('statusConfirmModal').classList.add('show');
        }
        
        // Cancel status update
        function cancelStatusUpdate() {
            const select = document.querySelector(`.status-select[data-order-id="${currentOrderId}"]`);
            if (select) {
                // Reset to previous value using data attribute
                const originalStatus = select.getAttribute('data-current-status');
                select.value = originalStatus;
            }
            
            document.getElementById('statusConfirmModal').classList.remove('show');
            currentOrderId = null;
            currentStatus = null;
        }
        
        // Confirm status update
        function confirmStatusUpdate() {
            const note = document.getElementById('statusNote').value;
            
            console.log('Updating status:', { currentOrderId, currentStatus, note });
            
            fetch('manajemen_pesanan.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=update_status&order_id=${currentOrderId}&status=${currentStatus}&notes=${encodeURIComponent(note)}`
            })
            .then(response => response.json())
            .then(result => {
                console.log('Status update result:', result);
                
                if (result.success) {
                    showToast('Status pesanan berhasil diperbarui', 'success');
                    loadOrders();
                    loadStats();
                    
                    // Update the data-current-status attribute after successful update
                    const select = document.querySelector(`.status-select[data-order-id="${currentOrderId}"]`);
                    if (select) {
                        select.setAttribute('data-current-status', currentStatus);
                    }
                } else {
                    showToast(result.error || 'Gagal memperbarui status pesanan', 'error');
                    
                    // Reset select to original value on error
                    const select = document.querySelector(`.status-select[data-order-id="${currentOrderId}"]`);
                    if (select) {
                        const originalStatus = select.getAttribute('data-current-status');
                        select.value = originalStatus;
                    }
                }
                
                document.getElementById('statusConfirmModal').classList.remove('show');
                currentOrderId = null;
                currentStatus = null;
            })
            .catch(error => {
                console.error('Error updating status:', error);
                showToast('Terjadi kesalahan saat memperbarui status', 'error');
                
                // Reset select to original value on error
                const select = document.querySelector(`.status-select[data-order-id="${currentOrderId}"]`);
                if (select) {
                    const originalStatus = select.getAttribute('data-current-status');
                    select.value = originalStatus;
                }
                
                document.getElementById('statusConfirmModal').classList.remove('show');
                currentOrderId = null;
                currentStatus = null;
            });
        }
        
        // Get status text for display
        function getStatusText(status) {
            const statusMap = {
                'menunggu-konfirmasi': 'Menunggu Konfirmasi',
                'dikonfirmasi': 'Dikonfirmasi',
                'diproses': 'Diproses',
                'dikemas': 'Dikemas',
                'menunggu-dikirim': 'Menunggu Dikirim',
                'dikirim': 'Dikirim',
                'selesai': 'Selesai',
                'dibatalkan': 'Dibatalkan'
            };
            return statusMap[status] || status;
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
    </script>
</body>
</html>
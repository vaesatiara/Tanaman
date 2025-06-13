<?php
//session_start();
include "koneksi.php";

// // Check if user is logged in
// if (!isset($_SESSION['username'])){
//     header("Location:login.php?login dulu");
//     exit;
//}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'get_orders':
            try {
                // Tambahkan log untuk debugging
                error_log("Executing get_orders query");
                
                // Cek koneksi database
                if ($koneksi->connect_error) {
                    error_log("Database connection failed: " . $koneksi->connect_error);
                    echo json_encode(['error' => 'Database connection failed']);
                    exit;
                }
                
                $stmt = $koneksi->query("SELECT * FROM pesanan ORDER BY tgl_pesanan DESC");
                
                // Cek apakah query berhasil
                if (!$stmt) {
                    error_log("Query failed: " . $koneksi->error);
                    echo json_encode(['error' => 'Query failed: ' . $koneksi->error]);
                    exit;
                }
                
                $orders = $stmt->fetch_all(MYSQLI_ASSOC);
                
                // Log jumlah data yang ditemukan
                error_log("Found " . count($orders) . " orders");
                
                echo json_encode($orders);
            } catch (Exception $e) {
                error_log("Exception in get_orders: " . $e->getMessage());
                echo json_encode(['error' => 'Exception: ' . $e->getMessage()]);
            }
            exit;
            
        case 'get_order_detail':
            $orderId = $_POST['order_id'] ?? 0;
            $stmt = $koneksi->prepare("SELECT * FROM pesanan WHERE id_pesanan = ?");
            $stmt->bind_param("i", $orderId);
            $stmt->execute();
            $result = $stmt->get_result();
            $order = $result->fetch_assoc();
            
            if ($order) {
                // If you have order items table, fetch them here
                // $stmt = $koneksi->prepare("SELECT * FROM order_items WHERE order_id = ?");
                // $stmt->bind_param("i", $orderId);
                // $stmt->execute();
                // $result = $stmt->get_result();
                // $items = $result->fetch_all(MYSQLI_ASSOC);
                // $order['items'] = $items;
                
                // For now, we'll just return the order data
                $order['items'] = []; // Empty array since we don't have items table in your DB
            }
            
            echo json_encode($order);
            exit;
            
        case 'update_status':
            $orderId = $_POST['order_id'] ?? 0;
            $status = $_POST['status'] ?? '';
            $notes = $_POST['notes'] ?? '';
            
            // Check if catatan column exists, if not, update only status
            $stmt = $koneksi->prepare("UPDATE pesanan SET status_pesanan = ? WHERE id_pesanan = ?");
            $stmt->bind_param("si", $status, $orderId);
            $result = $stmt->execute();
            
            echo json_encode(['success' => $result]);
            exit;
            
        case 'get_stats':
            $stats = [];
            
            // Total orders today
            $stmt = $koneksi->query("SELECT COUNT(*) FROM pesanan WHERE DATE(tgl_pesanan) = CURDATE()");
            $stats['total'] = $stmt->fetch_row()[0];
            
            // Pending orders (menunggu-konfirmasi)
            $stmt = $koneksi->query("SELECT COUNT(*) FROM pesanan WHERE status_pesanan = 'menunggu-konfirmasi'");
            $stats['pending'] = $stmt->fetch_row()[0];
            
            // Processing orders (diproses)
            $stmt = $koneksi->query("SELECT COUNT(*) FROM pesanan WHERE status_pesanan = 'diproses'");
            $stats['processing'] = $stmt->fetch_row()[0];
            
            // Shipped orders (dikirim)
            $stmt = $koneksi->query("SELECT COUNT(*) FROM pesanan WHERE status_pesanan = 'dikirim'");
            $stats['shipped'] = $stmt->fetch_row()[0];
            
            // Completed orders (selesai)
            $stmt = $koneksi->query("SELECT COUNT(*) FROM pesanan WHERE status_pesanan = 'selesai'");
            $stats['completed'] = $stmt->fetch_row()[0];
            
            // Canceled orders (dibatalkan)
            $stmt = $koneksi->query("SELECT COUNT(*) FROM pesanan WHERE status_pesanan = 'dibatalkan'");
            $stats['canceled'] = $stmt->fetch_row()[0];
            
            echo json_encode($stats);
            exit;
    }
}

// Get orders for initial page load
$sql = "SELECT * FROM pesanan ORDER BY tgl_pesanan DESC";
$query = mysqli_query($koneksi, $sql);
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
                        <span class="stat-title">Menunggu Konfirmasi</span>
                        <i class="fas fa-clock stat-icon"></i>
                    </div>
                    <div class="stat-value pending" id="pendingOrders">0</div>
                    <div class="stat-info">
                        <i class="fas fa-exclamation-circle"></i>
                        <span>Perlu tindakan</span>
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
        
        // DOM Ready
        document.addEventListener('DOMContentLoaded', function() {
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
                    modal.classList.remove('show');
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
                console.log('Response status:', response.status);
                return response.text();
            })
            .then(text => {
                console.log('Raw response:', text);
                try {
                    return JSON.parse(text);
                } catch (e) {
                    console.error('Error parsing JSON:', e);
                    showToast('Format respons tidak valid', 'error');
                    throw e;
                }
            })
            .then(orders => {
                console.log('Parsed orders:', orders);
                
                // Check if response contains error
                if (orders.error) {
                    console.error('Server error:', orders.error);
                    showToast('Error server: ' + orders.error, 'error');
                    return;
                }
                
                const tableBody = document.getElementById('ordersTableBody');
                if (!tableBody) return;
                
                tableBody.innerHTML = '';
                
                // Filter orders if search term exists
                if (searchTerm) {
                    const term = searchTerm.toLowerCase();
                    orders = orders.filter(order => 
                        (order.id_pesanan && order.id_pesanan.toString().includes(term)) ||
                        (order.nomor_pesanan && order.nomor_pesanan.toLowerCase().includes(term)) ||
                        (order.tgl_pesanan && order.tgl_pesanan.toLowerCase().includes(term)) ||
                        (order.total && order.total.toString().includes(term)) ||
                        (order.status_pesanan && order.status_pesanan.toLowerCase().includes(term))
                    );
                }
                
                if (!Array.isArray(orders) || orders.length === 0) {
                    const row = document.createElement('tr');
                    row.innerHTML = `<td colspan="9" class="text-center">Tidak ada pesanan ditemukan</td>`;
                    tableBody.appendChild(row);
                    return;
                }
                
                orders.forEach(order => {
                    console.log('Processing order:', order);
                    
                    const row = document.createElement('tr');
                    
                    // Format date if exists
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
                            console.error('Error formatting date:', e);
                            formattedDate = order.tgl_pesanan;
                        }
                    }
                    
                    // Format total as currency if exists
                    let formattedTotal = '-';
                    if (order.total) {
                        try {
                            formattedTotal = new Intl.NumberFormat('id-ID', {
                                style: 'currency',
                                currency: 'IDR',
                                minimumFractionDigits: 0
                            }).format(order.total);
                        } catch (e) {
                            console.error('Error formatting total:', e);
                            formattedTotal = order.total;
                        }
                    }
                    
                    // Safely get status or default to empty string
                    const status = order.status_pesanan || '';
                    
                    row.innerHTML = `
                        <td>${order.id_pesanan || '-'}</td>
                        <td>${order.nomor_pesanan || '-'}</td>
                        <td>${order.id_pelanggan || '-'}</td>
                        <td>${order.id_produk || '-'}</td>
                        <td>${formattedDate}</td>
                        <td>${formattedTotal}</td>
                        <td><span class="status-badge status-${status}">${getStatusText(status)}</span></td>
                        <td>
                            <select class="status-select" onchange="showStatusConfirmation(${order.id_pesanan}, this.value)" data-order-id="${order.id_pesanan}">
                                <option value="menunggu-konfirmasi" ${status === 'menunggu-konfirmasi' ? 'selected' : ''}>Menunggu Konfirmasi</option>
                                <option value="diproses" ${status === 'diproses' ? 'selected' : ''}>Diproses</option>
                                <option value="dikirim" ${status === 'dikirim' ? 'selected' : ''}>Dikirim</option>
                                <option value="selesai" ${status === 'selesai' ? 'selected' : ''}>Selesai</option>
                                <option value="dibatalkan" ${status === 'dibatalkan' ? 'selected' : ''}>Dibatalkan</option>
                            </select>
                        </td>
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
                showToast('Gagal memuat data pesanan', 'error');
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
            .then(stats => {
                const totalElement = document.getElementById('totalOrders');
                const pendingElement = document.getElementById('pendingOrders');
                const processingElement = document.getElementById('processingOrders');
                const shippedElement = document.getElementById('shippedOrders');
                
                if (totalElement) totalElement.textContent = stats.total || 0;
                if (pendingElement) pendingElement.textContent = stats.pending || 0;
                if (processingElement) processingElement.textContent = stats.processing || 0;
                if (shippedElement) shippedElement.textContent = stats.shipped || 0;
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
            .then(order => {
                if (!order) {
                    showToast('Pesanan tidak ditemukan', 'error');
                    return;
                }
                
                // Format date
                const orderDate = new Date(order.tgl_pesanan);
                const formattedDate = orderDate.toLocaleDateString('id-ID', {
                    day: '2-digit',
                    month: 'long',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
                
                // Format total as currency
                const formattedTotal = new Intl.NumberFormat('id-ID', {
                    style: 'currency',
                    currency: 'IDR',
                    minimumFractionDigits: 0
                }).format(order.total);
                
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
                                <label>Status:</label>
                                <span class="status-badge status-${order.status_pesanan}">${getStatusText(order.status_pesanan)}</span>
                            </div>
                            <div class="detail-item">
                                <label>Total:</label>
                                <span class="total-amount">${formattedTotal}</span>
                            </div>
                        </div>
                    </div>
                `;
                
                // Show the modal
                document.getElementById('orderDetailModal').classList.add('show');
            })
            .catch(error => {
                console.error('Error loading order detail:', error);
                showToast('Gagal memuat detail pesanan', 'error');
            });
        }
        
        // Show status confirmation modal
        function showStatusConfirmation(orderId, newStatus) {
            currentOrderId = orderId;
            currentStatus = newStatus;
            
            const statusText = getStatusText(newStatus);
            document.getElementById('statusChangeText').textContent = `Anda akan mengubah status pesanan #${orderId} menjadi "${statusText}"`;
            document.getElementById('statusNote').value = '';
            
            // Show the modal
            document.getElementById('statusConfirmModal').classList.add('show');
        }
        
        // Cancel status update
        function cancelStatusUpdate() {
            // Reset the select to previous value
            const select = document.querySelector(`.status-select[data-order-id="${currentOrderId}"]`);
            if (select) {
                select.value = select.dataset.previousValue;
            }
            
            // Close modal
            document.getElementById('statusConfirmModal').classList.remove('show');
            
            // Reset globals
            currentOrderId = null;
            currentStatus = null;
        }
        
        // Confirm status update
        function confirmStatusUpdate() {
            const note = document.getElementById('statusNote').value;
            
            fetch('manajemen_pesanan.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=update_status&order_id=${currentOrderId}&status=${currentStatus}&notes=${encodeURIComponent(note)}`
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    showToast('Status pesanan berhasil diperbarui', 'success');
                    loadOrders();
                    loadStats();
                } else {
                    showToast('Gagal memperbarui status pesanan', 'error');
                }
                
                // Close modal
                document.getElementById('statusConfirmModal').classList.remove('show');
                
                // Reset globals
                currentOrderId = null;
                currentStatus = null;
            })
            .catch(error => {
                console.error('Error updating status:', error);
                showToast('Terjadi kesalahan saat memperbarui status', 'error');
                
                // Close modal
                document.getElementById('statusConfirmModal').classList.remove('show');
                
                // Reset globals
                currentOrderId = null;
                currentStatus = null;
            });
        }
        
        // Get status text for display
        function getStatusText(status) {
            const statusMap = {
                'menunggu-konfirmasi': 'Menunggu Konfirmasi',
                'diproses': 'Diproses',
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
            
            // Set content
            toastMessage.textContent = message;
            
            // Set icon based on type
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
            
            // Set toast class
            toast.className = 'toast';
            toast.classList.add(type, 'show');
            
            // Auto hide after 5 seconds
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

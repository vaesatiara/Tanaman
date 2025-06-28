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
        case 'get_accounts':
            try {
                $search = $_POST['search'] ?? '';
                
                $sql = "SELECT id_pelanggan, username, email, password, no_hp, tanggal_lahir FROM pelanggan WHERE 1=1";
                
                $params = [];
                $types = "";
                
                // Add search filter
                if (!empty($search)) {
                    $sql .= " AND (username LIKE ? OR email LIKE ? OR id_pelanggan LIKE ?)";
                    $searchParam = "%$search%";
                    $params[] = $searchParam;
                    $params[] = $searchParam;
                    $params[] = $searchParam;
                    $types .= "sss";
                }
                
                $sql .= " ORDER BY id_pelanggan DESC";
                
                if (!empty($params)) {
                    $stmt = $koneksi->prepare($sql);
                    $stmt->bind_param($types, ...$params);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $accounts = $result->fetch_all(MYSQLI_ASSOC);
                } else {
                    $result = $koneksi->query($sql);
                    $accounts = $result->fetch_all(MYSQLI_ASSOC);
                }
                
                echo json_encode(['success' => true, 'data' => $accounts]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
            exit;
            
        case 'get_account_detail':
            $accountId = intval($_POST['account_id'] ?? 0);
            
            if ($accountId <= 0) {
                echo json_encode(['success' => false, 'error' => 'ID akun tidak valid']);
                exit;
            }
            
            try {
                $stmt = $koneksi->prepare("SELECT * FROM pelanggan WHERE id_pelanggan = ?");
                $stmt->bind_param("i", $accountId);
                $stmt->execute();
                $result = $stmt->get_result();
                $account = $result->fetch_assoc();
                
                if ($account) {
                    // Don't send password in response for security
                    unset($account['password']);
                    echo json_encode(['success' => true, 'data' => $account]);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Akun tidak ditemukan']);
                }
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
            exit;
            
        case 'add_account':
            $username = $_POST['username'] ?? '';
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $no_hp = $_POST['no_hp'] ?? '';
            $tanggal_lahir = $_POST['tanggal_lahir'] ?? '';
            
            // Validation
            if (empty($username) || empty($email) || empty($password)) {
                echo json_encode(['success' => false, 'error' => 'Username, email, dan password wajib diisi']);
                exit;
            }
            
            try {
                // Check if username or email already exists
                $stmt = $koneksi->prepare("SELECT id_pelanggan FROM pelanggan WHERE username = ? OR email = ?");
                $stmt->bind_param("ss", $username, $email);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    echo json_encode(['success' => false, 'error' => 'Username atau email sudah terdaftar']);
                    exit;
                }
                
                // Hash password
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                
                // Insert new account
                $stmt = $koneksi->prepare("INSERT INTO pelanggan (username, email, password, no_hp, tanggal_lahir) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("sssss", $username, $email, $hashedPassword, $no_hp, $tanggal_lahir);
                
                if ($stmt->execute()) {
                    echo json_encode(['success' => true, 'message' => 'Akun berhasil ditambahkan']);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Gagal menambahkan akun']);
                }
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
            exit;
            
        case 'update_account':
            $accountId = intval($_POST['account_id'] ?? 0);
            $username = $_POST['username'] ?? '';
            $email = $_POST['email'] ?? '';
            $no_hp = $_POST['no_hp'] ?? '';
            $tanggal_lahir = $_POST['tanggal_lahir'] ?? '';
            
            if ($accountId <= 0) {
                echo json_encode(['success' => false, 'error' => 'ID akun tidak valid']);
                exit;
            }
            
            try {
                // Check if username or email already exists for other accounts
                $stmt = $koneksi->prepare("SELECT id_pelanggan FROM pelanggan WHERE (username = ? OR email = ?) AND id_pelanggan != ?");
                $stmt->bind_param("ssi", $username, $email, $accountId);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    echo json_encode(['success' => false, 'error' => 'Username atau email sudah digunakan oleh akun lain']);
                    exit;
                }
                
                // Update account
                $stmt = $koneksi->prepare("UPDATE pelanggan SET username = ?, email = ?, no_hp = ?, tanggal_lahir = ? WHERE id_pelanggan = ?");
                $stmt->bind_param("ssssi", $username, $email, $no_hp, $tanggal_lahir, $accountId);
                
                if ($stmt->execute()) {
                    echo json_encode(['success' => true, 'message' => 'Akun berhasil diperbarui']);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Gagal memperbarui akun']);
                }
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
            exit;
            
        case 'reset_password':
            $accountId = intval($_POST['account_id'] ?? 0);
            
            if ($accountId <= 0) {
                echo json_encode(['success' => false, 'error' => 'ID akun tidak valid']);
                exit;
            }
            
            try {
                // Generate new password
                $newPassword = 'newpass' . rand(1000, 9999);
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                
                // Update password
                $stmt = $koneksi->prepare("UPDATE pelanggan SET password = ? WHERE id_pelanggan = ?");
                $stmt->bind_param("si", $hashedPassword, $accountId);
                
                if ($stmt->execute()) {
                    echo json_encode([
                        'success' => true, 
                        'message' => 'Password berhasil direset',
                        'new_password' => $newPassword
                    ]);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Gagal mereset password']);
                }
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
            exit;
            
        case 'delete_account':
            $accountId = intval($_POST['account_id'] ?? 0);
            
            if ($accountId <= 0) {
                echo json_encode(['success' => false, 'error' => 'ID akun tidak valid']);
                exit;
            }
            
            try {
                // Check if account has orders
                $stmt = $koneksi->prepare("SELECT COUNT(*) as order_count FROM pesanan WHERE id_pelanggan = ?");
                $stmt->bind_param("i", $accountId);
                $stmt->execute();
                $result = $stmt->get_result();
                $orderData = $result->fetch_assoc();
                
                if ($orderData['order_count'] > 0) {
                    echo json_encode(['success' => false, 'error' => 'Tidak dapat menghapus akun yang memiliki riwayat pesanan']);
                    exit;
                }
                
                // Delete account
                $stmt = $koneksi->prepare("DELETE FROM pelanggan WHERE id_pelanggan = ?");
                $stmt->bind_param("i", $accountId);
                
                if ($stmt->execute()) {
                    echo json_encode(['success' => true, 'message' => 'Akun berhasil dihapus']);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Gagal menghapus akun']);
                }
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
            exit;
            
        case 'get_stats':
            try {
                $stats = [];
                
                // Total accounts
                $stmt = $koneksi->query("SELECT COUNT(*) as count FROM pelanggan");
                $result = $stmt->fetch_assoc();
                $stats['total'] = $result['count'];
                
                // Active accounts (accounts with orders)
                $stmt = $koneksi->query("SELECT COUNT(DISTINCT id_pelanggan) as count FROM pesanan");
                $result = $stmt->fetch_assoc();
                $stats['active'] = $result['count'];
                
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

// Get initial data for page load
try {
    $sql = "SELECT id_pelanggan, username, email, no_hp, tanggal_lahir FROM pelanggan ORDER BY id_pelanggan DESC LIMIT 50";
    $query = mysqli_query($koneksi, $sql);
    $initialAccounts = [];
    while ($row = mysqli_fetch_assoc($query)) {
        $initialAccounts[] = $row;
    }
} catch (Exception $e) {
    $initialAccounts = [];
    error_log("Error loading initial accounts: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Management Akun - The Secret Garden</title>
    
    <!-- External CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/manajemen_akun.css">
</head>
<body>
    <div class="container">
        <!-- Mobile Menu Button -->
        <button class="mobile-menu-btn" onclick="toggleSidebar()">
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
                    <a class="menu-item" href="dashboard.php">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li>
                    <a class="menu-item" href="manajemen_pesanan.php">
                        <i class="fas fa-shopping-cart"></i>
                        <span>Management Pesanan</span>
                    </a>
                </li>
                <li>
                    <a class="menu-item" href="manajemen_produk.php">
                        <i class="fas fa-box"></i>
                        <span>Management Produk</span>
                    </a>
                </li>
                
                <li>
                    <a class="menu-item active" href="manajemen_akun.php">
                        <i class="fas fa-user"></i>
                        <span>Management Akun</span>
                    </a>
                </li>
                
                <li>
                    <a class="menu-item" href="manajemen_pembayaran.php">
                        <i class="fas fa-credit-card"></i>
                        <span>Management Pembayaran</span>
                    </a>
                </li>
               
                <li>
                    <a class="menu-item" href="manajemen_saran.php">
                        <i class="fas fa-heart"></i>
                        <span>Management Saran</span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <div class="search-container">
                    <input type="text" placeholder="Cari akun pelanggan..." class="search-input" id="searchInput">
                    <button class="search-button" onclick="performSearch()"><i class="fas fa-search"></i></button>
                </div>
                <div class="user-menu">
                    <span>Admin</span>
                    <div class="notification">
                        <i class="fas fa-bell"></i>
                        <div class="notification-badge"></div>
                    </div>
                    <div class="profile-pic">
                        <img src="images/4396a60b-6455-40ed-8331-89a96395469f.jpeg" alt="Profile">
                    </div>
                </div>
            </div>

            <h1 class="dashboard-title">
                <i class="fas fa-user"></i>
                Management Akun Pelanggan
            </h1>

            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <h3><i class="fas fa-users"></i> Total Akun</h3>
                    <p class="value" id="totalAccounts">0</p>
                    <p class="info"><i class="fas fa-arrow-up"></i> Terdaftar di sistem</p>
                </div>
                <div class="stat-card">
                    <h3><i class="fas fa-user-plus"></i> Akun Aktif</h3>
                    <p class="value" id="activeAccounts">0</p>
                    <p class="info"><i class="fas fa-shopping-cart"></i> Memiliki riwayat pesanan</p>
                </div>
            </div>

            <!-- Account Table -->
            <div class="table-container">
                <div class="table-header">
                    <i class="fas fa-users"></i> Daftar Akun Pelanggan
                </div>
                <div class="table-content">
                    <table class="table" id="accountTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>No. HP</th>
                                <th>Tanggal Lahir</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="accountTableBody">
                            <tr>
                                <td colspan="6" class="text-center">
                                    <div id="loadingMessage">
                                        <i class="fas fa-spinner fa-spin"></i> Memuat data...
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Add Account Button -->
            <div class="add-admin-section">
                <button class="btn-add-admin" onclick="showAddModal()">
                    <i class="fas fa-plus"></i>
                    Tambah Akun Baru
                </button>
            </div>

            <!-- Footer -->
            <div class="footer">
                <p>&copy; 2024 The Secret Garden. All rights reserved.</p>
            </div>
        </div>
    </div>

    <!-- Modal untuk tambah/edit akun -->
    <div id="accountModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle">Tambah Akun Baru</h3>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            <div class="modal-body">
                <form id="accountForm">
                    <input type="hidden" id="accountId" name="account_id">
                    <div class="form-group">
                        <label for="username">Username:</label>
                        <input type="text" id="username" name="username" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <div class="form-group" id="passwordGroup">
                        <label for="password">Password:</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    <div class="form-group" id="confirmPasswordGroup">
                        <label for="confirm_password">Konfirmasi Password:</label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                    </div>
                    <div class="form-group">
                        <label for="no_hp">No. HP:</label>
                        <input type="text" id="no_hp" name="no_hp">
                    </div>
                    <div class="form-group">
                        <label for="tanggal_lahir">Tanggal Lahir:</label>
                        <input type="date" id="tanggal_lahir" name="tanggal_lahir">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal()">Batal</button>
                <button type="button" class="btn btn-primary" onclick="saveAccount()">Simpan</button>
            </div>
        </div>
    </div>

    <!-- Modal untuk detail akun -->
    <div id="detailModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Detail Akun</h3>
                <span class="close" onclick="closeDetailModal()">&times;</span>
            </div>
            <div class="modal-body">
                <div id="accountDetailContent">
                    <!-- Content will be populated by JavaScript -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeDetailModal()">Tutup</button>
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div id="toast" class="toast" style="position: fixed; top: 20px; right: 20px; background: white; padding: 15px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); display: none; z-index: 1001;">
        <div class="toast-content" style="display: flex; align-items: center; gap: 10px;">
            <div class="toast-icon">
                <i class="toast-icon-element"></i>
            </div>
            <div class="toast-message"></div>
            <button class="toast-close" onclick="closeToast()" style="background: none; border: none; cursor: pointer; margin-left: 10px;">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>

    <script>
        // Global variables
        let currentAccountId = null;
        let isEditMode = false;
        let allAccounts = [];
        
        // DOM Ready
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM loaded, initializing...');
            
            // Load accounts and stats
            loadAccounts();
            loadStats();
            
            // Initialize search
            initializeSearch();
        });
        
        // Initialize search
        function initializeSearch() {
            const searchInput = document.getElementById('searchInput');
            if (searchInput) {
                searchInput.addEventListener('keyup', function(e) {
                    if (e.key === 'Enter') {
                        performSearch();
                    }
                });
                
                // Auto search with debounce
                let searchTimeout;
                searchInput.addEventListener('input', function() {
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(() => {
                        performSearch();
                    }, 500);
                });
            }
        }
        
        // Perform search
        function performSearch() {
            const searchTerm = document.getElementById('searchInput').value;
            loadAccounts(searchTerm);
        }
        
        // Load accounts from server
        function loadAccounts(searchTerm = '') {
            console.log('Loading accounts...');
            
            const formData = new FormData();
            formData.append('action', 'get_accounts');
            if (searchTerm) formData.append('search', searchTerm);
            
            fetch('manajemen_akun.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(result => {
                console.log('Accounts response:', result);
                
                if (!result.success) {
                    console.error('Server error:', result.error);
                    showToast('Error server: ' + result.error, 'error');
                    return;
                }
                
                allAccounts = result.data || [];
                renderAccountsTable(allAccounts);
            })
            .catch(error => {
                console.error('Error loading accounts:', error);
                showToast('Gagal memuat data akun: ' + error.message, 'error');
            });
        }
        
        // Render accounts table
        function renderAccountsTable(accounts) {
            const tableBody = document.getElementById('accountTableBody');
            if (!tableBody) return;
            
            tableBody.innerHTML = '';
            
            if (accounts.length === 0) {
                const row = document.createElement('tr');
                row.innerHTML = `<td colspan="6" class="text-center">Tidak ada akun ditemukan</td>`;
                tableBody.appendChild(row);
                return;
            }
            
            accounts.forEach(account => {
                const row = document.createElement('tr');
                
                // Format tanggal lahir
                let formattedDate = '-';
                if (account.tanggal_lahir && account.tanggal_lahir !== '0000-00-00') {
                    try {
                        const date = new Date(account.tanggal_lahir);
                        formattedDate = date.toLocaleDateString('id-ID');
                    } catch (e) {
                        formattedDate = account.tanggal_lahir;
                    }
                }
                
                row.innerHTML = `
                    <td><strong>${account.id_pelanggan || '-'}</strong></td>
                    <td>${account.username || '-'}</td>
                    <td>${account.email || '-'}</td>
                    <td>${account.no_hp || '-'}</td>
                    <td>${formattedDate}</td>
                    <td class="actions">
                        <button class="btn-icon" title="Lihat Detail" onclick="viewAccount(${account.id_pelanggan})">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn-icon" title="Edit Akun" onclick="editAccount(${account.id_pelanggan})">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn-icon" title="Reset Password" onclick="resetPassword(${account.id_pelanggan}, '${account.username}')">
                            <i class="fas fa-key"></i>
                        </button>
                        <button class="btn-delete" title="Hapus Akun" onclick="deleteAccount(${account.id_pelanggan}, '${account.username}')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                `;
                
                tableBody.appendChild(row);
            });
        }
        
        // Load stats from server
        function loadStats() {
            fetch('manajemen_akun.php', {
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
                    const totalElement = document.getElementById('totalAccounts');
                    const activeElement = document.getElementById('activeAccounts');
                    
                    if (totalElement) totalElement.textContent = stats.total || 0;
                    if (activeElement) activeElement.textContent = stats.active || 0;
                } else {
                    console.error('Error loading stats:', result.error);
                }
            })
            .catch(error => {
                console.error('Error loading stats:', error);
            });
        }
        
        // Toggle sidebar untuk mobile
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('show');
        }
        
        // Fungsi untuk menampilkan modal tambah akun
        function showAddModal() {
            isEditMode = false;
            currentAccountId = null;
            document.getElementById('modalTitle').textContent = 'Tambah Akun Baru';
            document.getElementById('accountForm').reset();
            document.getElementById('accountId').value = '';
            
            // Show password fields for new account
            document.getElementById('passwordGroup').style.display = 'block';
            document.getElementById('confirmPasswordGroup').style.display = 'block';
            document.getElementById('password').required = true;
            document.getElementById('confirm_password').required = true;
            
            document.getElementById('accountModal').style.display = 'block';
        }
        
        // Fungsi untuk menutup modal
        function closeModal() {
            document.getElementById('accountModal').style.display = 'none';
        }
        
        // Fungsi untuk menutup modal detail
        function closeDetailModal() {
            document.getElementById('detailModal').style.display = 'none';
        }
        
        // Fungsi untuk melihat detail akun
        function viewAccount(id) {
            fetch('manajemen_akun.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=get_account_detail&account_id=${id}`
            })
            .then(response => response.json())
            .then(result => {
                if (!result.success) {
                    showToast(result.error || 'Akun tidak ditemukan', 'error');
                    return;
                }
                
                const account = result.data;
                
                // Format tanggal lahir
                let formattedDate = '-';
                if (account.tanggal_lahir && account.tanggal_lahir !== '0000-00-00') {
                    try {
                        const date = new Date(account.tanggal_lahir);
                        formattedDate = date.toLocaleDateString('id-ID', {
                            day: '2-digit',
                            month: 'long',
                            year: 'numeric'
                        });
                    } catch (e) {
                        formattedDate = account.tanggal_lahir;
                    }
                }
                
                const modalContent = document.getElementById('accountDetailContent');
                modalContent.innerHTML = `
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div>
                            <div style="margin-bottom: 15px;">
                                <label style="font-weight: 600; color: #6c757d; font-size: 0.875rem;">ID Pelanggan:</label>
                                <div style="color: #333; font-weight: 500;">${account.id_pelanggan}</div>
                            </div>
                            <div style="margin-bottom: 15px;">
                                <label style="font-weight: 600; color: #6c757d; font-size: 0.875rem;">Username:</label>
                                <div style="color: #333; font-weight: 500;">${account.username || '-'}</div>
                            </div>
                            <div style="margin-bottom: 15px;">
                                <label style="font-weight: 600; color: #6c757d; font-size: 0.875rem;">Email:</label>
                                <div style="color: #333; font-weight: 500;">${account.email || '-'}</div>
                            </div>
                        </div>
                        <div>
                            <div style="margin-bottom: 15px;">
                                <label style="font-weight: 600; color: #6c757d; font-size: 0.875rem;">No. HP:</label>
                                <div style="color: #333; font-weight: 500;">${account.no_hp || '-'}</div>
                            </div>
                            <div style="margin-bottom: 15px;">
                                <label style="font-weight: 600; color: #6c757d; font-size: 0.875rem;">Tanggal Lahir:</label>
                                <div style="color: #333; font-weight: 500;">${formattedDate}</div>
                            </div>
                        </div>
                    </div>
                `;
                
                document.getElementById('detailModal').style.display = 'block';
            })
            .catch(error => {
                console.error('Error loading account detail:', error);
                showToast('Gagal memuat detail akun', 'error');
            });
        }
        
        // Fungsi untuk edit akun
        function editAccount(id) {
            fetch('manajemen_akun.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=get_account_detail&account_id=${id}`
            })
            .then(response => response.json())
            .then(result => {
                if (!result.success) {
                    showToast(result.error || 'Akun tidak ditemukan', 'error');
                    return;
                }
                
                const account = result.data;
                isEditMode = true;
                currentAccountId = id;
                
                document.getElementById('modalTitle').textContent = 'Edit Akun';
                document.getElementById('accountId').value = id;
                document.getElementById('username').value = account.username || '';
                document.getElementById('email').value = account.email || '';
                document.getElementById('no_hp').value = account.no_hp || '';
                document.getElementById('tanggal_lahir').value = account.tanggal_lahir || '';
                
                // Hide password fields for edit
                document.getElementById('passwordGroup').style.display = 'none';
                document.getElementById('confirmPasswordGroup').style.display = 'none';
                document.getElementById('password').required = false;
                document.getElementById('confirm_password').required = false;
                
                document.getElementById('accountModal').style.display = 'block';
            })
            .catch(error => {
                console.error('Error loading account for edit:', error);
                showToast('Gagal memuat data akun', 'error');
            });
        }
        
        // Fungsi untuk reset password
        function resetPassword(id, username) {
            if (!confirm(`Apakah Anda yakin ingin mereset password untuk akun "${username}"?`)) {
                return;
            }
            
            fetch('manajemen_akun.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=reset_password&account_id=${id}`
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    alert(`Password berhasil direset!\nPassword baru: ${result.new_password}\n\nPassword baru telah dikirim ke email pelanggan.`);
                    showToast('Password berhasil direset', 'success');
                } else {
                    showToast(result.error || 'Gagal mereset password', 'error');
                }
            })
            .catch(error => {
                console.error('Error resetting password:', error);
                showToast('Terjadi kesalahan saat mereset password', 'error');
            });
        }
        
        // Fungsi untuk hapus akun
        function deleteAccount(id, username) {
            if (!confirm(`Apakah Anda yakin ingin menghapus akun "${username}"?\n\nTindakan ini tidak dapat dibatalkan!`)) {
                return;
            }
            
            fetch('manajemen_akun.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=delete_account&account_id=${id}`
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    showToast(`Akun "${username}" berhasil dihapus`, 'success');
                    loadAccounts();
                    loadStats();
                } else {
                    showToast(result.error || 'Gagal menghapus akun', 'error');
                }
            })
            .catch(error => {
                console.error('Error deleting account:', error);
                showToast('Terjadi kesalahan saat menghapus akun', 'error');
            });
        }
        
        // Fungsi untuk menyimpan akun
        function saveAccount() {
            const form = document.getElementById('accountForm');
            const formData = new FormData(form);
            
            // Validation
            const username = document.getElementById('username').value;
            const email = document.getElementById('email').value;
            
            if (!username || !email) {
                showToast('Username dan email wajib diisi', 'error');
                return;
            }
            
            if (!isEditMode) {
                const password = document.getElementById('password').value;
                const confirmPassword = document.getElementById('confirm_password').value;
                
                if (!password) {
                    showToast('Password wajib diisi', 'error');
                    return;
                }
                
                if (password !== confirmPassword) {
                    showToast('Password dan konfirmasi password tidak sama', 'error');
                    return;
                }
                
                formData.append('action', 'add_account');
            } else {
                formData.append('action', 'update_account');
                formData.append('account_id', currentAccountId);
            }
            
            fetch('manajemen_akun.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    showToast(result.message, 'success');
                    closeModal();
                    loadAccounts();
                    loadStats();
                } else {
                    showToast(result.error || 'Gagal menyimpan akun', 'error');
                }
            })
            .catch(error => {
                console.error('Error saving account:', error);
                showToast('Terjadi kesalahan saat menyimpan akun', 'error');
            });
        }
        
        // Show toast notification
        function showToast(message, type = 'info') {
            const toast = document.getElementById('toast');
            if (!toast) return;
            
            const toastIcon = toast.querySelector('.toast-icon-element');
            const toastMessage = toast.querySelector('.toast-message');
            
            toastMessage.textContent = message;
            
            let iconClass, iconColor;
            switch (type) {
                case 'success':
                    iconClass = 'fas fa-check-circle';
                    iconColor = '#28a745';
                    break;
                case 'error':
                    iconClass = 'fas fa-exclamation-circle';
                    iconColor = '#dc3545';
                    break;
                case 'warning':
                    iconClass = 'fas fa-exclamation-triangle';
                    iconColor = '#ffc107';
                    break;
                default:
                    iconClass = 'fas fa-info-circle';
                    iconColor = '#17a2b8';
            }
            
            toastIcon.className = iconClass;
            toastIcon.style.color = iconColor;
            toast.style.display = 'block';
            
            setTimeout(() => {
                closeToast();
            }, 5000);
        }
        
        // Close toast
        function closeToast() {
            const toast = document.getElementById('toast');
            if (toast) {
                toast.style.display = 'none';
            }
        }
        
        // Tutup modal jika klik di luar modal
        window.onclick = function(event) {
            const accountModal = document.getElementById('accountModal');
            const detailModal = document.getElementById('detailModal');
            
            if (event.target == accountModal) {
                closeModal();
            }
            if (event.target == detailModal) {
                closeDetailModal();
            }
        }
    </script>
</body>
</html>
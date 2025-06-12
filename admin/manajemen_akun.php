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
                <img src="images/logo.png" alt="The Secret Garden">
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
                    <a class="menu-item" href="manajemen
                    _pembayaran.php">
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
                    <button class="search-button"><i class="fas fa-search"></i></button>
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
                    <p class="value">156</p>
                    <p class="info"><i class="fas fa-arrow-up"></i> +5% dari bulan lalu</p>
                </div>
                <div class="stat-card">
                    <h3><i class="fas fa-user-plus"></i> Akun Baru</h3>
                    <p class="value">12</p>
                    <p class="info"><i class="fas fa-arrow-up"></i> +8% dari bulan lalu</p>
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
                                <th>ID Pelanggan</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Password</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>001</strong></td>
                                <td>nanjo87</td>
                                <td>nanjo87@gmail.com</td>
                                <td>••••••••</td>
                                <td class="actions">
                                    <button class="btn-icon" title="Lihat Detail" onclick="viewAccount(1)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn-icon" title="Edit Akun" onclick="editAccount(1)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn-icon" title="Reset Password" onclick="resetPassword(1)">
                                        <i class="fas fa-key"></i>
                                    </button>
                                    <button class="btn-delete" title="Hapus Akun" onclick="deleteAccount(1, 'nanjo87')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>002</strong></td>
                                <td>cahyanda23</td>
                                <td>yanda23@gmail.com</td>
                                <td>••••••••</td>
                                <td class="actions">
                                    <button class="btn-icon" title="Lihat Detail" onclick="viewAccount(2)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn-icon" title="Edit Akun" onclick="editAccount(2)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn-icon" title="Reset Password" onclick="resetPassword(2)">
                                        <i class="fas fa-key"></i>
                                    </button>
                                    <button class="btn-delete" title="Hapus Akun" onclick="deleteAccount(2, 'cahyanda23')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>003</strong></td>
                                <td>haerin8</td>
                                <td>haerin8@gmail.com</td>
                                <td>••••••••</td>
                                <td class="actions">
                                    <button class="btn-icon" title="Lihat Detail" onclick="viewAccount(3)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn-icon" title="Edit Akun" onclick="editAccount(3)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn-icon" title="Reset Password" onclick="resetPassword(3)">
                                        <i class="fas fa-key"></i>
                                    </button>
                                    <button class="btn-delete" title="Hapus Akun" onclick="deleteAccount(3, 'haerin8')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>004</strong></td>
                                <td>kassacc</td>
                                <td>kassacc@gmail.com</td>
                                <td>••••••••</td>
                                <td class="actions">
                                    <button class="btn-icon" title="Lihat Detail" onclick="viewAccount(4)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn-icon" title="Edit Akun" onclick="editAccount(4)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn-icon" title="Reset Password" onclick="resetPassword(4)">
                                        <i class="fas fa-key"></i>
                                    </button>
                                    <button class="btn-delete" title="Hapus Akun" onclick="deleteAccount(4, 'kassacc')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>005</strong></td>
                                <td>kairi5</td>
                                <td>kairi5@gmail.com</td>
                                <td>••••••••</td>
                                <td class="actions">
                                    <button class="btn-icon" title="Lihat Detail" onclick="viewAccount(5)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn-icon" title="Edit Akun" onclick="editAccount(5)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn-icon" title="Reset Password" onclick="resetPassword(5)">
                                        <i class="fas fa-key"></i>
                                    </button>
                                    <button class="btn-delete" title="Hapus Akun" onclick="deleteAccount(5, 'kairi5')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>006</strong></td>
                                <td>reza_garden</td>
                                <td>reza@gmail.com</td>
                                <td>••••••••</td>
                                <td class="actions">
                                    <button class="btn-icon" title="Lihat Detail" onclick="viewAccount(6)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn-icon" title="Edit Akun" onclick="editAccount(6)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn-icon" title="Reset Password" onclick="resetPassword(6)">
                                        <i class="fas fa-key"></i>
                                    </button>
                                    <button class="btn-delete" title="Hapus Akun" onclick="deleteAccount(6, 'reza_garden')">
                                        <i class="fas fa-trash"></i>
                                    </button>
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
                    <div class="form-group">
                        <label for="username">Username:</label>
                        <input type="text" id="username" name="username" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password:</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Konfirmasi Password:</label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal()">Batal</button>
                <button type="button" class="btn btn-primary" onclick="saveAccount()">Simpan</button>
            </div>
        </div>
    </div>

    <script>
        // Data akun untuk simulasi
        let accounts = [
            {id: 1, username: 'nanjo87', email: 'nanjo87@gmail.com', password: 'password123'},
            {id: 2, username: 'cahyanda23', email: 'yanda23@gmail.com', password: 'mypass456'},
            {id: 3, username: 'haerin8', email: 'haerin8@gmail.com', password: 'secret789'},
            {id: 4, username: 'kassacc', email: 'kassacc@gmail.com', password: 'pass1234'},
            {id: 5, username: 'kairi5', email: 'kairi5@gmail.com', password: 'kairi2023'},
            {id: 6, username: 'reza_garden', email: 'reza@gmail.com', password: 'garden123'}
        ];

        // Toggle sidebar untuk mobile
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('show');
        }

        // Inisialisasi fitur
        document.addEventListener('DOMContentLoaded', function() {
            initializeSearch();
            initializeResponsiveMenu();
            updateStats();
        });
        
        // Fungsi pencarian
        function initializeSearch() {
            const searchInput = document.getElementById('searchInput');
            
            searchInput.addEventListener('input', function(e) {
                const searchTerm = e.target.value.toLowerCase();
                filterTable(searchTerm);
            });
            
            document.querySelector('.search-button').addEventListener('click', function() {
                const searchTerm = searchInput.value.toLowerCase();
                filterTable(searchTerm);
            });
        }

        // Filter tabel berdasarkan pencarian
        function filterTable(searchTerm) {
            const table = document.getElementById('accountTable');
            const rows = table.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        // Update statistik
        function updateStats() {
            const totalAccounts = accounts.length;
            const newAccounts = 12; // Static value for "Akun Baru"
            
            document.querySelector('.stats-grid .stat-card:nth-child(1) .value').textContent = totalAccounts;
            document.querySelector('.stats-grid .stat-card:nth-child(2) .value').textContent = newAccounts;
        }
        
        // Fungsi untuk menampilkan modal tambah akun
        function showAddModal() {
            document.getElementById('modalTitle').textContent = 'Tambah Akun Baru';
            document.getElementById('accountForm').reset();
            document.getElementById('accountModal').style.display = 'block';
        }
        
        // Fungsi untuk menutup modal
        function closeModal() {
            document.getElementById('accountModal').style.display = 'none';
        }
        
        // Fungsi untuk melihat detail akun
        function viewAccount(id) {
            const account = accounts.find(acc => acc.id === id);
            if (account) {
                alert(`Detail Akun:\n\nID: ${account.id}\nUsername: ${account.username}\nEmail: ${account.email}`);
            }
        }
        
        // Fungsi untuk edit akun
        function editAccount(id) {
            const account = accounts.find(acc => acc.id === id);
            if (account) {
                document.getElementById('modalTitle').textContent = 'Edit Akun';
                document.getElementById('username').value = account.username;
                document.getElementById('email').value = account.email;
                document.getElementById('password').value = account.password;
                document.getElementById('confirm_password').value = account.password;
                document.getElementById('accountModal').style.display = 'block';
            }
        }
        
        // Fungsi untuk reset password
        function resetPassword(id) {
            const account = accounts.find(acc => acc.id === id);
            if (account && confirm(`Apakah Anda yakin ingin mereset password untuk akun "${account.username}"?`)) {
                const newPassword = 'newpass' + Math.floor(Math.random() * 1000);
                account.password = newPassword;
                alert(`Password berhasil direset!\nPassword baru: ${newPassword}\n\nPassword baru telah dikirim ke email ${account.email}`);
            }
        }
        
        // Fungsi untuk hapus akun
        function deleteAccount(id, username) {
            if (confirm(`Apakah Anda yakin ingin menghapus akun "${username}"?\n\nTindakan ini tidak dapat dibatalkan!`)) {
                // Hapus dari array
                accounts = accounts.filter(acc => acc.id !== id);
                
                // Hapus dari tabel
                const table = document.getElementById('accountTable');
                const rows = table.querySelectorAll('tbody tr');
                rows.forEach(row => {
                    const idCell = row.querySelector('td:first-child strong');
                    if (idCell && idCell.textContent.padStart(3, '0') === id.toString().padStart(3, '0')) {
                        row.remove();
                    }
                });
                
                // Update statistik
                updateStats();
                
                alert(`Akun "${username}" berhasil dihapus!`);
            }
        }
        
        // Fungsi untuk menyimpan akun
        function saveAccount() {
            const form = document.getElementById('accountForm');
            
            // Validasi password
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password !== confirmPassword) {
                alert('Password dan konfirmasi password tidak sama!');
                return;
            }
            
            // Validasi email
            const email = document.getElementById('email').value;
            const emailExists = accounts.some(acc => acc.email === email);
            if (emailExists) {
                alert('Email sudah terdaftar!');
                return;
            }
            
            // Validasi username
            const username = document.getElementById('username').value;
            const usernameExists = accounts.some(acc => acc.username === username);
            if (usernameExists) {
                alert('Username sudah digunakan!');
                return;
            }
            
            alert('Akun berhasil disimpan!');
            closeModal();
        }
        
        // Fungsi menu responsif
        function initializeResponsiveMenu() {
            // Close sidebar when clicking outside on mobile
            document.addEventListener('click', function(event) {
                const sidebar = document.getElementById('sidebar');
                const menuBtn = document.querySelector('.mobile-menu-btn');
                
                if (window.innerWidth <= 768 && 
                    !sidebar.contains(event.target) && 
                    !menuBtn.contains(event.target) &&
                    sidebar.classList.contains('show')) {
                    sidebar.classList.remove('show');
                }
            });
        }
        
        // Tutup modal jika klik di luar modal
        window.onclick = function(event) {
            const modal = document.getElementById('accountModal');
            if (event.target == modal) {
                closeModal();
            }
        }
    </script>
</body>
</html>

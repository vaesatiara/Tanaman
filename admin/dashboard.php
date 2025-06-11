<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - The Secret Garden</title>
    
    <!-- External CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="dashboard.css">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="logo">
                <img src="images/logo.png" alt="The Secret Garden">
            </div>
            <div class="menu-label">MENU</div>
            <ul class="menu-items">
                <li>
                    <a class="menu-item" onclick="location.href='dashboard.html'">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li>
                    <a class="menu-item" onclick="location.href='manajemen_pesanan.html'">
                        <i class="fas fa-shopping-cart"></i>
                        <span>Management Pesanan</span>
                    </a>
                </li>
                <li>
                    <a class="menu-item" onclick="location.href='manajemen_produk.html'">
                        <i class="fas fa-box"></i>
                        <span>Management Produk</span>
                    </a>
                </li>
                <li>
                    <a class="menu-item" onclick="location.href='manajemen_pengiriman.html'">
                        <i class="fas fa-truck"></i>
                        <span>Management Pengiriman</span>
                    </a>
                </li>
                <li>
                    <a class="menu-item active" onclick="location.href='manajemen_akun.html'">
                        <i class="fas fa-user"></i>
                        <span>Management Akun</span>
                    </a>
                </li>
                <li>
                    <a class="menu-item" onclick="location.href='manajemen_admin.html'">
                        <i class="fas fa-user-shield"></i>
                        <span>Management Admin</span>
                    </a>
                </li>
                <li>
                    <a class="menu-item" onclick="location.href='manajemen_pembayaran.html'">
                        <i class="fas fa-credit-card"></i>
                        <span>Management Pembayaran</span>
                    </a>
                </li>
                <li>
                    <a class="menu-item" onclick="location.href='manajemen_diskon.html'">
                        <i class="fas fa-percent"></i>
                        <span>Management Diskon</span>
                    </a>
                </li>
                <li>
                    <a class="menu-item" onclick="location.href='manajemen_saran.html'">
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
                <div class="search-container">
                    <input type="text" placeholder="Cari pesanan, customer, produk..." class="search-input" id="searchInput">
                    <i class="fas fa-search search-icon"></i>
                </div>
                <div class="user-menu">
                    <span>Admin</span>
                    <div class="profile-pic">
                        <img src="images/4396a60b-6455-40ed-8331-89a96395469f.jpeg" alt="Admin Profile">
                    </div>
                </div>
            </div>

            <h1 class="dashboard-title">
                <i class="fas fa-chart-line"></i>
                Dashboard Overview
            </h1>

            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <h3><i class="fas fa-shopping-cart"></i> Total Pesanan</h3>
                    <p class="value">1,250</p>
                    <p class="info"><i class="fas fa-arrow-up"></i> +12% dari bulan lalu</p>
                </div>
                <div class="stat-card">
                    <h3><i class="fas fa-users"></i> Total Customer</h3>
                    <p class="value">890</p>
                    <p class="info"><i class="fas fa-arrow-up"></i> +8% dari bulan lalu</p>
                </div>
                <div class="stat-card">
                    <h3><i class="fas fa-box"></i> Total Produk</h3>
                    <p class="value">156</p>
                    <p class="info"><i class="fas fa-arrow-up"></i> +5% dari bulan lalu</p>
                </div>
                <div class="stat-card">
                    <h3><i class="fas fa-money-bill-wave"></i> Total Revenue</h3>
                    <p class="value">Rp 45,231,890</p>
                    <p class="info"><i class="fas fa-arrow-up"></i> +15% dari bulan lalu</p>
                </div>
            </div>

       
         


            <!-- Footer -->
            <div class="footer">
                <p>&copy; 2023 The Secret Garden. All rights reserved.</p>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="assets/js/dashboard.js"></script>
</body>
</html>

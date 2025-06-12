<?php
// Start session for potential user authentication
//session_start();

// Database connection (if needed)
// $conn = mysqli_connect("localhost", "username", "password", "database");

// Function to format currency
function formatCurrency($amount) {
    return "Rp " . number_format($amount, 0, ',', '.');
}

// Function to format numbers
function formatNumber($number) {
    return number_format($number, 0, ',', '.');
}

// Get current date information
$currentMonth = date('F');
$currentYear = date('Y');
$currentDate = date('d M Y');

// Sample data for charts and tables (in a real app, this would come from database)
$salesData = [12, 19, 15, 25, 22, 30];
$salesLabels = ["Jan", "Feb", "Mar", "Apr", "May", "Jun"];

// Updated category data - removed "Pot & Aksesoris"
$categoryData = [70, 30];
$categoryLabels = ["Tanaman Hias Daun", "Tanaman Hias Bunga"];

$recentOrders = [
    ["id" => "#ORD-001", "customer" => "Budi Santoso", "product" => "Monstera Deliciosa", "total" => "Rp 250,000", "status" => "Selesai", "date" => "15 Nov 2024"],
    ["id" => "#ORD-002", "customer" => "Sari Dewi", "product" => "Fiddle Leaf Fig", "total" => "Rp 180,000", "status" => "Proses", "date" => "14 Nov 2024"],
    ["id" => "#ORD-003", "customer" => "Ahmad Rahman", "product" => "Snake Plant", "total" => "Rp 120,000", "status" => "Dikirim", "date" => "13 Nov 2024"],
    ["id" => "#ORD-004", "customer" => "Maya Putri", "product" => "Peace Lily", "total" => "Rp 95,000", "status" => "Pending", "date" => "12 Nov 2024"],
    ["id" => "#ORD-005", "customer" => "Rizki Pratama", "product" => "Rubber Plant", "total" => "Rp 150,000", "status" => "Selesai", "date" => "11 Nov 2024"]
];

$topProducts = [
    ["rank" => "#1", "product" => "Monstera Deliciosa", "category" => "Tanaman Hias Daun", "sold" => "85 unit", "revenue" => "Rp 21,250,000"],
    ["rank" => "#2", "product" => "Fiddle Leaf Fig", "category" => "Tanaman Hias Daun", "sold" => "72 unit", "revenue" => "Rp 12,960,000"],
    ["rank" => "#3", "product" => "Snake Plant", "category" => "Tanaman Hias Daun", "sold" => "68 unit", "revenue" => "Rp 8,160,000"],
    ["rank" => "#4", "product" => "Peace Lily", "category" => "Tanaman Hias Bunga", "sold" => "54 unit", "revenue" => "Rp 5,130,000"],
    ["rank" => "#5", "product" => "Rubber Plant", "category" => "Tanaman Hias Daun", "sold" => "48 unit", "revenue" => "Rp 7,200,000"]
];

$stats = [
    ["label" => "Total Pesanan", "value" => "1,250", "growth" => "+12%", "icon" => "fa-shopping-cart"],
    ["label" => "Total Customer", "value" => "890", "growth" => "+8%", "icon" => "fa-users"],
    ["label" => "Total Produk", "value" => "156", "growth" => "+5%", "icon" => "fa-box"],
    ["label" => "Total Revenue", "value" => "Rp 45,231,890", "growth" => "+15%", "icon" => "fa-money-bill-wave"]
];

// Check if current month has orders
$currentMonthNumber = date('n');
$hasOrdersThisMonth = true; // In real app, query database for current month orders

// Sample logic to simulate no orders (you can modify this)
if ($currentMonthNumber == 2) { // February as example of no orders
    $hasOrdersThisMonth = false;
}
?>

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
    
    <!-- html2canvas and jsPDF for PDF generation -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
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
                    <a class="menu-item active" href="dashboard.php">
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
                    <a class="menu-item" href="manajemen_pengiriman.php">
                        <i class="fas fa-truck"></i>
                        <span>Management Pengiriman</span>
                    </a>
                </li>
                <li>
                    <a class="menu-item" href="manajemen_akun.php">
                        <i class="fas fa-user"></i>
                        <span>Management Akun</span>
                    </a>
                </li>
                <li>
                    <a class="menu-item" href="manajemen_admin.php">
                        <i class="fas fa-user-shield"></i>
                        <span>Management Admin</span>
                    </a>
                </li>
                <li>
                    <a class="menu-item" href="manajemen_pembayaran.php">
                        <i class="fas fa-credit-card"></i>
                        <span>Management Pembayaran</span>
                    </a>
                </li>
                <li>
                    <a class="menu-item" href="manajemen_diskon.php">
                        <i class="fas fa-percent"></i>
                        <span>Management Diskon</span>
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

            <!-- Report Button Section -->
            <div class="report-section">
                <button class="btn-report" id="generateReportBtn">
                    <i class="fas fa-file-pdf"></i>
                    <span>Generate Laporan Bulanan</span>
                    <div class="btn-loader">
                        <div class="spinner"></div>
                    </div>
                </button>
                <div class="report-info">
                    <p>Unduh laporan lengkap untuk bulan ini dalam format PDF</p>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="stats-grid" id="statsGrid">
                <?php foreach ($stats as $stat): ?>
                <div class="stat-card">
                    <h3><i class="fas <?php echo $stat['icon']; ?>"></i> <?php echo $stat['label']; ?></h3>
                    <p class="value"><?php echo $stat['value']; ?></p>
                    <p class="info"><i class="fas fa-arrow-up"></i> <?php echo $stat['growth']; ?> dari bulan lalu</p>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Report Preview Modal -->
            <div id="reportPreviewModal" class="report-modal" style="display: none;">
                <div class="report-modal-content">
                    <div class="report-modal-header">
                        <h2><i class="fas fa-chart-bar"></i> Preview Laporan Bulanan - <?php echo $currentMonth . ' ' . $currentYear; ?></h2>
                        <button class="close-modal" onclick="closeReportPreview()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    
                    <div class="report-modal-body" id="reportContent">
                        <!-- Report content will be generated here -->
                    </div>
                    
                    <div class="report-modal-footer">
                        <button class="btn-print" onclick="printReport()">
                            <i class="fas fa-print"></i>
                            <span>Cetak Laporan</span>
                        </button>
                        <button class="btn-pdf" onclick="generatePDFFromPreview()">
                            <i class="fas fa-file-pdf"></i>
                            <span>Download PDF</span>
                        </button>
                        <button class="btn-cancel" onclick="closeReportPreview()">
                            <i class="fas fa-times"></i>
                            <span>Tutup</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="content-grid">
                <!-- Sales Chart -->
                <div class="chart-container">
                    <div class="chart-title">
                        <i class="fas fa-chart-line"></i>
                        Penjualan Bulanan
                    </div>
                    <div class="chart-canvas">
                        <canvas id="salesChart"></canvas>
                    </div>
                </div>
                
                <!-- Category Chart -->
                <div class="chart-container">
                    <div class="chart-title">
                        <i class="fas fa-chart-pie"></i>
                        Distribusi Kategori
                    </div>
                    <div class="chart-canvas">
                        <canvas id="categoryChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Recent Orders Table -->
            <div class="table-container" id="recentOrdersTable">
                <div class="table-header">
                    <i class="fas fa-shopping-cart"></i>
                    Pesanan Terbaru
                </div>
                <div class="table-content">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID Pesanan</th>
                                <th>Customer</th>
                                <th>Produk</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Tanggal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentOrders as $order): ?>
                            <tr>
                                <td><strong><?php echo $order['id']; ?></strong></td>
                                <td><?php echo $order['customer']; ?></td>
                                <td><?php echo $order['product']; ?></td>
                                <td><?php echo $order['total']; ?></td>
                                <td>
                                    <span class="status <?php echo strtolower($order['status']); ?>">
                                        <?php echo $order['status']; ?>
                                    </span>
                                </td>
                                <td><?php echo $order['date']; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Top Products Table -->
            <div class="table-container" id="topProductsTable">
                <div class="table-header">
                    <i class="fas fa-trophy"></i>
                    Produk Terlaris
                </div>
                <div class="table-content">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Rank</th>
                                <th>Produk</th>
                                <th>Kategori</th>
                                <th>Terjual</th>
                                <th>Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($topProducts as $product): ?>
                            <tr>
                                <td><strong><?php echo $product['rank']; ?></strong></td>
                                <td><?php echo $product['product']; ?></td>
                                <td><?php echo $product['category']; ?></td>
                                <td><?php echo $product['sold']; ?></td>
                                <td><?php echo $product['revenue']; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Footer -->
            <div class="footer">
                <p>&copy; <?php echo date('Y'); ?> The Secret Garden. All rights reserved.</p>
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

    <!-- Hidden canvas elements for PDF generation -->
    <div id="chartImages" style="display: none;"></div>

    <!-- JavaScript for charts and interactivity -->
    <script>
        // Initialize charts when DOM is loaded
        document.addEventListener("DOMContentLoaded", () => {
            // Initialize Charts
            initializeSalesChart();
            initializeCategoryChart();

            // Initialize other features
            initializeSearch();
            initializeNotifications();
            initializeResponsiveMenu();
            
            // Set up report generation button
            document.getElementById('generateReportBtn').addEventListener('click', generateMonthlyReport);
        });

        // Sales Chart
        function initializeSalesChart() {
            const salesCtx = document.getElementById("salesChart")?.getContext("2d");
            if (!salesCtx) return;

            window.salesChart = new Chart(salesCtx, {
                type: "line",
                data: {
                    labels: <?php echo json_encode($salesLabels); ?>,
                    datasets: [
                        {
                            label: "Penjualan (Juta Rupiah)",
                            data: <?php echo json_encode($salesData); ?>,
                            borderColor: "#8ed7a9",
                            backgroundColor: "rgba(142, 215, 169, 0.1)",
                            tension: 0.4,
                            fill: true,
                            pointBackgroundColor: "#8ed7a9",
                            pointBorderColor: "#ffffff",
                            pointBorderWidth: 2,
                            pointRadius: 6,
                            pointHoverRadius: 8,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false,
                        },
                        tooltip: {
                            backgroundColor: "rgba(0, 0, 0, 0.8)",
                            titleColor: "#ffffff",
                            bodyColor: "#ffffff",
                            borderColor: "#8ed7a9",
                            borderWidth: 1,
                            cornerRadius: 8,
                            displayColors: false,
                            callbacks: {
                                label: (context) => "Penjualan: Rp " + context.parsed.y + " Juta",
                            },
                        },
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: "rgba(0,0,0,0.1)",
                                drawBorder: false,
                            },
                            ticks: {
                                color: "#4a5568",
                                callback: (value) => "Rp " + value + "M",
                            },
                        },
                        x: {
                            grid: {
                                display: false,
                            },
                            ticks: {
                                color: "#4a5568",
                            },
                        },
                    },
                    interaction: {
                        intersect: false,
                        mode: "index",
                    },
                },
            });
        }

        // Category Chart - Updated to only show 2 categories
        function initializeCategoryChart() {
            const categoryCtx = document.getElementById("categoryChart")?.getContext("2d");
            if (!categoryCtx) return;

            window.categoryChart = new Chart(categoryCtx, {
                type: "doughnut",
                data: {
                    labels: <?php echo json_encode($categoryLabels); ?>,
                    datasets: [
                        {
                            data: <?php echo json_encode($categoryData); ?>,
                            backgroundColor: ["#8ed7a9", "#ffb6c1"],
                            borderWidth: 0,
                            hoverOffset: 10,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: "bottom",
                            labels: {
                                padding: 20,
                                usePointStyle: true,
                                font: {
                                    size: 12,
                                    weight: "500",
                                },
                                color: "#4a5568",
                            },
                        },
                        tooltip: {
                            backgroundColor: "rgba(0, 0, 0, 0.8)",
                            titleColor: "#ffffff",
                            bodyColor: "#ffffff",
                            borderColor: "#8ed7a9",
                            borderWidth: 1,
                            cornerRadius: 8,
                            callbacks: {
                                label: (context) => context.label + ": " + context.parsed + "%",
                            },
                        },
                    },
                    cutout: "60%",
                },
            });
        }

        // Search functionality
        function initializeSearch() {
            const searchInput = document.querySelector(".search-input");
            if (!searchInput) return;

            searchInput.addEventListener("input", (e) => {
                const searchTerm = e.target.value.toLowerCase();
                filterTables(searchTerm);
            });
        }

        // Filter tables based on search term
        function filterTables(searchTerm) {
            const tables = document.querySelectorAll("table tbody tr");

            tables.forEach((row) => {
                const text = row.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    row.style.display = "";
                } else {
                    row.style.display = "none";
                }
            });
        }

        // Notification functionality
        function initializeNotifications() {
            const notificationBtn = document.querySelector(".notification");
            if (!notificationBtn) return;

            notificationBtn.addEventListener("click", () => {
                showToast("Anda memiliki 3 notifikasi baru!", "info");
            });
        }

        // Responsive menu
        function initializeResponsiveMenu() {
            // Handle window resize
            window.addEventListener("resize", () => {
                const sidebar = document.getElementById("sidebar");
                if (window.innerWidth > 768) {
                    sidebar.classList.remove("show");
                }
            });
        }

        // Toggle sidebar for mobile
        function toggleSidebar() {
            const sidebar = document.getElementById("sidebar");
            sidebar.classList.toggle("show");
        }

        // Toast notification system
        function showToast(message, type = "info") {
            const toast = document.getElementById("toast");
            const icon = document.querySelector(".toast-icon-element");
            const messageEl = document.querySelector(".toast-message");

            messageEl.textContent = message;
            toast.className = `toast show ${type}`;

            if (type === "success") {
                icon.className = "toast-icon-element fas fa-check-circle";
            } else if (type === "error") {
                icon.className = "toast-icon-element fas fa-exclamation-circle";
            } else if (type === "info") {
                icon.className = "toast-icon-element fas fa-info-circle";
            }

            setTimeout(() => {
                closeToast();
            }, 4000);
        }

        function closeToast() {
            const toast = document.getElementById("toast");
            toast.classList.remove("show");
        }

        // Monthly Report Generation - Show Preview First
        function generateMonthlyReport() {
            const reportBtn = document.querySelector(".btn-report");
            const hasOrders = <?php echo $hasOrdersThisMonth ? 'true' : 'false'; ?>;

            // Add loading state
            reportBtn.classList.add("loading");

            // Show progress toast
            showToast("Memproses laporan bulanan...", "info");

            setTimeout(() => {
                reportBtn.classList.remove("loading");
                
                if (!hasOrders) {
                    showNoOrdersMessage();
                } else {
                    showReportPreview();
                }
            }, 1500);
        }

        function showNoOrdersMessage() {
            const modal = document.getElementById('reportPreviewModal');
            const reportContent = document.getElementById('reportContent');
            
            reportContent.innerHTML = `
                <div class="no-orders-message">
                    <div class="no-orders-icon">
                        <i class="fas fa-calendar-times"></i>
                    </div>
                    <h3>Tidak Ada Data Pesanan</h3>
                    <p>Bulan ini tidak memiliki pesanan</p>
                    <div class="no-orders-details">
                        <p>Periode: <?php echo $currentMonth . ' ' . $currentYear; ?></p>
                        <p>Status: Tidak ada transaksi yang tercatat</p>
                    </div>
                </div>
            `;
            
            // Hide print buttons when no orders
            document.querySelector('.report-modal-footer .btn-print').style.display = 'none';
            document.querySelector('.report-modal-footer .btn-pdf').style.display = 'none';
            
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function showReportPreview() {
            const modal = document.getElementById('reportPreviewModal');
            const reportContent = document.getElementById('reportContent');
            
            // Show print buttons when there are orders
            document.querySelector('.report-modal-footer .btn-print').style.display = 'inline-flex';
            document.querySelector('.report-modal-footer .btn-pdf').style.display = 'inline-flex';
            
            // Generate report content
            generateReportContent(reportContent);
            
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
            
            // Initialize charts in the preview
            setTimeout(() => {
                initializePreviewCharts();
            }, 100);
        }

        function generateReportContent(container) {
            const currentMonth = '<?php echo $currentMonth; ?>';
            const currentYear = '<?php echo $currentYear; ?>';
            const currentDate = '<?php echo $currentDate; ?>';
            
            container.innerHTML = `
                <div class="report-preview">
                    <!-- Report Header -->
                    <div class="report-header">
                        <h1>LAPORAN BULANAN</h1>
                        <h2>The Secret Garden</h2>
                        <p>Periode: ${currentMonth} ${currentYear}</p>
                        <p>Tanggal Cetak: ${currentDate}</p>
                        <hr>
                    </div>
                    
                    <!-- Statistics Summary -->
                    <div class="report-section">
                        <h3><i class="fas fa-chart-bar"></i> Ringkasan Statistik</h3>
                        <div class="stats-summary">
                            <?php foreach ($stats as $stat): ?>
                            <div class="stat-item">
                                <span class="stat-label"><?php echo $stat['label']; ?>:</span>
                                <span class="stat-value"><?php echo $stat['value']; ?></span>
                                <span class="stat-growth"><?php echo $stat['growth']; ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <!-- Charts Section -->
                    <div class="report-section">
                        <h3><i class="fas fa-chart-line"></i> Grafik Penjualan</h3>
                        <div class="charts-container">
                            <div class="chart-preview">
                                <h4>Penjualan Bulanan</h4>
                                <canvas id="previewSalesChart" width="400" height="200"></canvas>
                            </div>
                            <div class="chart-preview">
                                <h4>Distribusi Kategori</h4>
                                <canvas id="previewCategoryChart" width="400" height="200"></canvas>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Recent Orders Table -->
                    <div class="report-section">
                        <h3><i class="fas fa-shopping-cart"></i> Pesanan Terbaru</h3>
                        <table class="report-table">
                            <thead>
                                <tr>
                                    <th>ID Pesanan</th>
                                    <th>Customer</th>
                                    <th>Produk</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentOrders as $order): ?>
                                <tr>
                                    <td><?php echo $order['id']; ?></td>
                                    <td><?php echo $order['customer']; ?></td>
                                    <td><?php echo $order['product']; ?></td>
                                    <td><?php echo $order['total']; ?></td>
                                    <td><?php echo $order['status']; ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Top Products Table -->
                    <div class="report-section">
                        <h3><i class="fas fa-trophy"></i> Produk Terlaris</h3>
                        <table class="report-table">
                            <thead>
                                <tr>
                                    <th>Rank</th>
                                    <th>Produk</th>
                                    <th>Kategori</th>
                                    <th>Terjual</th>
                                    <th>Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($topProducts as $product): ?>
                                <tr>
                                    <td><?php echo $product['rank']; ?></td>
                                    <td><?php echo $product['product']; ?></td>
                                    <td><?php echo $product['category']; ?></td>
                                    <td><?php echo $product['sold']; ?></td>
                                    <td><?php echo $product['revenue']; ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Report Footer -->
                    <div class="report-footer">
                        <p>Laporan ini dibuat secara otomatis oleh sistem The Secret Garden</p>
                        <p>© ${currentYear} The Secret Garden. All rights reserved.</p>
                    </div>
                </div>
            `;
        }

        function initializePreviewCharts() {
            // Initialize preview sales chart
            const previewSalesCtx = document.getElementById("previewSalesChart")?.getContext("2d");
            if (previewSalesCtx) {
                new Chart(previewSalesCtx, {
                    type: "line",
                    data: {
                        labels: <?php echo json_encode($salesLabels); ?>,
                        datasets: [{
                            label: "Penjualan (Juta Rupiah)",
                            data: <?php echo json_encode($salesData); ?>,
                            borderColor: "#8ed7a9",
                            backgroundColor: "rgba(142, 215, 169, 0.1)",
                            tension: 0.4,
                            fill: true,
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            y: { beginAtZero: true },
                        },
                    },
                });
            }

            // Initialize preview category chart - Updated for 2 categories
            const previewCategoryCtx = document.getElementById("previewCategoryChart")?.getContext("2d");
            if (previewCategoryCtx) {
                new Chart(previewCategoryCtx, {
                    type: "doughnut",
                    data: {
                        labels: <?php echo json_encode($categoryLabels); ?>,
                        datasets: [{
                            data: <?php echo json_encode($categoryData); ?>,
                            backgroundColor: ["#8ed7a9", "#ffb6c1"],
                            borderWidth: 0,
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: "bottom" },
                        },
                        cutout: "60%",
                    },
                });
            }
        }

        function closeReportPreview() {
            const modal = document.getElementById('reportPreviewModal');
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        function printReport() {
            const reportContent = document.getElementById('reportContent').innerHTML;
            const printWindow = window.open('', '_blank');
            
            printWindow.document.write(`
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Laporan Bulanan - The Secret Garden</title>
                    <style>
                        body { font-family: Arial, sans-serif; margin: 20px; }
                        .report-preview { max-width: 800px; margin: 0 auto; }
                        .report-header { text-align: center; margin-bottom: 30px; }
                        .report-header h1 { color: #333; margin-bottom: 10px; }
                        .report-header h2 { color: #8ed7a9; margin-bottom: 20px; }
                        .report-section { margin-bottom: 30px; }
                        .report-section h3 { color: #333; border-bottom: 2px solid #8ed7a9; padding-bottom: 5px; }
                        .stats-summary { display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; margin: 20px 0; }
                        .stat-item { padding: 10px; border: 1px solid #ddd; border-radius: 5px; }
                        .stat-label { font-weight: bold; }
                        .stat-value { color: #333; font-size: 18px; font-weight: bold; }
                        .stat-growth { color: #28a745; font-size: 12px; }
                        .charts-container canvas { max-width: 300px; max-height: 200px; }
                        .charts-container { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; margin: 20px 0; }
                        .chart-preview { text-align: center; }
                        .report-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
                        .report-table th, .report-table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                        .report-table th { background-color: #f2f2f2; }
                        .report-footer { text-align: center; margin-top: 40px; color: #666; font-size: 12px; }
                        .no-orders-message { text-align: center; padding: 40px; }
                        .no-orders-icon { font-size: 48px; color: #ccc; margin-bottom: 20px; }
                        @media print {
                            body { margin: 0; }
                            .charts-container canvas { max-width: 300px; max-height: 200px; }
                        }
                    </style>
                </head>
                <body>
                    ${reportContent}
                </body>
                </html>
            `);
            
            printWindow.document.close();
            printWindow.focus();
            
            setTimeout(() => {
                printWindow.print();
                printWindow.close();
            }, 500);
        }

        function generatePDFFromPreview() {
            const reportBtn = document.querySelector(".btn-pdf");
            
            // Add loading state
            reportBtn.classList.add("loading");
            reportBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span>Generating PDF...</span>';
            
            showToast("Membuat PDF dari preview...", "info");
            
            // Capture the report content
            const reportContent = document.getElementById('reportContent');
            
            html2canvas(reportContent, {
                scale: 2,
                useCORS: true,
                allowTaint: true,
                backgroundColor: '#ffffff'
            }).then(canvas => {
                const imgData = canvas.toDataURL('image/png');
                const pdf = new jsPDF('p', 'mm', 'a4');
                
                const imgWidth = 210;
                const pageHeight = 295;
                const imgHeight = (canvas.height * imgWidth) / canvas.width;
                let heightLeft = imgHeight;
                let position = 0;
                
                pdf.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight);
                heightLeft -= pageHeight;
                
                while (heightLeft >= 0) {
                    position = heightLeft - imgHeight;
                    pdf.addPage();
                    pdf.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight);
                    heightLeft -= pageHeight;
                }
                
                const currentMonth = '<?php echo $currentMonth; ?>';
                const currentYear = '<?php echo $currentYear; ?>';
                const fileName = `Laporan_Bulanan_${currentMonth}_${currentYear}.pdf`;
                
                pdf.save(fileName);
                
                // Remove loading state
                reportBtn.classList.remove("loading");
                reportBtn.innerHTML = '<i class="fas fa-file-pdf"></i> <span>Download PDF</span>';
                
                showToast(`PDF berhasil diunduh: ${fileName}`, "success");
            }).catch(error => {
                console.error('Error generating PDF:', error);
                reportBtn.classList.remove("loading");
                reportBtn.innerHTML = '<i class="fas fa-file-pdf"></i> <span>Download PDF</span>';
                showToast("Gagal membuat PDF. Silakan coba lagi.", "error");
            });
        }
    </script>
    <style>
        /* Report Modal Styles */
        .report-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 2000;
        }

        .report-modal-content {
            background: white;
            border-radius: 12px;
            width: 90%;
            max-width: 1200px;
            max-height: 90vh;
            display: flex;
            flex-direction: column;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }

        .report-modal-header {
            padding: 20px 30px;
            border-bottom: 1px solid #e5e5e5;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: linear-gradient(135deg, var(--primary-color), var(--dark-green));
            color: white;
            border-radius: 12px 12px 0 0;
        }

        .report-modal-header h2 {
            margin: 0;
            font-size: 18px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .close-modal {
            background: none;
            border: none;
            color: white;
            font-size: 20px;
            cursor: pointer;
            padding: 5px;
            border-radius: 50%;
            transition: background 0.3s;
        }

        .close-modal:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .report-modal-body {
            flex: 1;
            overflow-y: auto;
            padding: 30px;
        }

        .report-modal-footer {
            padding: 20px 30px;
            border-top: 1px solid #e5e5e5;
            display: flex;
            justify-content: center;
            gap: 15px;
            background: #f8f9fa;
            border-radius: 0 0 12px 12px;
        }

        .btn-print, .btn-pdf, .btn-cancel {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
            font-size: 14px;
        }

        .btn-print {
            background: #28a745;
            color: white;
        }

        .btn-print:hover {
            background: #218838;
            transform: translateY(-2px);
        }

        .btn-pdf {
            background: #dc3545;
            color: white;
        }

        .btn-pdf:hover {
            background: #c82333;
            transform: translateY(-2px);
        }

        .btn-cancel {
            background: #6c757d;
            color: white;
        }

        .btn-cancel:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }

        /* Report Preview Styles */
        .report-preview {
            max-width: 800px;
            margin: 0 auto;
            font-family: Arial, sans-serif;
        }

        .report-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #8ed7a9;
        }

        .report-header h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 24px;
        }

        .report-header h2 {
            color: #8ed7a9;
            margin-bottom: 20px;
            font-size: 20px;
        }

        .report-section {
            margin-bottom: 30px;
        }

        .report-section h3 {
            color: #333;
            border-bottom: 2px solid #8ed7a9;
            padding-bottom: 5px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .stats-summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }

        .stat-item {
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background: #f8f9fa;
        }

        .stat-label {
            font-weight: bold;
            color: #666;
            display: block;
            margin-bottom: 5px;
        }

        .stat-value {
            color: #333;
            font-size: 18px;
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
        }

        .stat-growth {
            color: #28a745;
            font-size: 12px;
        }

        .charts-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin: 20px 0;
        }

        .chart-preview {
            text-align: center;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background: #f8f9fa;
        }

        .chart-preview h4 {
            margin-bottom: 15px;
            color: #333;
        }

        .report-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 14px;
        }

        .report-table th,
        .report-table td {
            border: 1px solid #ddd;
            padding: 12px 8px;
            text-align: left;
        }

        .report-table th {
            background-color: #8ed7a9;
            color: white;
            font-weight: bold;
        }

        .report-table tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .report-footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            color: #666;
            font-size: 12px;
        }

        /* No Orders Message Styles */
        .no-orders-message {
            text-align: center;
            padding: 60px 40px;
            color: #666;
        }

        .no-orders-icon {
            font-size: 64px;
            color: #ccc;
            margin-bottom: 20px;
        }

        .no-orders-message h3 {
            color: #333;
            margin-bottom: 10px;
            font-size: 24px;
        }

        .no-orders-message p {
            font-size: 16px;
            margin-bottom: 20px;
        }

        .no-orders-details {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #ffc107;
            margin-top: 20px;
        }

        .no-orders-details p {
            margin: 5px 0;
            font-size: 14px;
        }

        /* Responsive Design for Modal */
        @media (max-width: 768px) {
            .report-modal-content {
                width: 95%;
                max-height: 95vh;
            }
            
            .report-modal-header,
            .report-modal-body,
            .report-modal-footer {
                padding: 15px 20px;
            }
            
            .report-modal-footer {
                flex-direction: column;
            }
            
            .btn-print, .btn-pdf, .btn-cancel {
                width: 100%;
                justify-content: center;
            }
            
            .stats-summary {
                grid-template-columns: 1fr;
            }
            
            .charts-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</body>
</html>

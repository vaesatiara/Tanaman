<?php
session_start();
include "koneksi.php";
$id = isset($_GET['id_produk']) ? $_GET['id_produk'] : null;

$sql="SELECT * FROM produk WHERE id_produk= '$id'";
$query = mysqli_query($koneksi, $sql);

$produk=mysqli_fetch_assoc($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <style>
        .tab-navigation {
            display: flex;
            background-color: #f8f9fa;
            border-bottom: 1px solid #e9ecef;
        }

        .tab-button {
            flex: 1;
            padding: 15px 20px;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
            color: #6c757d;
            transition: all 0.3s ease;
            position: relative;
        }

        .tab-button:hover {
            background-color: #e9ecef;
            color: #495057;
        }

        .tab-button.active {
            color: #28a745;
            background-color: white;
        }

        .tab-button.active::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 3px;
            background-color: #28a745;
        }

        .tab-content {
            padding: 30px;
            min-height: 300px;
        }

        .tab-pane {
            display: none;
            animation: fadeIn 0.3s ease-in-out;
        }

        .tab-pane.active {
            display: block;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .content-section {
            margin-bottom: 20px;
        }

        .content-section h3 {
            color: #343a40;
            margin-bottom: 10px;
            font-size: 18px;
        }

        .content-section p {
            color: #6c757d;
            line-height: 1.6;
            margin-bottom: 15px;
        }

        .feature-list {
            list-style: none;
            padding: 0;
        }

        .feature-list li {
            padding: 8px 0;
            border-bottom: 1px solid #f1f3f4;
            color: #495057;
        }

        .feature-list li:before {
            content: "✓";
            color: #28a745;
            font-weight: bold;
            margin-right: 10px;
        }

        .rating {
            display: flex;
            align-items: center;
            margin: 10px 0;
        }

        .stars {
            color: #ffc107;
            margin-right: 10px;
        }

        .review-item {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin: 10px 0;
        }

        .review-author {
            font-weight: bold;
            color: #343a40;
            margin-bottom: 5px;
        }

        /* Styling untuk quantity input */
        .quantity-input {
            display: flex;
            align-items: center;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            overflow: hidden;
            width: fit-content;
            background: white;
            margin-top: 10px;
        }

        .quantity-btn {
            background: #f8f9fa;
            border: none;
            width: 45px;
            height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
            color: #666;
            font-size: 16px;
        }

        .quantity-btn:hover:not(:disabled) {
            background: #28a745;
            color: white;
        }

        .quantity-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            background: #f1f1f1;
        }

        .quantity-btn:active:not(:disabled) {
            transform: scale(0.95);
        }

        .quantity-input input {
            border: none;
            width: 70px;
            height: 45px;
            text-align: center;
            font-size: 16px;
            font-weight: 500;
            outline: none;
            background: white;
        }

        .quantity-input input::-webkit-outer-spin-button,
        .quantity-input input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        .quantity-input input[type="number"] {
            -moz-appearance: textfield;
        }

        .stock-indicator {
            margin-top: 8px;
            font-size: 12px;
            color: #666;
        }

        .stock-warning {
            color: #dc3545;
            font-weight: 500;
        }

        .out-of-stock .quantity-input {
            opacity: 0.5;
            pointer-events: none;
        }
    </style>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($produk['nama_tanaman']); ?> - The Secret Garden</title>
    <link rel="stylesheet" href="css/detail_produk.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Header -->
    <header>
        <div class="container">
            <div class="logo">
                <a href="index.php">
                    <img src="images/logo.png" alt="The Secret Garden">
                </a>
            </div>
            <nav>
                <ul>
                    <li><a href="index.php">BERANDA</a></li>
                    <li><a href="produk.php" class="active">PRODUK</a></li>
                    <li><a href="kontak.php">KONTAK</a></li>
                    <li><a href="tentang-kami.php">TENTANG KAMI</a></li>
                </ul>
            </nav>
             <div class="icons">
                <?php if (isset($_SESSION['username'])): ?>
                    <a href="keranjang.php" class="action-btn">
                        <i class="fas fa-shopping-cart"></i>  
                    </a>
                    <a href="profil.php"><i class="fas fa-user"></i></a>
                    <a href="logout.php"> <i class="fas fa-sign-out-alt"></i> Logout</a>
                <?php else: ?>
                    <a href="login.php" class="action-btn">
                        <i class="fas fa-shopping-cart"></i>
                    </a>
                    <a href="login.php">Login</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <!-- Breadcrumb -->
    <div class="breadcrumb-container">
        <div class="container">
            <div class="breadcrumb">
                <a href="index.php">Beranda</a>
                <span class="separator">/</span>
                <a href="produk.php">Produk</a>
                <span class="separator">/</span>
                <span class="current"><?php echo $produk['nama_tanaman'] ?></span>
            </div>
        </div>
    </div>

    <!-- Product Detail -->
    <section class="product-detail">
        <div class="container">
            <div class="product-detail-container">
                <!-- Product Gallery -->
                <div class="product-gallery">
                    <div class="main-image">
                        <img src="/admin/Admin_WebTanaman/uploads/<?php echo htmlspecialchars($produk['foto']); ?>" alt="<?php echo htmlspecialchars($produk['nama_tanaman']); ?>">
                    </div>
                </div>

                <!-- Product Info -->
                <div class="product-info">
                    <span class="product-badge">Baru</span>
                    <h1 class="product-title"><?php echo htmlspecialchars($produk['nama_tanaman']); ?></h1>
                    <div class="product-rating">
                        <div class="stars">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star-half-alt"></i>
                        </div>
                        <span class="rating-count">(4.7)</span>
                    </div>
                        
                    <div class="product-price">
                        <span class="price">Rp <?php echo number_format($produk['harga'], 0, ',', '.'); ?></span>
                    </div>
                    
                    <div class="product-stock">
                        <i class="fas fa-<?php echo $produk['stok'] > 0 ? 'check' : 'times'; ?>-circle"></i>
                        <span><?php echo $produk['stok'] > 0 ? 'Stok tersedia (' . $produk['stok'] . ' tersisa)' : 'Stok habis'; ?></span>
                    </div>
                    
                    <div class="product-description">
                        <p><?php echo htmlspecialchars($produk['deskripsi']); ?></p>
                    </div>
                    
                    <div class="product-quantity <?php echo $produk['stok'] <= 0 ? 'out-of-stock' : ''; ?>">
                        <label>Jumlah:</label>
                        <div class="quantity-input">
                            <button type="button" class="quantity-btn minus" id="btn-minus">
                                <i class="fas fa-minus"></i>
                            </button>
                            <input type="number" id="quantity-input" value="1" min="1" max="<?php echo $produk['stok']; ?>" readonly>
                            <button type="button" class="quantity-btn plus" id="btn-plus">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                        <div class="stock-indicator">
                            <span id="stock-info">Stok tersedia: <?php echo $produk['stok']; ?></span>
                        </div>
                    </div>
                   
                    <div class="product-actions">
                        <?php if (isset($_SESSION['username'])): ?>
                            <a href="keranjang.php?id_produk=<?php echo $produk['id_produk']; ?>" class="btn primary-btn" id="add-to-cart-btn">
                                <i class="fas fa-shopping-cart"></i>
                                Tambahkan ke Keranjang</a>
                            
                            <a href="#" class="btn secondary-btn" id="buy-now-btn">
                                <i class="fas fa-bolt"></i>
                                Beli Sekarang</a>
                           
                        <?php else: ?>
                            <a href="login.php" onclick="alert('Silakan login terlebih dahulu untuk menambahkan ke keranjang.');" class="btn primary-btn">
                                <i class="fas fa-shopping-cart"></i>
                                Tambahkan ke Keranjang
                            </a>
                            <a href="login.php" onclick="alert('Silakan login terlebih dahulu untuk membeli produk.');" class="btn secondary-btn">
                                <i class="fas fa-bolt"></i>
                                Beli Sekarang
                            </a>
                        <?php endif; ?>
                    </div>

                    <div class="product-delivery">
                        <div class="delivery-item">
                            <i class="fas fa-truck"></i>
                            <span>Pengiriman gratis untuk pembelian di atas Rp 500.000</span>
                        </div>
                        <div class="delivery-item">
                            <i class="fas fa-undo"></i>
                            <span>Pengembalian gratis dalam 7 hari</span>
                        </div>
                        <div class="delivery-item">
                            <i class="fas fa-shield-alt"></i>
                            <span>Garansi tanaman sehat selama 30 hari</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Product Tabs -->
    <div class="container">
        <div class="tab-navigation">
            <button class="tab-button active" data-tab="deskripsi">Deskripsi</button>
            <button class="tab-button" data-tab="perawatan">Perawatan</button>
            <button class="tab-button" data-tab="ulasan">Ulasan</button>
        </div>

        <div class="tab-content">
            <!-- Tab Deskripsi -->
            <div id="deskripsi" class="tab-pane active">
                <div class="content-section">
                    <h3>Deskripsi Produk</h3>
                    <p><?php echo isset($produk['deskripsi_lengkap']) ? $produk['deskripsi_lengkap'] : $produk['deskripsi']; ?></p>
                    <p>Tanaman berkualitas tinggi yang telah melalui proses seleksi ketat untuk memastikan kesehatan dan kualitas terbaik. Cocok untuk mempercantik rumah dan memberikan suasana segar alami.</p>
                    
                    <h3>Keunggulan Utama</h3>
                    <ul class="feature-list">
                        <li>Tanaman sehat dan berkualitas premium</li>
                        <li>Mudah perawatan untuk pemula</li>
                        <li>Tahan terhadap berbagai kondisi cuaca</li>
                        <li>Garansi tanaman sehat</li>
                        <li>Ramah lingkungan dan aman</li>
                    </ul>
                </div>
            </div>

            <!-- Tab Perawatan -->
            <div id="perawatan" class="tab-pane">
                <div class="content-section">
                    <h3>Panduan Perawatan</h3>
                    <p>Untuk menjaga kesehatan dan keindahan tanaman, ikuti panduan perawatan berikut:</p>
                    
                    <h3>Perawatan Harian</h3>
                    <ul class="feature-list">
                        <li>Siram secukupnya sesuai kebutuhan tanaman</li>
                        <li>Letakkan di tempat dengan pencahayaan yang cukup</li>
                        <li>Jaga kelembaban udara di sekitar tanaman</li>
                        <li>Bersihkan debu pada daun secara berkala</li>
                    </ul>
                    
                    <h3>Perawatan Berkala</h3>
                    <ul class="feature-list">
                        <li>Berikan pupuk sesuai jadwal yang direkomendasikan</li>
                        <li>Pangkas daun yang layu atau rusak</li>
                        <li>Ganti media tanam jika diperlukan</li>
                        <li>Periksa kondisi akar secara berkala</li>
                    </ul>
                    
                    <p><strong>Catatan:</strong> Setiap tanaman memiliki kebutuhan perawatan yang berbeda. Konsultasikan dengan ahli tanaman jika diperlukan.</p>
                </div>
            </div>

            <!-- Tab Ulasan -->
            <div id="ulasan" class="tab-pane">
                <div class="content-section">
                    <h3>Ulasan Pelanggan</h3>
                    <div class="rating">
                        <div class="stars">★★★★★</div>
                        <span>4.8/5 (124 ulasan)</span>
                    </div>
                    
                    <div class="review-item">
                        <div class="review-author">Ahmad Rizki</div>
                        <div class="rating">
                            <div class="stars">★★★★★</div>
                        </div>
                        <p>Tanaman sampai dengan kondisi sangat baik. Packaging rapi dan aman. Tanaman sehat dan sudah mulai tumbuh tunas baru. Sangat puas dengan pembelian ini!</p>
                    </div>
                    
                    <div class="review-item">
                        <div class="review-author">Sari Dewi</div>
                        <div class="rating">
                            <div class="stars">★★★★☆</div>
                        </div>
                        <p>Kualitas tanaman bagus, sesuai dengan foto. Hanya saja pengiriman agak lama. Overall recommended untuk yang suka tanaman hias!</p>
                    </div>
                    
                    <div class="review-item">
                        <div class="review-author">Budi Santoso</div>
                        <div class="rating">
                            <div class="stars">★★★★★</div>
                        </div>
                        <p>Sudah beli beberapa kali di sini, selalu puas. Tanaman sehat dan tumbur subur. Pelayanan juga ramah dan responsif.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <!-- Feedback Section -->
        <div class="feedback-section">
            <div class="container">
                <h2 class="feedback-title">Kirim kritik/saran untuk kami</h2>
                <p class="feedback-subtitle">Ceritakan kepada kami kritik dan/atau saran Anda</p>
                <form action="submit-feedback.php" method="POST" class="feedback-form">
                    <input type="hidden" name="id_produk" value="<?php echo $produk['id_produk']; ?>">
                    <input type="text" name="feedback" placeholder="Masukkan kritik/saran" class="feedback-input" required>
                    <button type="submit" class="feedback-btn">KIRIM</button>
                </form>
            </div>
        </div>
        
        <!-- Main Footer -->
        <div class="main-footer">
            <div class="container">
                <div class="footer-content">
                    <!-- Company Info -->
                    <div class="footer-company">
                        <div class="footer-logo">
                            <img src="images/logo.png" alt="The Secret Garden">
                        </div>
                        <p class="company-description">
                            Toko tanaman hias terpercaya dengan berbagai koleksi tanaman berkualitas untuk mempercantik rumah dan ruangan Anda.
                        </p>
                        <div class="social-links">
                            <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                            <a href="#" class="social-link"><i class="fab fa-facebook-f"></i></a>
                            <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                            <a href="#" class="social-link"><i class="fab fa-youtube"></i></a>
                        </div>
                    </div>
                    
                    <!-- Quick Links -->
                    <div class="footer-links">
                        <h3 class="footer-heading">Tautan Cepat</h3>
                        <ul class="footer-menu">
                            <li><a href="index.php">BERANDA</a></li>
                            <li><a href="produk.php">PRODUK</a></li>
                            <li><a href="kontak.php">KONTAK</a></li>
                        </ul>
                    </div>
                    
                    <!-- Categories -->
                    <div class="footer-categories">
                        <h3 class="footer-heading">Kategori</h3>
                        <ul class="footer-menu">
                            <li><a href="produk.php?kategori=bunga">Tanaman Hias Bunga</a></li>
                            <li><a href="produk.php?kategori=daun">Tanaman Hias Daun</a></li>
                        </ul>
                    </div>
                    
                    <!-- Contact Info -->
                    <div class="footer-contact">
                        <h3 class="footer-heading">Kontak Kami</h3>
                        <ul class="contact-info">
                            <li>
                                <i class="fas fa-map-marker-alt"></i>
                                <span>Jl. Tanaman Indah No. 123, Purwokerto</span>
                            </li>
                            <li>
                                <i class="fas fa-phone-alt"></i>
                                <span>0812-3456-7890</span>
                            </li>
                            <li>
                                <i class="fas fa-envelope"></i>
                                <span>thesecretgarden@gmail.com</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Copyright -->
        <div class="copyright">
            <div class="container">
                <p>© <?= date('Y') ?> The Secret Garden. Hak Cipta Dilindungi.</p>
            </div>
        </div>
    </footer>

    <!-- Back to Top Button -->
    <button id="back-to-top" class="back-to-top">
        <i class="fas fa-arrow-up"></i>
    </button>

    <!-- Toast Notification -->
    <div id="notification-toast" class="toast">
        <div class="toast-content">
            <div class="toast-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="toast-message">Produk telah ditambahkan ke keranjang!</div>
        </div>
        <button class="toast-close">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <!-- JavaScript -->
    <script>
        // Fungsi untuk tab navigation yang diperbaiki
        function showTab(tabName) {
            // Sembunyikan semua tab pane
            const tabPanes = document.querySelectorAll('.tab-pane');
            tabPanes.forEach(pane => {
                pane.classList.remove('active');
            });
            
            // Hapus class active dari semua button
            const tabButtons = document.querySelectorAll('.tab-button');
            tabButtons.forEach(button => {
                button.classList.remove('active');
            });
            
            // Tampilkan tab yang dipilih
            const targetTab = document.getElementById(tabName);
            if (targetTab) {
                targetTab.classList.add('active');
            }
            
            // Tambahkan class active ke button yang diklik
            const clickedButton = document.querySelector(`[data-tab="${tabName}"]`);
            if (clickedButton) {
                clickedButton.classList.add('active');
            }
        }

        // Event listener untuk tab buttons
        document.addEventListener('DOMContentLoaded', function() {
            const tabButtons = document.querySelectorAll('.tab-button');
            tabButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const tabName = this.getAttribute('data-tab');
                    showTab(tabName);
                });
            });
        });

        // Fungsi untuk quantity control dengan batas stok
        document.addEventListener('DOMContentLoaded', function() {
            const quantityInput = document.getElementById('quantity-input');
            const minusBtn = document.getElementById('btn-minus');
            const plusBtn = document.getElementById('btn-plus');
            const stockInfo = document.getElementById('stock-info');
            const addToCartBtn = document.getElementById('add-to-cart-btn');
            const buyNowBtn = document.getElementById('buy-now-btn');
            
            // Ambil nilai stok maksimal dari PHP
            const maxStock = <?php echo $produk['stok']; ?>;
            const minQuantity = 1;
            
            // Fungsi untuk update tombol berdasarkan nilai current
            function updateButtons() {
                const currentValue = parseInt(quantityInput.value);
                
                // Update minus button
                if (currentValue <= minQuantity) {
                    minusBtn.disabled = true;
                } else {
                    minusBtn.disabled = false;
                }
                
                // Update plus button
                if (currentValue >= maxStock) {
                    plusBtn.disabled = true;
                } else {
                    plusBtn.disabled = false;
                }
                
                // Update stock info
                const remaining = maxStock - currentValue;
                if (remaining <= 5 && remaining > 0) {
                    stockInfo.innerHTML = '<span class="stock-warning">Sisa stok: ' + remaining + '</span>';
                } else if (remaining === 0) {
                    stockInfo.innerHTML = '<span class="stock-warning">Stok akan habis!</span>';
                } else {
                    stockInfo.innerHTML = 'Stok tersedia: ' + maxStock;
                }
            }
            
            // Event listener untuk tombol minus
            minusBtn.addEventListener('click', function(e) {
                e.preventDefault();
                let currentValue = parseInt(quantityInput.value);
                
                if (currentValue > minQuantity) {
                    currentValue--;
                    quantityInput.value = currentValue;
                    updateButtons();
                }
            });
            
            // Event listener untuk tombol plus
            plusBtn.addEventListener('click', function(e) {
                e.preventDefault();
                let currentValue = parseInt(quantityInput.value);
                
                if (currentValue < maxStock) {
                    currentValue++;
                    quantityInput.value = currentValue;
                    updateButtons();
                } else {
                    alert('Stok tidak mencukupi! Maksimal ' + maxStock + ' item.');
                }
            });
            
            // Inisialisasi state tombol saat halaman dimuat
            updateButtons();
            
            // Jika stok habis, disable semua kontrol
            if (maxStock <= 0) {
                quantityInput.disabled = true;
                minusBtn.disabled = true;
                plusBtn.disabled = true;
                document.querySelector('.product-quantity').classList.add('out-of-stock');
            }

            // Add to cart functionality
            if (addToCartBtn) {
                addToCartBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const productId = <?php echo $produk['id_produk']; ?>;
                    const quantity = document.getElementById('quantity-input').value;
                    
                    // Kirim data ke keranjang via AJAX atau redirect
                    fetch('add-to-cart.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'id_produk=' + productId + '&quantity=' + quantity
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Show toast notification
                            const toast = document.getElementById('notification-toast');
                            toast.classList.add('show');
                            
                            setTimeout(() => {
                                toast.classList.remove('show');
                            }, 3000);
                        } else {
                            alert('Gagal menambahkan ke keranjang: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        // Fallback: redirect ke keranjang
                        window.location.href = 'keranjang.php?id_produk=' + productId + '&quantity=' + quantity;
                    });
                });
            }

            // Buy Now functionality - langsung ke alamat pengiriman
            if (buyNowBtn) {
                buyNowBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const productId = <?php echo $produk['id_produk']; ?>;
                    const quantity = document.getElementById('quantity-input').value;
                    
                    // Redirect langsung ke alamat pengiriman dengan parameter buy_now
                    window.location.href = 'alamat_pengiriman.php?source=buy_now&id_produk=' + productId + '&qty=' + quantity;
                });
            }
        });

        // Close toast notification
        document.addEventListener('DOMContentLoaded', function() {
            const toastClose = document.querySelector('.toast-close');
            if (toastClose) {
                toastClose.addEventListener('click', function() {
                    document.getElementById('notification-toast').classList.remove('show');
                });
            }
        });

        // Keyboard navigation untuk tabs
        document.addEventListener('keydown', function(e) {
            if (e.key === 'ArrowLeft' || e.key === 'ArrowRight') {
                const activeButton = document.querySelector('.tab-button.active');
                const buttons = Array.from(document.querySelectorAll('.tab-button'));
                const currentIndex = buttons.indexOf(activeButton);
                
                let newIndex;
                if (e.key === 'ArrowLeft') {
                    newIndex = currentIndex > 0 ? currentIndex - 1 : buttons.length - 1;
                } else {
                    newIndex = currentIndex < buttons.length - 1 ? currentIndex + 1 : 0;
                }
                
                const targetTab = buttons[newIndex].getAttribute('data-tab');
                showTab(targetTab);
            }
        });
    </script>
</body>
</html>
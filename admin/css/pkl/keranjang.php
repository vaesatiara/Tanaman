<?php
session_start();
include "koneksi.php";

// Handle adding product to cart if ID is provided in URL
if(isset($_GET['id_produk'])) {
    $id_produk = $_GET['id_produk'];
    
    // If user is logged in, save to database as well
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        
        // Check if product already exists in database cart
        $check_cart = $koneksi->prepare("SELECT * FROM keranjang WHERE id_user = ? AND id_produk = ?");
        $check_cart->bind_param("ss", $user_id, $id_produk);
        $check_cart->execute();
        $result = $check_cart->get_result();
        
        if ($result->num_rows > 0) {
            // Update quantity
            $update_cart = $koneksi->prepare("UPDATE keranjang SET jumlah = jumlah + 1 WHERE id_user = ? AND id_produk = ?");
            $update_cart->bind_param("ss", $user_id, $id_produk);
            $update_cart->execute();
        } else {
            // Insert new item
            $insert_cart = $koneksi->prepare("INSERT INTO keranjang (id_user, id_produk, jumlah) VALUES (?, ?, 1)");
            $insert_cart->bind_param("ss", $user_id, $id_produk);
            $insert_cart->execute();
        }
    }
    
    // Also update session cart
    if(!isset($_SESSION['keranjang'][$id_produk])) {
        $_SESSION['keranjang'][$id_produk] = 1;
    } else {
        $_SESSION['keranjang'][$id_produk] += 1;
    }
    
    unset($_SESSION['keranjang']['']);
    header("Location: keranjang.php");
    exit;
}

// Load cart from database if user is logged in
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $cart_query = $koneksi->prepare("SELECT id_produk, jumlah FROM keranjang WHERE id_user = ?");
    $cart_query->bind_param("s", $user_id);
    $cart_query->execute();
    $cart_result = $cart_query->get_result();
    
    // Sync database cart with session cart
    $_SESSION['keranjang'] = array();
    while ($cart_item = $cart_result->fetch_assoc()) {
        $_SESSION['keranjang'][$cart_item['id_produk']] = $cart_item['jumlah'];
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang Belanja - Toko Tanaman</title>
    <link rel="stylesheet" href="css/keranjang.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Additional styles for selection feature */
        .select-column {
            width: 50px;
            text-align: center;
        }
        
        .select-all-container {
            margin-bottom: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .select-all-container input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }
        
        .select-all-container label {
            font-weight: 500;
            cursor: pointer;
            margin: 0;
        }
        
        .product-checkbox {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }
        
        .cart-item {
            transition: all 0.3s ease;
        }
        
        .cart-item.selected {
            background-color: #f0f8ff;
            border-left: 4px solid #4CAF50;
        }
        
        .selected-actions {
            margin-top: 20px;
            padding: 15px;
            background: #e8f5e8;
            border-radius: 8px;
            display: none;
        }
        
        .selected-actions.show {
            display: block;
        }
        
        .selected-count {
            font-weight: 500;
            color: #2e7d32;
            margin-bottom: 10px;
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .action-btn {
            padding: 8px 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        .checkout-selected-btn {
            background: #4CAF50;
            color: white;
        }
        
        .checkout-selected-btn:hover {
            background: #45a049;
        }
        
        .delete-selected-btn {
            background: #f44336;
            color: white;
        }
        
        .delete-selected-btn:hover {
            background: #da190b;
        }
        
        .move-selected-btn {
            background: #2196F3;
            color: white;
        }
        
        .move-selected-btn:hover {
            background: #1976D2;
        }
        
        /* Update cart header to include select column */
        .cart-header {
            display: grid;
            grid-template-columns: 50px 1fr 2fr 120px 120px 120px 80px;
            gap: 20px;
            padding: 15px 20px;
            background: #f8f9fa;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 600;
            color: #333;
        }
        
        .cart-item {
            display: grid;
            grid-template-columns: 50px 1fr 2fr 120px 120px 120px 80px;
            gap: 20px;
            padding: 20px;
            background: white;
            border-radius: 8px;
            margin-bottom: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            align-items: center;
        }
        
        @media (max-width: 768px) {
            .cart-header,
            .cart-item {
                grid-template-columns: 1fr;
                gap: 10px;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <div class="logo">
                <a href="index.html">
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
                <a href="keranjang.php" class="cart-icon active">
                    <i class="fas fa-shopping-cart"></i>
                    <?php
                    $totalItems = 0;
                    if(isset($_SESSION['keranjang']) && !empty($_SESSION['keranjang'])) {
                        foreach($_SESSION['keranjang'] as $id => $qty) {
                            if(!empty($id)) {
                                $totalItems += $qty;
                            }
                        }
                    }
                    
                    if($totalItems > 0) {
                        echo '<span class="cart-badge">' . $totalItems . '</span>';
                    }
                    ?>
                </a>
                <a href="profil.html"><i class="fas fa-user"></i></a>
            </div>
        </div>
    </header>

    <main class="cart-section">
        <h1 class="cart-title">Keranjang Belanja</h1>
        <p class="cart-subtitle">Tinjau dan pilih item yang Anda inginkan</p>
        
        <div class="cart-container">
            <div class="cart-items">
                <?php if(isset($_SESSION['keranjang']) && !empty($_SESSION['keranjang'])): ?>
                
                <!-- Select All Option -->
                <div class="select-all-container">
                    <input type="checkbox" id="selectAll" onchange="toggleSelectAll()">
                    <label for="selectAll">Pilih Semua</label>
                    <span class="select-info">(<span id="totalItems"><?php echo count($_SESSION['keranjang']); ?></span> item)</span>
                </div>
                
                <!-- Selected Items Actions -->
                <div class="selected-actions" id="selectedActions">
                    <div class="selected-count" id="selectedCount">0 item dipilih</div>
                    <div class="action-buttons">
                        <button class="action-btn checkout-selected-btn" onclick="checkoutSelected()">
                            <i class="fas fa-shopping-bag"></i> Checkout Terpilih
                        </button>
                        <button class="action-btn delete-selected-btn" onclick="deleteSelected()">
                            <i class="fas fa-trash"></i> Hapus Terpilih
                        </button>
                        <button class="action-btn move-selected-btn" onclick="moveToWishlist()">
                            <i class="fas fa-heart"></i> Pindah ke Wishlist
                        </button>
                    </div>
                </div>
                
                <div class="cart-header">
                    <div class="header-select">Pilih</div>
                    <div class="header-product">Produk</div>
                    <div class="header-name">Nama</div>
                    <div class="header-price">Harga</div>
                    <div class="header-quantity">Jumlah</div>
                    <div class="header-subtotal">Subtotal</div>
                    <div class="header-remove">Hapus</div>
                </div>
                
                <?php
                $totalHarga = 0;
                
                foreach ($_SESSION['keranjang'] as $id_produk => $jumlah) {
                    if(empty($id_produk)) continue;
                    
                    $ambil = $koneksi->query("SELECT * FROM produk WHERE id_produk='$id_produk'");
                    $pecah = $ambil->fetch_assoc();
                    
                    if (!$pecah) continue; // Skip if product not found
                    
                    $subtotal = $pecah['harga'] * $jumlah;
                    $totalHarga += $subtotal;
                ?>
                <div class="cart-item" data-product-id="<?php echo $id_produk; ?>">
                    <div class="item-select">
                        <input type="checkbox" class="product-checkbox" 
                               value="<?php echo $id_produk; ?>" 
                               onchange="updateSelection()" 
                               data-price="<?php echo $subtotal; ?>">
                    </div>
                    <div class="product-info">
                        <img src="/admin/Admin_WebTanaman/uploads/<?php echo $pecah['foto']; ?>" 
                             class="product-image" alt="<?php echo $pecah['nama_tanaman']; ?>">
                    </div>
                    <div class="product-details">
                        <h3><?php echo $pecah['nama_tanaman']; ?></h3>
                        <p class="product-description"><?php echo substr($pecah['deskripsi'] ?? '', 0, 50); ?>...</p>
                        <a href="alamat_pengiriman.php?source=individual_checkout&id_produk=<?php echo $id_produk; ?>&qty=<?php echo $jumlah; ?>" 
                           class="checkout-item-btn">
                            <i class="fas fa-shopping-bag"></i> Checkout Produk Ini
                        </a>
                    </div>
                    <div class="item-price">
                        <span class="harga" data-harga="<?php echo $pecah['harga']; ?>">
                            Rp <?php echo number_format($pecah['harga'], 0, ',', '.'); ?>
                        </span>
                    </div>
                    <div class="item-quantity">
                        <div class="quantity-control" data-idproduk="<?php echo $id_produk; ?>">
                            <button type="button" class="btn-minus" onclick="ubahJumlah(this, -1)">-</button>
                            <input type="text" name="jumlah" value="<?php echo $jumlah; ?>" readonly>
                            <button type="button" class="btn-plus" onclick="ubahJumlah(this, 1)">+</button>
                        </div>
                    </div>
                    <div class="item-subtotal subtotal">
                        Rp <?php echo number_format($subtotal, 0, ',', '.'); ?>
                    </div>
                    <div class="item-remove">
                        <a href="hapusK.php?id_produk=<?php echo $id_produk; ?>" class="btn-delete">
                            <i class="fas fa-trash"></i>
                        </a>
                    </div>
                </div>
                <?php } ?>
                
                <?php else: ?>
                <div class='empty-cart-message'>Keranjang belanja Anda kosong</div>
                <?php endif; ?>
                
                <div class="cart-actions">
                    <a href="produk.php" class="continue-shopping-btn">
                        <i class="fas fa-arrow-left"></i> Lanjutkan Belanja
                    </a>
                    <a href="hapus_keranjang.php" class="empty-cart-btn">
                        <i class="fas fa-trash"></i> Kosongkan Keranjang
                    </a>
                </div>
            </div>
            
            <div class="order-summary">
                <h2 class="summary-title">Ringkasan Pesanan</h2>
                
                <div class="summary-row">
                    <span>Subtotal (<span id="selectedItemsCount">0</span> item)</span>
                    <span id="selectedSubtotal">Rp 0</span>
                </div>
                
                <div class="summary-row">
                    <span>Diskon</span>
                    <span>- Rp 0</span>
                </div>
                
                <?php
                $biayaPengiriman = 25000;
                $totalBayar = $totalHarga + $biayaPengiriman;
                ?>
                
                <div class="summary-row">
                    <span>Estimasi Pengiriman</span>
                    <span>Rp <?php echo number_format($biayaPengiriman, 0, ',', '.'); ?></span>
                </div>
                
                <div class="summary-row total">
                    <span>Total</span>
                    <span id="selectedTotal">Rp <?php echo number_format($biayaPengiriman, 0, ',', '.'); ?></span>
                </div>
                
                <div class="promo-section">
                    <h3 class="promo-title">Kode Promo</h3>
                    <a href="diskon.php" class="promo-link">
                        <div class="promo-banner">
                            <i class="fas fa-ticket-alt"></i>
                            <span>Lihat Diskon Tersedia</span>
                            <i class="fas fa-chevron-right"></i>
                        </div>
                    </a>
                </div>
                
                <!-- Updated checkout button for selected items -->
                <button class="checkout-btn" id="checkoutBtn" onclick="checkoutSelected()" disabled>
                    Checkout Item Terpilih
                </button>
                
                <a href="<?php echo !empty($_SESSION['keranjang']) ? 'alamat_pengiriman.php?source=cart' : 'javascript:void(0)'; ?>" 
                   class="checkout-btn <?php echo empty($_SESSION['keranjang']) ? 'disabled' : ''; ?>" 
                   style="margin-top: 10px;">
                    Checkout Semua Itemas
                </a>
            </div>
        </div>
    </main>

    <!-- Footer remains the same -->
    <footer>
        <section class="feedback">
            <div class="container">
                <h2>Kirim kritik/saran untuk kami</h2>
                <p>Ceritakan kepada kami kritik dan/atau saran Anda</p>
                
                <div class="feedback-form">
                    <input type="text" placeholder="Masukkan kritik/saran">
                    <button type="submit">KIRIM</button>
                </div>
            </div>
        </section>
         
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
        // Global variables
        let selectedItems = [];
        const shippingCost = 25000;

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateSelection();
        });

        // Toggle select all functionality
        function toggleSelectAll() {
            const selectAllCheckbox = document.getElementById('selectAll');
            const productCheckboxes = document.querySelectorAll('.product-checkbox');
            
            productCheckboxes.forEach(checkbox => {
                checkbox.checked = selectAllCheckbox.checked;
                updateItemSelection(checkbox);
            });
            
            updateSelection();
        }

        // Update selection when individual checkbox changes
        function updateSelection() {
            const productCheckboxes = document.querySelectorAll('.product-checkbox');
            const selectAllCheckbox = document.getElementById('selectAll');
            
            selectedItems = [];
            let selectedCount = 0;
            let selectedSubtotal = 0;
            
            productCheckboxes.forEach(checkbox => {
                if (checkbox.checked) {
                    selectedItems.push(checkbox.value);
                    selectedCount++;
                    selectedSubtotal += parseFloat(checkbox.dataset.price);
                    updateItemSelection(checkbox);
                } else {
                    updateItemSelection(checkbox);
                }
            });
            
            // Update select all checkbox
            selectAllCheckbox.checked = selectedCount === productCheckboxes.length && selectedCount > 0;
            
            // Update UI
            updateSelectedActions(selectedCount);
            updateSummary(selectedCount, selectedSubtotal);
        }

        // Update individual item selection appearance
        function updateItemSelection(checkbox) {
            const cartItem = checkbox.closest('.cart-item');
            if (checkbox.checked) {
                cartItem.classList.add('selected');
            } else {
                cartItem.classList.remove('selected');
            }
        }

        // Update selected actions panel
        function updateSelectedActions(count) {
            const selectedActions = document.getElementById('selectedActions');
            const selectedCount = document.getElementById('selectedCount');
            
            if (count > 0) {
                selectedActions.classList.add('show');
                selectedCount.textContent = count + ' item dipilih';
            } else {
                selectedActions.classList.remove('show');
            }
        }

        // Update summary section
        function updateSummary(count, subtotal) {
            const selectedItemsCount = document.getElementById('selectedItemsCount');
            const selectedSubtotalEl = document.getElementById('selectedSubtotal');
            const selectedTotal = document.getElementById('selectedTotal');
            const checkoutBtn = document.getElementById('checkoutBtn');
            
            selectedItemsCount.textContent = count;
            selectedSubtotalEl.textContent = 'Rp ' + subtotal.toLocaleString('id-ID');
            
            const total = subtotal + (count > 0 ? shippingCost : 0);
            selectedTotal.textContent = 'Rp ' + total.toLocaleString('id-ID');
            
            // Enable/disable checkout button
            if (count > 0) {
                checkoutBtn.disabled = false;
                checkoutBtn.classList.remove('disabled');
            } else {
                checkoutBtn.disabled = true;
                checkoutBtn.classList.add('disabled');
            }
        }

        // Checkout selected items
        function checkoutSelected() {
            if (selectedItems.length === 0) {
                alert('Pilih setidaknya satu item untuk checkout');
                return;
            }
            
            // Create form with selected items
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'alamat_pengiriman.php';
            
            // Add source parameter
            const sourceInput = document.createElement('input');
            sourceInput.type = 'hidden';
            sourceInput.name = 'source';
            sourceInput.value = 'selected_items';
            form.appendChild(sourceInput);
            
            // Add selected items
            selectedItems.forEach(itemId => {
                const itemInput = document.createElement('input');
                itemInput.type = 'hidden';
                itemInput.name = 'selected_items[]';
                itemInput.value = itemId;
                form.appendChild(itemInput);
            });
            
            document.body.appendChild(form);
            form.submit();
        }

        // Delete selected items
        function deleteSelected() {
            if (selectedItems.length === 0) {
                alert('Pilih setidaknya satu item untuk dihapus');
                return;
            }
            
            if (confirm('Yakin ingin menghapus ' + selectedItems.length + ' item terpilih?')) {
                // Create form to delete selected items
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'hapus_terpilih.php';
                
                selectedItems.forEach(itemId => {
                    const itemInput = document.createElement('input');
                    itemInput.type = 'hidden';
                    itemInput.name = 'delete_items[]';
                    itemInput.value = itemId;
                    form.appendChild(itemInput);
                });
                
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Move to wishlist
        function moveToWishlist() {
            if (selectedItems.length === 0) {
                alert('Pilih setidaknya satu item untuk dipindah ke wishlist');
                return;
            }
            
            if (confirm('Pindahkan ' + selectedItems.length + ' item ke wishlist?')) {
                // Create form to move selected items to wishlist
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'pindah_wishlist.php';
                
                selectedItems.forEach(itemId => {
                    const itemInput = document.createElement('input');
                    itemInput.type = 'hidden';
                    itemInput.name = 'move_items[]';
                    itemInput.value = itemId;
                    form.appendChild(itemInput);
                });
                
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Update quantity function (modified to update selection)
        function ubahJumlah(btn, delta) {
            const container = btn.closest('.quantity-control');
            const input = container.querySelector('input[name="jumlah"]');
            const idProduk = container.getAttribute('data-idproduk');
            
            const itemContainer = btn.closest('.cart-item');
            const hargaEl = itemContainer.querySelector('.harga');
            const subtotalEl = itemContainer.querySelector('.subtotal');
            const checkbox = itemContainer.querySelector('.product-checkbox');

            const harga = parseInt(hargaEl.getAttribute('data-harga'));
            let jumlah = parseInt(input.value) || 0;

            jumlah += delta;
            if (jumlah < 1) jumlah = 1;
            
            input.value = jumlah;

            const subtotal = harga * jumlah;
            subtotalEl.textContent = "Rp " + subtotal.toLocaleString('id-ID');
            
            // Update checkbox data-price
            checkbox.dataset.price = subtotal;

            // Update both session and database
            fetch('update_keranjang.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'id_produk=' + idProduk + '&jumlah=' + jumlah
            })
            .then(response => response.text())
            .then(data => {
                console.log("Update berhasil:", data);
                // Update selection totals
                updateSelection();
            })
            .catch(error => {
                console.error("Gagal update:", error);
            });
        }
    </script>
</body>
</html>
<?php
session_start();
include "koneksi.php";

// Fungsi untuk mendapatkan data pesanan
function getOrderData($source = 'cart', $product_id = null, $quantity = 1) {
    global $koneksi;
    $orderItems = [];
    $totalHarga = 0;
    
    if ($source === 'cart') {
        // Ambil dari keranjang
        if (isset($_SESSION['keranjang']) && !empty($_SESSION['keranjang'])) {
            foreach ($_SESSION['keranjang'] as $id_produk => $jumlah) {
                if (empty($id_produk)) continue;
                
                $query = $koneksi->query("SELECT * FROM produk WHERE id_produk='$id_produk'");
                $produk = $query->fetch_assoc();
                
                if ($produk) {
                    $subtotal = $produk['harga'] * $jumlah;
                    $orderItems[] = [
                        'id_produk' => $id_produk,
                        'nama_tanaman' => $produk['nama_tanaman'],
                        'harga' => $produk['harga'],
                        'foto' => $produk['foto'],
                        'jumlah' => $jumlah,
                        'subtotal' => $subtotal
                    ];
                    $totalHarga += $subtotal;
                }
            }
        }
    } else if ($source === 'buy_now' && $product_id) {
        // Ambil dari beli sekarang
        $query = $koneksi->query("SELECT * FROM produk WHERE id_produk='$product_id'");
        $produk = $query->fetch_assoc();
        
        if ($produk) {
            $subtotal = $produk['harga'] * $quantity;
            $orderItems[] = [
                'id_produk' => $product_id,
                'nama_tanaman' => $produk['nama_tanaman'],
                'harga' => $produk['harga'],
                'foto' => $produk['foto'],
                'jumlah' => $quantity,
                'subtotal' => $subtotal
            ];
            $totalHarga = $subtotal;
        }
    }
    
    // Simpan data pesanan ke session untuk persistensi
    $_SESSION['order_data'] = [
        'items' => $orderItems,
        'subtotal' => $totalHarga,
        'source' => $source,
        'product_id' => $product_id,
        'quantity' => $quantity
    ];
    
    return [
        'items' => $orderItems,
        'subtotal' => $totalHarga
    ];
}

// Tentukan sumber data berdasarkan parameter
$source = isset($_GET['source']) ? $_GET['source'] : (isset($_POST['order_source']) ? $_POST['order_source'] : (isset($_SESSION['order_data']['source']) ? $_SESSION['order_data']['source'] : 'cart'));
$product_id = isset($_GET['id_produk']) ? $_GET['id_produk'] : (isset($_POST['product_id']) ? $_POST['product_id'] : (isset($_SESSION['order_data']['product_id']) ? $_SESSION['order_data']['product_id'] : null));
$quantity = isset($_GET['qty']) ? (int)$_GET['qty'] : (isset($_POST['quantity']) ? (int)$_POST['quantity'] : (isset($_SESSION['order_data']['quantity']) ? $_SESSION['order_data']['quantity'] : 1));

// Ambil data pesanan
$orderData = getOrderData($source, $product_id, $quantity);

// Fungsi untuk menghitung biaya pengiriman berdasarkan metode
function getShippingCost($method = 'jne') {
    $shippingCosts = [
        'jne' => 25000,
        'jnt' => 30000,
        'ninja' => 28000,
        'anteraja' => 26000
    ];
    
    return isset($shippingCosts[$method]) ? $shippingCosts[$method] : 25000;
}

// Fungsi untuk mendapatkan nama metode pengiriman
function getShippingName($method = 'jne') {
    $shippingNames = [
        'jne' => 'JNE Regular',
        'jnt' => 'J&T Express',
        'ninja' => 'Ninja Xpress',
        'anteraja' => 'AnterAja Regular'
    ];
    
    return isset($shippingNames[$method]) ? $shippingNames[$method] : 'JNE Regular';
}

// Tentukan metode pengiriman (default atau dari session)
$selectedShipping = isset($_SESSION['shipping_method']) ? $_SESSION['shipping_method'] : 'jne';
$shippingCost = getShippingCost($selectedShipping);
$shippingName = getShippingName($selectedShipping);

// Hitung total
$total = $orderData['subtotal'] + $shippingCost;

// Proses jika alamat dipilih
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pilih_alamat'])) {
    $selected_address_id = $_POST['alamat_id'];
    $_SESSION['alamat_terpilih'] = $selected_address_id;
    
    // Simpan data pesanan ke session
    $_SESSION['order_data']['shipping_address_id'] = $selected_address_id;
    $_SESSION['order_data']['shipping_method'] = $selectedShipping;
    $_SESSION['order_data']['shipping_cost'] = $shippingCost;
    $_SESSION['order_data']['total'] = $total;
    
    // Redirect ke halaman metode pengiriman
    header("Location: metode_pengiriman.php");
    exit;
}

$sql_alamat = "SELECT * FROM pengiriman";
$query_alamat = mysqli_query($koneksi, $sql_alamat);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alamat Pengiriman - Toko Tanaman</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .address-selection {
            margin-bottom: 20px;
        }
        
        .address-card {
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
        }
        
        .address-card:hover {
            border-color: #4CAF50;
            box-shadow: 0 2px 8px rgba(76, 175, 80, 0.2);
        }
        
        .address-card.selected {
            border-color: #4CAF50;
            background-color: #f8fff8;
        }
        
        .address-card.selected::before {
            content: '✓';
            position: absolute;
            top: 10px;
            right: 10px;
            background: #4CAF50;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
        }
        
        .address-form {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
            display: none;
        }
        
        .address-form.show {
            display: block;
        }
        
        .form-toggle {
            background: #4CAF50;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            margin-bottom: 15px;
        }
        
        .alert {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .badge-primary {
            background: #4CAF50;
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 12px;
            margin-left: 10px;
        }
        
        .address-actions {
            margin-top: 10px;
        }
        
        .address-actions .btn {
            margin-right: 5px;
        }
        
        .use-address-btn {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 10px;
            display: block;
            width: 100%;
            text-align: center;
            font-weight: bold;
        }
        
        .use-address-btn:hover {
            background-color: #45a049;
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
                <a href="profil.php"><i class="fas fa-user"></i></a>
            </div>
        </div>
    </header>

    <main class="checkout-section">
        <div class="container">
            <div class="checkout-steps">
                <div class="step active">
                    <div class="step-number">1</div>
                    <div class="step-label">Alamat</div>
                </div>
                <div class="step">
                    <div class="step-number">2</div>
                    <div class="step-label">Pengiriman</div>
                </div>
                <div class="step">
                    <div class="step-number">3</div>
                    <div class="step-label">Pembayaran</div>
                </div>
            </div>
            
            <div class="checkout-content">
                <div class="checkout-form">
                    <h2>Pilih Alamat Pengiriman</h2>
                    
                    <?php if(isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <?= $_SESSION['success_message']; ?>
                        <?php unset($_SESSION['success_message']); ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if(isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <?= $_SESSION['error_message']; ?>
                        <?php unset($_SESSION['error_message']); ?>
                    </div>
                    <?php endif; ?>
                    
                     <div class="address-list">
                        <?php if ($query_alamat && mysqli_num_rows($query_alamat) > 0) :  ?>
                            <?php while($alamat = mysqli_fetch_assoc($query_alamat)): ?>
                            <form method="POST" class="address-form-select" id="form_<?= $alamat['id_pengiriman'] ?>">
                                <input type="hidden" name="alamat_id" value="<?= $alamat['id_pengiriman'] ?>">
                                <input type="hidden" name="order_source" value="<?= htmlspecialchars($source) ?>">
                                <?php if ($source === 'buy_now'): ?>
                                    <input type="hidden" name="product_id" value="<?= htmlspecialchars($product_id) ?>">
                                    <input type="hidden" name="quantity" value="<?= $quantity ?>">
                                <?php endif; ?>
                                <input type="hidden" name="pilih_alamat" value="1">
                                
                                <div class="address-card <?= (isset($_SESSION['alamat_terpilih']) && $_SESSION['alamat_terpilih'] == $alamat['id_pengiriman']) ? 'selected' : '' ?>" 
                                     onclick="selectAndSubmit(<?= $alamat['id_pengiriman'] ?>)">
                                    <h3>
                                        <?= htmlspecialchars($alamat['label_alamat']) ?>
                                    </h3>
                                    
                                    <p class="address-name"><?= htmlspecialchars($alamat['nama_penerima']) ?></p>
                                    <p class="address-phone"><?= htmlspecialchars($alamat['no_telepon']) ?></p>
                                    <p class="address-detail">
                                        <?= htmlspecialchars($alamat['alamat_lengkap'] . ', ' . $alamat['kecamatan'] . ', ' . $alamat['kota'] . ', ' . $alamat['provinsi']) ?>
                                    </p>
                                    
                                    <div class="address-actions" onclick="event.stopPropagation();">
                                        <a href="edit_alamat.php?id=<?= $alamat['id_pengiriman'] ?>" class="btn btn-outline btn-sm">
                                            <i class="fas fa-edit"></i> Ubah
                                        </a>
                                        <a href="hapus_alamat.php?id_pengiriman=<?= $alamat['id_pengiriman'] ?>" 
                                           class="btn btn-outline btn-sm btn-danger"
                                           onclick="return confirm('Yakin ingin menghapus alamat ini?')">
                                            <i class="fas fa-trash"></i> Hapus
                                        </a>
                                    </div>
                                    
                                    <button type="submit" name="pilih_alamat" class="use-address-btn">
                                        Gunakan Alamat Ini
                                    </button>
                                </div>
                            </form>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-map-marker-alt"></i>
                                <h3>Belum Ada Alamat Tersimpan</h3>
                                <p>Tambahkan alamat untuk mempermudah proses checkout</p>
                            </div>
                        <?php endif; ?>
                        </div>
                        <button type="button" class="form-toggle" onclick="toggleAddressForm()">
                            <i class="fas fa-plus"></i> Tambah Alamat Baru
                        </button>
                       
                        <div class="address-form" id="addressForm">
                        <form action="prosesT_pengiriman.php" method="POST">
                            <div class="form-grid">
                                <div class="form-group full-width">
                                    <label for="label_alamat" class="required">Label Alamat</label>
                                    <input type="text" id="label_alamat" name="label_alamat" 
                                           placeholder="Contoh: Rumah, Kantor, Kos" required>
                                    <div class="field-help">Berikan nama untuk alamat ini agar mudah diingat</div>
                                </div>
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="nama_penerima" class="required">Nama Penerima</label>
                                        <input type="text" id="nama_penerima" name="nama_penerima" 
                                               placeholder="Nama lengkap penerima" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="no_telepon" class="required">Nomor Telepon</label>
                                        <input type="tel" id="no_telepon" name="no_telepon" 
                                               placeholder="08xxxxxxxxxx" required>
                                        <div class="field-help">Nomor yang dapat dihubungi kurir</div>
                                    </div>
                                </div>
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="provinsi" class="required">Provinsi</label>
                                        <select id="provinsi" name="provinsi" required>
                                            <option value="">Pilih Provinsi</option>
                                            <option value="DKI Jakarta">DKI Jakarta</option>
                                            <option value="Jawa Barat">Jawa Barat</option>
                                            <option value="Jawa Tengah">Jawa Tengah</option>
                                            <option value="Jawa Timur">Jawa Timur</option>
                                            <option value="Yogyakarta">D.I. Yogyakarta</option>
                                            <option value="Banten">Banten</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="kota" class="required">Kota/Kabupaten</label>
                                        <select id="kota" name="kota" required>
                                            <option value="">Pilih Kota/Kabupaten</option>
                                             <option value="Yogyakarta">D.I. Yogyakarta</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="kecamatan" class="required">Kecamatan</label>
                                        <select id="kecamatan" name="kecamatan" required>
                                            <option value="">Pilih Kecamatan</option>
                                             <option value="Yogyakarta">D.I. Yogyakarta</option>
                                        </select>
                                    </div>
                                 
                                </div>
                                
                                <div class="form-group full-width">
                                    <label for="alamat_lengkap" class="required">Alamat Lengkap</label>
                                    <textarea id="alamat_lengkap" name="alamat_lengkap" rows="4" 
                                              placeholder="Nama jalan, nomor rumah, RT/RW, patokan, dll" required></textarea>
                                    <div class="char-counter"><span id="alamat-count">0</span>/200 karakter</div>
                                </div>
                                
                                <div class="form-check">
                                    <input type="checkbox" id="is_primary" name="is_primary" class="form-check-input">
                                    <label for="is_primary" class="form-check-label">Jadikan sebagai alamat utama</label>
                                </div>
                                
                                <!-- Hidden fields for order data -->
                                <input type="hidden" name="order_source" value="<?= htmlspecialchars($source) ?>">
                                <?php if ($source === 'buy_now'): ?>
                                    <input type="hidden" name="product_id" value="<?= htmlspecialchars($product_id) ?>">
                                    <input type="hidden" name="quantity" value="<?= $quantity ?>">
                                <?php endif; ?>
                            </div>
                            
                            <div class="form-actions">
                                <button type="button" class="btn btn-outline" onclick="toggleAddressForm()">
                                    <i class="fas fa-times"></i> Batal
                                </button>
                                <button type="submit" class="btn btn-primary" id="submit-btn">
                                    <i class="fas fa-save"></i> Simpan Alamat
                                </button>
                            </div>
                        </form>
                    </div>
                    </div>
                

                
                <div class="order-summary">
                    <h2 class="summary-title">Ringkasan Pesanan</h2>
                    
                    <?php if (!empty($orderData['items'])): ?>
                        <div class="summary-items">
                            <?php foreach ($orderData['items'] as $item): ?>
                            <div class="summary-item">
                                <img src="admin/Admin_WebTanaman/uploads<?= htmlspecialchars($item['foto']) ?>" alt="<?= htmlspecialchars($item['nama_tanaman']) ?>" class="item-image">
                                <div class="item-info">
                                    <h3><?= htmlspecialchars($item['nama_tanaman']) ?></h3>
                                    <p><?= $item['jumlah'] ?> x Rp<?= number_format($item['harga'], 0, ',', '.') ?></p>
                                </div>
                                <div class="item-price">Rp<?= number_format($item['subtotal'], 0, ',', '.') ?></div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="summary-row">
                            <span>Subtotal</span>
                            <span>Rp<?= number_format($orderData['subtotal'], 0, ',', '.') ?></span>
                        </div>
                        
                        <div class="summary-row total">
                            <span>Total</span>
                            <span>Rp<?= number_format($total, 0, ',', '.') ?></span>
                        </div>
                        
                        <div style="text-align: center; margin-top: 20px;">
                            <p><i class="fas fa-info-circle"></i> Pilih alamat pengiriman terlebih dahulu untuk melanjutkan</p>
                        </div>
                    <?php else: ?>
                        <div class="empty-order">
                            <p>Tidak ada item dalam pesanan</p>
                            <a href="produk.php" class="btn btn-primary">Belanja Sekarang</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

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
        function selectAndSubmit(id) {
            // Remove selected class from all cards
            document.querySelectorAll('.address-card').forEach(card => {
                card.classList.remove('selected');
            });
            
            // Add selected class to clicked card
            event.currentTarget.classList.add('selected');
            
            // Submit the form automatically
            document.getElementById('form_' + id).submit();
        }
        
        function toggleAddressForm() {
            const form = document.getElementById('addressForm');
            form.classList.toggle('show');
        }
        
        // Character counter for alamat_lengkap
        document.addEventListener('DOMContentLoaded', function() {
            const alamatTextarea = document.getElementById('alamat_lengkap');
            const alamatCount = document.getElementById('alamat-count');
            
            if (alamatTextarea && alamatCount) {
                alamatTextarea.addEventListener('input', function() {
                    alamatCount.textContent = this.value.length;
                });
            }
            
            // Mark selected address if exists in session
            <?php if(isset($_SESSION['alamat_terpilih'])): ?>
            const selectedAddressId = <?= $_SESSION['alamat_terpilih'] ?>;
            const selectedCard = document.querySelector(.address-card[data-id="${selectedAddressId}"]);
            if (selectedCard) {
                selectedCard.classList.add('selected');
            }
            <?php endif; ?>
        });
    </script>
</body>
</html>
<?php
session_start();
include "koneksi.php";

// Periksa apakah user sudah login
if (!isset($_SESSION['username'])) {
    die("Akses ditolak. Silakan login terlebih dahulu.");
}

// Fungsi untuk mendapatkan ID pelanggan yang valid
function getValidCustomerId($koneksi) {
    $possible_session_keys = ['id_pelanggan', 'user_id', 'customer_id', 'pelanggan_id'];
    
    foreach ($possible_session_keys as $key) {
        if (isset($_SESSION[$key]) && !empty($_SESSION[$key])) {
            $id = $_SESSION[$key];
            
            $query = $koneksi->query("SELECT id_pelanggan FROM pelanggan WHERE id_pelanggan = '$id'");
            if ($query && $query->num_rows > 0) {
                return $id;
            }
        }
    }
    
    if (isset($_SESSION['username'])) {
        $username = $_SESSION['username'];
        $query = $koneksi->query("SELECT id_pelanggan FROM pelanggan WHERE username = '$username' OR email = '$username'");
        if ($query && $query->num_rows > 0) {
            $row = $query->fetch_assoc();
            return $row['id_pelanggan'];
        }
    }
    
    return null;
}

// Fungsi untuk mendapatkan nama metode pembayaran
function getPaymentMethodName($method) {
    $paymentNames = [
        'bca' => 'Transfer Bank BCA',
        'bni' => 'Transfer Bank BNI', 
        'mandiri' => 'Transfer Bank Mandiri',
        'gopay' => 'GoPay',
        'ovo' => 'OVO',
        'dana' => 'DANA'
    ];
    
    return isset($paymentNames[$method]) ? $paymentNames[$method] : 'Transfer Bank';
}

// Dapatkan ID pelanggan yang valid
$id_pelanggan = getValidCustomerId($koneksi);

if (!$id_pelanggan) {
    die("Error: ID pelanggan tidak ditemukan. Silakan login ulang.");
}

// Inisialisasi variabel
$pesanan_data = null;
$error_message = '';
$success_message = '';

// Ambil parameter dari URL
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : null;
$nomor_pesanan_param = isset($_GET['nomor_pesanan']) ? $_GET['nomor_pesanan'] : null;
$new_order = isset($_GET['new_order']) ? $_GET['new_order'] : null;

// Jika ini pesanan baru, ambil dari session current_order
if ($new_order && isset($_SESSION['current_order'])) {
    $current_order = $_SESSION['current_order'];
    
    // Validasi pesanan di database
    $sql_validate = "
        SELECT 
            p.id_pesanan,
            p.nomor_pesanan,
            p.tgl_pesanan,
            p.total as subtotal,
            p.status_pesanan
        FROM pesanan p
        WHERE p.id_pesanan = ? AND p.id_pelanggan = ?
    ";
    
    $stmt_validate = $koneksi->prepare($sql_validate);
    if ($stmt_validate) {
        $stmt_validate->bind_param("ii", $current_order['id_pesanan'], $id_pelanggan);
        $stmt_validate->execute();
        $result_validate = $stmt_validate->get_result();
        
        if ($result_validate->num_rows > 0) {
            $pesanan_data = $result_validate->fetch_assoc();
        }
    }
}

// Jika tidak ada dari session, coba ambil berdasarkan parameter
if (!$pesanan_data && ($order_id || $nomor_pesanan_param)) {
    $sql_pesanan = "
        SELECT 
            p.id_pesanan,
            p.nomor_pesanan,
            p.tgl_pesanan,
            p.total as subtotal,
            p.status_pesanan
        FROM pesanan p
        WHERE p.id_pelanggan = ? 
        AND (p.id_pesanan = ? OR p.nomor_pesanan = ?)
        ORDER BY p.tgl_pesanan DESC
        LIMIT 1
    ";
    
    $stmt = $koneksi->prepare($sql_pesanan);
    if ($stmt) {
        $stmt->bind_param("iis", $id_pelanggan, $order_id, $nomor_pesanan_param);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $pesanan_data = $result->fetch_assoc();
        }
    }
}

// Jika masih tidak ada, ambil pesanan terakhir
if (!$pesanan_data) {
    $sql_last = "
        SELECT 
            p.id_pesanan,
            p.nomor_pesanan,
            p.tgl_pesanan,
            p.total as subtotal,
            p.status_pesanan
        FROM pesanan p
        WHERE p.id_pelanggan = ?
        ORDER BY p.tgl_pesanan DESC
        LIMIT 1
    ";
    
    $stmt_last = $koneksi->prepare($sql_last);
    if ($stmt_last) {
        $stmt_last->bind_param("i", $id_pelanggan);
        $stmt_last->execute();
        $result_last = $stmt_last->get_result();
        
        if ($result_last->num_rows > 0) {
            $pesanan_data = $result_last->fetch_assoc();
        }
    }
}

// Cek pembayaran dengan lebih ketat
$pembayaran_exists = false;
$show_payment_form = true;

if ($pesanan_data) {
    // Cek apakah sudah ada pembayaran di tabel pembayaran
    $sql_check_payment = "SELECT id_pembayaran FROM pembayaran WHERE id_pesanan = ?";
    $stmt_check = $koneksi->prepare($sql_check_payment);
    if ($stmt_check) {
        $stmt_check->bind_param("i", $pesanan_data['id_pesanan']);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
        
        if ($result_check->num_rows > 0) {
            $pembayaran_exists = true;
            $show_payment_form = false;
        }
    }
    
    // Jika status pesanan sudah selesai atau dibatalkan, jangan tampilkan form
    if (in_array($pesanan_data['status_pesanan'], ['selesai', 'dibatalkan'])) {
        $show_payment_form = false;
        $pembayaran_exists = true;
    }
    
    // Jika status pesanan masih 'diproses' dan belum ada pembayaran, tampilkan form
    if ($pesanan_data['status_pesanan'] === 'diproses' && !$pembayaran_exists) {
        $show_payment_form = true;
    }
}

// PERBAIKAN UTAMA: Proses form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['konfirmasi_pembayaran'])) {
    if (!$pesanan_data) {
        $error_message = "Data pesanan tidak ditemukan.";
    } elseif ($pembayaran_exists) {
        $error_message = "Pembayaran untuk pesanan ini sudah pernah dikonfirmasi.";
    } else {
        try {
            $koneksi->begin_transaction();
            
            // Ambil data dari form
            $tgl_pembayaran = $_POST['tgl_pembayaran_hidden'] ?? date('Y-m-d');
            $waktu_bayar = $_POST['waktu_bayar_hidden'] ?? date('H:i:s');
            $catatan = $_POST['catatan'] ?? '';
            
            // Validasi ID pesanan
            if (!$pesanan_data['id_pesanan'] || $pesanan_data['id_pesanan'] <= 0) {
                throw new Exception("ID pesanan tidak valid.");
            }
            
            // Handle file upload
            $file_image = '';
            if (isset($_FILES['file_image']) && $_FILES['file_image']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = 'admin/pembayaran/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                
                $file_extension = strtolower(pathinfo($_FILES['file_image']['name'], PATHINFO_EXTENSION));
                $allowed_extensions = ['jpg', 'jpeg', 'png', 'pdf'];
                
                if (!in_array($file_extension, $allowed_extensions)) {
                    throw new Exception("Format file tidak didukung. Gunakan JPG, PNG, atau PDF.");
                }
                
                if ($_FILES['file_image']['size'] > 2 * 1024 * 1024) {
                    throw new Exception("Ukuran file terlalu besar. Maksimal 2MB.");
                }
                
                $file_name = 'payment_' . $pesanan_data['id_pesanan'] . '_' . time() . '.' . $file_extension;
                $file_path = $upload_dir . $file_name;
                
                if (move_uploaded_file($_FILES['file_image']['tmp_name'], $file_path)) {
                    $file_image = $file_name;
                } else {
                    throw new Exception("Gagal mengupload file bukti pembayaran.");
                }
            } else {
                throw new Exception("Bukti pembayaran harus diupload.");
            }
            
            // Insert pembayaran
            $sql_insert_payment = "
                INSERT INTO pembayaran (id_pesanan, tgl_bayar, waktu_bayar, file_image, catatan) 
                VALUES (?, ?, ?, ?, ?)
            ";
            
            $stmt_insert = $koneksi->prepare($sql_insert_payment);
            if (!$stmt_insert) {
                throw new Exception("Prepare statement gagal: " . $koneksi->error);
            }
            
            $stmt_insert->bind_param("issss", 
                $pesanan_data['id_pesanan'], 
                $tgl_pembayaran, 
                $waktu_bayar, 
                $file_image, 
                $catatan
            );
            
            if (!$stmt_insert->execute()) {
                throw new Exception("Gagal menyimpan konfirmasi pembayaran: " . $stmt_insert->error);
            }
            
            // PERBAIKAN: Update status pesanan menjadi 'menunggu_verifikasi'
            $sql_update_status = "UPDATE pesanan SET status_pesanan = 'menunggu_verifikasi' WHERE id_pesanan = ?";
            $stmt_update = $koneksi->prepare($sql_update_status);
            if (!$stmt_update) {
                throw new Exception("Gagal mempersiapkan update status: " . $koneksi->error);
            }
            
            $stmt_update->bind_param("i", $pesanan_data['id_pesanan']);
            if (!$stmt_update->execute()) {
                throw new Exception("Gagal mengupdate status pesanan: " . $stmt_update->error);
            }
            
            // Verifikasi bahwa status berhasil diupdate
            $sql_verify = "SELECT status_pesanan FROM pesanan WHERE id_pesanan = ?";
            $stmt_verify = $koneksi->prepare($sql_verify);
            $stmt_verify->bind_param("i", $pesanan_data['id_pesanan']);
            $stmt_verify->execute();
            $result_verify = $stmt_verify->get_result();
            $updated_status = $result_verify->fetch_assoc();
            
            if ($updated_status['status_pesanan'] !== 'menunggu_verifikasi') {
                throw new Exception("Status pesanan gagal diupdate.");
            }
            
            $koneksi->commit();
            
            // Update data pesanan lokal
            $pesanan_data['status_pesanan'] = 'menunggu_verifikasi';
            
            // Bersihkan session current_order setelah pembayaran berhasil
            unset($_SESSION['current_order']);
            
            $success_message = "Konfirmasi pembayaran berhasil dikirim! Status pesanan Anda telah diubah menjadi 'Menunggu Verifikasi'. Tim kami akan memverifikasi pembayaran Anda dalam 1x24 jam.";
            
            // Update status
            $pembayaran_exists = true;
            $show_payment_form = false;
            
        } catch (Exception $e) {
            $koneksi->rollback();
            $error_message = $e->getMessage();
            error_log("Payment confirmation error: " . $e->getMessage());
        }
    }
}

// Set default values untuk tanggal dan waktu
$default_date = date('Y-m-d');
$default_time = date('H:i');
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Pembayaran - Toko Tanaman</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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

    <main class="confirmation-section">
        <div class="container">
            <div class="confirmation-header">
                <h1>Konfirmasi Pembayaran</h1>
                <?php if ($pesanan_data): ?>
                    <p>Silakan konfirmasi pembayaran Anda untuk pesanan <strong><?= htmlspecialchars($pesanan_data['nomor_pesanan']) ?></strong></p>
                <?php else: ?>
                    <p>Mencari data pesanan...</p>
                <?php endif; ?>
            </div>
            
            <?php if (!empty($success_message)): ?>
                <div class="success-message" style="background-color: #d4edda; color: #155724; padding: 20px; border-radius: 8px; margin: 20px 0; border: 1px solid #c3e6cb;">
                    <div style="display: flex; align-items: center; margin-bottom: 15px;">
                        <i class="fas fa-check-circle" style="font-size: 24px; margin-right: 10px;"></i>
                        <h3 style="margin: 0; color: #155724;">Pembayaran Berhasil Dikonfirmasi!</h3>
                    </div>
                    <p style="margin-bottom: 15px;"><?= htmlspecialchars($success_message) ?></p>
                    <div style="background-color: #b8dabc; padding: 15px; border-radius: 5px; margin-bottom: 15px;">
                        <p style="margin: 0; font-weight: 600;"><i class="fas fa-info-circle"></i> Status Pesanan: <span style="color: #7b1fa2;">MENUNGGU VERIFIKASI</span></p>
                    </div>
                    <p><a href="riwayat_pesanan.php" class="btn btn-primary" style="display: inline-block; padding: 10px 20px; background-color: #28a745; color: white; text-decoration: none; border-radius: 5px;">Lihat Riwayat Pesanan</a></p>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($error_message)): ?>
                <div class="error-message" style="background-color: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 20px 0;">
                    <p><strong>Error:</strong> <?= htmlspecialchars($error_message) ?></p>
                </div>
            <?php endif; ?>
            
            <?php if ($pesanan_data): ?>
            <div class="confirmation-content">
                <div class="confirmation-form">
                    <div class="form-card">
                        <h2>Detail Pembayaran</h2>
                        <div class="payment-details">
                            <div class="detail-row">
                                <span>Nomor Pesanan</span>
                                <span><?= htmlspecialchars($pesanan_data['nomor_pesanan']) ?></span>
                            </div>
                            <div class="detail-row">
                                <span>Tanggal Pesanan</span>
                                <span><?= date('d M Y, H:i', strtotime($pesanan_data['tgl_pesanan'])) ?> WIB</span>
                            </div>
                            <div class="detail-row">
                                <span>Total Pembayaran</span>
                                <span>Rp<?= number_format($pesanan_data['subtotal'], 0, ',', '.') ?></span>
                            </div>
                            <div class="detail-row">
                                <span>Status Pesanan</span>
                                <span style="font-weight: 600; color: <?= $pesanan_data['status_pesanan'] === 'menunggu_verifikasi' ? '#7b1fa2' : '#666' ?>;">
                                    <?= $pesanan_data['status_pesanan'] === 'menunggu_verifikasi' ? 'MENUNGGU VERIFIKASI' : strtoupper($pesanan_data['status_pesanan']) ?>
                                </span>
                            </div>
                        </div>
                        
                        <?php if ($show_payment_form && !$success_message): ?>
                        <h3>Silakan isi form konfirmasi pembayaran di bawah ini:</h3>
                        <form action="" method="post" enctype="multipart/form-data">
                            <input type="hidden" name="tgl_pembayaran_hidden" value="<?= $default_date ?>">
                            <input type="hidden" name="waktu_bayar_hidden" value="<?= $default_time ?>">
                            
                            <div class="form-group">
                                <label for="tgl_pembayaran">Tanggal Pembayaran *</label>
                                <input type="date" id="tgl_pembayaran" name="tgl_pembayaran" 
                                       value="<?= $default_date ?>" disabled>
                                <small>Tanggal otomatis diset ke hari ini</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="waktu_bayar">Waktu Pembayaran *</label>
                                <input type="time" id="waktu_bayar" name="waktu_bayar" 
                                       value="<?= $default_time ?>" disabled>
                                <small>Waktu otomatis diset ke waktu sekarang</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="payment-amount">Jumlah Pembayaran</label>
                                <input type="text" id="payment-amount" name="payment-amount"
                                       value="Rp<?= number_format($pesanan_data['subtotal'], 0, ',', '.') ?>" readonly>
                            </div>
                            
                            <div class="form-group">
                                <label for="file_image">Bukti Pembayaran *</label>
                                <div class="file-upload">
                                    <input type="file" id="file_image" name="file_image" class="file-input" accept=".jpg,.jpeg,.png,.pdf" required>
                                    <label for="file_image" class="file-label">
                                        <i class="fas fa-upload"></i> Pilih File
                                    </label>
                                    <span class="file-name">Belum ada file dipilih</span>
                                </div>
                                <p class="file-help">Format yang diterima: JPG, PNG, PDF. Maksimal 2MB.</p>
                            </div>
                            
                            <div class="form-group">
                                <label for="catatan">Catatan (Opsional)</label>
                                <textarea id="catatan" name="catatan" rows="3" placeholder="Tambahkan catatan jika diperlukan..."></textarea>
                            </div>
                            
                            <div class="form-buttons">
                                <a href="riwayat_pesanan.php" class="btn btn-outline">Kembali</a>
                                <button type="submit" name="konfirmasi_pembayaran" class="btn btn-primary">Konfirmasi Pembayaran</button>
                            </div>
                        </form>
                        
                        <?php else: ?>
                            <div class="payment-confirmed">
                                <p><i class="fas fa-check-circle"></i> 
                                <?php if ($pesanan_data['status_pesanan'] === 'selesai'): ?>
                                    Pesanan ini sudah selesai.
                                <?php elseif ($pesanan_data['status_pesanan'] === 'dibatalkan'): ?>
                                    Pesanan ini telah dibatalkan.
                                <?php elseif ($pesanan_data['status_pesanan'] === 'menunggu_verifikasi'): ?>
                                    <span style="color: #7b1fa2; font-weight: 600;">Pembayaran untuk pesanan ini sudah dikonfirmasi dan sedang menunggu verifikasi dari admin.</span>
                                <?php elseif ($pesanan_data['status_pesanan'] === 'diverifikasi'): ?>
                                    Pembayaran sudah diverifikasi dan pesanan sedang diproses.
                                <?php else: ?>
                                    Pembayaran untuk pesanan ini sudah dikonfirmasi sebelumnya.
                                <?php endif; ?>
                                </p>
                                <a href="riwayat_pesanan.php" class="btn btn-primary">Lihat Riwayat Pesanan</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="confirmation-info">
                    <div class="info-card">
                        <h2>Informasi Pembayaran</h2>
                        <div class="bank-info">
                            <img src="images/bca.jpg" alt="BCA">
                            <div>
                                <h3>Bank BCA</h3>
                                <p>Nomor Rekening: 1234567890</p>
                                <p>Atas Nama: Toko Tanaman Indonesia</p>
                            </div>
                        </div>
                        
                        <div class="payment-instructions">
                            <h3>Petunjuk Konfirmasi</h3>
                            <ol>
                                <li>Pastikan Anda telah melakukan pembayaran sesuai dengan total yang tertera.</li>
                                <li>Isi formulir konfirmasi dengan data yang benar.</li>
                                <li>Unggah bukti pembayaran (screenshot atau foto struk).</li>
                                <li>Klik tombol "Konfirmasi Pembayaran".</li>
                                <li>Status pesanan akan berubah menjadi "Menunggu Verifikasi".</li>
                                <li>Tim kami akan memverifikasi pembayaran Anda dalam 1x24 jam.</li>
                            </ol>
                        </div>
                        
                        <div class="help-contact">
                            <h3>Butuh Bantuan?</h3>
                            <p>Jika Anda mengalami kesulitan dalam melakukan konfirmasi pembayaran, silakan hubungi customer service kami:</p>
                            <p><i class="fas fa-phone"></i> 0812-3456-7890</p>
                            <p><i class="fab fa-whatsapp"></i> 0812-3456-7890</p>
                            <p><i class="fas fa-envelope"></i> cs@tokotanaman.com</p>
                        </div>
                    </div>
                </div>
            </div>
            <?php else: ?>
                <div class="no-order-found">
                    <div class="error-message" style="background-color: #f8d7da; color: #721c24; padding: 20px; border-radius: 5px; text-align: center;">
                        <h3>Tidak Ada Pesanan Ditemukan</h3>
                        <p>Belum ada pesanan yang dapat dikonfirmasi pembayarannya.</p>
                        <div style="margin-top: 20px;">
                            <a href="keranjang.php" class="btn btn-primary">Buat Pesanan Baru</a>
                            <a href="riwayat_pesanan.php" class="btn btn-outline">Lihat Riwayat</a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-logo">
                    <img src="images/logo.png" alt="Toko Tanaman">
                    <p>Toko tanaman hias terlengkap dan terpercaya</p>
                </div>
                <div class="footer-links">
                    <h3 class="footer-title">Tautan Cepat</h3>
                    <ul>
                        <li><a href="index.php">Beranda</a></li>
                        <li><a href="produk.php">Produk</a></li>
                        <li><a href="tentang_kami.php">Tentang Kami</a></li>
                    </ul>
                </div>
                <div class="footer-contact">
                    <h3 class="footer-title">Kontak Kami</h3>
                    <p><i class="fas fa-map-marker-alt"></i> Jl. Tanaman Indah No. 123, Jakarta</p>
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
        // File upload preview
        const fileInput = document.getElementById('file_image');
        const fileName = document.querySelector('.file-name');
        
        if (fileInput && fileName) {
            fileInput.addEventListener('change', function(e) {
                const name = e.target.files[0] ? e.target.files[0].name : 'Belum ada file dipilih';
                fileName.textContent = name;
            });
        }
        
        // Update hidden inputs saat nilai berubah
        const dateInput = document.getElementById('tgl_pembayaran');
        const timeInput = document.getElementById('waktu_bayar');
        const hiddenDateInput = document.querySelector('input[name="tgl_pembayaran_hidden"]');
        const hiddenTimeInput = document.querySelector('input[name="waktu_bayar_hidden"]');
        
        if (dateInput && hiddenDateInput) {
            dateInput.addEventListener('change', function() {
                hiddenDateInput.value = this.value;
            });
        }
        
        if (timeInput && hiddenTimeInput) {
            timeInput.addEventListener('change', function() {
                hiddenTimeInput.value = this.value;
            });
        }
        
        // Set waktu real-time setiap detik
        function updateDateTime() {
            const now = new Date();
            const dateString = now.getFullYear() + '-' + 
                              String(now.getMonth() + 1).padStart(2, '0') + '-' + 
                              String(now.getDate()).padStart(2, '0');
            const timeString = String(now.getHours()).padStart(2, '0') + ':' + 
                              String(now.getMinutes()).padStart(2, '0');
            
            if (dateInput) dateInput.value = dateString;
            if (timeInput) timeInput.value = timeString;
            if (hiddenDateInput) hiddenDateInput.value = dateString;
            if (hiddenTimeInput) hiddenTimeInput.value = timeString;
        }
        
        // Update setiap detik
        updateDateTime();
        setInterval(updateDateTime, 1000);
    </script>
</body>
</html>

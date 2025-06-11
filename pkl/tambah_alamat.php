<?php
session_start();
include "koneksi.php";
$id_pelanggan = $_SESSION['id_pelanggan'] ?? '';

// Jika ada data yang dikirim dari form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $label_alamat = mysqli_real_escape_string($koneksi, $_POST['label_alamat']);
    $nama_penerima = mysqli_real_escape_string($koneksi, $_POST['nama_penerima']);
    $no_telepon = mysqli_real_escape_string($koneksi, $_POST['no_telepon']);
    $provinsi = mysqli_real_escape_string($koneksi, $_POST['provinsi']);
    $kota = mysqli_real_escape_string($koneksi, $_POST['kota']);
    $kecamatan = mysqli_real_escape_string($koneksi, $_POST['kecamatan']);
    $kode_pos = mysqli_real_escape_string($koneksi, $_POST['kode_pos']);
    $alamat_lengkap = mysqli_real_escape_string($koneksi, $_POST['alamat_lengkap']);
    $is_primary = isset($_POST['is_primary']) ? 1 : 0;
    
    // Jika dijadikan alamat utama, update alamat lain menjadi tidak utama
    if ($is_primary) {
        $update_sql = "UPDATE pengiriman SET is_primary = 0 WHERE id_pelanggan = '$id_pelanggan'";
        mysqli_query($koneksi, $update_sql);
    }
    
    // Insert alamat baru
    $insert_sql = "INSERT INTO pengiriman(id_pelanggan, label_alamat, nama_penerima, no_telepon, provinsi, kota, kecamatan, kode_pos, alamat_lengkap, is_primary) 
                   VALUES ('$id_pelanggan', '$label_alamat', '$nama_penerima', '$no_telepon', '$provinsi', '$kota', '$kecamatan', '$kode_pos', '$alamat_lengkap', '$is_primary')";
    
    if (mysqli_query($koneksi, $insert_sql)) {
        $_SESSION['success_message'] = "Alamat berhasil ditambahkan!";
        header("Location: alamat_tersimpan.php");
        exit();
    } else {
        $_SESSION['error_message'] = "Terjadi kesalahan saat menambahkan alamat.";
    }
}

$username = $_SESSION['username'];
$sql = "SELECT * FROM pelanggan WHERE username= '$username'";
$query = mysqli_query($koneksi, $sql);
$pelanggan = mysqli_fetch_assoc($query);

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Alamat - Toko Tanaman</title>
    <link rel="stylesheet" href="css/tambah_alamat.css">
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
                <a href="profil.php" class="active"><i class="fas fa-user"></i></a>
            </div>
        </div>
    </header>

    <main class="profile-section">
        <div class="container">
            <div class="profile-container">
                <div class="profile-sidebar">
                    <div class="profile-header">
                        <div class="profile-avatar">
                            <img src="<?= !empty($pelanggan['foto_profil']) ? 'images/profiles/'.$pelanggan['foto_profil'] : 'images/user.jpg' ?>" alt="<?=$pelanggan['username']?>">
                        </div>
                        <div class="profile-info">
                            <h2><?=$pelanggan['username']?></h2>
                            <p><?=$pelanggan['email']?></p>
                        </div>
                    </div>
                    <div class="profile-nav">
                        <ul>
                            <li>
                                <a href="profil.php">
                                    <i class="fas fa-user"></i> Profil Saya
                                </a>
                            </li>
                            <li>
                                <a href="riwayat_pesanan.php">
                                    <i class="fas fa-shopping-bag"></i> Riwayat Pesanan
                                </a>
                            </li>
                            <li class="active">
                                <a href="alamat_tersimpan.php">
                                    <i class="fas fa-map-marker-alt"></i> Alamat Tersimpan
                                </a>
                            </li>
                            <li>
                                <a href="ubah_password.php">
                                    <i class="fas fa-lock"></i> Ubah Password
                                </a>
                            </li>
                            <li>
                                <a href="login.php" class="logout">
                                    <i class="fas fa-sign-out-alt"></i> Logout
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
                
                <div class="profile-content">
                    <div class="content-header">
                        <h1>Tambah Alamat Baru</h1>
                        <a href="alamat_tersimpan.php" class="btn btn-outline">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                    
                    <?php if(isset($_SESSION['success_message'])): ?>
                    <div class="success-message">
                        <i class="fas fa-check-circle"></i>
                        <?= $_SESSION['success_message']; ?>
                        <?php unset($_SESSION['success_message']); ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if(isset($_SESSION['error_message'])): ?>
                    <div class="error-message">
                        <i class="fas fa-exclamation-circle"></i>
                        <?= $_SESSION['error_message']; ?>
                        <?php unset($_SESSION['error_message']); ?>
                    </div>
                    <?php endif; ?>
                    
                    <div class="address-form-container">
                        <form action="proses_tambah_alamat.php" method="POST" id="addressForm">
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
                            </div>
                            
                            <div class="form-actions">
                                <a href="alamat_tersimpan.php" class="btn btn-outline">
                                    <i class="fas fa-times"></i> Batal
                                </a>
                                <button type="submit" class="btn btn-primary" id="submit-btn">
                                    <i class="fas fa-save"></i> Simpan Alamat
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-logo">
                    <img src="images/logo.png" alt="Toko Tanaman">
                    <p>Toko tanaman hias terpercaya dengan berbagai koleksi tanaman berkualitas untuk mempercantik rumah dan ruangan Anda.</p>
                </div>
                <div class="footer-links">
                    <h3 class="footer-title">Tautan Cepat</h3>
                    <ul>
                        <li><a href="index.php">Beranda</a></li>
                        <li><a href="produk.php">Produk</a></li>
                        <li><a href="kontak.php">Kontak</a></li>
                    </ul>
                </div>
                <div class="footer-links">
                    <h3 class="footer-title">Kategori</h3>
                    <ul>
                        <li><a href="#">Tanaman Hias Daun</a></li>
                        <li><a href="#">Tanaman Hias Bunga</a></li>
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
        // Data kota berdasarkan provinsi
        const kotaData = {
            'DKI Jakarta': ['Jakarta Pusat', 'Jakarta Utara', 'Jakarta Barat', 'Jakarta Selatan', 'Jakarta Timur'],
            'Jawa Barat': ['Bandung', 'Bekasi', 'Bogor', 'Depok', 'Cimahi', 'Sukabumi', 'Tasikmalaya'],
            'Jawa Tengah': ['Semarang', 'Solo', 'Yogyakarta', 'Magelang', 'Purwokerto', 'Tegal'],
            'Jawa Timur': ['Surabaya', 'Malang', 'Kediri', 'Blitar', 'Madiun', 'Jember'],
            'Yogyakarta': ['Yogyakarta', 'Bantul', 'Sleman', 'Kulon Progo', 'Gunung Kidul'],
            'Banten': ['Tangerang', 'Tangerang Selatan', 'Serang', 'Cilegon', 'Lebak', 'Pandeglang']
        };

        // Data kecamatan berdasarkan kota (contoh untuk beberapa kota)
        const kecamatanData = {
            'Bandung': ['Bandung Wetan', 'Bandung Kulon', 'Coblong', 'Cicendo', 'Sukajadi'],
            'Jakarta Pusat': ['Menteng', 'Tanah Abang', 'Gambir', 'Sawah Besar', 'Kemayoran'],
            'Semarang': ['Semarang Tengah', 'Semarang Utara', 'Semarang Selatan', 'Candisari', 'Gajahmungkur'],
            'Surabaya': ['Surabaya Pusat', 'Surabaya Utara', 'Surabaya Selatan', 'Gubeng', 'Wonokromo']
        };

        // Update kota berdasarkan provinsi
        document.getElementById('provinsi').addEventListener('change', function() {
            const provinsi = this.value;
            const kotaSelect = document.getElementById('kota');
            const kecamatanSelect = document.getElementById('kecamatan');
            
            // Reset kota dan kecamatan
            kotaSelect.innerHTML = '<option value="">Pilih Kota/Kabupaten</option>';
            kecamatanSelect.innerHTML = '<option value="">Pilih Kecamatan</option>';
            
            if (provinsi && kotaData[provinsi]) {
                kotaData[provinsi].forEach(kota => {
                    const option = document.createElement('option');
                    option.value = kota;
                    option.textContent = kota;
                    kotaSelect.appendChild(option);
                });
            }
        });

        // Update kecamatan berdasarkan kota
        document.getElementById('kota').addEventListener('change', function() {
            const kota = this.value;
            const kecamatanSelect = document.getElementById('kecamatan');
            
            // Reset kecamatan
            kecamatanSelect.innerHTML = '<option value="">Pilih Kecamatan</option>';
            
            if (kota && kecamatanData[kota]) {
                kecamatanData[kota].forEach(kecamatan => {
                    const option = document.createElement('option');
                    option.value = kecamatan;
                    option.textContent = kecamatan;
                    kecamatanSelect.appendChild(option);
                });
            }
        });

        // Hitung karakter alamat lengkap
        document.getElementById('alamat_lengkap').addEventListener('input', function() {
            const count = this.value.length;
            const countElement = document.getElementById('alamat-count');
            countElement.textContent = count;
            
            const counterElement = countElement.parentElement;
            counterElement.classList.remove('warning', 'danger');
            
            if (count > 150) {
                counterElement.classList.add('warning');
            }
            
            if (count > 200) {
                counterElement.classList.add('danger');
            }
        });

        // Validasi nomor telepon
        document.getElementById('no_telepon').addEventListener('input', function() {
            let value = this.value.replace(/\D/g, ''); // Hapus karakter non-digit
            
            // Format nomor telepon
            if (value.startsWith('0')) {
                value = value;
            } else if (value.startsWith('62')) {
                value = '0' + value.substring(2);
            } else if (value.startsWith('8')) {
                value = '0' + value;
            }
            
            this.value = value;
        });

        // Validasi kode pos
        document.getElementById('kode_pos').addEventListener('input', function() {
            this.value = this.value.replace(/\D/g, ''); // Hanya angka
        });

        // Validasi form
        document.getElementById('addressForm').addEventListener('submit', function(e) {
            const requiredFields = ['label_alamat', 'nama_penerima', 'no_telepon', 'provinsi', 'kota', 'kecamatan', 'kode_pos', 'alamat_lengkap'];
            let isValid = true;

            // Reset semua error states
            document.querySelectorAll('.form-group input, .form-group select, .form-group textarea').forEach(input => {
                input.classList.remove('error', 'success');
            });

            // Validasi field wajib
            requiredFields.forEach(field => {
                const input = document.getElementById(field);
                if (!input.value.trim()) {
                    isValid = false;
                    input.classList.add('error');
                } else {
                    input.classList.add('success');
                }
            });

            // Validasi nomor telepon
            const phoneInput = document.getElementById('no_telepon');
            const phonePattern = /^0[0-9]{9,12}$/;
            if (phoneInput.value && !phonePattern.test(phoneInput.value)) {
                isValid = false;
                phoneInput.classList.remove('success');
                phoneInput.classList.add('error');
            }

            // Validasi kode pos
            const postalInput = document.getElementById('kode_pos');
            if (postalInput.value && postalInput.value.length !== 5) {
                isValid = false;
                postalInput.classList.remove('success');
                postalInput.classList.add('error');
            }

            if (!isValid) {
                e.preventDefault();
                alert('Mohon lengkapi semua field yang wajib diisi dengan benar!');
            } else {
                // Show loading state
                const submitBtn = document.getElementById('submit-btn');
                submitBtn.classList.add('loading');
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
            }
        });
    </script>
</body>
</html>

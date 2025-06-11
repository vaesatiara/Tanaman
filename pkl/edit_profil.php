<?php
session_start();
include "koneksi.php";

if (!isset($_SESSION['username'])){
    header("Location:login.php?login dulu");
    exit;
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
    <title>Edit Profil - Toko Tanaman</title>
    <link rel="stylesheet" href="css/edit_profil.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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
                <a href="keranjang.php"><i class="fas fa-shopping-cart"></i></a>
                <a href="profil.html" class="active"><i class="fas fa-user"></i></a>
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
                            <li class="active">
                                <a href="profil.php">
                                    <i class="fas fa-user"></i> Profil Saya
                                </a>
                            </li>
                            <li>
                                <a href="riwayat_pesanan.php">
                                    <i class="fas fa-shopping-bag"></i> Riwayat Pesanan
                                </a>
                            </li>
                            <li>
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
                        <h1>Edit Profil</h1>
                        <a href="profil.php" class="btn btn-outline">
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
                    
                    <div class="edit-profile-form">
                        <form action="proses_edit_profil.php" method="POST" enctype="multipart/form-data" id="edit-form">
                            <input type="hidden" name="id_pelanggan" value="<?=$pelanggan['id_pelanggan']?>">
                            
                            <!-- Upload Foto Profil -->
                            <div class="profile-photo-section">
                                <div class="current-photo">
                                    <img src="<?= !empty($pelanggan['foto_profil']) ? 'images/profiles/'.$pelanggan['foto_profil'] : 'images/user.jpg' ?>" alt="Foto Profil" id="preview-photo">
                                </div>
                                <div class="photo-upload">
                                    <label for="foto_profil" class="btn btn-secondary">
                                        <i class="fas fa-camera"></i> Ubah Foto
                                    </label>
                                    <input type="file" id="foto_profil" name="foto_profil" accept="image/*" style="display: none;">
                                    <p class="photo-note">Format: JPG, PNG. Maksimal 2MB</p>
                                </div>
                            </div>

                            <div class="form-grid">
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="nama_lengkap" class="required">Nama Lengkap</label>
                                        <input type="text" id="nama_lengkap" name="nama_lengkap" 
                                               value="<?=$pelanggan['nama_lengkap'] ?? 'Caselline Grzyline'?>" required>
                                        <div class="field-help">Nama lengkap sesuai identitas</div>
                                    </div>
                                    <div class="form-group">
                                        <label for="username" class="required">Username</label>
                                        <input type="text" id_pelanggan="" name="username" 
                                               value="<?=$pelanggan['username']?>" required>
                                    </div>
                                </div>
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="email" class="required">Email</label>
                                        <input type="text" name="email"  id_pelanggan=""
                                         value="<?= $pelanggan['email'] ?>" required>

                                    </div>
                                    <div class="form-group">
                                        <label for="no_telepon">Nomor Telepon</label>
                                        <input type="" id="no_telepon" name="no_hp" 
                                               value="<?=$pelanggan['no_hp'] ?>">
                                        <div class="field-help">Format: 08xx-xxxx-xxxx</div>
                                    </div>
                                </div>
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="tanggal_lahir">Tanggal Lahir</label>
                                        <input type="date" id="tanggal_lahir" name="tanggal_lahir" 
                                               value="<?=$pelanggan['tanggal_lahir'] ?? '2002-01-15'?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="jenis_kelamin">Jenis Kelamin</label>
                                        <select id="jenis_kelamin" name="jenis_kelamin">
                                            <option value="">Pilih Jenis Kelamin</option>
                                            <option value="Laki-laki" <?=($pelanggan['jenis_kelamin'] ?? '') == 'Laki-laki' ? 'selected' : ''?>>Laki-laki</option>
                                            <option value="Perempuan" <?=($pelanggan['jenis_kelamin'] ?? 'Perempuan') == 'Perempuan' ? 'selected' : ''?>>Perempuan</option>
                                        </select>
                                    </div>
                                </div>

                            <div class="form-actions">
                                <button type="button" class="btn btn-outline" onclick="window.location.href='profil.php'">
                                    <i class="fas fa-times"></i> Batal
                                </button>
                                <button type="submit" class="btn btn-primary" id="submit-btn">
                                    <i class="fas fa-save"></i> Simpan Perubahan
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
                        <li><a href="index.html">Beranda</a></li>
                        <li><a href="produk.html">Produk</a></li>
                        <li><a href="kontak.html">Kontak</a></li>
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
        // Preview foto profil
        document.getElementById('foto_profil').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                // Validasi ukuran file (2MB)
                if (file.size > 2 * 1024 * 1024) {
                    alert('Ukuran file terlalu besar. Maksimal 2MB.');
                    this.value = '';
                    return;
                }
                
                // Validasi tipe file
                const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
                if (!allowedTypes.includes(file.type)) {
                    alert('Format file tidak didukung. Gunakan JPG atau PNG.');
                    this.value = '';
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('preview-photo').src = e.target.result;
                }
                reader.readAsDataURL(file);
            }
        });

        // Hitung karakter di textarea
        document.getElementById('alamat').addEventListener('input', function() {
            const count = this.value.length;
            const countElement = document.getElementById('alamat-count');
            countElement.textContent = count;
            
            const counterElement = countElement.parentElement;
            counterElement.classList.remove('warning', 'danger');
            
            if (count > 180) {
                counterElement.classList.add('warning');
            }
            
            if (count > 200) {
                counterElement.classList.add('danger');
            }
        });
        
        // Trigger alamat count on page load
        document.addEventListener('DOMContentLoaded', function() {
            const alamatElement = document.getElementById('alamat');
            if (alamatElement) {
                const event = new Event('input');
                alamatElement.dispatchEvent(event);
            }
        });

        // Validasi form
        document.getElementById('edit-form').addEventListener('submit', function(e) {
            const requiredFields = ['nama_lengkap', 'username', 'email'];
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

            // Validasi email
            const emailInput = document.getElementById('email');
            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (emailInput.value && !emailPattern.test(emailInput.value)) {
                isValid = false;
                emailInput.classList.remove('success');
                emailInput.classList.add('error');
            }

            // Validasi nomor telepon (opsional)
            const phoneInput = document.getElementById('no_telepon');
            if (phoneInput.value) {
                const phonePattern = /^(\+62|62|0)[0-9]{9,13}$/;
                if (!phonePattern.test(phoneInput.value.replace(/[-\s]/g, ''))) {
                    isValid = false;
                    phoneInput.classList.add('error');
                } else {
                    phoneInput.classList.add('success');
                }
            }

            if (!isValid) {
                e.preventDefault();
                alert('Mohon lengkapi semua field yang wajib diisi dengan benar!');
            } else {
                // Show loading state
                const submitBtn = document.getElementById('submit-btn');
                submitBtn.classList.add('loading');
                submitBtn.innerHTML = '';
            }
        });
    </script>
</body>
</html>

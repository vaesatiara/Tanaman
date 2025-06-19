<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

include "koneksi.php";

// Get current user info
$stmt = $koneksi->prepare("SELECT * FROM pelanggan WHERE username = ?");
$stmt->bind_param("s", $_SESSION['username']); // "s" = string
$stmt->execute();

$result = $stmt->get_result();
$data = $result->fetch_assoc(); // Ambil datanya


// Handle form submission
if ($_POST) {
    $currentPassword = $_POST['currentPassword'] ?? '';
    $newPassword = $_POST['newPassword'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';
    
    // Validation
    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        $error = 'Semua field harus diisi!';
    } elseif (!password_verify($currentPassword, $user['password'])) {
        $error = 'Password saat ini tidak benar!';
    } elseif (strlen($newPassword) < 8) {
        $error = 'Password baru minimal 8 karakter!';
    } elseif ($newPassword !== $confirmPassword) {
        $error = 'Konfirmasi password tidak cocok!';
    } else {
        // Update password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        
        if ($stmt->execute([$hashedPassword, $_SESSION['user_id']])) {
            $message = 'Password berhasil diubah!';
        } else {
            $error = 'Gagal mengubah password. Silakan coba lagi.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ubah Password - Toko Tanaman</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: 4px;
        }
        .alert-success {
            color: #3c763d;
            background-color: #dff0d8;
            border-color: #d6e9c6;
        }
        .alert-danger {
            color: #a94442;
            background-color: #f2dede;
            border-color: #ebccd1;
        }
        .password-strength {
            margin-top: 5px;
            font-size: 12px;
        }
        .strength-weak { color: #dc3545; }
        .strength-medium { color: #ffc107; }
        .strength-strong { color: #28a745; }
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
                    <li><a href="tentang.php">TENTANG KAMI</a></li>
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
                            <img src="images/user.jpg" alt="<?php echo htmlspecialchars($data['username']); ?>">
                        </div>
                        <div class="profile-info">
                            <h2><?php echo htmlspecialchars($data['username']); ?></h2>
                            <p><?php echo htmlspecialchars($data['email']); ?></p>
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
                            <li>
                                <a href="alamat_tersimpan.php">
                                    <i class="fas fa-map-marker-alt"></i> Alamat Tersimpan
                                </a>
                            </li>
                            <li class="active">
                                <a href="ubah_password.php">
                                    <i class="fas fa-lock"></i> Ubah Password
                                </a>
                            </li>
                            <li>
                                <a href="logout.php" class="logout">
                                    <i class="fas fa-sign-out-alt"></i> Logout
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
                
                <div class="profile-content">
                    <div class="content-header">
                        <h1>Ubah Password</h1>
                    </div>
                    
                    <?php if ($message): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> <?php echo $message; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="password-form">
                        <div class="form-section">
                            <form id="changePasswordForm" method="POST" action="">
                                <div class="form-group">
                                    <label for="currentPassword" class="form-label">Password Saat Ini</label>
                                    <div class="password-input-wrapper">
                                        <input type="password" id="currentPassword" name="currentPassword" class="form-control" placeholder="Masukkan password saat ini" required>
                                        <button type="button" class="password-toggle" onclick="togglePassword('currentPassword')">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="newPassword" class="form-label">Password Baru</label>
                                    <div class="password-input-wrapper">
                                        <input type="password" id="newPassword" name="newPassword" class="form-control" placeholder="Masukkan password baru" required onkeyup="checkPasswordStrength()">
                                        <button type="button" class="password-toggle" onclick="togglePassword('newPassword')">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    <div id="passwordStrength" class="password-strength"></div>
                                    <p class="password-hint">Password minimal 8 karakter dengan kombinasi huruf dan angka</p>
                                </div>
                                <div class="form-group">
                                    <label for="confirmPassword" class="form-label">Konfirmasi Password Baru</label>
                                    <div class="password-input-wrapper">
                                        <input type="password" id="confirmPassword" name="confirmPassword" class="form-control" placeholder="Konfirmasi password baru" required onkeyup="checkPasswordMatch()">
                                        <button type="button" class="password-toggle" onclick="togglePassword('confirmPassword')">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    <div id="passwordMatch" class="password-strength"></div>
                                </div>
                                <div class="form-actions">
                                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                                </div>
                            </form>
                        </div>
                        
                        <div class="password-tips">
                            <h3>Tips Keamanan Password</h3>
                            <ul>
                                <li>Gunakan minimal 8 karakter</li>
                                <li>Kombinasikan huruf besar, huruf kecil, angka, dan simbol</li>
                                <li>Hindari menggunakan informasi pribadi seperti tanggal lahir</li>
                                <li>Jangan gunakan password yang sama untuk akun yang berbeda</li>
                                <li>Ganti password secara berkala</li>
                            </ul>
                        </div>
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
                        <li><a href="tentang.php">Tentang Kami</a></li>
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
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const button = field.nextElementSibling;
            const icon = button.querySelector('i');
            
            if (field.type === 'password') {
                field.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                field.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        function checkPasswordStrength() {
            const password = document.getElementById('newPassword').value;
            const strengthDiv = document.getElementById('passwordStrength');
            
            let strength = 0;
            let feedback = '';
            
            if (password.length >= 8) strength++;
            if (password.match(/[a-z]/)) strength++;
            if (password.match(/[A-Z]/)) strength++;
            if (password.match(/[0-9]/)) strength++;
            if (password.match(/[^a-zA-Z0-9]/)) strength++;
            
            switch (strength) {
                case 0:
                case 1:
                case 2:
                    feedback = '<span class="strength-weak">Lemah</span>';
                    break;
                case 3:
                case 4:
                    feedback = '<span class="strength-medium">Sedang</span>';
                    break;
                case 5:
                    feedback = '<span class="strength-strong">Kuat</span>';
                    break;
            }
            
            strengthDiv.innerHTML = 'Kekuatan password: ' + feedback;
        }

        function checkPasswordMatch() {
            const newPassword = document.getElementById('newPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            const matchDiv = document.getElementById('passwordMatch');
            
            if (confirmPassword === '') {
                matchDiv.innerHTML = '';
                return;
            }
            
            if (newPassword === confirmPassword) {
                matchDiv.innerHTML = '<span class="strength-strong">Password cocok</span>';
            } else {
                matchDiv.innerHTML = '<span class="strength-weak">Password tidak cocok</span>';
            }
        }

        // Form validation
        document.getElementById('changePasswordForm').addEventListener('submit', function(e) {
            const newPassword = document.getElementById('newPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            
            if (newPassword !== confirmPassword) {
                e.preventDefault();
                alert('Konfirmasi password tidak cocok!');
                return false;
            }
            
            if (newPassword.length < 8) {
                e.preventDefault();
                alert('Password baru minimal 8 karakter!');
                return false;
            }
        });
    </script>
</body>
</html>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - The Secret Garden</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/login.css">
</head>
<body>  
    <header>
        <div class="container">
            <div class="logo">
                <a href="index.html">
                    <img src="images/logo.png" alt="The Secret Garden Logo">
                </a>
            </div>
            <nav>
                <ul>
                    <li><a href="index.html">BERANDA</a></li>
                    <li><a href="produk.html">PRODUK</a></li>
                    <li><a href="kontak.html">KONTAK</a></li>
                    <li><a href="tentang_kami.html">TENTANG KAMI</a></li>
                </ul>
            </nav>
            <div class="icons">
                <a href="#"><i class="fas fa-shopping-cart"></i></a>
                <a href="login.html" class="active"><i class="fas fa-user"></i></a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main>
         <form action="proses_register.php" method="post">
        <div class="auth-container">
            <div class="auth-left">
                <div class="auth-image-container">
                    <img src="images/daftar.jpg" alt="Register Background" class="auth-image">
                    <div class="auth-overlay"></div>
                </div>
            </div>
            <div class="auth-right">
                <div class="auth-form-container">
                    <h2>Daftar Akun</h2>
                    <p class="auth-subtitle">Sudah punya akun? <a href="login.php">Masuk di sini</a></p>
                    
                   
                        <div class="form-group">
                            <label for="email">Email</label>
                            <div class="input-with-icon">
                                <i class="fas fa-envelope"></i>
                                <input type="email" name="email" id_pelanggan="" placeholder="Masukkan email Anda">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="username">Username</label>
                            <div class="input-with-icon">
                                <i class="fas fa-user"></i>
                                <input type="text" name="username" id_pelanggan="" placeholder="Buat username Anda">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="password">Password</label>
                            <div class="input-with-icon">
                                <i class="fas fa-lock"></i>
                                <input type="password" name="password" id_pelanggan="" placeholder="Buat password Anda">
                                <i class="fas fa-eye toggle-password"></i>
                            </div>
                        </div>
                        
                        <!-- <div class="form-group">
                            <label for="confirm-password">Konfirmasi Password</label>
                            <div class="input-with-icon">
                                <i class="fas fa-lock"></i>
                                <input type="password" id="confirm-password" placeholder="Konfirmasi password Anda">
                                <i class="fas fa-eye toggle-password"></i>
                            </div>
                        </div> -->
                        
                        <div class="terms-agreement">
                            <label class="custom-checkbox">
                                <input type="checkbox" id="terms">
                                <span class="checkmark"></span>
                                <span>Saya setuju dengan <a href="#">Syarat & Ketentuan</a> dan <a href="#">Kebijakan Privasi</a></span>
                            </label>
                        </div>
                        
                        <button type="submit" class="btn-submit">Daftar</button>
                    
                </div>
            </div>
        </div>
</form>
    </main>


    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-logo">
                    <img src="images/logo.png" alt="The Secret Garden Logo">
                    <p>Temukan berbagai jenis tanaman hias berkualitas untuk mempercantik ruangan Anda.</p>
                    <div class="social-icons">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
                <div class="footer-links">
                    <h3>Tautan Cepat</h3>
                    <ul>
                        <li><a href="index.php">Beranda</a></li>
                        <li><a href="produk.php">Produk</a></li>
                        <li><a href="kontak.php">Kontak</a></li>
                        <li><a href="tentang-kami.php">Tentang Kami</a></li>
                    </ul>
                </div>
                <div class="footer-links">
                    <h3>Kategori</h3>
                    <ul>
                        <li><a href="tanaman_hias_daun.php">Tanaman Hias Daun</a></li>
                        <li><a href="tanaman_hias_bunga.php">Tanaman Hias Bunga</a></li>
                    </ul>
                </div>
                <div class="footer-contact">
                    <h3>Kontak Kami</h3>
                    <p><i class="fas fa-map-marker-alt"></i> Jl. Tanaman Indah No. 123, Jakarta Selatan</p>
                    <p><i class="fas fa-phone"></i> +62 812 3456 7890</p>
                    <p><i class="fas fa-envelope"></i> info@thesecretgarden.com</p>
                    <p><i class="fas fa-clock"></i> Senin - Minggu: 08.00 - 20.00 WIB</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2023 The Secret Garden. All Rights Reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        document.querySelectorAll('.toggle-password').forEach(function(toggle) {
            toggle.addEventListener('click', function() {
                const passwordInput = this.previousElementSibling;
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                this.classList.toggle('fa-eye');
                this.classList.toggle('fa-eye-slash');
            });
        });
    </script>
</body>
</html>
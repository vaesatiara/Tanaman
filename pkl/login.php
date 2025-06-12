<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - The Secret Garden</title>
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
                    <li><a href="index.php">BERANDA</a></li>
                    <li><a href="produk.php">PRODUK</a></li>
                    <li><a href="kontak.php">KONTAK</a></li>
                    <li><a href="tentang_kami.php">TENTANG KAMI</a></li>
                </ul>
            </nav>
            <div class="icons">
                <a href="cart.html"><i class="fas fa-shopping-cart"></i></a>
                <a href="login.html" class="active"><i class="fas fa-user"></i></a>
            </div>
        </div>
    </header>

      
    <main>
         <form action="proses_login.php" method="post">
        <div class="auth-container">
            <div class="auth-left">
                <div class="auth-image-container">
                    <img src="images/login.jpg" alt="Login Background" class="auth-image">
                    <div class="auth-overlay"></div>
                </div>
            </div>
            <div class="auth-right">
                <div class="auth-form-container">
                    <h2>Masuk</h2>
                    <p class="auth-subtitle">Belum punya akun? <a href="register.php">Daftar sekarang</a></p>
                   
                 
                        <div class="form-group">
                            <label for="">Username</label>
                            <div class="input-with-icon">
                                <i class="fas fa-user"></i>
                                <input type="text" name="username" id_pelanggan="" placeholder="Masukkan username Anda">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="password">Password</label>
                            <div class="input-with-icon">
                                <i class="fas fa-lock"></i>
                                <input type="password" name="password" id_pelanggan="" placeholder="Masukkan password Anda">
                                <i class="fas fa-eye toggle-password"></i>
                            </div>
                        </div>
                        
                        <!-- <div class="form-options">
                            <label class="custom-checkbox">
                                <input type="checkbox" id="remember">
                                <span class="checkmark"></span>
                                Ingat saya
                            </label>
                            <a href="#" class="forgot-password">Lupa password?</a>
                        </div> -->
                        
                        <button type="submit" class="btn-submit">Masuk</button>
                    
                </div>
            </div>
        </div>
         </form>
    </main>
   


    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-logo">
                    <img src="images/logo.png" alt="The Secret Garden Logo">
                    <p>Toko tanaman hias terpercaya dengan berbagai koleksi tanaman berkualitas untuk mempercantik rumah dan ruangan Anda.</p>
                    <div class="social-icons">
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
                <div class="footer-links">
                    <h3>Tautan Cepat</h3>
                    <ul>
                        <li><a href="index.html">BERANDA</a></li>
                        <li><a href="produk.html">PRODUK</a></li>
                        <li><a href="kontak.html">KONTAK</a></li>
                    </ul>
                </div>
                <div class="footer-links">
                    <h3>Kategori</h3>
                    <ul>
                        <li><a href="tanaman-hias-bunga.html">Tanaman Hias Bunga</a></li>
                        <li><a href="tanaman-hias-daun.html">Tanaman Hias Daun</a></li>
                    </ul>
                </div>
                <div class="footer-contact">
                    <h3>Kontak Kami</h3>
                    <p><i class="fas fa-map-marker-alt"></i> Jl. Tanaman Indah No. 123, Purwokerto</p>
                    <p><i class="fas fa-phone"></i> 0812-3456-7890</p>
                    <p><i class="fas fa-envelope"></i> thesecretgarden@gmail.com</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2023 The Secret Garden. Hak Cipta Dilindungi.</p>
            </div>
        </div>
    </footer>

    <script>
        // Toggle password visibility
        document.querySelector('.toggle-password').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });
    </script>
</body>
</html>
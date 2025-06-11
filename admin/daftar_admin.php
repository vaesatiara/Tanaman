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
    

    <!-- Main Content -->
    <main>
         <form action="proses_register.php" method="post">
        <div class="auth-container">
            <div class="auth-left">
                <div class="auth-image-container">
                    <img src="images/daisy.jpg" alt="Register Background" class="auth-image">
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
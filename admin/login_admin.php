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

      
    <main>
         <form action="proses_login.php" method="post">
        <div class="auth-container">
            <div class="auth-left">
                <div class="auth-image-container">
                    <img src="images/login_admin.jpg" alt="Login Background" class="auth-image">
                    <div class="auth-overlay"></div>
                </div>
            </div>
            <div class="auth-right">
                <div class="auth-form-container">
                    <h2>Masuk</h2>
                    <p class="auth-subtitle">Belum punya akun? <a href="daftar_admin.php">Daftar sekarang</a></p>
                   
                 
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
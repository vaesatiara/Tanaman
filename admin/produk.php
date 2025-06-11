<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="produk.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title>Manajemen Produk</title>
</head>
<body>

<div class="sidebar">
    <div class="logo"><img src="uploads/logo.png" alt="The Secret Garden"></div>
    <div class="menu-label">MENU</div>
    <ul class="menu-items">
        
        <li>
        <a class="menu-item active" onclick="location.href='dashboard.php'">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
                </a>
        </li>
        <li>
        <a class="menu-item" onclick="location.href='manajemen_pesanan.php'">
                <i class="fas fa-shopping-cart"></i>
                <span>Management Pesanan</span>
                </a>
        </li>
        <li>
        <a class="menu-item" onclick="location.href='manajemen_produk.php'">
                <i class="fas fa-box"></i>
                <span>Management Produk</span>
                </a>
        </li>
        <li>
        <a class="menu-item" onclick="location.href='manajemen_pengiriman.php'">
                <i class="fas fa-truck"></i>
               <span>Management Pengiriman</span> </a>  
        </li>
        <li>
            <a class="menu-item" onclick="location.href='manajemen_akun.php'">
                <i class="fas fa-user"></i>
                <span>Management Akun</span> </a>
        </li>
        <li>
        <a class="menu-item" onclick="location.href='manajemen_admin.php'">
                <i class="fas fa-user-shield"></i>
                <span>Management Admin</span></a>
        </li>
        <li>
            <a class="menu-item " onclick="location.href='manajemen_pembayaran.php'">
            <i class="fas fa-percent"></i>
            <span>Management Pembayaran</span></a>
        </li>
        <li>
            <a class="menu-item " onclick="location.href='manajemen_diskon.php'">
            <i class="fas fa-percent"></i>
            <span>Management diskon</span></a>
        </li>
     </ul>
 </div>
   

        <div class="main-content">
            <div class="header">
                <div class="search-bar">
                    <input type="text" placeholder="Search">
                    <button>🔍</button>
                </div>
                <div class="user-profile">
                    <span>Admin</span>
                    <div class="avatar">
                        <img src="https://hebbkx1anhila5yf.public.blob.vercel-storage.com/TOKO_TANAMAN-XhfiA1RdZBsZHyBoY7moCEKLRHlmdQ.png" alt="Admin Avatar">
                    </div>
                </div>
            </div>
    
            <h1 class="page-title">Management Produk</h1>
            <h2 class="form-title">Tambah Produk</h2>

<form action="tambah_produk.php" method="post" enctype="multipart/form-data">
            <div class="form-container">
                <div class="form-section">
                   
                    <div class="form-group">
                        <label for="upload-image">Upload Image</label>
                        <input type="file" name="foto" id_produk="" class="form-control" >
                    </div>
    
                    <div class="form-group">
                        <label for="nama-tanaman">Nama Tanaman</label>
                        <input type="text" name="nama_tanaman" id_produk="" class="form-control">
                    </div>
    
                    <div class="form-group">
                        <label for="kategori">Kategori</label>
                        <select id_produk="" name="kategori" class="form-control">
                            <option value="" selected disabled>Kategori</option>
                            <option value="Tanaman Hias Daun">Tanaman Hias Daun</option>
                            <option value="Tanaman Hias Bunga">Tanaman Hias Bunga</option>
                        </select>
                    </div>
    
                    <div class="form-group">
                        <label for="harga">Harga</label>
                        <input type="number" name="harga" id_produk="" class="form-control">
                    </div>
    
                    <div class="form-group">
                        <label for="stok">Stok</label>
                        <input type="number" name="stok" id_produk="" class="form-control">
                    </div>
                </div>
    
                <div class="form-section">
                    <div class="form-group">
                        <label for="deskripsi">Deskripsi</label>
                        <textarea id_produk="" name="deskripsi" class="form-control textarea"></textarea>
                    </div>
              <input type="submit" value="Simpan">
                </div> 
            </div>
            </form>
        </div>
</body>
</html>
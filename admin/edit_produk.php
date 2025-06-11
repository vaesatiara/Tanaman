<?php

include "koneksi.php";
$id_produk=$_GET['id_produk'];
$sql="SELECT * FROM produk WHERE id_produk= '$id_produk' ";
$query=mysqli_query($koneksi,$sql);

while($produk=mysqli_fetch_assoc($query)) : 
?>
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
    <div class="logo">The Secret Garden</div>
    <div class="menu-label">MENU</div>
    <ul class="menu-items">
        <li>
            <a href="dashboard.html" class="menu-item">
                <i class="icon">📊</i>
                Dashboard
            </a>
        </li>
        <li>
            <a href="manajemen_pesanan.html" class="menu-item">
                <i class="icon">🛒</i>
                Management Pesanan
            </a>
        </li>
        <li>
            <a href="manajemen_produk.html" class="menu-item active">
                <i class="icon">📦</i>
                Management Produk
            </a>
        </li>
        <li>
            <a href="manajemen_pengiriman.html" class="menu-item">
                <i class="icon">🚚</i>
                Management Pengiriman
            </a>
        </li>
        <li>
            <a href="manajemen_akun.html" class="menu-item">
                <i class="icon">👤</i>
                Management Akun
            </a>
        </li>
        <li>
            <a href="manajemen_admin.html" class="menu-item">
                <i class="icon">👑</i>
                Management Admin
            </a>
        </li>
        <li>
            <a href="manajemen_diskon.html" class="menu-item">
                <i class="icon">🏷️</i>
                Management Diskon
            </a>
        </li>
        <li>
            <a href="manajemen_pembayaran.php" class="menu-item">
                <i class="icon">💰</i>
                Management Pembayaran
            </a>
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
            <h2 class="form-title">Edit Produk</h2>


<form action="prosesedit_produk.php" method="get">
    <input type="hidden" name="id_produk" value=" <?=$produk['id_produk']?>"><br>
            <div class="form-container">
                <div class="form-section">
                   
                    <div class="form-group">
                        <label for="upload-image">Upload Image</label>
                        <button class="upload-btn">Upload Gambar</button>
                    </div>
    
                    <div class="form-group">
                        <label for="nama-tanaman">Nama Tanaman</label>
                        <input type="text" name="nama_tanaman" id_produk="" class="form-control" value= " <?=$produk['nama_tanaman']?>">
                    </div>
    
                    <div class="form-group">
                        <label for="kategori">Kategori</label>
                        <select id_produk="" name="kategori" class="form-control">
                            <option value="" disabled <?=$produk['nama_tanaman']?> selected>Kategori</option>
                            <option value="Tanaman Hias Daun">Tanaman Hias Daun</option>
                            <option value="Tanaman Hias Bunga">Tanaman Hias Bunga</option>
                           </select>
                    </div>
    
                    <div class="form-group">
                        <label for="harga">Harga</label>
                        <input type="number" name="harga" id_produk="" class="form-control" value="<?=$produk['harga']?>"><br>
                    </div>
    
                    <div class="form-group">
                        <label for="stok">Stok</label>
                        <input type="number" name="stok" id_produk="" class="form-control" value="<?=$produk['stok']?>"><br>
                    </div>
                </div>
    
                <div class="form-section">
                    <div class="form-group">
                        <label for="deskripsi">Deskripsi</label>
                        <textarea id_produk="" name="deskripsi" class="form-control textarea" ><?=$produk['deskripsi']?></textarea>
                    </div>
              <input type="submit" value="Simpan">
                </div> 
            </div>
            </form>
            </body>
</html>
<?php endwhile ?>
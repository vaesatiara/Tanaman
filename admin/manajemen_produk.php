<?php
session_start();
include "koneksi.php";
if (!isset($_SESSION['username'])){
    header("Location:login.php?login dulu");
    exit;
}
$sql="SELECT * FROM produk";
$query=mysqli_query($koneksi,$sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Pembayaran</title>
    <link rel="stylesheet" href="css/manajemen_diskon.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
<div class="sidebar">
    <div class="logo"><img src="uploads/logo.png" alt="The Secret Garden"></div>
    <div class="menu-label">MENU</div>
    <ul class="menu-items">
        
        <li>
        <a class="menu-item" onclick="location.href='dashboard.php'">
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
            <a class="menu-item" onclick="location.href='manajemen_akun.php'">
                <i class="fas fa-user"></i>
                <span>Management Akun</span> </a>
        </li>
        
        <li>
            <a class="menu-item " onclick="location.href='manajemen_pembayaran.php'">
            <i class="fas fa-percent"></i>
            <span>Management Pembayaran</span></a>
        </li>
        
        <li>
            <a class="menu-item " onclick="location.href='manajemen_saran.php'">
            <i class="fas fa-heart"></i>
            <span>Management Saran</span></a>
        </li>
     </ul>
 </div>

    <div class="main-content">
        <div class="header">
            <div class="search-bar">
                <input type="text" placeholder="Search">
                <button><i class="fas fa-search"></i></button>
            </div>
            <div class="user-profile">
                <span>Admin</span>
                <img src="https://randomuser.me/api/portraits/women/44.jpg" alt="Admin Profile">
            </div>
        </div>

        <?php
        include "koneksi.php";
        $sql= "SELECT * FROM produk";
        $query= mysqli_query($koneksi,$sql);

?>

        <div class="content">
            
            <h2 class="content-title">Management Produk</h2>
            <div class="add-button-container">
            <button class="btn-tambah" onclick="window.location.href='produk.php'">
        + Tambah Produk
     </button> </div> 
            <div class="data-table">
                
                <table class="table">
                    
                    <thead>
                        <tr>
                            <th>Id Produk</th>
                            <th>Nama Tanaman</th>
                            <th>Gambar</th>
                            <th>Kategori</th>
                            <th>Harga</th>
                            <th>Stok</th>
                            <th>Deskripsi</th>
                            <th>Action</th>
                        </tr>
                        </thead>
                        <tbody>
                            <tr>
                    
        <?php while($produk=mysqli_fetch_assoc($query)) { ?>
             
            <td><?=$produk['id_produk']?></td>
            <td><?=$produk['nama_tanaman']?></td>
            <td><img src="uploads/<?=$produk['foto']?>"style="max-width:150px; height:100px;">
            </td>  
            <td><?=$produk['kategori']?></td>
            <td><?=$produk['harga']?></td>
            <td><?=$produk['stok']?></td>
            <td><?=$produk['deskripsi']?></td>
              
                            <td class="action-buttons">
                            
                            <a href="edit_produk.php?id_produk=<?=$produk['id_produk']?>" class="btn-edit">
                                <i class="fas fa-pencil"></i></a>
                          
                            <a href="hapus_produk.php?id_produk=<?=$produk['id_produk']?>" class="btn-delete">
                                    <i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                    </tbody>
                    <?php } ?>
                </table>
                
            </div>
            
</div>
        </div>
    </div>
</body>
</html>

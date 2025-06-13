<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Pembayaran</title>
    <link rel="stylesheet" href="css/manajemen_saran.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
 <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="logo">
            <img src="uploads/logo.png" alt="The Secret Garden">
        </div>
        <div class="menu-label">MENU</div>
        <ul class="menu-items">
            <li>
                <a class="menu-item" onclick="location.href='dashboard.php'">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li>
                <a class="menu-item active" onclick="location.href='manajemen_pesanan.php'">
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
                    <span>Management Akun</span>
                </a>
            </li>
            <li>
                <a class="menu-item" onclick="location.href='manajemen_pembayaran.php'">
                    <i class="fas fa-percent"></i>
                    <span>Management Pembayaran</span>
                </a>
            </li>
            <li>
                <a class="menu-item" onclick="location.href='manajemen_saran.php'">
                    <i class="fas fa-heart"></i>
                    <span>Management Saran</span>
                </a>
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
                <img src="images/4396a60b-6455-40ed-8331-89a96395469f.jpeg" alt="Admin Profile">
            </div>
        </div>

        <?php
        include "koneksi.php";
        $sql= "SELECT * FROM saran";
        $query= mysqli_query($koneksi,$sql);

?>

        <div class="content">
            <h2 class="content-title">Management Saran</h2>
            
            <div class="data-table">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Id Saran</th>
                            <th>Nama Pengirim</th>
                            <th>No Hp</th>
                            <th>Email</th>
                            <th>Isi</th>
                            <th>Action</th>
                        </tr>
                        </thead>
                        <tbody>
                            <tr>
                    
        <?php while($saran=mysqli_fetch_assoc($query)) { ?>
            <td><?=$saran['id_saran']?></td>
            <td><?=$saran['nama_pengirim']?></td>
            <td><?=$saran['no_hp']?></td>
            <td><?=$saran['email']?></td>
            <td><?=$saran['isi_saran']?></td>
                            <td class="action-buttons">
                                <button class="btn-edit"><i class="fas fa-check"></i></button>
                                <button class="btn-delete"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                  
                </table>
                
            </div><br><br>
            
</div>

        </div>
    </div>
    
</body>
</html>
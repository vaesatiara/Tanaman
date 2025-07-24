<?php
session_start();
include "koneksi.php";
// if (!isset($_SESSION['username'])){
//     header("Location:login.php?login dulu");
//     exit;
//}
$sql="SELECT * FROM produk";
$query=mysqli_query($koneksi,$sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Produk</title>
    <link rel="stylesheet" href="css/manajemen_produk.css">
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
        <a class="menu-item active" onclick="location.href='manajemen_produk.php'">
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
            <a class="menu-item" onclick="location.href='manajemen_pembayaran.php'">
            <i class="fas fa-percent"></i>
            <span>Management Pembayaran</span></a>
        </li>
        
        <li>
            <a class="menu-item" onclick="location.href='manajemen_saran.php'">
            <i class="fas fa-heart"></i>
            <span>Management Saran</span></a>
        </li>
     </ul>
 </div>

    <div class="main-content">
        <div class="header">
            <div class="search-bar">
                <input type="text" id="searchInput" placeholder="Cari produk...">
                <button><i class="fas fa-search"></i></button>
            </div>
            <div class="user-profile">
                <span>Admin</span>
                <img src="images/4396a60b-6455-40ed-8331-89a96395469f.jpeg" alt="Admin Profile">
            </div>
        </div>

        <div class="content">
            <h2 class="content-title">Management Produk</h2>
            
            <div class="add-button-container">
                <button class="btn-tambah" onclick="window.location.href='produk.php'">
                    <i class="fas fa-plus"></i> Tambah Produk
                </button>
            </div>
            
            <div class="data-table">
                <div class="table-responsive">
                    <table class="table" id="productTable">
                        <thead>
                            <tr>
                                <th>ID</th>
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
                        <?php while($produk=mysqli_fetch_assoc($query)) { ?>
                            <tr>
                                <td><?=$produk['id_produk']?></td>
                                <td><?=$produk['nama_tanaman']?></td>
                                <td>
                                    <img src="uploads/<?=$produk['foto']?>" alt="<?=$produk['nama_tanaman']?>" class="product-image">
                                </td>
                                <td><?=$produk['kategori']?></td>
                                <td><?=number_format($produk['harga'], 0, ',', '.')?></td>
                                <td><?=$produk['stok']?></td>
                                <td class="description-cell">
                                    <div class="truncate-text"><?=$produk['deskripsi']?></div>
                                    <?php if(strlen($produk['deskripsi']) > 100): ?>
                                        <button class="btn-expand" onclick="toggleText(this)">Lihat Selengkapnya</button>
                                    <?php endif; ?>
                                </td>
                              
                                <td class="action-buttons">
                                    <a href="edit_produk.php?id_produk=<?=$produk['id_produk']?>" class="btn-edit" title="Edit">
                                        <i class="fas fa-pencil"></i>
                                    </a>
                                    <a href="javascript:void(0);" onclick="confirmDelete(<?=$produk['id_produk']?>)" class="btn-delete" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Image Preview Modal -->
    <div id="imageModal" class="modal">
        <div class="modal-content">
            <span class="modal-close">&times;</span>
            <img id="modalImage" class="modal-image" src="/placeholder.svg" alt="">
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content" style="max-width: 400px;">
            <h3>Konfirmasi Hapus</h3>
            <p>Apakah Anda yakin ingin menghapus produk ini?</p>
            <div class="modal-actions">
                <button id="cancelDelete" class="btn-cancel">Batal</button>
                <button id="confirmDelete" class="btn-confirm">Hapus</button>
            </div>
        </div>
    </div>

    <script>
        // Image preview functionality
        const productImages = document.querySelectorAll('.product-image');
        const imageModal = document.getElementById('imageModal');
        const modalImage = document.getElementById('modalImage');
        const modalClose = document.querySelector('.modal-close');

        productImages.forEach(img => {
            img.addEventListener('click', function() {
                imageModal.style.display = 'block';
                modalImage.src = this.src;
                modalImage.alt = this.alt;
            });
        });

        modalClose.addEventListener('click', function() {
            imageModal.style.display = 'none';
        });

        window.addEventListener('click', function(event) {
            if (event.target == imageModal) {
                imageModal.style.display = 'none';
            }
        });

        // Toggle text expansion for long descriptions
        function toggleText(button) {
            const textDiv = button.previousElementSibling;
            if (textDiv.classList.contains('truncate-text')) {
                textDiv.classList.remove('truncate-text');
                button.textContent = 'Sembunyikan';
            } else {
                textDiv.classList.add('truncate-text');
                button.textContent = 'Lihat Selengkapnya';
            }
        }

        // Delete confirmation
        const deleteModal = document.getElementById('deleteModal');
        const cancelDelete = document.getElementById('cancelDelete');
        const confirmDeleteBtn = document.getElementById('confirmDelete');
        let productIdToDelete = null;

        function confirmDelete(productId) {
            deleteModal.style.display = 'block';
            productIdToDelete = productId;
        }

        cancelDelete.addEventListener('click', function() {
            deleteModal.style.display = 'none';
        });

        confirmDeleteBtn.addEventListener('click', function() {
            if (productIdToDelete) {
                window.location.href = 'hapus_produk.php?id_produk=' + productIdToDelete;
            }
        });

        // Search functionality
        document.getElementById('searchInput').addEventListener('keyup', function() {
            const searchValue = this.value.toLowerCase();
            const table = document.getElementById('productTable');
            const rows = table.getElementsByTagName('tr');

            for (let i = 1; i < rows.length; i++) {
                let found = false;
                const cells = rows[i].getElementsByTagName('td');
                
                for (let j = 0; j < cells.length; j++) {
                    const cellText = cells[j].textContent || cells[j].innerText;
                    
                    if (cellText.toLowerCase().indexOf(searchValue) > -1) {
                        found = true;
                        break;
                    }
                }
                
                if (found) {
                    rows[i].style.display = '';
                } else {
                    rows[i].style.display = 'none';
                }
            }
        });
    </script>
</body>
</html>

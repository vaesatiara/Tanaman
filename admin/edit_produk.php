<?php
include "koneksi.php";
$id_produk = $_GET['id_produk'];
$sql = "SELECT * FROM produk WHERE id_produk = '$id_produk'";
$query = mysqli_query($koneksi, $sql);

while ($produk = mysqli_fetch_assoc($query)) :
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/edit_produk.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title>Edit Produk</title>
</head>
<body>
<div class="sidebar">
    <div class="logo">
            <img src="uploads/logo.png" alt="The Secret Garden">
        </div>
    <div class="menu-label">MENU</div>
    <ul class="menu-items">
        <li>
            <a href="dashboard.php" class="menu-item">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
        </li>
        <li>
            <a href="manajemen_pesanan.php" class="menu-item">
                <i class="fas fa-shopping-cart"></i>
                <span>Management Pesanan</span>
            </a>
        </li>
        <li>
            <a href="manajemen_produk.php" class="menu-item active">
                <i class="fas fa-box"></i>
                <span>Management Produk</span>
            </a>
        </li>
        <li>
            <a href="manajemen_akun.php" class="menu-item">
                <i class="fas fa-user"></i>
                <span>Management Akun</span>
            </a>
        </li>
        <li>
            <a href="manajemen_pembayaran.php" class="menu-item">
                <i class="fas fa-percent"></i>
                <span>Management Pembayaran</span>
            </a>
        </li>
        <li>
            <a href="manajemen_saran.php" class="menu-item">
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
            <div class="avatar">
                <img src="images/4396a60b-6455-40ed-8331-89a96395469f.jpeg" alt="Admin Avatar">
            </div>
        </div>
    </div>

    <h1 class="page-title">Management Produk</h1>
    <h2 class="form-title">Edit Produk</h2>

    <form action="prosesedit_produk.php" method="post" enctype="multipart/form-data">
        <input type="hidden" name="id_produk" value="<?= $produk['id_produk'] ?>">
        <div class="form-container">
            <div class="form-section">
                <div class="form-group">
                    <label for="current-image">Gambar Saat Ini</label>
                    <div class="current-image">
                        <span class="current-image-label">Gambar produk saat ini:</span>
                        <img src="uploads/<?= $produk['foto'] ?>" alt="<?= $produk['nama_tanaman'] ?>">
                    </div>
                    <input type="hidden" name="foto_lama" value="<?= $produk['foto'] ?>">
                    <div class="file-upload">
                        <label for="foto" class="upload-btn">
                            <i class="fas fa-cloud-upload-alt"></i> Upload Gambar Baru
                        </label>
                        <input type="file" id="foto" name="foto" style="display: none;">
                    </div>
                </div>

                <div class="form-group">
                    <label for="nama_tanaman">Nama Tanaman</label>
                    <input type="text" name="nama_tanaman" id="nama_tanaman" class="form-control" value="<?= $produk['nama_tanaman'] ?>">
                </div>

                <div class="form-group">
                    <label for="kategori">Kategori</label>
                    <select id="kategori" name="kategori" class="form-control">
                        <option value="" disabled>Pilih Kategori</option>
                        <option value="Tanaman Hias Daun" <?php if ($produk['kategori'] == 'Tanaman Hias Daun') echo 'selected'; ?>>Tanaman Hias Daun</option>
                        <option value="Tanaman Hias Bunga" <?php if ($produk['kategori'] == 'Tanaman Hias Bunga') echo 'selected'; ?>>Tanaman Hias Bunga</option>
                        <option value="Tanaman Buah" <?php if ($produk['kategori'] == 'Tanaman Buah') echo 'selected'; ?>>Tanaman Buah</option>
                        <option value="Tanaman Obat" <?php if ($produk['kategori'] == 'Tanaman Obat') echo 'selected'; ?>>Tanaman Obat</option>
                        <option value="Tanaman Sayur" <?php if ($produk['kategori'] == 'Tanaman Sayur') echo 'selected'; ?>>Tanaman Sayur</option>
                    </select>
                </div>
            </div>

            <div class="form-section">
                <div class="form-group">
                    <label for="harga">Harga</label>
                    <input type="number" name="harga" id="harga" class="form-control" value="<?= $produk['harga'] ?>">
                </div>

                <div class="form-group">
                    <label for="stok">Stok</label>
                    <input type="number" name="stok" id="stok" class="form-control" value="<?= $produk['stok'] ?>">
                </div>

                <div class="form-group">
                    <label for="deskripsi">Deskripsi</label>
                    <textarea id="deskripsi" name="deskripsi" class="form-control textarea"><?= $produk['deskripsi'] ?></textarea>
                </div>
            </div>
        </div>

        <!-- Tambahan Detail Produk -->
        <div class="form-container">
            <div class="form-section">
                <h3 class="section-title">Keunggulan Utama</h3>
                <div class="form-group">
                    <label for="keunggulan">Keunggulan Produk</label>
                    <textarea id="keunggulan" name="keunggulan" class="form-control textarea" placeholder="Contoh: Tanaman sehat dan berkualitas premium, Mudah perawatan untuk pemula, dll"><?= isset($produk['keunggulan']) ? $produk['keunggulan'] : '' ?></textarea>
                    <small class="form-text">Pisahkan setiap keunggulan dengan baris baru</small>
                </div>
            </div>

            <div class="form-section">
                <h3 class="section-title">Panduan Perawatan</h3>
                <div class="form-group">
                    <label for="perawatan_harian">Perawatan Harian</label>
                    <textarea id="perawatan_harian" name="perawatan_harian" class="form-control textarea" placeholder="Contoh: Siram secukupnya sesuai kebutuhan tanaman, Letakkan di tempat dengan pencahayaan yang cukup, dll"><?= isset($produk['perawatan_harian']) ? $produk['perawatan_harian'] : '' ?></textarea>
                    <small class="form-text">Pisahkan setiap instruksi dengan baris baru</small>
                </div>

                <div class="form-group">
                    <label for="perawatan_berkala">Perawatan Berkala</label>
                    <textarea id="perawatan_berkala" name="perawatan_berkala" class="form-control textarea" placeholder="Contoh: Berikan pupuk sesuai jadwal yang direkomendasikan, Pangkas daun yang layu atau rusak, dll"><?= isset($produk['perawatan_berkala']) ? $produk['perawatan_berkala'] : '' ?></textarea>
                    <small class="form-text">Pisahkan setiap instruksi dengan baris baru</small>
                </div>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-submit">Simpan Perubahan</button>
            <a href="manajemen_produk.php" class="btn-cancel">Batal</a>
        </div>
    </form>
</div>

<script>
    // Image preview functionality
    document.getElementById('foto').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                // Create or update image preview
                let preview = document.querySelector('.image-preview');
                if (!preview) {
                    preview = document.createElement('img');
                    preview.className = 'image-preview';
                    document.querySelector('.file-upload').after(preview);
                }
                preview.src = e.target.result;
            }
            
            reader.readAsDataURL(file);
        }
    });
</script>
</body>
</html>
<?php endwhile ?>

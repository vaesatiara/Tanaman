<?php
include "koneksi.php";

// Basic validation
if (!isset($_POST['nama_tanaman']) || !isset($_FILES['foto'])) {
    die("Error: Required fields are missing");
}

// Get form data
$nama_tanaman = $_POST['nama_tanaman'];
$foto = $_FILES['foto']['name'];
$tmp = $_FILES['foto']['tmp_name'];
$kategori = $_POST['kategori'];
$harga = $_POST['harga'];
$stok = $_POST['stok'];
$deskripsi = $_POST['deskripsi'];

// Get additional data
$keunggulan = isset($_POST['keunggulan']) ? $_POST['keunggulan'] : '';
$perawatan_harian = isset($_POST['perawatan_harian']) ? $_POST['perawatan_harian'] : '';
$perawatan_berkala = isset($_POST['perawatan_berkala']) ? $_POST['perawatan_berkala'] : '';

// Generate a unique filename to prevent overwriting
$extension = pathinfo($foto, PATHINFO_EXTENSION);
$unique_filename = uniqid() . '.' . $extension;

// Upload the file
if (move_uploaded_file($tmp, "uploads/" . $unique_filename)) {
    // Insert into database
    $sql = "INSERT INTO produk (nama_tanaman, foto, kategori, harga, stok, deskripsi, keunggulan, perawatan_harian, perawatan_berkala)
            VALUES ('$nama_tanaman', '$unique_filename', '$kategori', '$harga', '$stok', '$deskripsi', '$keunggulan', '$perawatan_harian', '$perawatan_berkala')";
    
    $query = mysqli_query($koneksi, $sql);

    if ($query) {
        header("Location: manajemen_produk.php?simpan=sukses");
        exit;
    } else {
        echo "Gagal simpan database: " . mysqli_error($koneksi);
    }
} else {
    echo "Gagal upload file";
}
?>

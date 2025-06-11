<?php
include "koneksi.php";

$nama_tanaman = $_POST['nama_tanaman'];
$foto=  $_FILES['foto']['name'];
$tmp = $_FILES['foto']['tmp_name'];
$kategori = $_POST['kategori'];
$harga = $_POST['harga'];
$stok = $_POST['stok'];
$deskripsi = $_POST['deskripsi'];

move_uploaded_file($tmp, "uploads/".$foto);

    $sql = "INSERT INTO produk (nama_tanaman, foto, kategori, harga, stok, deskripsi)
            VALUES ('$nama_tanaman','$foto', '$kategori', '$harga', '$stok', '$deskripsi')";
    $query = mysqli_query($koneksi, $sql);

    if ($query) {
        header("Location: manajemen_produk.php?simpan=sukses");
        exit;
    } else {
        echo "Gagal simpan database: " . mysqli_error($koneksi);
    }

?>

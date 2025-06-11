<?php

include "koneksi.php";

$id_produk=$_GET['id_produk'];
$nama_tanaman = $_POST['nama_tanaman'];
$foto=  $_FILES['foto']['name'];
$tmp = $_FILES['foto']['tmp_name'];
$kategori = $_POST['kategori'];
$harga = $_POST['harga'];
$stok = $_POST['stok'];
$deskripsi = $_POST['deskripsi'];

move_uploaded_file($tmp, "uploads/".$foto);

$sql="UPDATE produk SET nama_tanaman='$nama_tanaman', foto='$foto', kategori='$kategori', harga='$harga'
, stok='$stok', dekripsi='$deskripsi' 
        WHERE id_produk = '$id_produk'";
$query=mysqli_query($koneksi,$sql);

if($query){
    header ("location:manajemen_produk.php?edit=sukses");
    exit;
}else{
    header ("location:manajemen_produk.php?edit=gagal");
    exit;
}
?>
<?php

include "koneksi.php";

$id_produk=$_GET['id_pembayaran'];
$sql="DELETE FROM produk WHERE id_pembayaran= '$id_pembayaran' ";
$query=mysqli_query($koneksi,$sql);
if($query){
    header ("Location: manajemen_pembayaran.php?hapus=sukses");
    exit;
}else{
    header ("Location: manajemen_pembayaran.php?hapus=gagal");
    exit;
}

?>
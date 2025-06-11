<?php

include "koneksi.php";

$id_produk=$_GET['id_produk'];
$sql="DELETE FROM produk WHERE id_produk= '$id_produk' ";
$query=mysqli_query($koneksi,$sql);
if($query){
    header ("Location: manajemen_produk.php?hapus=sukses");
    exit;
}else{
    header ("Location: manajemen_produk.php?hapus=gagal");
    exit;
}

?>
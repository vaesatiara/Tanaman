<?php

include "koneksi.php";

$id_pengiriman=$_GET['id_pengiriman'];
$sql="DELETE FROM pengiriman WHERE id_pengiriman= '$id_pengiriman' ";
$query=mysqli_query($koneksi,$sql);
if($query){
    header ("Location: alamat_pengiriman.php?hapus=sukses");
    exit;
}else{
    header ("Location: alamat_tersimpan.php?hapus=gagal");
    exit;
}

?>
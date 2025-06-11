<?php

include "koneksi.php";

$id_pelanggan=$_GET['id_pelanggan'];
$sql="DELETE FROM pelanggan WHERE id_pelanggan= '$id_pelanggan' ";
$query=mysqli_query($koneksi,$sql);
if($query){
    header ("Location: manajemen_akun.php?hapus=sukses");
    exit;
}else{
    header ("Location: manajemen_akun.php?hapus=gagal");
    exit;
}

?>
<?php

include "koneksi.php";

$label_alamat=$_POST['label_alamat'];
$nama_penerima = $_POST['nama_penerima'];
$no_telepon = $_POST['no_telepon'];
$provinsi = $_POST['provinsi'];
$kota = $_POST['kota'];
$kecamatan = $_POST['kecamatan'];
$alamat_lengkap = $_POST['alamat_lengkap'];

session_start();
$id_pelanggan = $_SESSION['id_pelanggan'] ?? '';

$sql= "INSERT INTO pengiriman (id_pelanggan,label_alamat,nama_penerima,no_telepon,provinsi,kota,kecamatan,alamat_lengkap)
VALUES ('$id_pelanggan','$label_alamat','$nama_penerima','$no_telepon','$provinsi','$kota','$kecamatan','$alamat_lengkap')";
$query=mysqli_query($koneksi,$sql);

if($query){
    header ("location:alamat_tersimpan.php?simpan=sukses");
    exit;
}else{
    header ("location:tambah_alamat.php?simpan=gagal");
    exit;
}

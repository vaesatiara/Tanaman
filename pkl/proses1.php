<?php
include "koneksi.php";

$label_alamat=$_POST['label_alamat'];
$nama_penerima=$_POST['nama_penerima'];
$no_telepon=$_POST['no_telepon'];
$provinsi=$_POST['provinsi'];
$kota=$_POST['kota'];
$kecamatan=$_POST['kecamatan'];
$alamat_lengkap=$_POST['alamat_lengkap'];

$sql="INSERT INTO pengiriman (label_alamat, nama_penerima, no_telepon, provinsi, kota, kecamatan, alamat_lengkap) 
VALUES ('$label_alamat','$nama_penerima','$no_telepon','$provinsi','$kota','$kecamatan','$alamat_lengkap')";
$query=mysqli_query($koneksi,$sql);

if($query){
    header(
        "Location:alamat.php?simpan=sukses"
    );
}else{
     header(
        "Location:alamat.php?simpan=gagal"
     );
}
?>
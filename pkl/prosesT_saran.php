<?php

include "koneksi.php";

$nama_pengirim = $_POST['nama_pengirim'];
$no_hp = $_POST['no_hp'];
$email = $_POST['email'];
$subjek = $_POST['subjek'];
$isi_saran = $_POST['isi_saran'];

$sql= "INSERT INTO saran (nama_pengirim,no_hp,email,subjek,isi_saran)
VALUES ('$nama_pengirim','$no_hp','$email','$subjek','$isi_saran')";
$query=mysqli_query($koneksi,$sql);

if($query){
    header ("location:kontak.php?simpan=sukses");
    exit;
}else{
    header ("location:kontak.php?simpan=gagal");
    exit;
}
?>
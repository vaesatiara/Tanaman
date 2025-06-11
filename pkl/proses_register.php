<?php
session_start();
include "koneksi.php";

$email=$_POST['email'];
$username=$_POST['username'];
$password=$_POST['password'];

$sql="INSERT INTO pelanggan (email,username,password) VALUES ('$email','$username',md5('$password'))";
$query=mysqli_query($koneksi,$sql);

if($query) {
    header("Location:login.php?register=sukses");
    exit;
}else{
    header("Location:register.php?register=gagal");
    exit;
}
?>
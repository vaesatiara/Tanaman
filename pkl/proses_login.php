<?php
session_start();
include "koneksi.php";

$username=$_POST['username'];
$password=$_POST['password'];

$sql="SELECT * FROM pelanggan WHERE username='$username' AND password=md5('$password')";
$query=mysqli_query($koneksi,$sql);


if(mysqli_num_rows($query) ==1){
    $_SESSION['username']=$username;
    header("Location:index.php?login=sukses");
    exit;
}else{
    header("Location:login.php?login=gagal");
    exit;
}

?>
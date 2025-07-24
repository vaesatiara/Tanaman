<?php
session_start();
include "koneksi.php";

$username=$_POST['username'];
$password=$_POST['password'];

$sql="SELECT * FROM pelanggan WHERE username='$username' AND password=md5('$password')";
$query=mysqli_query($koneksi,$sql);


if (mysqli_num_rows($query) > 0) {
    $row = mysqli_fetch_assoc($query);

    // Set session
    $_SESSION['username'] = $row['username'];
    $_SESSION['id_pelanggan'] = $row['id_pelanggan'];
    header("Location:profil.php?login=sukses");
    exit;
}else{
    header("Location:login.php?login=gagal");
    exit;
}

?>
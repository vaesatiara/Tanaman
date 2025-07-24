<?php
include "koneksi.php";
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

$username = $_SESSION['username'];

$nama_lengkap   = $_POST['nama_lengkap'];
$no_hp      = $_POST['no_hp'];
$tanggal_lahir  = $_POST['tanggal_lahir'];
$jenis_kelamin  = $_POST['jenis_kelamin'];

$sql = "UPDATE pelanggan SET 
            nama_lengkap = '$nama_lengkap',
             no_hp = '$no_hp',
            tanggal_lahir = '$tanggal_lahir',
            jenis_kelamin = '$jenis_kelamin'
        WHERE username = '$username'";

$query = mysqli_query($koneksi, $sql);

if ($query) {
    header("Location: profil.php?edit=sukses");
    exit;
} else {
    header("Location: profil.php?edit=gagal");
    exit;
}
?>

<?php
include "koneksi.php";

$tgl_bayar = $_POST['tgl_bayar'];
$waktu_bayar=$_POST['waktu_bayar'];
$file_image=  $_FILES['file_image']['name'];
$tmp = $_FILES['file_image']['tmp_name'];
$catatan = $_POST['catatan'];

move_uploaded_file($tmp, "images/".$file_image);

    $sql = "INSERT INTO pembayaran (tgl_bayar, waktu_bayar, file_image, catatan )
            VALUES ('$tgl_bayaran', '$waktu_bayar', '$file_images', '$catatan')";
    $query = mysqli_query($koneksi, $sql);

    if ($query) {
        header("Location: riwayat_pesanan.php?simpan=sukses");
        exit;
    } else {
        echo "Gagal simpan database: " . mysqli_error($koneksi);
    }

?>
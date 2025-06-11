<?php
session_start();
include "koneksi.php";

// Cek apakah user sudah login
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

// Cek apakah data dikirim melalui POST
if (isset($_POST['id_pesanan']) && isset($_POST['status'])) {

    // Ambil dan sanitasi data dari POST
    $id_pesanan = intval($_POST['id_pesanan']); // Pastikan angka
    $status = mysqli_real_escape_string($koneksi, $_POST['status']); // Hindari SQL injection

    // Query update
    $sql = "UPDATE pesanan SET status_pesanan = '$status' WHERE id_pesanan = $id_pesanan";
    $query = mysqli_query($koneksi, $sql);

    if ($query) {
        // Redirect kembali ke halaman manajemen_pesanan
        header("Location: manajemen_pesanan.php?pesan=update_sukses");
        exit;
    } else {
        // Jika gagal, tampilkan pesan error
        echo "Gagal mengupdate status pesanan: " . mysqli_error($koneksi);
    }

} else {
    echo "Data tidak lengkap.";
}

mysqli_close($koneksi);
?>

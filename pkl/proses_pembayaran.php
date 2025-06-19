<?php
include "koneksi.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_pesanan = $_POST['id_pesanan'] ?? '';
    $tgl_bayar = $_POST['tgl_bayar'] ?? '';
    $waktu_bayar = $_POST['waktu_bayar'] ?? '';
    $file_image = $_FILES['file_image']['name'] ?? '';
    $tmp = $_FILES['file_image']['tmp_name'] ?? '';
    $catatan = $_POST['catatan'] ?? '';

    if (!$id_pesanan || !$tgl_bayar || !$waktu_bayar || !$file_image) {
        die("❌ Lengkapi semua data yang dibutuhkan.");
    }

    // Cek apakah id_pesanan valid
    $cek = $koneksi->prepare("SELECT 1 FROM pesanan WHERE id_pesanan = ?");
    $cek->bind_param("s", $id_pesanan);
    $cek->execute();
    $cek->store_result();

    if ($cek->num_rows == 0) {
        die("❌ Gagal: ID pesanan tidak ditemukan di database.");
    }

    move_uploaded_file($tmp, "images/" . $file_image);

    $sql = "INSERT INTO pembayaran (id_pesanan, tgl_bayar, waktu_bayar, file_image, catatan)
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $koneksi->prepare($sql);
    $stmt->bind_param("sssss", $id_pesanan, $tgl_bayar, $waktu_bayar, $file_image, $catatan);
    $query = $stmt->execute();

    if ($query) {
        header("Location: riwayat_pesanan.php?simpan=sukses");
        exit;
    } else {
        echo "Gagal simpan database: " . $stmt->error;
    }
}
?>

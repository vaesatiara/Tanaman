<?php
session_start();
include "koneksi.php";

// Periksa apakah user sudah login
if (!isset($_SESSION['id_pelanggan'])) {
    $_SESSION['error_message'] = "Anda harus login terlebih dahulu.";
    header("Location: login.php");
    exit();
}

// Ambil data dari session
$id_pelanggan = $_SESSION['id_pelanggan'] ?? '';

// Validasi id_pelanggan
if (empty($id_pelanggan)) {
    $_SESSION['error_message'] = "Data pelanggan tidak ditemukan. Silakan login ulang.";
    header("Location: login.php");
    exit();
}

// Validasi input POST
$required_fields = ['label_alamat', 'nama_penerima', 'no_telepon', 'provinsi', 'kota', 'kecamatan', 'alamat_lengkap'];
foreach ($required_fields as $field) {
    if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
        $_SESSION['error_message'] = "Field $field harus diisi.";
        header("Location: alamat_pengiriman.php");
        exit();
    }
}

// Sanitasi dan validasi input
$label_alamat = mysqli_real_escape_string($koneksi, trim($_POST['label_alamat']));
$nama_penerima = mysqli_real_escape_string($koneksi, trim($_POST['nama_penerima']));
$no_telepon = mysqli_real_escape_string($koneksi, trim($_POST['no_telepon']));
$provinsi = mysqli_real_escape_string($koneksi, trim($_POST['provinsi']));
$kota = mysqli_real_escape_string($koneksi, trim($_POST['kota']));
$kecamatan = mysqli_real_escape_string($koneksi, trim($_POST['kecamatan']));
$alamat_lengkap = mysqli_real_escape_string($koneksi, trim($_POST['alamat_lengkap']));
$is_primary = isset($_POST['is_primary']) ? 1 : 0;

// Validasi nomor telepon
if (!preg_match('/^[0-9+\-\s()]+$/', $no_telepon)) {
    $_SESSION['error_message'] = "Format nomor telepon tidak valid.";
    header("Location: alamat_pengiriman.php");
    exit();
}

// Jika dijadikan alamat utama, update alamat lain milik user menjadi tidak utama
if ($is_primary) {
    $update_sql = "UPDATE pengiriman SET is_primary = 0 WHERE id_pelanggan = '$id_pelanggan'";
    $update_result = mysqli_query($koneksi, $update_sql);
    
    if (!$update_result) {
        error_log("Error updating primary address: " . mysqli_error($koneksi));
    }
}

// Ubah query INSERT untuk menyertakan id_produk

// Ambil id_produk dari POST jika ada
$id_produk = isset($_POST['product_id']) ? intval($_POST['product_id']) : null;

// Jika tidak ada di POST, coba ambil dari parameter GET
if (!$id_produk && isset($_POST['order_source']) && $_POST['order_source'] === 'buy_now') {
    $id_produk = isset($_POST['product_id']) ? intval($_POST['product_id']) : null;
}

// Jika masih tidak ada, gunakan nilai default (misalnya produk pertama)
if (!$id_produk) {
    // Coba ambil produk pertama dari database sebagai fallback
    $query_produk = mysqli_query($koneksi, "SELECT id_produk FROM produk LIMIT 1");
    if ($query_produk && mysqli_num_rows($query_produk) > 0) {
        $produk = mysqli_fetch_assoc($query_produk);
        $id_produk = $produk['id_produk'];
    } else {
        // Jika tidak ada produk sama sekali
        $_SESSION['error_message'] = "Tidak dapat menambahkan alamat: Produk tidak ditemukan.";
        header("Location: alamat_pengiriman.php");
        exit();
    }
}

// Ubah query INSERT untuk menyertakan id_produk
$sql = "INSERT INTO pengiriman (id_pelanggan, id_produk, label_alamat, nama_penerima, no_telepon, provinsi, kota, kecamatan, alamat_lengkap, is_primary)
        VALUES ('$id_pelanggan', '$id_produk', '$label_alamat', '$nama_penerima', '$no_telepon', '$provinsi', '$kota', '$kecamatan', '$alamat_lengkap', '$is_primary')";

$query = mysqli_query($koneksi, $sql);

if ($query) {
    $_SESSION['success_message'] = "Alamat berhasil ditambahkan!";
    
    // Ambil parameter untuk redirect
    $redirect_params = "";
    if (isset($_POST['order_source'])) {
        $redirect_params .= "?source=" . urlencode($_POST['order_source']);
        
        if ($_POST['order_source'] === 'buy_now' && isset($_POST['product_id'])) {
            $redirect_params .= "&id_produk=" . urlencode($_POST['product_id']);
            $redirect_params .= "&qty=" . urlencode($_POST['quantity'] ?? 1);
        }
    }
    
    header("Location: alamat_pengiriman.php" . $redirect_params);
    exit();
} else {
    // Log error untuk debugging
    error_log("Error inserting address: " . mysqli_error($koneksi));
    error_log("SQL Query: " . $sql);
    
    $_SESSION['error_message'] = "Terjadi kesalahan saat menambahkan alamat: " . mysqli_error($koneksi);
    header("Location: alamat_pengiriman.php");
    exit();
}
?>

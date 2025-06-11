<?php
session_start();
include "koneksi.php";

// Cek login
if (!isset($_SESSION['username'])) {
    header("Location: login.php?login_dulu");
    exit;
}

$id_pelanggan = $_SESSION['id_pelanggan'] ?? '';
$username = $_SESSION['username'];

// Fungsi untuk mendapatkan data pesanan
function getOrderData($source = 'cart', $product_id = null, $quantity = 1) {
    global $koneksi;
    $orderItems = [];
    $totalHarga = 0;
    
    if ($source === 'cart') {
        if (isset($_SESSION['keranjang']) && !empty($_SESSION['keranjang'])) {
            foreach ($_SESSION['keranjang'] as $id_produk => $jumlah) {
                if (empty($id_produk)) continue;
                
                $query = $koneksi->query("SELECT * FROM produk WHERE id_produk='$id_produk'");
                $produk = $query->fetch_assoc();
                
                if ($produk) {
                    $subtotal = $produk['harga'] * $jumlah;
                    $orderItems[] = [
                        'id_produk' => $id_produk,
                        'nama_tanaman' => $produk['nama_tanaman'],
                        'harga' => $produk['harga'],
                        'foto' => $produk['foto'],
                        'jumlah' => $jumlah,
                        'subtotal' => $subtotal
                    ];
                    $totalHarga += $subtotal;
                }
            }
        }
    } else if ($source === 'buy_now' && $product_id) {
        $query = $koneksi->query("SELECT * FROM produk WHERE id_produk='$product_id'");
        $produk = $query->fetch_assoc();
        
        if ($produk) {
            $subtotal = $produk['harga'] * $quantity;
            $orderItems[] = [
                'id_produk' => $product_id,
                'nama_tanaman' => $produk['nama_tanaman'],
                'harga' => $produk['harga'],
                'foto' => $produk['foto'],
                'jumlah' => $quantity,
                'subtotal' => $subtotal
            ];
            $totalHarga = $subtotal;
        }
    }
    
    return [
        'items' => $orderItems,
        'subtotal' => $totalHarga,
        'shipping' => 25000,
        'total' => $totalHarga + 25000
    ];
}

// Proses pemilihan alamat
if (isset($_POST['pilih_alamat'])) {
    $_SESSION['alamat_terpilih'] = $_POST['alamat_id'];
}

// Proses penambahan alamat baru
if (isset($_POST['tambah_alamat'])) {
    $label_alamat = mysqli_real_escape_string($koneksi, $_POST['label_alamat']);
    $nama_penerima = mysqli_real_escape_string($koneksi, $_POST['nama_penerima']);
    $no_telepon = mysqli_real_escape_string($koneksi, $_POST['no_telepon']);
    $provinsi = mysqli_real_escape_string($koneksi, $_POST['provinsi']);
    $kota = mysqli_real_escape_string($koneksi, $_POST['kota']);
    $kecamatan = mysqli_real_escape_string($koneksi, $_POST['kecamatan']);
    $alamat_lengkap = mysqli_real_escape_string($koneksi, $_POST['alamat_lengkap']);
    $is_primary = isset($_POST['is_primary']) ? 1 : 0;
    
    // Jika dijadikan alamat utama, update alamat lain
    if ($is_primary) {
        $update_sql = "UPDATE pengiriman SET is_primary = 0 WHERE id_pelanggan = '$id_pelanggan'";
        mysqli_query($koneksi, $update_sql);
    }
    
    $insert_sql = "INSERT INTO pengiriman (id_pelanggan, label_alamat, nama_penerima, no_telepon, provinsi, kota, kecamatan, alamat_lengkap, is_primary) 
                   VALUES ('$id_pelanggan', '$label_alamat', '$nama_penerima', '$no_telepon', '$provinsi', '$kota', '$kecamatan', '$alamat_lengkap', '$is_primary')";
    
    if (mysqli_query($koneksi, $insert_sql)) {
        $new_id = mysqli_insert_id($koneksi);
        $_SESSION['alamat_terpilih'] = $new_id;
        $_SESSION['success_message'] = "Alamat berhasil ditambahkan dan dipilih!";
    } else {
        $_SESSION['error_message'] = "Terjadi kesalahan saat menambahkan alamat.";
    }
}

// Ambil data alamat tersimpan
$sql_alamat = "SELECT * FROM pengiriman WHERE id_pelanggan = '$id_pelanggan' ORDER BY is_primary DESC, id_pengiriman DESC";
$query_alamat = mysqli_query($koneksi, $sql_alamat);

// Tentukan sumber data pesanan
$source = isset($_GET['source']) ? $_GET['source'] : 'cart';
$product_id = isset($_GET['id_produk']) ? $_GET['id_produk'] : null;
$quantity = isset($_GET['qty']) ? (int)$_GET['qty'] : 1;

if (isset($_GET['direct_buy']) && $_GET['direct_buy'] == 1) {
    $source = 'buy_now';
}

$orderData = getOrderData($source, $product_id, $quantity);
?>


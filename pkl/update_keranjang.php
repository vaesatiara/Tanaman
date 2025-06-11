<?php
session_start();
include "koneksi.php";

// Check if the request is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get product ID and quantity from POST data
    $id_produk = isset($_POST['id_produk']) ? $_POST['id_produk'] : '';
    $jumlah = isset($_POST['jumlah']) ? (int)$_POST['jumlah'] : 0;
    
    // Validate inputs
    if (empty($id_produk) || $jumlah < 1) {
        echo "Invalid input data";
        exit;
    }
    
    // Update session data
    $_SESSION['keranjang'][$id_produk] = $jumlah;
    
    // Check if user is logged in (assuming you have a user ID in session)
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        
        // Check if cart item exists in database
        $check = $koneksi->query("SELECT * FROM keranjang WHERE id_user='$user_id' AND id_produk='$id_produk'");
        
        if ($check->num_rows > 0) {
            // Update existing cart item
            $koneksi->query("UPDATE keranjang SET jumlah='$jumlah' WHERE id_user='$user_id' AND id_produk='$id_produk'");
        } else {
            // Insert new cart item
            $koneksi->query("INSERT INTO keranjang (id_user, id_produk, jumlah) VALUES ('$user_id', '$id_produk', '$jumlah')");
        }
        
        echo "Cart updated in database";
    } else {
        echo "Cart updated in session only";
    }
} else {
    echo "Invalid request method";
}
?>

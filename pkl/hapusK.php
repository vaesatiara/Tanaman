<?php
session_start();
include "koneksi.php";

// Get product ID from URL
$id_produk = isset($_GET['id_produk']) ? $_GET['id_produk'] : '';

// Remove from session
if (isset($_SESSION['keranjang'][$id_produk])) {
    unset($_SESSION['keranjang'][$id_produk]);
    
    // Also remove from database if user is logged in
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        $koneksi->query("DELETE FROM keranjang WHERE id_user='$user_id' AND id_produk='$id_produk'");
    }
}

// Redirect back to cart page
header("Location: keranjang.php");
exit;
?>